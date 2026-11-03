<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\CardScanLog;
use App\Models\SchoolArrivalAttendance;
use App\Models\SchoolAttendanceSetting;
use App\Models\ParentNotificationLog;
use App\Services\ParentAttendanceNotifier;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentIdCard;
use App\Models\TeacherIdCard;
use App\Models\LibraryMember;
use App\Models\LibraryBook;
use App\Models\LibraryBorrowing;
use App\Models\LibraryReservation;
use App\Models\LibrarySetting;
use App\Models\FeePayment;
use App\Models\StudentFeeAllocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardScanController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  SCAN HUB PAGE
    // ══════════════════════════════════════════════════════════════

    public function hub()
    {
        PermissionHelper::denyUnlessFeature('view_hub');

        $schoolId = session('LoggedSchool');

        // Recent scan activity (last 20)
        $recentScans = CardScanLog::where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        // Today's scan stats
        $today = Carbon::today()->toDateString();
        $todayStats = CardScanLog::where('school_id', $schoolId)
            ->whereDate('created_at', $today)
            ->selectRaw("
                scan_category,
                COUNT(*) as total,
                SUM(scan_result='success') as success_count,
                SUM(scan_result='failed') as failed_count
            ")
            ->groupBy('scan_category')
            ->get()
            ->keyBy('scan_category');

        $todayTotal = CardScanLog::where('school_id', $schoolId)
            ->whereDate('created_at', $today)
            ->count();

        $categories = $this->getScanCategories();

        return view('CardScan.hub', compact(
            'recentScans',
            'todayStats',
            'todayTotal',
            'categories'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  MAIN SCAN ENDPOINT – receives card number + category
    // ══════════════════════════════════════════════════════════════

    public function scan(Request $request)
    {
        if (!PermissionHelper::canFeature('scan_cards')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to scan cards.'], 403);
        }

        $request->validate([
            'card_number' => 'required|string',
            'category' => 'required|string|in:attendance_arrival,attendance_departure,attendance_class,library_issue,library_return,library_reserve,finance_balance,finance_payment,info',
        ]);

        $schoolId = session('LoggedSchool');
        $cardNumber = trim($request->card_number);
        $category = $request->category;

        // Parse card number (may be JSON from QR)
        $parsedCard = $this->parseCardNumber($cardNumber);
        $cardNumber = $parsedCard['card'];

        // Resolve card (student or teacher)
        $resolved = $this->resolveCard($cardNumber, $schoolId);

        if (!$resolved['found']) {
            return $this->logAndReturn($schoolId, $cardNumber, null, $category, 'failed', $resolved['message'], []);
        }

        if (!$resolved['active']) {
            return $this->logAndReturn(
                $schoolId,
                $cardNumber,
                $resolved['card_type'],
                $category,
                'failed',
                'Card is ' . strtoupper($resolved['status']) . ' – access denied.',
                [
                    'person_name' => $resolved['person_name'],
                    'status' => $resolved['status'],
                ]
            );
        }

        // Route to appropriate handler
        return match ($category) {
            'attendance_arrival' => $this->handleArrivalAttendance($request, $schoolId, $resolved),
            'attendance_departure' => $this->handleDepartureAttendance($request, $schoolId, $resolved),
            'attendance_class' => $this->handleClassAttendance($request, $schoolId, $resolved),
            'library_issue' => $this->handleLibraryIssue($request, $schoolId, $resolved),
            'library_return' => $this->handleLibraryReturn($request, $schoolId, $resolved),
            'library_reserve' => $this->handleLibraryReserve($request, $schoolId, $resolved),
            'finance_balance' => $this->handleFinanceBalance($request, $schoolId, $resolved),
            'finance_payment' => $this->handleFinancePayment($request, $schoolId, $resolved),
            'info' => $this->handleCardInfo($request, $schoolId, $resolved),
            default => $this->logAndReturn($schoolId, $cardNumber, null, $category, 'failed', 'Unknown scan category.', []),
        };
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: School Arrival Attendance
    //  Early / on time / late is decided by THIS school's own hours
    //  (Card Scan → Attendance Hours), not a hard-coded 08:00.
    // ──────────────────────────────────────────────────────────────
    private function handleArrivalAttendance(Request $request, $schoolId, array $resolved)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $arrivalTime = $now->format('H:i:s');

        $settings = SchoolAttendanceSetting::forSchool($schoolId);
        $verdict = $settings->arrivalVerdict($resolved['card_type'], $now);
        $status = $verdict['status']; // 'present' | 'late'

        // Check already scanned today
        $existing = SchoolArrivalAttendance::where('school_id', $schoolId)
            ->where('person_id', $resolved['person_id'])
            ->where('person_type', $resolved['card_type'])
            ->where('attendance_date', $today)
            ->first();

        if ($existing) {
            $data = [
                'already_scanned' => true,
                'person_name' => $resolved['person_name'],
                'person_type' => $resolved['card_type'],
                'arrival_time' => $existing->arrival_time,
                'departure_time' => $existing->departure_time,
                'status' => $existing->status,
                'attendance_date' => $today,
                'late_cutoff' => Carbon::parse($verdict['cutoff'])->format('h:i A'),
                'class' => $resolved['class'] ?? null,
                'photo' => $resolved['photo'] ?? null,
            ];
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'attendance_arrival',
                'success',
                $resolved['person_name'] . ' already marked present today at ' . $existing->arrival_time,
                $data
            );
        }

        SchoolArrivalAttendance::create([
            'school_id' => $schoolId,
            'person_id' => $resolved['person_id'],
            'person_type' => $resolved['card_type'],
            'attendance_date' => $today,
            'arrival_time' => $arrivalTime,
            'status' => $status,
            'method' => 'card_scan',
            'card_number' => $resolved['card_number'],
            'recorded_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
        ]);

        $statusLabel = $status === 'late' ? '⚠️ LATE arrival' : '✅ Present';
        $data = [
            'person_name' => $resolved['person_name'],
            'person_type' => $resolved['card_type'],
            'arrival_time' => $arrivalTime,
            'status' => $status,
            'status_label' => $statusLabel,
            'punctuality' => $verdict['punctuality'],       // early | on_time | late
            'punctuality_label' => $verdict['label'],       // e.g. "Early by 12 min", "Late by 7 min"
            'late_cutoff' => Carbon::parse($verdict['cutoff'])->format('h:i A'),
            'attendance_date' => $today,
            'class' => $resolved['class'] ?? null,
            'photo' => $resolved['photo'] ?? null,
        ];

        // Tell the parent (students only). Never lets a push problem break the scan.
        $data['parent_notification'] = $this->notifyParent(
            $settings, $resolved, 'arrival',
            [
                'class' => $resolved['class'] ?? null,
                'time' => $now->format('h:i A'),
                'label' => $verdict['label'],
                'status' => $status,
            ]
        );

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'attendance_arrival',
            'success',
            $resolved['person_name'] . ' – ' . $statusLabel . ' (' . $verdict['label'] . ') at ' . $now->format('h:i A'),
            $data
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: School Sign-Out (departure)
    //  Fills the departure_time column that existed but was never used.
    // ──────────────────────────────────────────────────────────────
    private function handleDepartureAttendance(Request $request, $schoolId, array $resolved)
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $settings = SchoolAttendanceSetting::forSchool($schoolId);

        $record = SchoolArrivalAttendance::where('school_id', $schoolId)
            ->where('person_id', $resolved['person_id'])
            ->where('person_type', $resolved['card_type'])
            ->where('attendance_date', $today)
            ->first();

        $base = [
            'person_name' => $resolved['person_name'],
            'person_type' => $resolved['card_type'],
            'attendance_date' => $today,
            'class' => $resolved['class'] ?? null,
            'photo' => $resolved['photo'] ?? null,
        ];

        // No arrival today (or marked absent) → can't sign out of a day they never started.
        if (!$record || $record->status === 'absent') {
            return $this->logAndReturn(
                $schoolId, $resolved['card_number'], $resolved['card_type'], 'attendance_departure', 'failed',
                $resolved['person_name'] . ' has no School Arrival recorded today' . ($record ? ' (marked absent)' : '')
                . '. Scan them in with “School Arrival” first.',
                $base
            );
        }

        // Already signed out → report it, don't overwrite the first sign-out time.
        if ($record->departure_time) {
            return $this->logAndReturn(
                $schoolId, $resolved['card_number'], $resolved['card_type'], 'attendance_departure', 'success',
                $resolved['person_name'] . ' already signed out today at ' . Carbon::parse($record->departure_time)->format('h:i A'),
                $base + [
                    'already_scanned' => true,
                    'arrival_time' => $record->arrival_time,
                    'departure_time' => $record->departure_time,
                    'departure_status' => $record->departure_status,
                ]
            );
        }

        // Double-scan guard: a sign-out seconds after arrival is almost certainly the wrong category.
        $arrivedAt = Carbon::parse($today . ' ' . $record->arrival_time);
        $minGap = (int) $settings->min_minutes_before_signout;
        if ($minGap > 0 && ($now->timestamp - $arrivedAt->timestamp) < $minGap * 60) {
            return $this->logAndReturn(
                $schoolId, $resolved['card_number'], $resolved['card_type'], 'attendance_departure', 'failed',
                $resolved['person_name'] . ' arrived at ' . $arrivedAt->format('h:i A') . '. Sign-out opens '
                . $minGap . ' min after arrival (looks like a double scan).',
                $base + ['arrival_time' => $record->arrival_time]
            );
        }

        $verdict = $settings->departureVerdict($resolved['card_type'], $now);

        $record->update([
            'departure_time' => $now->format('H:i:s'),
            'departure_status' => $verdict['status'], // early | on_time
            'departure_method' => 'card_scan',
            'departure_recorded_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
        ]);

        $data = $base + [
            'arrival_time' => $record->arrival_time,
            'departure_time' => $now->format('H:i:s'),
            'departure_status' => $verdict['status'],
            'departure_label' => $verdict['label'],
            'closing_time' => Carbon::parse($verdict['closing'])->format('h:i A'),
            'status' => $record->status,
        ];

        $data['parent_notification'] = $this->notifyParent(
            $settings, $resolved, 'signout',
            [
                'class' => $resolved['class'] ?? null,
                'time' => $now->format('h:i A'),
                'label' => $verdict['label'],
                'status' => $verdict['status'],
            ]
        );

        $icon = $verdict['status'] === 'early' ? '⚠️' : '✅';
        return $this->logAndReturn(
            $schoolId, $resolved['card_number'], $resolved['card_type'], 'attendance_departure', 'success',
            $resolved['person_name'] . ' – ' . $icon . ' signed out at ' . $now->format('h:i A') . ' (' . $verdict['label'] . ')',
            $data
        );
    }

    /**
     * Push a parent alert for a student scan. Returns null for teachers,
     * or the notifier result (sent / failed + reason / skipped).
     */
    private function notifyParent(SchoolAttendanceSetting $settings, array $resolved, string $event, array $context): ?array
    {
        if (($resolved['card_type'] ?? null) !== 'student') {
            return null;
        }

        $student = Student::find($resolved['person_id']);
        if (!$student) {
            return null;
        }

        $enabled = $event === 'arrival' ? $settings->notify_parent_on_arrival : $settings->notify_parent_on_signout;
        if (!$enabled) {
            return ParentAttendanceNotifier::skipped(
                $student, $event,
                'Parent ' . ($event === 'arrival' ? 'arrival' : 'sign-out') . ' alerts are switched off in Attendance Hours.'
            );
        }

        return ParentAttendanceNotifier::notify($student, $event, $context);
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: Class Attendance (info only via scan – redirect to form)
    // ──────────────────────────────────────────────────────────────
    private function handleClassAttendance(Request $request, $schoolId, array $resolved)
    {
        if ($resolved['card_type'] !== 'student') {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'attendance_class',
                'failed',
                'Class attendance is only for students.',
                []
            );
        }

        $student = Student::find($resolved['person_id']);
        $className = Helper::recordMdname($student->senior ?? '');
        $streamName = Helper::recordMdname($student->stream ?? '');

        $data = [
            'person_name' => $resolved['person_name'],
            'class' => $className,
            'stream' => $streamName,
            'class_id' => $student->senior,
            'stream_id' => $student->stream,
            'attendance_url' => route('attendance.take', [$student->senior, $student->stream]),
            'photo' => $resolved['photo'] ?? null,
        ];

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'attendance_class',
            'success',
            $resolved['person_name'] . ' – ' . $className . ' / ' . $streamName . '. Use class attendance form to mark.',
            $data
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: Library – Issue Book
    // ──────────────────────────────────────────────────────────────
    private function handleLibraryIssue(Request $request, $schoolId, array $resolved)
    {
        $member = $this->getLibraryMember($schoolId, $resolved['person_id'], $resolved['card_type']);

        if (!$member) {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'library_issue',
                'failed',
                $resolved['person_name'] . ' is not registered as a library member.',
                [
                    'person_name' => $resolved['person_name'],
                    'action' => 'register_member',
                ]
            );
        }

        if (!$member->canBorrow()) {
            $reason = 'Cannot borrow: ';
            if ($member->status !== 'active')
                $reason .= 'membership is ' . $member->status . '. ';
            elseif ($member->activeBorrowings()->count() >= $member->max_books_allowed)
                $reason .= 'borrowing limit reached. ';
            elseif ($member->unpaidFines()->sum('amount') > 0)
                $reason .= 'has unpaid fines of UGX ' . number_format($member->unpaidFines()->sum('amount'));

            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'library_issue',
                'failed',
                $resolved['person_name'] . '. ' . $reason,
                [
                    'person_name' => $resolved['person_name'],
                    'member_status' => $member->status,
                    'books_borrowed' => $member->activeBorrowings()->count(),
                    'max_books' => $member->max_books_allowed,
                    'unpaid_fines' => $member->unpaidFines()->sum('amount'),
                    'library_card' => $member->library_card_number,
                ]
            );
        }

        // Return member info + active borrowings for the librarian to pick a book
        $activeBorrowings = LibraryBorrowing::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->with('book')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'book' => $b->book->title ?? 'N/A',
                'due_date' => $b->due_date?->format('d M Y'),
                'overdue' => $b->isOverdue(),
            ]);

        $data = [
            'person_name' => $resolved['person_name'],
            'library_card' => $member->library_card_number,
            'member_id' => $member->id,
            'member_status' => $member->status,
            'books_borrowed' => $member->activeBorrowings()->count(),
            'max_books' => $member->max_books_allowed,
            'books_left' => $member->max_books_allowed - $member->activeBorrowings()->count(),
            'active_borrowings' => $activeBorrowings,
            'unpaid_fines' => $member->unpaidFines()->sum('amount'),
            'action_required' => 'select_book', // frontend should show book search
            'photo' => $resolved['photo'] ?? null,
        ];

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'library_issue',
            'success',
            $resolved['person_name'] . ' verified. Can borrow ' . ($member->max_books_allowed - $member->activeBorrowings()->count()) . ' more book(s). Select a book to issue.',
            $data
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: Library – Return Book
    // ──────────────────────────────────────────────────────────────
    private function handleLibraryReturn(Request $request, $schoolId, array $resolved)
    {
        $member = $this->getLibraryMember($schoolId, $resolved['person_id'], $resolved['card_type']);

        if (!$member) {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'library_return',
                'failed',
                $resolved['person_name'] . ' is not a library member.',
                []
            );
        }

        $activeBorrowings = LibraryBorrowing::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->with('book')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'book' => $b->book->title ?? 'N/A',
                'isbn' => $b->book->isbn ?? '',
                'due_date' => $b->due_date?->format('d M Y'),
                'overdue' => $b->isOverdue(),
                'overdue_days' => $b->overdue_days,
            ]);

        if ($activeBorrowings->isEmpty()) {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'library_return',
                'failed',
                $resolved['person_name'] . ' has no books currently borrowed.',
                [
                    'person_name' => $resolved['person_name'],
                ]
            );
        }

        $data = [
            'person_name' => $resolved['person_name'],
            'library_card' => $member->library_card_number,
            'member_id' => $member->id,
            'active_borrowings' => $activeBorrowings,
            'action_required' => 'select_return',
            'photo' => $resolved['photo'] ?? null,
        ];

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'library_return',
            'success',
            $resolved['person_name'] . ' has ' . $activeBorrowings->count() . ' book(s) to return. Select which to return.',
            $data
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: Library – Reserve
    // ──────────────────────────────────────────────────────────────
    private function handleLibraryReserve(Request $request, $schoolId, array $resolved)
    {
        $member = $this->getLibraryMember($schoolId, $resolved['person_id'], $resolved['card_type']);

        if (!$member) {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'library_reserve',
                'failed',
                $resolved['person_name'] . ' is not a library member.',
                []
            );
        }

        $activeReservations = LibraryReservation::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->whereIn('status', ['pending', 'ready'])
            ->with('book')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'book' => $r->book->title ?? 'N/A',
                'status' => $r->status,
                'expiry_date' => $r->expiry_date?->format('d M Y') ?? '—',
            ]);

        $data = [
            'person_name' => $resolved['person_name'],
            'member_id' => $member->id,
            'library_card' => $member->library_card_number,
            'active_reservations' => $activeReservations,
            'action_required' => 'search_book_reserve',
            'photo' => $resolved['photo'] ?? null,
        ];

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'library_reserve',
            'success',
            $resolved['person_name'] . ' verified. Search for a book to reserve.',
            $data
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: Finance – Balance Check
    // ──────────────────────────────────────────────────────────────
    private function handleFinanceBalance(Request $request, $schoolId, array $resolved)
    {
        if ($resolved['card_type'] !== 'student') {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'finance_balance',
                'failed',
                'Fee balance is only available for students.',
                []
            );
        }

        $year = Helper::active_year() ?: date('Y');

        $allocations = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('student_id', $resolved['person_id'])
            ->where('academic_year', $year)
            ->get();

        $totalBilled = 0;
        $totalPaid = 0;
        $totalBalance = 0;

        $allocationData = $allocations->map(function ($a) use (&$totalBilled, &$totalPaid, &$totalBalance) {
            $due = $a->allocated_amount - $a->discount_amount;
            $paid = FeePayment::where('allocation_id', $a->id)->where('status', 'confirmed')->sum('amount_paid');
            $bal = $due - $paid;
            $totalBilled += $due;
            $totalPaid += $paid;
            $totalBalance += $bal;
            return [
                'term' => $a->term,
                'billed' => $due,
                'paid' => $paid,
                'balance' => $bal,
                'status' => $a->payment_status,
            ];
        });

        $student = Student::find($resolved['person_id']);
        $className = Helper::recordMdname($student->senior ?? '');

        $data = [
            'person_name' => $resolved['person_name'],
            'student_id' => $resolved['person_id'],
            'class' => $className,
            'academic_year' => $year,
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'allocations' => $allocationData,
            'payment_status' => $totalBalance <= 0 ? 'FULLY PAID' : ($totalPaid > 0 ? 'PARTIAL' : 'UNPAID'),
            'photo' => $resolved['photo'] ?? null,
        ];

        $statusMsg = $totalBalance <= 0
            ? '✅ Fully paid'
            : '⚠️ Balance: UGX ' . number_format($totalBalance);

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'finance_balance',
            'success',
            $resolved['person_name'] . ' – ' . $statusMsg,
            $data
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: Finance – Payment (returns info for payment form)
    // ──────────────────────────────────────────────────────────────
    private function handleFinancePayment(Request $request, $schoolId, array $resolved)
    {
        if ($resolved['card_type'] !== 'student') {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'finance_payment',
                'failed',
                'Fee payment is only for students.',
                []
            );
        }

        $year = Helper::active_year() ?: date('Y');

        $allocations = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('student_id', $resolved['person_id'])
            ->where('academic_year', $year)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->get();

        if ($allocations->isEmpty()) {
            return $this->logAndReturn(
                $schoolId,
                $resolved['card_number'],
                $resolved['card_type'],
                'finance_payment',
                'success',
                $resolved['person_name'] . ' has no outstanding fees for ' . $year . '.',
                [
                    'person_name' => $resolved['person_name'],
                    'fully_paid' => true,
                    'academic_year' => $year,
                    'photo' => $resolved['photo'] ?? null,
                ]
            );
        }

        $outstandingData = $allocations->map(function ($a) {
            $paid = FeePayment::where('allocation_id', $a->id)->where('status', 'confirmed')->sum('amount_paid');
            $due = $a->allocated_amount - $a->discount_amount;
            return [
                'allocation_id' => $a->id,
                'term' => $a->term,
                'total_due' => $due,
                'paid' => $paid,
                'balance' => $due - $paid,
            ];
        });

        $totalOutstanding = $outstandingData->sum('balance');

        $data = [
            'person_name' => $resolved['person_name'],
            'student_id' => $resolved['person_id'],
            'academic_year' => $year,
            'total_outstanding' => $totalOutstanding,
            'allocations' => $outstandingData,
            'action_required' => 'process_payment',
            'payment_url' => route('finance.payments.create') . '?student_id=' . $resolved['person_id'],
            'photo' => $resolved['photo'] ?? null,
        ];

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'finance_payment',
            'success',
            $resolved['person_name'] . ' – Outstanding: UGX ' . number_format($totalOutstanding) . '. Proceed to payment form.',
            $data
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  HANDLER: Card Info / Verification
    // ──────────────────────────────────────────────────────────────
    private function handleCardInfo(Request $request, $schoolId, array $resolved)
    {
        $data = [
            'person_name' => $resolved['person_name'],
            'person_type' => $resolved['card_type'],
            'card_number' => $resolved['card_number'],
            'card_status' => $resolved['status'],
            'academic_year' => $resolved['academic_year'] ?? null,
            'issue_date' => $resolved['issue_date'] ?? null,
            'expiry_date' => $resolved['expiry_date'] ?? null,
            'class' => $resolved['class'] ?? null,
            'stream' => $resolved['stream'] ?? null,
            'photo' => $resolved['photo'] ?? null,
        ];

        return $this->logAndReturn(
            $schoolId,
            $resolved['card_number'],
            $resolved['card_type'],
            'info',
            'success',
            'Card verified: ' . $resolved['person_name'],
            $data
        );
    }

    // ══════════════════════════════════════════════════════════════
    //  ARRIVAL ATTENDANCE MANAGEMENT PAGE
    // ══════════════════════════════════════════════════════════════

    public function arrivalAttendancePage(Request $request)
    {
        PermissionHelper::denyUnlessFeature('manage_arrival_attendance');

        $schoolId = session('LoggedSchool');
        $date = $request->get('date', Carbon::today()->toDateString());
        $personType = $request->get('person_type', 'student');

        // Stats
        $stats = SchoolArrivalAttendance::where('school_id', $schoolId)
            ->where('attendance_date', $date)
            ->where('person_type', $personType)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status='present') as present,
                SUM(status='late') as late,
                SUM(status='absent') as absent,
                SUM(status='half_day') as half_day,
                SUM(status='excused') as excused,
                SUM(method='card_scan') as card_scans,
                SUM(method='manual') as manual_entries
            ")
            ->first();

        // Records
        $records = SchoolArrivalAttendance::where('school_id', $schoolId)
            ->where('attendance_date', $date)
            ->where('person_type', $personType)
            ->orderBy('arrival_time')
            ->get()
            ->map(function ($r) use ($personType) {
                $r->person_name = $r->person_name_attribute;
                if ($personType === 'student') {
                    $s = Student::find($r->person_id);
                    $r->extra = $s ? Helper::recordMdname($s->senior) : '';
                } else {
                    $t = Teacher::find($r->person_id);
                    $r->extra = $t ? ($t->phonenumber ?? '') : '';
                }
                return $r;
            });

        // Total enrolled (for "not arrived" count)
        $totalEnrolled = $personType === 'student'
            ? Student::where('school_id', $schoolId)->count()
            : Teacher::where('school_id', $schoolId)->count();

        // Trend (last 7 days)
        $trend = SchoolArrivalAttendance::where('school_id', $schoolId)
            ->where('person_type', $personType)
            ->where('attendance_date', '>=', Carbon::parse($date)->subDays(6)->toDateString())
            ->where('attendance_date', '<=', $date)
            ->selectRaw("attendance_date, SUM(status='present') as present, SUM(status='late') as late, COUNT(*) as total")
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();

        return view('Attendance.arrival-attendance', compact(
            'records',
            'stats',
            'date',
            'personType',
            'totalEnrolled',
            'trend'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  SAVE MANUAL ARRIVAL ATTENDANCE
    // ══════════════════════════════════════════════════════════════

    public function saveArrivalAttendance(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_arrival_attendance')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'person_id' => 'required|integer',
            'person_type' => 'required|in:student,teacher',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,late,absent,half_day,excused',
            'remarks' => 'nullable|string|max:255',
        ]);

        $schoolId = session('LoggedSchool');
        $arrivalTime = Carbon::now()->format('H:i:s'); // auto-set, not from request

        SchoolArrivalAttendance::updateOrCreate(
            [
                'school_id' => $schoolId,
                'person_id' => $request->person_id,
                'person_type' => $request->person_type,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'arrival_time' => $arrivalTime,
                'status' => $request->status,
                'method' => 'manual',
                'remarks' => $request->remarks,
                'recorded_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Arrival attendance saved.', 'arrival_time' => $arrivalTime]);
    }

    // ══════════════════════════════════════════════════════════════
    //  BULK SAVE ARRIVAL ATTENDANCE (all students/teachers at once)
    // ══════════════════════════════════════════════════════════════

    public function saveBulkArrivalAttendance(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_arrival_attendance')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'attendance_date' => 'required|date',
            'person_type' => 'required|in:student,teacher',
            'attendance' => 'required|array',
        ]);

        $schoolId = session('LoggedSchool');
        $arrivalTime = Carbon::now()->format('H:i:s');

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $personId => $data) {
                if (empty($data['status']))
                    continue;

                SchoolArrivalAttendance::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'person_id' => $personId,
                        'person_type' => $request->person_type,
                        'attendance_date' => $request->attendance_date,
                    ],
                    [
                        'arrival_time' => $arrivalTime,
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'method' => 'manual',
                        'recorded_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
                    ]
                );
            }
            DB::commit();
            return redirect()->back()->with('success', 'Arrival attendance saved for ' . count($request->attendance) . ' people.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  SCAN LOG / HISTORY PAGE
    // ══════════════════════════════════════════════════════════════

    public function scanLogs(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_scan_logs');

        $schoolId = session('LoggedSchool');
        $category = $request->category;
        $date = $request->date ?? Carbon::today()->toDateString();
        $result = $request->result;

        $query = CardScanLog::where('school_id', $schoolId)
            ->whereDate('created_at', $date);

        if ($category)
            $query->where('scan_category', $category);
        if ($result)
            $query->where('scan_result', $result);

        $logs = $query->orderByDesc('created_at')->paginate(50);

        // Category summary for today
        $categorySummary = CardScanLog::where('school_id', $schoolId)
            ->whereDate('created_at', $date)
            ->selectRaw("scan_category, scan_result, COUNT(*) as cnt")
            ->groupBy('scan_category', 'scan_result')
            ->get();

        return view('CardScan.logs', compact('logs', 'categorySummary', 'category', 'date', 'result'));
    }

    // ══════════════════════════════════════════════════════════════
    //  AJAX: Arrival Attendance Report
    // ══════════════════════════════════════════════════════════════

    public function arrivalReport(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_arrival_reports');

        $schoolId = session('LoggedSchool');
        $from = $request->from ?? Carbon::today()->startOfMonth()->toDateString();
        $to = $request->to ?? Carbon::today()->toDateString();
        $personType = $request->person_type ?? 'student';

        $summary = SchoolArrivalAttendance::where('school_id', $schoolId)
            ->where('person_type', $personType)
            ->whereBetween('attendance_date', [$from, $to])
            ->selectRaw("
                person_id,
                COUNT(*) as total_days,
                SUM(status='present') as present,
                SUM(status='late') as late,
                SUM(status='absent') as absent,
                ROUND(SUM(status IN ('present','late'))/COUNT(*)*100,1) as attendance_rate,
                AVG(TIME_TO_SEC(arrival_time))/3600 as avg_arrival_hour
            ")
            ->groupBy('person_id')
            ->get()
            ->map(function ($row) use ($personType) {
                if ($personType === 'student') {
                    $p = Student::find($row->person_id);
                    $row->name = $p ? trim($p->firstname . ' ' . $p->lastname) : 'N/A';
                    $row->extra = $p ? Helper::recordMdname($p->senior) : '';
                } else {
                    $p = Teacher::find($row->person_id);
                    $row->name = $p ? trim($p->firstname . ' ' . $p->surname) : 'N/A';
                    $row->extra = '';
                }
                return $row;
            })
            ->sortByDesc('attendance_rate');

        return view('Attendance.arrival-report', compact(
            'summary',
            'from',
            'to',
            'personType'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  ATTENDANCE HOURS (per-school late time / closing time / alerts)
    // ══════════════════════════════════════════════════════════════

    public function attendanceHours()
    {
        PermissionHelper::denyUnlessFeature('manage_arrival_attendance');

        $schoolId = session('LoggedSchool');
        $settings = SchoolAttendanceSetting::forSchool($schoolId);

        $since = Carbon::now()->subDays(7);
        $notifStats = ['sent' => 0, 'failed' => 0];
        $recentIssues = collect();

        try {
            $rows = ParentNotificationLog::where('school_id', $schoolId)
                ->where('created_at', '>=', $since)
                ->whereIn('status', ['sent', 'failed'])
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
            $notifStats['sent'] = (int) ($rows['sent'] ?? 0);
            $notifStats['failed'] = (int) ($rows['failed'] ?? 0);

            $recentIssues = ParentNotificationLog::with('student')
                ->where('school_id', $schoolId)
                ->where('status', 'failed')
                ->orderByDesc('created_at')
                ->limit(15)
                ->get();
        } catch (\Throwable $e) {
            // migration not run yet – page still renders
        }

        return view('CardScan.hours', compact('settings', 'notifStats', 'recentIssues'));
    }

    public function saveAttendanceHours(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_arrival_attendance')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to change attendance hours.');
        }

        $data = $request->validate([
            'student_late_time' => 'required|date_format:H:i',
            'teacher_late_time' => 'required|date_format:H:i',
            'grace_minutes' => 'required|integer|min:0|max:120',
            'student_closing_time' => 'required|date_format:H:i',
            'teacher_closing_time' => 'required|date_format:H:i',
            'min_minutes_before_signout' => 'required|integer|min:0|max:240',
        ]);

        // A school can't close before it opens.
        if ($data['student_closing_time'] <= $data['student_late_time'] || $data['teacher_closing_time'] <= $data['teacher_late_time']) {
            return redirect()->back()->withInput()
                ->with('error', 'Closing time must be later than the late-arrival time.');
        }

        SchoolAttendanceSetting::updateOrCreate(
            ['school_id' => session('LoggedSchool')],
            $data + [
                'notify_parent_on_arrival' => $request->boolean('notify_parent_on_arrival'),
                'notify_parent_on_signout' => $request->boolean('notify_parent_on_signout'),
            ]
        );

        return redirect()->route('card-scan.hours')->with('success', 'Attendance hours saved.');
    }

    // ══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    private function parseCardNumber(string $raw): array
    {
        try {
            $parsed = json_decode($raw, true);
            if (isset($parsed['card'])) {
                return ['card' => $parsed['card'], 'raw' => $raw];
            }
        } catch (\Exception $e) {
        }
        return ['card' => $raw, 'raw' => $raw];
    }

    private function resolveCard(string $cardNumber, $schoolId): array
    {
        // Try student card first
        $studentCard = StudentIdCard::where('card_number', $cardNumber)->first();
        if ($studentCard) {
            $student = Student::find($studentCard->student_id);
            if (!$student) {
                return ['found' => false, 'message' => 'Student record not found for this card.'];
            }
            $className = Helper::recordMdname($student->senior ?? '');
            $streamName = Helper::recordMdname($student->stream ?? '');
            $photo = $this->resolveStudentPhoto($student);

            return [
                'found' => true,
                'active' => $studentCard->status === 'active',
                'status' => $studentCard->status,
                'card_type' => 'student',
                'card_number' => $cardNumber,
                'card_id' => $studentCard->id,
                'person_id' => $student->id,
                'person_name' => trim($student->firstname . ' ' . $student->lastname),
                'class' => $className,
                'stream' => $streamName,
                'academic_year' => $studentCard->academic_year,
                'issue_date' => $studentCard->issue_date?->format('d M Y'),
                'expiry_date' => $studentCard->expiry_date?->format('d M Y'),
                'photo' => $photo,
            ];
        }

        // Try teacher card
        $teacherCard = TeacherIdCard::where('card_number', $cardNumber)->first();
        if ($teacherCard) {
            $teacher = Teacher::find($teacherCard->teacher_id);
            if (!$teacher) {
                return ['found' => false, 'message' => 'Teacher record not found for this card.'];
            }

            return [
                'found' => true,
                'active' => $teacherCard->status === 'active',
                'status' => $teacherCard->status,
                'card_type' => 'teacher',
                'card_number' => $cardNumber,
                'card_id' => $teacherCard->id,
                'person_id' => $teacher->id,
                'person_name' => trim($teacher->firstname . ' ' . $teacher->surname),
                'class' => null,
                'stream' => null,
                'academic_year' => $teacherCard->academic_year,
                'issue_date' => $teacherCard->issue_date?->format('d M Y'),
                'expiry_date' => $teacherCard->expiry_date?->format('d M Y'),
                'photo' => null,
            ];
        }

        return ['found' => false, 'message' => 'No card found with number: ' . $cardNumber];
    }

    private function getLibraryMember($schoolId, $personId, $personType): ?LibraryMember
    {
        return LibraryMember::where('school_id', $schoolId)
            ->where('member_type', $personType)
            ->where('member_id', $personId)
            ->first();
    }

    /** @deprecated kept for callers; use SchoolAttendanceSetting directly. */
    private function getSchoolLateTime($schoolId): string
    {
        return SchoolAttendanceSetting::forSchool($schoolId)->lateTimeFor('student');
    }

    private function resolveStudentPhoto(Student $student): ?string
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

    private function logAndReturn($schoolId, $cardNumber, $cardType, $category, $result, $message, array $data)
    {
        // Log the scan
        CardScanLog::create([
            'school_id' => $schoolId,
            'card_number' => $cardNumber,
            'card_type' => $cardType ?? 'unknown',
            'scan_category' => $category,
            'scan_result' => $result,
            'result_message' => $message,
            'result_data' => $data,
            'scanned_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
            'scanned_by_type' => session('LoggedTeacher') ? 'teacher' : 'admin',
        ]);

        return response()->json([
            'success' => $result === 'success',
            'result' => $result,
            'category' => $category,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function getScanCategories(): array
    {
        return [
            ['key' => 'attendance_arrival', 'label' => 'School Arrival', 'icon' => 'fa-school', 'color' => '#2f2ccb', 'desc' => 'Record school gate arrival for students & teachers'],
            ['key' => 'attendance_departure', 'label' => 'School Sign-Out', 'icon' => 'fa-door-open', 'color' => '#be123c', 'desc' => 'Record students & teachers leaving school (early / on time)'],
            ['key' => 'attendance_class', 'label' => 'Class Attendance', 'icon' => 'fa-chalkboard-teacher', 'color' => '#7c3aed', 'desc' => 'Look up class for manual class attendance marking'],
            ['key' => 'library_issue', 'label' => 'Issue Book', 'icon' => 'fa-book-open', 'color' => '#059669', 'desc' => 'Issue a library book to the card holder'],
            ['key' => 'library_return', 'label' => 'Return Book', 'icon' => 'fa-undo', 'color' => '#0891b2', 'desc' => 'Return a borrowed book via card scan'],
            ['key' => 'library_reserve', 'label' => 'Reserve Book', 'icon' => 'fa-bookmark', 'color' => '#d97706', 'desc' => 'Reserve an unavailable book'],
            ['key' => 'finance_balance', 'label' => 'Fee Balance', 'icon' => 'fa-wallet', 'color' => '#1d4ed8', 'desc' => 'Check student fee balance via card'],
            ['key' => 'finance_payment', 'label' => 'Fee Payment', 'icon' => 'fa-cash-register', 'color' => '#15803d', 'desc' => 'Start a fee payment process via card scan'],
            ['key' => 'info', 'label' => 'Card Info', 'icon' => 'fa-id-card', 'color' => '#64748b', 'desc' => 'Verify card and view person details'],
        ];
    }
}