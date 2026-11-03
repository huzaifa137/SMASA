<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\House;
use App\Models\ParentAccount;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentFeeAllocation;
use App\Services\ParentVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Self-service portal for parents/guardians. Identity here is a phone
 * number (ParentAccount), not tied to any one school — a parent with
 * children at different schools sees all of them from one login. Every
 * action below re-checks that the requested student's primary_contact
 * actually matches the logged-in parent's phone before showing anything,
 * since nothing in the schema formally links a parent row to a student.
 */
class ParentPortalController extends Controller
{
    // ─── Auth ───────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (session('ParentId')) {
            return redirect()->route('parents.dashboard');
        }

        // Same unified login screen used for School / Admin, just opened
        // with the "Parent" tab pre-selected. The school dropdown is part
        // of that shared markup, so it needs the same $schools list even
        // though it stays hidden while the Parent tab is active.
        $schools = House::whereNotIn('Number', function ($query) {
            $query->select('registration_code')
                ->from('schools')
                ->where('school_status', 1);
        })->get();

        return view('users.login', [
            'schools' => $schools,
            'activeRole' => 'parent',
        ]);
    }

    public function login(Request $request)
    {
        // Same JSON auth-check contract used by School/Admin (UserController::checkUser
        // / authenticateSchool): field-level errors keyed by the input's `name` so the
        // shared login page can render them inline instead of a form submit + flash message.
        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            [
                'username' => 'required|string',
                'password' => 'required|string',
            ],
            [
                'username.required' => 'Parent phone number is required.',
                'password.required' => 'Password is required.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = trim($request->input('username'));

        $account = ParentAccount::findOrProvisionByPhone($phone);

        if (!$account) {
            return response()->json([
                'errors' => [
                    'username' => ['We could not find a student record with that contact number. Please check with your school if you believe this is a mistake.'],
                ],
            ], 422);
        }

        if (!$account->checkPassword($request->input('password'))) {
            return response()->json([
                'errors' => [
                    'password' => ['Incorrect password.'],
                ],
            ], 422);
        }

        $account->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        session([
            'ParentId' => $account->id,
            'ParentPhone' => $account->phone,
        ]);

        if ($account->must_change_password) {
            return response()->json([
                'status' => true,
                'message' => 'Welcome! Please set your own password to continue.',
                'redirect' => route('parents.change-password'),
            ]);
        }

        $intended = session()->pull('url.intended');

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'redirect' => $intended ?: route('parents.dashboard'),
        ]);
    }

    public function logout()
    {
        session()->forget(['ParentId', 'ParentPhone', 'ParentActiveStudent']);
        return redirect()->route('parents.login')->with('info', 'You have been logged out.');
    }

    public function showChangePassword()
    {
        return view('parents.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:4|confirmed',
        ]);

        $account = ParentAccount::findOrFail(session('ParentId'));

        $account->update([
            'password' => Hash::make($request->input('password')),
            'must_change_password' => false,
        ]);

        return redirect()->route('parents.dashboard')->with('success', 'Password updated. Welcome to your parent portal.');
    }

    // ─── Dashboard / child switcher ────────────────────────────────────

    public function dashboard()
    {
        $children = $this->myChildren();

        return view('parents.dashboard', compact('children'));
    }

    public function childOverview($studentId)
    {
        $student = $this->authorizedStudent($studentId);

        $attendanceSummary = StudentAttendance::where('student_id', $student->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $feeSummary = StudentFeeAllocation::where('student_id', $student->id)
            ->selectRaw('sum(allocated_amount - discount_amount) as charged, sum(balance) as owing')
            ->first();

        $releasedResultsCount = ParentVisibility::sectionVisible($student, 'results')
            ? $this->releasedExamsFor($student)->filter(fn($e) => $e->parent_access['allowed'])->count()
            : 0;

        return view('parents.child-overview', compact(
            'student',
            'attendanceSummary',
            'feeSummary',
            'releasedResultsCount'
        ));
    }

    // ─── Results ────────────────────────────────────────────────────────

    public function results($studentId)
    {
        $student = $this->authorizedStudent($studentId);

        if (!ParentVisibility::sectionVisible($student, 'results')) {
            return $this->blocked($student, 'results', ParentVisibility::sectionMessage($student, 'results'));
        }

        $exams = $this->releasedExamsFor($student);

        return view('parents.results', compact('student', 'exams'));
    }

    public function viewResult($studentId, $examId)
    {
        $student = $this->authorizedStudent($studentId);

        // withReportCards(): O-Level report cards are stored as hidden
        // "report card" examinations; without this they 404 for parents.
        $exam = Examination::withReportCards()
            ->where('id', $examId)
            ->where('school_id', $student->school_id)
            ->firstOrFail();

        if (!$this->examReleasedForStudent($exam, $student)) {
            abort(403, 'Results for this exam have not been released yet.');
        }

        // School-controlled visibility (section switch, manual / scheduled
        // release, fee-balance hold, per-student override).
        $access = ParentVisibility::examAccess($exam, $student);
        if (!$access['visible'] && !$access['message']) {
            abort(403, 'Results for this exam have not been released yet.');
        }
        if (!$access['allowed']) {
            return $this->blocked($student, 'results', $access['message'] ?: ParentVisibility::DEFAULT_MESSAGE);
        }

        // A parent must see EXACTLY the design the school chose, with the
        // school's saved settings. Nothing in the URL (?template=, ?lang=,
        // ?exam_ids=, show/hide toggles ...) may override that, so drop the
        // whole query/input before the shared renderer reads request().
        // The Arabic card's AR/EN switch is a harmless display toggle, so it
        // is the one value kept.
        $keep = in_array(request('card_lang'), ['ar', 'en'], true)
            ? ['card_lang' => request('card_lang')]
            : [];
        request()->query->replace($keep);
        request()->request->replace([]);

        /** @var ExaminationController $examController */
        $examController = app(ExaminationController::class);

        // Same renderer the school portal uses: picks Nursery / Primary /
        // Secondary / Arabic / the school's custom design and builds the
        // Progressive Assessment Record.
        return $examController->renderStudentSlip(
            $exam,
            $student,
            $student->school_id,
            ['parentView' => true, 'backUrl' => route('parents.results', $student->id)]
        );
    }

    // ─── Attendance ─────────────────────────────────────────────────────

    public function attendance(Request $request, $studentId)
    {
        $student = $this->authorizedStudent($studentId);

        if (!ParentVisibility::sectionVisible($student, 'attendance')) {
            return $this->blocked($student, 'attendance', ParentVisibility::sectionMessage($student, 'attendance'));
        }

        $month = $request->input('month', now()->format('Y-m'));

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereRaw("DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$month])
            ->orderByDesc('attendance_date')
            ->get();

        $summary = $records->countBy('status');

        $totalDays = $records->count();
        $presentDays = $summary->get('present', 0) + $summary->get('late', 0);
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : null;

        $availableMonths = StudentAttendance::where('student_id', $student->id)
            ->selectRaw("DISTINCT DATE_FORMAT(attendance_date, '%Y-%m') as ym")
            ->orderByDesc('ym')
            ->pluck('ym');

        // Gate sign-out time (opt-in) comes from the card-scan arrival log.
        $vis = ParentVisibility::settings((int) $student->school_id);
        $showArrival = (bool) $vis->show_arrival_time;
        $showSignout = (bool) $vis->show_signout_time;
        $signouts = $showSignout
            ? DB::table('school_arrival_attendances')
                ->where('school_id', $student->school_id)
                ->where('person_type', 'student')
                ->where('person_id', $student->id)
                ->whereRaw("DATE_FORMAT(attendance_date, '%Y-%m') = ?", [$month])
                ->pluck('departure_time', 'attendance_date')
                ->mapWithKeys(fn($t, $d) => [substr((string) $d, 0, 10) => $t])
            : collect();

        return view('parents.attendance', compact(
            'showArrival',
            'showSignout',
            'signouts',
            'student',
            'records',
            'summary',
            'attendanceRate',
            'month',
            'availableMonths'
        ));
    }

    // ─── Finance ────────────────────────────────────────────────────────

    public function finance(Request $request, $studentId)
    {
        $student = $this->authorizedStudent($studentId);

        if (!ParentVisibility::sectionVisible($student, 'fees')) {
            return $this->blocked($student, 'fees', ParentVisibility::sectionMessage($student, 'fees'));
        }

        $year = $request->input('year', date('Y'));

        $allocations = StudentFeeAllocation::where('student_id', $student->id)
            ->when($year, fn($q) => $q->where('academic_year', $year))
            ->with(['feeStructure', 'payments' => fn($q) => $q->orderBy('payment_date')])
            ->orderBy('academic_year')
            ->orderBy('term')
            ->get();

        $statement = collect();
        $runningBalance = 0;

        foreach ($allocations as $allocation) {
            $charge = (float) $allocation->allocated_amount - (float) $allocation->discount_amount;
            $runningBalance += $charge;

            $statement->push([
                'date' => $allocation->created_at,
                'type' => 'charge',
                'description' => 'Fee charge — ' . ($allocation->feeStructure->name ?? 'Fee Structure')
                    . " (" . \App\Support\Term::label($allocation->term) . ", {$allocation->academic_year})"
                    . ($allocation->discount_amount > 0 ? ' — discount applied' : ''),
                'debit' => $charge,
                'credit' => null,
                'balance' => $runningBalance,
            ]);

            foreach ($allocation->payments as $payment) {
                if ($payment->status !== 'confirmed') {
                    continue;
                }
                $runningBalance -= (float) $payment->amount_paid;

                $statement->push([
                    'date' => $payment->payment_date,
                    'type' => 'payment',
                    'description' => 'Payment received — Receipt ' . $payment->receipt_number . ' (' . $payment->methodLabel() . ')',
                    'debit' => null,
                    'credit' => (float) $payment->amount_paid,
                    'balance' => $runningBalance,
                ]);
            }
        }

        $totalCharges = $allocations->sum(fn($a) => $a->allocated_amount - $a->discount_amount);
        $totalPaid = $allocations->flatMap->payments->where('status', 'confirmed')->sum('amount_paid');
        $arrears = $totalCharges - $totalPaid;

        $availableYears = StudentFeeAllocation::where('student_id', $student->id)
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('parents.finance', compact(
            'student', 'statement', 'totalCharges', 'totalPaid', 'arrears', 'year', 'availableYears'
        ));
    }

    // ─── Shared helpers ─────────────────────────────────────────────────

    private function myChildren()
    {
        $phone = session('ParentPhone');

        return Student::where('primary_contact', $phone)
            ->get()
            ->map(function ($student) {
                $student->school_name = Helper::schoolNameBySchoolID($student->school_id);
                $student->class_name = Helper::recordMdname($student->senior);
                $student->stream_name = Helper::recordMdname($student->stream);
                return $student;
            })
            ->groupBy('school_id');
    }

    private function authorizedStudent($studentId): Student
    {
        $phone = session('ParentPhone');
        $student = Student::where('id', $studentId)->where('primary_contact', $phone)->first();

        if (!$student) {
            abort(403, "That student isn't linked to your account.");
        }

        $student->school_name = Helper::schoolNameBySchoolID($student->school_id);
        $student->class_name = Helper::recordMdname($student->senior);
        $student->stream_name = Helper::recordMdname($student->stream);

        return $student;
    }

    /**
     * Every released exam for this student's class/stream, newest first —
     * "released" checked per-class first (a class can be released ahead
     * of the rest of the exam), falling back to exam-level status.
     */
    private function releasedExamsFor(Student $student)
    {
        return Examination::withReportCards()
            ->where('school_id', $student->school_id)
            ->whereHas('examinationClasses', function ($q) use ($student) {
                $q->where('class_id', $student->senior)->where('stream_id', $student->stream);
            })
            ->orderByDesc('start_date')
            ->get()
            ->filter(fn($exam) => $this->examReleasedForStudent($exam, $student))
            ->map(function ($exam) use ($student) {
                $exam->parent_access = ParentVisibility::examAccess($exam, $student);
                return $exam;
            })
            // Not published to parents yet (manual / scheduled): don't list it.
            ->filter(fn($exam) => $exam->parent_access['visible'])
            ->values();
    }

    /** Friendly "the school has limited this" page instead of a bare 403. */
    private function blocked(Student $student, string $section, string $message)
    {
        return response()->view('parents.blocked', compact('student', 'section', 'message'), 403);
    }

    /**
     * The ONE gate deciding whether a parent may open an exam's report card
     * (list + detail both use it). Today: the student's class sat the exam
     * and it is released. An O-Level report card is a computed card built
     * on a base examination, so it is also held back until that base exam
     * is released for the class - report cards are stored as 'closed' and
     * would otherwise open to parents before the results were published.
     */
    private function examReleasedForStudent(Examination $exam, Student $student): bool
    {
        /** @var ExaminationController $examController */
        $examController = app(ExaminationController::class);

        $sat = ExaminationClass::where('examination_id', $exam->id)
            ->where('school_id', $student->school_id)
            ->where('class_id', $student->senior)
            ->where('stream_id', $student->stream)
            ->exists();

        if (!$sat) {
            return false;
        }

        if (!$examController->classIsReleased($exam, $student->senior, $student->stream, $student->school_id)) {
            return false;
        }

        if ($exam->isReportCard() && $exam->base_examination_id) {
            $base = Examination::withReportCards()->find($exam->base_examination_id);
            if ($base && !$examController->classIsReleased($base, $student->senior, $student->stream, $student->school_id)) {
                return false;
            }
        }

        return true;
    }

    // ─── Web Push (parent's phone) ──────────────────────────────────────
    // Lets a parent switch attendance alerts (arrival / sign-out) ON or OFF
    // for this device. Same flow admins/teachers use, keyed by ParentAccount.

    private function pushAccount(): ?ParentAccount
    {
        $id = session('ParentId');
        return $id ? ParentAccount::find($id) : null;
    }

    public function pushSubscribe(Request $request)
    {
        $account = $this->pushAccount();
        if (!$account) {
            return response()->json(['error' => 'Unauthenticated user.'], 401);
        }
        if (!filled($request->endpoint)) {
            return response()->json(['error' => 'Missing push endpoint from the browser.'], 422);
        }

        try {
            $account->updatePushSubscription(
                endpoint: $request->endpoint,
                key: $request->key,
                token: $request->token,
                contentEncoding: $request->contentEncoding ?? 'aesgcm'
            );

            return response()->json(['success' => true, 'message' => 'Notifications are ON for this device.']);
        } catch (\Throwable $e) {
            Log::error('Parent push subscribe failed', ['parent_id' => $account->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save push subscription.'], 500);
        }
    }

    public function pushUnsubscribe(Request $request)
    {
        $account = $this->pushAccount();
        if (!$account) {
            return response()->json(['error' => 'Unauthenticated user.'], 401);
        }
        if (!filled($request->endpoint)) {
            return response()->json(['error' => 'Missing endpoint.'], 422);
        }

        try {
            $account->deletePushSubscription($request->endpoint);
            return response()->json(['success' => true, 'message' => 'Notifications are OFF for this device.']);
        } catch (\Throwable $e) {
            Log::error('Parent push unsubscribe failed', ['parent_id' => $account->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to remove push subscription.'], 500);
        }
    }

    public function pushStatus(Request $request)
    {
        $account = $this->pushAccount();
        if (!$account) {
            return response()->json(['error' => 'Unauthenticated user.'], 401);
        }

        return response()->json([
            'registered' => filled($request->endpoint)
                && $account->pushSubscriptions()->where('endpoint', $request->endpoint)->exists(),
            'devices' => $account->pushSubscriptions()->count(),
        ]);
    }
}