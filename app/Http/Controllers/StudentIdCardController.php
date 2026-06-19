<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\School;
use App\Models\SchoolProfile;
use App\Models\Student;
use App\Models\StudentIdCard;
use App\Models\Stream;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class StudentIdCardController extends Controller
{
    // ──────────────────────────────────────────────
    //  INDEX – list / filter cards
    // ──────────────────────────────────────────────
    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_cards');

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $schoolProfile = SchoolProfile::where('school_id', $schoolId)->first();
        $school = School::find($schoolId);

        $classrooms = Classroom::where('school_id', $schoolId)->get();

        // Fetch existing cards with student info
        $cards = StudentIdCard::where('school_id', $schoolId)
            ->with('student')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('student.id-cards.index', compact('cards', 'school', 'schoolProfile', 'classrooms'));
    }

    // ──────────────────────────────────────────────
    //  CREATE FORM – pick class/stream to bulk-generate
    // ──────────────────────────────────────────────
    public function create()
    {
        PermissionHelper::denyUnlessFeature('generate_cards');

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $classrooms = Classroom::where('school_id', $schoolId)->get();
        $streams = Stream::where('school_id', $schoolId)->get();

        return view('student.id-cards.create', compact('classrooms', 'streams'));
    }

    // ──────────────────────────────────────────────
    //  GENERATE – bulk or single
    // ──────────────────────────────────────────────
    public function generate(Request $request)
    {
        if (!PermissionHelper::canFeature('generate_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. You do not have permission to generate ID cards.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $request->validate([
            'senior' => 'required|string',
            'stream' => 'nullable|string',
        ]);

        $query = Student::where('school_id', $schoolId)
            ->where('senior', $request->senior);

        if ($request->stream) {
            $query->where('stream', $request->stream);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No students found for the selected class/stream.'], 422);
        }

        $generated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                // Skip if active card already exists for this academic year
                $existing = StudentIdCard::where('student_id', $student->id)
                    ->where('school_id', $schoolId)
                    ->where('academic_year', $activeYear)
                    ->where('status', 'active')
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                $cardNumber = $this->generateCardNumber($schoolId, $student->id);
                $qrData = $this->buildQrData($student, $cardNumber, $activeYear);

                StudentIdCard::create([
                    'student_id' => $student->id,
                    'school_id' => $schoolId,
                    'academic_year' => $activeYear,
                    'card_number' => $cardNumber,
                    'status' => 'active',
                    'issue_date' => now()->toDateString(),
                    'expiry_date' => Carbon::now()->endOfYear()->toDateString(),
                    'issued_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
                    'qr_code_data' => $qrData,
                ]);

                $generated++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "{$generated} ID card(s) generated. {$skipped} already existed.",
                'generated' => $generated,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  GENERATE SINGLE for one student
    // ──────────────────────────────────────────────
    public function generateSingle(Request $request)
    {
        if (!PermissionHelper::canFeature('generate_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $student = Student::where('id', $request->student_id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Check if card already exists for this student + year
        $existingCard = StudentIdCard::where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->where('academic_year', $activeYear)
            ->first();

        if ($existingCard) {

            // Just reactivate existing card
            $existingCard->update([
                'status' => 'active',
                'issue_date' => now()->toDateString(),
                'expiry_date' => Carbon::now()->endOfYear()->toDateString(),
                'issued_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Existing ID card reactivated successfully.',
                'card_id' => $existingCard->id,
            ]);
        }

        // Otherwise create new card
        $cardNumber = $this->generateCardNumber($schoolId, $student->id);
        $qrData = $this->buildQrData($student, $cardNumber, $activeYear);

        $card = StudentIdCard::create([
            'student_id' => $student->id,
            'school_id' => $schoolId,
            'academic_year' => $activeYear,
            'card_number' => $cardNumber,
            'status' => 'active',
            'issue_date' => now()->toDateString(),
            'expiry_date' => Carbon::now()->endOfYear()->toDateString(),
            'issued_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
            'qr_code_data' => $qrData,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ID card generated successfully.',
            'card_id' => $card->id,
        ]);
    }

    // ──────────────────────────────────────────────
    //  PREVIEW single card (HTML)
    // ──────────────────────────────────────────────
    public function preview($cardId)
    {
        PermissionHelper::denyUnlessFeature('print_cards');

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');

        $card = StudentIdCard::where('id', $cardId)->where('school_id', $schoolId)->firstOrFail();
        $student = $card->student;
        $school = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();

        $qrSvg = $this->generateQrSvg($card->qr_code_data);
        $className = Helper::recordMdname($student->senior);
        $streamName = Helper::recordMdname($student->stream);
        $photoUrl = $this->getStudentPhotoUrl($student);

        return view('student.id-cards.preview', compact(
            'card',
            'student',
            'school',
            'profile',
            'qrSvg',
            'className',
            'streamName',
            'photoUrl'
        ));
    }

    // ──────────────────────────────────────────────
    //  PRINT PDF – single card
    // ──────────────────────────────────────────────
    public function printCard($cardId)
    {
        if (!PermissionHelper::canFeature('print_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');

        $card = StudentIdCard::where('id', $cardId)->where('school_id', $schoolId)->firstOrFail();
        $student = $card->student;
        $school = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();

        $qrSvg = $this->generateQrSvg($card->qr_code_data);
        $className = Helper::recordMdname($student->senior);
        $streamName = Helper::recordMdname($student->stream);
        $photoUrl = $this->getStudentPhotoUrl($student);
        $logoUrl = $this->getLogoUrl($profile);

        $pdf = Pdf::loadView('student.id-cards.pdf-card', compact(
            'card',
            'student',
            'school',
            'profile',
            'qrSvg',
            'className',
            'streamName',
            'photoUrl',
            'logoUrl'
        ))
            ->setPaper([0, 0, 241.89, 153.07]) // CR80 card size: 85.6mm x 54mm in pts
            ->setOption('dpi', 150);

        return $pdf->download("ID_Card_{$student->firstname}_{$student->lastname}.pdf");
    }

    // ──────────────────────────────────────────────
    //  PRINT PDF – bulk (class/stream)
    // ──────────────────────────────────────────────
    public function printBulk(Request $request)
    {
        if (!PermissionHelper::canFeature('print_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $request->validate(['senior' => 'required|string']);

        $query = StudentIdCard::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('academic_year', $activeYear)
            ->with('student');

        // Filter by class/stream via student relationship
        $query->whereHas('student', function ($q) use ($request, $schoolId) {
            $q->where('school_id', $schoolId)->where('senior', $request->senior);
            if ($request->stream) {
                $q->where('stream', $request->stream);
            }
        });

        $cards = $query->get();
        $school = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();
        $logoUrl = $this->getLogoUrl($profile);

        $cardsData = $cards->map(function ($card) {
            $student = $card->student;
            return [
                'card' => $card,
                'student' => $student,
                'qrSvg' => $this->generateQrSvg($card->qr_code_data),
                'className' => Helper::recordMdname($student->senior),
                'streamName' => Helper::recordMdname($student->stream),
                'photoUrl' => $this->getStudentPhotoUrl($student),
            ];
        });

        $pdf = Pdf::loadView('student.id-cards.pdf-bulk', compact('cardsData', 'school', 'profile', 'logoUrl'))
            ->setPaper('a4');

        return $pdf->download("ID_Cards_Bulk_{$activeYear}.pdf");
    }

    // ──────────────────────────────────────────────
    //  REVOKE a card
    // ──────────────────────────────────────────────
    public function revoke($cardId)
    {
        if (!PermissionHelper::canFeature('revoke_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');

        $card = StudentIdCard::where('id', $cardId)->where('school_id', $schoolId)->firstOrFail();
        $card->update(['status' => 'revoked']);

        return response()->json(['status' => 'success', 'message' => 'ID card revoked successfully.']);
    }

    // ──────────────────────────────────────────────
    //  SCAN / VERIFY QR (public-facing or internal)
    // ──────────────────────────────────────────────
    public function verify($cardNumber)
    {
        // Public endpoint - no permission check needed
        $card = StudentIdCard::where('card_number', $cardNumber)->with('student')->first();

        if (!$card) {
            return response()->json(['valid' => false, 'message' => 'Card not found.'], 404);
        }

        $student = $card->student;
        $className = Helper::recordMdname($student->senior);
        $stream = Helper::recordMdname($student->stream);
        $school = School::find($card->school_id);

        return response()->json([
            'valid' => $card->status === 'active',
            'status' => $card->status,
            'card_number' => $card->card_number,
            'student_name' => $student->firstname . ' ' . $student->lastname,
            'class' => $className,
            'stream' => $stream,
            'gender' => $student->gender,
            'school' => $school->name ?? 'N/A',
            'academic_year' => $card->academic_year,
            'issue_date' => $card->issue_date?->format('d M Y'),
            'expiry_date' => $card->expiry_date?->format('d M Y'),
        ]);
    }

    // ──────────────────────────────────────────────
    //  QR SCANNER PAGE
    // ──────────────────────────────────────────────
    public function scannerPage()
    {
        PermissionHelper::denyUnlessFeature('verify_cards');

        return view('student.id-cards.scanner');
    }

    // ──────────────────────────────────────────────
    //  AJAX – get streams by senior/class
    // ──────────────────────────────────────────────
    public function getStreamsBySenior(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_cards');

        $schoolId = session('LoggedSchool');
        $streams = Stream::where('school_id', $schoolId)
            ->where('class_id', $request->class_id)
            ->get()
            ->map(function ($s) {
                $s->display_name = Helper::recordMdname($s->stream_id);
                return $s;
            });

        return response()->json($streams);
    }

    // ──────────────────────────────────────────────
    //  AJAX – card stats for index hero
    // ──────────────────────────────────────────────
    public function stats()
    {
        PermissionHelper::denyUnlessFeature('view_cards');

        $schoolId = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $total = StudentIdCard::where('school_id', $schoolId)->where('academic_year', $activeYear)->count();
        $active = StudentIdCard::where('school_id', $schoolId)->where('academic_year', $activeYear)->where('status', 'active')->count();
        $revoked = StudentIdCard::where('school_id', $schoolId)->where('academic_year', $activeYear)->where('status', 'revoked')->count();

        $totalStudents = Student::where('school_id', $schoolId)->count();

        return response()->json(compact('total', 'active', 'revoked', 'totalStudents'));
    }

    // ──────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ──────────────────────────────────────────────
    private function generateCardNumber($schoolId, $studentId): string
    {
        $prefix = strtoupper(Str::random(3));
        $ts = now()->format('ymdHis');
        return "ID-{$prefix}-{$studentId}-{$ts}";
    }

    private function buildQrData(Student $student, string $cardNumber, string $year): string
    {
        return json_encode([
            'card' => $cardNumber,
            'name' => $student->firstname . ' ' . $student->lastname,
            'adm' => $student->admission_number ?? $student->registration_number,
            'class' => $student->senior,
            'stream' => $student->stream,
            'gender' => $student->gender,
            'year' => $year,
            'school' => $student->school_id,
        ]);
    }

    private function generateQrSvg(string $data): string
    {
        try {
            return QrCode::format('svg')
                ->size(120)
                ->errorCorrection('H')
                ->generate($data);
        } catch (\Exception $e) {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="#eee"/><text x="60" y="65" text-anchor="middle" fill="#666" font-size="10">QR N/A</text></svg>';
        }
    }

    private function getStudentPhotoUrl(Student $student): ?string
    {
        if (!$student->student_photo)
            return null;
        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            $path = 'uploads/studentPhotos/' . $student->student_photo . '.' . $ext;
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }
        return null;
    }

    private function getLogoUrl(?SchoolProfile $profile): ?string
    {
        if (!$profile || !$profile->logo)
            return null;
        $path = 'uploads/school_logos/' . $profile->logo;
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return null;
    }

    // ──────────────────────────────────────────────
    //  GET STUDENTS PREVIEW for generation
    // ──────────────────────────────────────────────
    public function getStudentsPreview(Request $request)
    {
        if (!PermissionHelper::canFeature('generate_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $request->validate([
            'senior' => 'required|string',
            'stream' => 'nullable|string',
        ]);

        $query = Student::where('school_id', $schoolId)
            ->where('senior', $request->senior);

        if ($request->stream) {
            $query->where('stream', $request->stream);
        }

        $students = $query->get();

        $studentsData = $students->map(function ($student) use ($schoolId, $activeYear) {
            $existingCard = StudentIdCard::where('student_id', $student->id)
                ->where('school_id', $schoolId)
                ->where('academic_year', $activeYear)
                ->where('status', 'active')
                ->exists();

            return [
                'id' => $student->id,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'admission_number' => $student->admission_number ?? $student->registration_number,
                'has_active_card' => $existingCard,
            ];
        });

        return response()->json(['students' => $studentsData]);
    }

    // ──────────────────────────────────────────────
    //  SEARCH STUDENTS for single card generation
    // ──────────────────────────────────────────────
    public function searchStudents(Request $request)
    {
        if (!PermissionHelper::canFeature('generate_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $students = Student::where('school_id', $schoolId)
            ->where(function ($q) use ($query) {
                $q->where('firstname', 'like', "%{$query}%")
                    ->orWhere('lastname', 'like', "%{$query}%")
                    ->orWhere('admission_number', 'like', "%{$query}%")
                    ->orWhere('registration_number', 'like', "%{$query}%");
            })
            ->limit(15)
            ->get()
            ->map(function ($student) use ($schoolId, $activeYear) {
                // Get the current card for this student
                $card = StudentIdCard::where('student_id', $student->id)
                    ->where('school_id', $schoolId)
                    ->where('academic_year', $activeYear)
                    ->first();

                $cardStatus = null;
                $cardId = null;
                $buttonType = 'generate'; // generate, reactivate, view
    
                if ($card) {
                    $cardStatus = $card->status;
                    $cardId = $card->id;

                    if ($card->status === 'active') {
                        $buttonType = 'active';
                    } elseif ($card->status === 'revoked') {
                        $buttonType = 'reactivate';
                    } elseif ($card->status === 'expired') {
                        $buttonType = 'expired';
                    }
                }

                return [
                    'id' => $student->id,
                    'firstname' => $student->firstname,
                    'lastname' => $student->lastname,
                    'admission_number' => $student->admission_number ?? $student->registration_number,
                    'senior' => $student->senior,
                    'stream' => $student->stream,
                    'class_name' => Helper::recordMdname($student->senior),
                    'card_status' => $cardStatus,
                    'card_id' => $cardId,
                    'button_type' => $buttonType,
                ];
            });

        return response()->json($students);
    }

    // ──────────────────────────────────────────────
    //  REACTIVATE a card
    // ──────────────────────────────────────────────
    public function reactivate($cardId)
    {
        if (!PermissionHelper::canFeature('reactivate_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        try {
            Helper::requireSchool();
            $schoolId = session('LoggedSchool');

            $card = StudentIdCard::where('id', $cardId)->where('school_id', $schoolId)->first();
            
            if (!$card) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'ID card not found.'
                ], 404);
            }
            
            // Check if card is expired
            if ($card->expiry_date && Carbon::parse($card->expiry_date)->isPast()) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot reactivate an expired card. Please generate a new one instead.',
                    'error_code' => 'CARD_EXPIRED'
                ], 422);
            }
            
            // Check if already active
            if ($card->status === 'active') {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'This card is already active.',
                    'error_code' => 'CARD_ALREADY_ACTIVE'
                ], 422);
            }
            
            $card->update([
                'status' => 'active',
                'issue_date' => now()->toDateString(),
                'issued_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'ID card reactivated successfully.',
                'card_id' => $card->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while reactivating the card.',
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}