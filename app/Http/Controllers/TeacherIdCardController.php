<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\School;
use App\Models\SchoolProfile;
use App\Models\Teacher;
use App\Models\TeacherIdCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TeacherIdCardController extends Controller
{
    // ──────────────────────────────────────────────
    //  INDEX – list / filter cards
    // ──────────────────────────────────────────────
    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_teacher_cards');

        Helper::requireSchool();
        $schoolId      = session('LoggedSchool');
        $school        = School::find($schoolId);
        $schoolProfile = SchoolProfile::where('school_id', $schoolId)->first();

        $cards = TeacherIdCard::where('school_id', $schoolId)
            ->with('teacher')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('teacher.id-cards.index', compact('cards', 'school', 'schoolProfile'));
    }

    // ──────────────────────────────────────────────
    //  CREATE FORM
    // ──────────────────────────────────────────────
    public function create()
    {
        PermissionHelper::denyUnlessFeature('generate_teacher_cards');

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('surname')->get();

        return view('teacher.id-cards.create', compact('teachers'));
    }

    // ──────────────────────────────────────────────
    //  GENERATE BULK – all teachers in school
    // ──────────────────────────────────────────────
    public function generate(Request $request)
    {
        if (!PermissionHelper::canFeature('generate_teacher_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. You do not have permission to generate teacher ID cards.'], 403);
        }

        Helper::requireSchool();
        $schoolId   = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $teachers = Teacher::where('school_id', $schoolId)->get();

        if ($teachers->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No teachers found for this school.'], 422);
        }

        $generated = 0;
        $skipped   = 0;

        DB::beginTransaction();
        try {
            foreach ($teachers as $teacher) {
                $existing = TeacherIdCard::where('teacher_id', $teacher->id)
                    ->where('school_id', $schoolId)
                    ->where('academic_year', $activeYear)
                    ->where('status', 'active')
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                $cardNumber = $this->generateCardNumber($schoolId, $teacher->id);
                $qrData     = $this->buildQrData($teacher, $cardNumber, $activeYear);

                TeacherIdCard::create([
                    'teacher_id'    => $teacher->id,
                    'school_id'     => $schoolId,
                    'academic_year' => $activeYear,
                    'card_number'   => $cardNumber,
                    'status'        => 'active',
                    'issue_date'    => now()->toDateString(),
                    'expiry_date'   => Carbon::now()->endOfYear()->toDateString(),
                    'issued_by'     => session('LoggedTeacher') ?? session('LoggedAdmin'),
                    'qr_code_data'  => $qrData,
                ]);

                $generated++;
            }

            DB::commit();

            return response()->json([
                'status'    => 'success',
                'message'   => "{$generated} Teacher ID card(s) generated. {$skipped} already existed.",
                'generated' => $generated,
                'skipped'   => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  GENERATE SINGLE
    // ──────────────────────────────────────────────
    public function generateSingle(Request $request)
    {
        if (!PermissionHelper::canFeature('generate_teacher_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId   = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $teacher = Teacher::where('id', $request->teacher_id)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $existingCard = TeacherIdCard::where('teacher_id', $teacher->id)
            ->where('school_id', $schoolId)
            ->where('academic_year', $activeYear)
            ->first();

        if ($existingCard) {
            $existingCard->update([
                'status'     => 'active',
                'issue_date' => now()->toDateString(),
                'expiry_date'=> Carbon::now()->endOfYear()->toDateString(),
                'issued_by'  => session('LoggedTeacher') ?? session('LoggedAdmin'),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Existing Teacher ID card reactivated successfully.',
                'card_id' => $existingCard->id,
            ]);
        }

        $cardNumber = $this->generateCardNumber($schoolId, $teacher->id);
        $qrData     = $this->buildQrData($teacher, $cardNumber, $activeYear);

        $card = TeacherIdCard::create([
            'teacher_id'    => $teacher->id,
            'school_id'     => $schoolId,
            'academic_year' => $activeYear,
            'card_number'   => $cardNumber,
            'status'        => 'active',
            'issue_date'    => now()->toDateString(),
            'expiry_date'   => Carbon::now()->endOfYear()->toDateString(),
            'issued_by'     => session('LoggedTeacher') ?? session('LoggedAdmin'),
            'qr_code_data'  => $qrData,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Teacher ID card generated successfully.',
            'card_id' => $card->id,
        ]);
    }

    // ──────────────────────────────────────────────
    //  PREVIEW single card (HTML)
    // ──────────────────────────────────────────────
    public function preview($cardId)
    {
        PermissionHelper::denyUnlessFeature('print_teacher_cards');

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');

        $card    = TeacherIdCard::where('id', $cardId)->where('school_id', $schoolId)->firstOrFail();
        $teacher = $card->teacher;
        $school  = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();

        $qrSvg    = $this->generateQrSvg($card->qr_code_data);
        $photoUrl = $this->getTeacherPhotoUrl($teacher);
        $logoUrl  = $this->getLogoUrl($profile);

        return view('teacher.id-cards.preview', compact(
            'card', 'teacher', 'school', 'profile', 'qrSvg', 'photoUrl', 'logoUrl'
        ));
    }

    // ──────────────────────────────────────────────
    //  PRINT PDF – single card
    // ──────────────────────────────────────────────
    public function printCard($cardId)
    {
        if (!PermissionHelper::canFeature('print_teacher_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');

        $card    = TeacherIdCard::where('id', $cardId)->where('school_id', $schoolId)->firstOrFail();
        $teacher = $card->teacher;
        $school  = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();

        $qrSvg    = $this->generateQrSvg($card->qr_code_data);
        $photoUrl = $this->getTeacherPhotoUrl($teacher);
        $logoUrl  = $this->getLogoUrl($profile);

        $pdf = Pdf::loadView('teacher.id-cards.pdf-card', compact(
            'card', 'teacher', 'school', 'profile', 'qrSvg', 'photoUrl', 'logoUrl'
        ))
            ->setPaper([0, 0, 241.89, 153.07])
            ->setOption('dpi', 150);

        return $pdf->download("Teacher_ID_{$teacher->firstname}_{$teacher->surname}.pdf");
    }

    // ──────────────────────────────────────────────
    //  PRINT PDF – bulk all active cards
    // ──────────────────────────────────────────────
    public function printBulk(Request $request)
    {
        if (!PermissionHelper::canFeature('print_teacher_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId   = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $cards   = TeacherIdCard::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('academic_year', $activeYear)
            ->with('teacher')
            ->get();

        $school  = School::find($schoolId);
        $profile = SchoolProfile::where('school_id', $schoolId)->first();
        $logoUrl = $this->getLogoUrl($profile);

        $cardsData = $cards->map(function ($card) {
            $teacher = $card->teacher;
            return [
                'card'     => $card,
                'teacher'  => $teacher,
                'qrSvg'    => $this->generateQrSvg($card->qr_code_data),
                'photoUrl' => $this->getTeacherPhotoUrl($teacher),
            ];
        });

        $pdf = Pdf::loadView('teacher.id-cards.pdf-bulk', compact('cardsData', 'school', 'profile', 'logoUrl'))
            ->setPaper('a4');

        return $pdf->download("Teacher_ID_Cards_Bulk_{$activeYear}.pdf");
    }

    // ──────────────────────────────────────────────
    //  REVOKE a card
    // ──────────────────────────────────────────────
    public function revoke($cardId)
    {
        if (!PermissionHelper::canFeature('revoke_teacher_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId = session('LoggedSchool');

        $card = TeacherIdCard::where('id', $cardId)->where('school_id', $schoolId)->firstOrFail();
        $card->update(['status' => 'revoked']);

        return response()->json(['status' => 'success', 'message' => 'Teacher ID card revoked successfully.']);
    }

    // ──────────────────────────────────────────────
    //  REACTIVATE a card
    // ──────────────────────────────────────────────
    public function reactivate($cardId)
    {
        if (!PermissionHelper::canFeature('reactivate_teacher_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        try {
            Helper::requireSchool();
            $schoolId = session('LoggedSchool');

            $card = TeacherIdCard::where('id', $cardId)->where('school_id', $schoolId)->first();

            if (!$card) {
                return response()->json(['status' => 'error', 'message' => 'Teacher ID card not found.'], 404);
            }

            if ($card->expiry_date && Carbon::parse($card->expiry_date)->isPast()) {
                return response()->json([
                    'status'     => 'error',
                    'message'    => 'Cannot reactivate an expired card. Please generate a new one instead.',
                    'error_code' => 'CARD_EXPIRED',
                ], 422);
            }

            if ($card->status === 'active') {
                return response()->json([
                    'status'     => 'error',
                    'message'    => 'This card is already active.',
                    'error_code' => 'CARD_ALREADY_ACTIVE',
                ], 422);
            }

            $card->update([
                'status'     => 'active',
                'issue_date' => now()->toDateString(),
                'issued_by'  => session('LoggedTeacher') ?? session('LoggedAdmin'),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Teacher ID card reactivated successfully.',
                'card_id' => $card->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while reactivating the card.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  VERIFY QR scan
    // ──────────────────────────────────────────────
    public function verify($cardNumber)
    {
        // Public endpoint - no permission check needed
        $card = TeacherIdCard::where('card_number', $cardNumber)->with('teacher')->first();

        if (!$card) {
            return response()->json(['valid' => false, 'message' => 'Card not found.'], 404);
        }

        $teacher = $card->teacher;
        $school  = School::find($card->school_id);

        return response()->json([
            'valid'        => $card->status === 'active',
            'status'       => $card->status,
            'card_number'  => $card->card_number,
            'teacher_name' => $teacher->firstname . ' ' . $teacher->surname,
            'employee_no'  => $teacher->employee_number ?? 'N/A',
            'phone'        => $teacher->phonenumber ?? 'N/A',
            'gender'       => $teacher->gender,
            'school'       => $school->name ?? 'N/A',
            'academic_year'=> $card->academic_year,
            'issue_date'   => $card->issue_date?->format('d M Y'),
            'expiry_date'  => $card->expiry_date?->format('d M Y'),
        ]);
    }

    // ──────────────────────────────────────────────
    //  QR SCANNER PAGE
    // ──────────────────────────────────────────────
    public function scannerPage()
    {
        PermissionHelper::denyUnlessFeature('verify_teacher_cards');

        return view('teacher.id-cards.scanner');
    }

    // ──────────────────────────────────────────────
    //  STATS
    // ──────────────────────────────────────────────
    public function stats()
    {
        PermissionHelper::denyUnlessFeature('view_teacher_cards');

        $schoolId   = session('LoggedSchool');
        $activeYear = Helper::active_year();

        $total         = TeacherIdCard::where('school_id', $schoolId)->where('academic_year', $activeYear)->count();
        $active        = TeacherIdCard::where('school_id', $schoolId)->where('academic_year', $activeYear)->where('status', 'active')->count();
        $revoked       = TeacherIdCard::where('school_id', $schoolId)->where('academic_year', $activeYear)->where('status', 'revoked')->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();

        return response()->json(compact('total', 'active', 'revoked', 'totalTeachers'));
    }

    // ──────────────────────────────────────────────
    //  SEARCH TEACHERS (AJAX)
    // ──────────────────────────────────────────────
    public function searchTeachers(Request $request)
    {
        if (!PermissionHelper::canFeature('generate_teacher_cards')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        Helper::requireSchool();
        $schoolId   = session('LoggedSchool');
        $activeYear = Helper::active_year();
        $query      = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $teachers = Teacher::where('school_id', $schoolId)
            ->where(function ($q) use ($query) {
                $q->where('firstname', 'like', "%{$query}%")
                    ->orWhere('surname', 'like', "%{$query}%")
                    ->orWhere('employee_number', 'like', "%{$query}%")
                    ->orWhere('phonenumber', 'like', "%{$query}%");
            })
            ->limit(15)
            ->get()
            ->map(function ($teacher) use ($schoolId, $activeYear) {
                $card       = TeacherIdCard::where('teacher_id', $teacher->id)
                    ->where('school_id', $schoolId)
                    ->where('academic_year', $activeYear)
                    ->first();

                $cardStatus  = null;
                $cardId      = null;
                $buttonType  = 'generate';

                if ($card) {
                    $cardStatus = $card->status;
                    $cardId     = $card->id;

                    if ($card->status === 'active') {
                        $buttonType = 'active';
                    } elseif ($card->status === 'revoked') {
                        $buttonType = 'reactivate';
                    } elseif ($card->status === 'expired') {
                        $buttonType = 'expired';
                    }
                }

                return [
                    'id'            => $teacher->id,
                    'firstname'     => $teacher->firstname,
                    'surname'       => $teacher->surname,
                    'employee_number'=> $teacher->employee_number ?? 'N/A',
                    'phonenumber'   => $teacher->phonenumber ?? 'N/A',
                    'card_status'   => $cardStatus,
                    'card_id'       => $cardId,
                    'button_type'   => $buttonType,
                ];
            });

        return response()->json($teachers);
    }

    // ──────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ──────────────────────────────────────────────
    private function generateCardNumber($schoolId, $teacherId): string
    {
        $prefix = strtoupper(Str::random(3));
        $ts     = now()->format('ymdHis');
        return "TID-{$prefix}-{$teacherId}-{$ts}";
    }

    private function buildQrData(Teacher $teacher, string $cardNumber, string $year): string
    {
        return json_encode([
            'card'     => $cardNumber,
            'name'     => $teacher->firstname . ' ' . $teacher->surname,
            'emp_no'   => $teacher->employee_number ?? '',
            'phone'    => $teacher->phonenumber ?? '',
            'gender'   => $teacher->gender ?? '',
            'year'     => $year,
            'school'   => $teacher->school_id,
            'type'     => 'teacher',
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

    private function getTeacherPhotoUrl(Teacher $teacher): ?string
    {
        if (!$teacher->teacher_profile) return null;
        $path = $teacher->teacher_profile;
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return null;
    }

    private function getLogoUrl(?SchoolProfile $profile): ?string
    {
        if (!$profile || !$profile->logo) return null;
        $path = 'uploads/school_logos/' . $profile->logo;
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return null;
    }
}