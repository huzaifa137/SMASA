<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use App\Models\FinanceTransaction;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use App\Models\Student;
use App\Models\StudentFeeAllocation;
use App\Models\Teacher;
use App\Models\TeacherSalaryStructure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Classroom;
use App\Models\Stream;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\PermissionHelper;

class FinanceController extends Controller
{

    public function dashboard()
    {
        PermissionHelper::denyUnlessFeature('view_finance');

        $schoolId = session('LoggedSchool');
        $year = date('Y');

        // ── Income (fee payments confirmed this year) ─────────────────────
        $totalIncome = FeePayment::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->where('status', 'confirmed')
            ->sum('amount_paid');

        // ── Total Expenses this year ──────────────────────────────────────
        $totalExpenses = Expense::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        // ── Payroll this year ─────────────────────────────────────────────
        $totalPayroll = PayrollSlip::where('school_id', $schoolId)
            ->whereHas('period', function ($q) use ($year) {
                $q->where('academic_year', $year)
                    ->where('status', 'paid');
            })
            ->sum('net_pay');

        // ── Fee collection stats ──────────────────────────────────────────
        $feeStats = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->selectRaw("
            COUNT(*) as total_students,
            SUM(allocated_amount - discount_amount) as total_billed,
            SUM(CASE WHEN payment_status='paid' THEN 1 ELSE 0 END) as fully_paid,
            SUM(CASE WHEN payment_status='partial' THEN 1 ELSE 0 END) as partial,
            SUM(CASE WHEN payment_status='unpaid' THEN 1 ELSE 0 END) as unpaid
        ")
            ->first();

        $totalBilled = $feeStats->total_billed ?? 0;
        $outstanding = $totalBilled - $totalIncome;

        // ── Monthly income trend (last 6 months) ─────────────────────────
        $monthlyTrend = FeePayment::where('school_id', $schoolId)
            ->where('status', 'confirmed')
            ->where('payment_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount_paid) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ── Expense breakdown by category ────────────────────────────────
        $expenseBreakdown = Expense::where('expenses.school_id', $schoolId)
            ->where('expenses.academic_year', $year)
            ->whereIn('expenses.status', ['approved', 'paid'])
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->selectRaw('
            expense_categories.name as cat_name,
            expense_categories.color as cat_color,
            SUM(expenses.amount) as total
        ')
            ->groupBy(
                'expense_categories.id',
                'expense_categories.name',
                'expense_categories.color'
            )
            ->orderByDesc('total')
            ->get();

        // ── Recent transactions ───────────────────────────────────────────
        $recentPayments = FeePayment::where('school_id', $schoolId)
            ->where('status', 'confirmed')
            ->with('student')
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get();

        // ── Pending approvals ─────────────────────────────────────────────
        $pendingExpenses = Expense::where('school_id', $schoolId)
            ->where('status', 'draft')
            ->count();

        $pendingPayroll = PayrollPeriod::where('school_id', $schoolId)
            ->where('status', 'processing')
            ->count();

        return view('Finance.dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'totalPayroll',
            'outstanding',
            'feeStats',
            'totalBilled',
            'monthlyTrend',
            'expenseBreakdown',
            'recentPayments',
            'pendingExpenses',
            'pendingPayroll',
            'year'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FEE STRUCTURES
    // ══════════════════════════════════════════════════════════════════════════

    public function feeStructures()
    {
        PermissionHelper::denyUnlessFeature('manage_fees');
        $schoolId = session('LoggedSchool');
        $structures = FeeStructure::where('school_id', $schoolId)
            ->orderByDesc('academic_year')
            ->orderBy('term')
            ->get();

        return view('Finance.fee-structures', compact('structures'));
    }

    public function createFeeStructure()
    {

        PermissionHelper::denyUnlessFeature('manage_fees');

        $schoolId = session('LoggedSchool');
        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('class_name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->class_name,
                'name' => \App\Http\Controllers\Helper::recordMdname($c->class_name) ?? $c->class_name,
            ]);

        return view('Finance.fee-structure-form', compact('classrooms'));
    }

    public function storeFeeStructure(Request $request)
    {

        if (!PermissionHelper::canFeature('manage_fees')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to manage fee structures.'], 403);
        }


        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'academic_year' => 'required|digits:4',
            'term' => 'required|in:1,2,3',
            'class_level' => 'nullable|string|max:50',
            'student_type' => 'required|in:boarding,day,all',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.category' => 'required|string|max:100',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.is_mandatory' => 'boolean',
        ]);

        $schoolId = session('LoggedSchool');

        DB::beginTransaction();
        try {
            $structure = FeeStructure::create([
                'school_id' => $schoolId,
                'name' => $validated['name'],
                'academic_year' => $validated['academic_year'],
                'term' => $validated['term'],
                'class_level' => $validated['class_level'] ?? null,
                'student_type' => $validated['student_type'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => session('LoggedUser'),
            ]);

            $total = 0;
            foreach ($validated['items'] as $i => $item) {
                $structure->items()->create([
                    'item_name' => $item['item_name'],
                    'category' => $item['category'],
                    'amount' => $item['amount'],
                    'is_mandatory' => $item['is_mandatory'] ?? true,
                    'sort_order' => $i,
                ]);
                $total += $item['amount'];
            }

            $structure->update(['total_amount' => $total]);
            DB::commit();

            return redirect()->route('finance.fee-structures.index')
                ->with('success', 'Fee structure created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create fee structure: ' . $e->getMessage());
        }
    }

    public function editFeeStructure(int $id)
    {

        PermissionHelper::denyUnlessFeature('manage_fees');

        $schoolId = session('LoggedSchool');
        $structure = FeeStructure::where('school_id', $schoolId)->with('items')->findOrFail($id);

        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('class_name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->class_name,
                'name' => \App\Http\Controllers\Helper::recordMdname($c->class_name) ?? $c->class_name,
            ]);

        return view('Finance.fee-structure-form', compact('structure', 'classrooms'));
    }

    public function updateFeeStructure(Request $request, int $id)
    {

        if (!PermissionHelper::canFeature('manage_fees')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $structure = FeeStructure::where('school_id', $schoolId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'class_level' => 'nullable|string|max:50',
            'student_type' => 'required|in:boarding,day,all',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.category' => 'required|string|max:100',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $structure->items()->delete();
            $total = 0;
            foreach ($validated['items'] as $i => $item) {
                $structure->items()->create([
                    'item_name' => $item['item_name'],
                    'category' => $item['category'],
                    'amount' => $item['amount'],
                    'is_mandatory' => $item['is_mandatory'] ?? true,
                    'sort_order' => $i,
                ]);
                $total += $item['amount'];
            }

            $structure->update([
                'name' => $validated['name'],
                'class_level' => $validated['class_level'] ?? null,
                'student_type' => $validated['student_type'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $total,
            ]);

            DB::commit();
            return redirect()->route('finance.fee-structures.index')
                ->with('success', 'Fee structure updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function deleteFeeStructure(int $id)
    {

        if (!PermissionHelper::canFeature('manage_fees')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to delete fee structures.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $structure = FeeStructure::where('school_id', $schoolId)->findOrFail($id);
        $structure->delete();
        return back()->with('success', 'Fee structure deleted.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FEE ALLOCATION TO STUDENTS
    // ══════════════════════════════════════════════════════════════════════════

    public function feeAllocations()
    {
        PermissionHelper::denyUnlessFeature('manage_fees');
        $schoolId = session('LoggedSchool');
        $year = request('year', date('Y'));
        $term = request('term', '');

        $query = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->with(['student', 'feeStructure']);

        if ($term)
            $query->where('term', $term);

        $allocations = $query->orderByDesc('created_at')->paginate(5);

        $structures = FeeStructure::where('school_id', $schoolId)
            ->where('academic_year', $year)->get();

        $students = Student::where('school_id', $schoolId)->orderBy('firstname')->get();

        // Add this line to load classrooms
        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('class_name')
            ->get();

        // Summary
        $summary = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->when($term, fn($q) => $q->where('term', $term))
            ->selectRaw("
            SUM(allocated_amount - discount_amount) as total_billed,
            SUM(CASE WHEN payment_status='paid' THEN (allocated_amount - discount_amount) ELSE 0 END) as total_collected,
            COUNT(*) as total_students,
            SUM(balance) as total_outstanding
        ")->first();

        return view('Finance.fee-allocations', compact('allocations', 'structures', 'students', 'summary', 'year', 'term', 'classrooms'));
    }

    public function allocateFees(Request $request)
    {

        if (!PermissionHelper::canFeature('manage_fees')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
        ]);

        $schoolId = session('LoggedSchool');
        $structure = FeeStructure::findOrFail($validated['fee_structure_id']);
        $discount = $validated['discount_amount'] ?? 0;

        // Server-side validation: discount cannot exceed structure total
        if ($discount > $structure->total_amount) {
            return back()->with('error', 'Discount cannot exceed the fee structure amount (UGX ' . number_format($structure->total_amount, 0) . ')');
        }

        $created = 0;
        foreach ($validated['student_ids'] as $studentId) {
            $existing = StudentFeeAllocation::where([
                'student_id' => $studentId,
                'fee_structure_id' => $structure->id,
                'academic_year' => $structure->academic_year,
                'term' => $structure->term,
            ])->first();

            if (!$existing) {
                $net = $structure->total_amount - $discount;
                StudentFeeAllocation::create([
                    'school_id' => $schoolId,
                    'student_id' => $studentId,
                    'fee_structure_id' => $structure->id,
                    'academic_year' => $structure->academic_year,
                    'term' => $structure->term,
                    'allocated_amount' => $structure->total_amount,
                    'discount_amount' => $discount,
                    'discount_reason' => $validated['discount_reason'] ?? null,
                    'balance' => $net,
                    'payment_status' => 'unpaid',
                    'allocated_by' => session('LoggedUser'),
                ]);
                $created++;
            }
        }

        return back()->with('success', "Fees allocated to {$created} student(s).");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FEE PAYMENTS
    // ══════════════════════════════════════════════════════════════════════════

    public function payments()
    {

        PermissionHelper::denyUnlessFeature('view_finance');

        $schoolId = session('LoggedSchool');
        $year = request('year', date('Y'));
        $term = request('term', '');
        $search = request('search', '');

        $query = FeePayment::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->with('student')
            ->orderByDesc('payment_date');

        if ($term)
            $query->where('term', $term);
        if ($search) {
            $query->whereHas(
                'student',
                fn($q) =>
                $q->where('firstname', 'like', "%$search%")
                    ->orWhere('lastname', 'like', "%$search%")
                    ->orWhere('admission_number', 'like', "%$search%")
            )->orWhere('receipt_number', 'like', "%$search%");
        }

        $payments = $query->paginate(25);

        $totals = FeePayment::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->where('status', 'confirmed')
            ->when($term, fn($q) => $q->where('term', $term))
            ->selectRaw("SUM(amount_paid) as total, COUNT(*) as count")->first();

        return view('Finance.payments', compact('payments', 'totals', 'year', 'term', 'search'));
    }

    public function createPayment()
    {
        PermissionHelper::denyUnlessFeature('record_payment');

        $schoolId = session('LoggedSchool');
        $receiptNum = FeePayment::generateReceiptNumber($schoolId);

        // Load classes that belong to this school
        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('class_name')
            ->get();

        return view('Finance.payment-form', compact('classrooms', 'receiptNum'));
    }

    public function getStudentAllocations(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $student = Student::where('school_id', $schoolId)->findOrFail($request->student_id);

        $allocations = StudentFeeAllocation::where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->where('academic_year', $request->year ?? date('Y'))
            ->with('feeStructure')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'label' => "Term {$a->term} — Balance: UGX " . number_format($a->balance, 0),
                'balance' => $a->balance,
                'term' => $a->term,
                'status' => $a->payment_status,
            ]);

        return response()->json(['student' => $student, 'allocations' => $allocations]);
    }

    public function storePayment(Request $request)
    {

        if (!PermissionHelper::canFeature('record_payment')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to record payments.'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'allocation_id' => 'nullable|exists:student_fee_allocations,id',
            'academic_year' => 'required|digits:4',
            'term' => 'required|in:1,2,3',
            'amount_paid' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque,other',
            'transaction_reference' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $schoolId = session('LoggedSchool');
        $receiptNum = FeePayment::generateReceiptNumber($schoolId);

        DB::beginTransaction();
        try {
            $payment = FeePayment::create([
                'receipt_number' => $receiptNum,
                'school_id' => $schoolId,
                'student_id' => $validated['student_id'],
                'allocation_id' => $validated['allocation_id'] ?? null,
                'academic_year' => $validated['academic_year'],
                'term' => $validated['term'],
                'amount_paid' => $validated['amount_paid'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'confirmed',
                'received_by' => session('LoggedUser'),
                'confirmed_by' => session('LoggedUser'),
                'confirmed_at' => now(),
            ]);

            // Update allocation balance. allocation_id is only ever set when
            // the form's allocation dropdown was used — payments recorded
            // without picking one (e.g. a quick payment before an allocation
            // dropdown loads) would otherwise never call syncBalance(),
            // leaving that student's balance/"amount paid" permanently
            // stale even though the payment itself was recorded fine.
            $allocation = $validated['allocation_id']
                ? StudentFeeAllocation::find($validated['allocation_id'])
                : StudentFeeAllocation::where('school_id', $schoolId)
                    ->where('student_id', $validated['student_id'])
                    ->where('academic_year', $validated['academic_year'])
                    ->where('term', $validated['term'])
                    ->first();
            $allocation?->syncBalance();

            // Log transaction — posted to the Tuition Fees Income ledger account
            FinanceTransaction::log(
                $schoolId,
                'income',
                'fee_payment',
                $payment->id,
                $validated['amount_paid'],
                "Fee payment by student #" . $validated['student_id'] . " — Receipt {$receiptNum}",
                $validated['payment_date'],
                $validated['academic_year'],
                $validated['term'],
                session('LoggedUser'),
                ChartOfAccount::findOrCreateDefault($schoolId, ChartOfAccount::CODE_TUITION_INCOME)->id
            );

            DB::commit();

            \App\Services\NotificationService::sendToAllAdmins([
                'title' => 'Fee Payment Received',
                'body' => "UGX " . number_format($validated['amount_paid'], 0) . " received (Receipt {$receiptNum}).",
                'type' => \App\Models\SmasaNotification::TYPE_FEE,
                'module' => 'finance',
                'triggered_by' => session('LoggedAdmin') ?? session('LoggedTeacher'),
            ], $schoolId);

            return redirect()->route('finance.payments')
                ->with('success', "Payment recorded. Receipt: {$receiptNum}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function receiptPdf(int $id)
    {
        PermissionHelper::denyUnlessFeature('view_finance');
        $schoolId = session('LoggedSchool');
        $payment = FeePayment::where('school_id', $schoolId)
            ->with(['student', 'allocation.feeStructure'])
            ->findOrFail($id);

        $school = \App\Models\School::find($schoolId);

        $pdf = Pdf::loadView('Finance.pdf.receipt', compact('payment', 'school'))
            ->setPaper('a5', 'portrait');
        return $pdf->stream("Receipt-{$payment->receipt_number}.pdf");
    }

    public function reversePayment(Request $request, int $id)
    {

        if (!PermissionHelper::canFeature('record_payment')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to reverse payments.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $payment = FeePayment::where('school_id', $schoolId)->findOrFail($id);
        $payment->update(['status' => 'reversed']);

        $allocation = $payment->allocation_id
            ? StudentFeeAllocation::find($payment->allocation_id)
            : StudentFeeAllocation::where('school_id', $schoolId)
                ->where('student_id', $payment->student_id)
                ->where('academic_year', $payment->academic_year)
                ->where('term', $payment->term)
                ->first();
        $allocation?->syncBalance();

        return back()->with('success', 'Payment reversed successfully.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPENSES
    // ══════════════════════════════════════════════════════════════════════════

    public function expenses()
    {

        PermissionHelper::denyUnlessFeature('manage_expenses');

        $schoolId = session('LoggedSchool');
        $year = request('year', date('Y'));
        $categoryId = request('category_id', '');
        $search = request('search', '');

        $query = Expense::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->with('category')
            ->orderByDesc('expense_date');

        if ($categoryId)
            $query->where('category_id', $categoryId);
        if ($search)
            $query->where(fn($q) => $q->where('title', 'like', "%$search%")
                ->orWhere('payee_name', 'like', "%$search%")
                ->orWhere('expense_number', 'like', "%$search%"));

        $expenses = $query->paginate(25);
        $categories = ExpenseCategory::where('school_id', $schoolId)->where('is_active', true)->get();

        $totals = Expense::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->whereIn('status', ['approved', 'paid'])
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->selectRaw("SUM(amount) as total, COUNT(*) as count")->first();

        return view('Finance.expenses', compact('expenses', 'categories', 'totals', 'year', 'categoryId', 'search'));
    }

    public function createExpense()
    {
        PermissionHelper::denyUnlessFeature('manage_expenses');

        $schoolId = session('LoggedSchool');
        $categories = ExpenseCategory::where('school_id', $schoolId)->where('is_active', true)->get();
        $expNum = Expense::generateExpenseNumber($schoolId);
        return view('Finance.expense-form', compact('categories', 'expNum'));
    }

    public function storeExpense(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_expenses')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to add expenses.'], 403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'academic_year' => 'required|digits:4',
            'term' => 'nullable|in:1,2,3',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque,other',
            'payee_name' => 'nullable|string|max:200',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $schoolId = session('LoggedSchool');

        DB::beginTransaction();
        try {
            $expense = Expense::create(array_merge($validated, [
                'expense_number' => Expense::generateExpenseNumber($schoolId),
                'school_id' => $schoolId,
                'status' => 'paid',
                'submitted_by' => session('LoggedUser'),
                'approved_by' => session('LoggedUser'),
                'approved_at' => now(),
            ]));

            $category = ExpenseCategory::find($validated['category_id']);
            $expenseAccount = ChartOfAccount::findOrCreateForExpenseCategory(
                $schoolId,
                $validated['category_id'],
                $category->name ?? 'Uncategorized Expense'
            );

            FinanceTransaction::log(
                $schoolId,
                'expense',
                'expense',
                $expense->id,
                $validated['amount'],
                "Expense: {$validated['title']}",
                $validated['expense_date'],
                $validated['academic_year'],
                $validated['term'] ?? null,
                session('LoggedUser'),
                $expenseAccount->id
            );

            DB::commit();
            return redirect()->route('finance.expenses.index')
                ->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function editExpense(int $id)
    {

        PermissionHelper::denyUnlessFeature('manage_expenses');


        $schoolId = session('LoggedSchool');
        $expense = Expense::where('school_id', $schoolId)->findOrFail($id);
        $categories = ExpenseCategory::where('school_id', $schoolId)->where('is_active', true)->get();
        return view('Finance.expense-form', compact('expense', 'categories'));
    }

    public function updateExpense(Request $request, int $id)
    {

        if (!PermissionHelper::canFeature('manage_expenses')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $expense = Expense::where('school_id', $schoolId)->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payee_name' => 'nullable|string|max:200',
        ]);

        $expense->update($validated);
        return redirect()->route('finance.expenses.index')->with('success', 'Expense updated.');
    }

    public function deleteExpense(int $id)
    {

        if (!PermissionHelper::canFeature('manage_expenses')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to delete expenses.'], 403);
        }

        $schoolId = session('LoggedSchool');
        Expense::where('school_id', $schoolId)->findOrFail($id)->delete();
        return back()->with('success', 'Expense deleted.');
    }

    // ── Expense Categories ─────────────────────────────────────────────────

    public function expenseCategories()
    {

        PermissionHelper::denyUnlessFeature('manage_expenses');


        $schoolId = session('LoggedSchool');
        $categories = ExpenseCategory::where('school_id', $schoolId)
            ->withCount('expenses')
            ->withSum('expenses', 'amount')
            ->get();
        return view('Finance.expense-categories', compact('categories'));
    }

    public function storeExpenseCategory(Request $request)
    {

        if (!PermissionHelper::canFeature('manage_expenses')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:10',
            'icon' => 'required|string|max:50',
        ]);

        ExpenseCategory::create(array_merge($validated, [
            'school_id' => session('LoggedSchool'),
        ]));

        return back()->with('success', 'Category added.');
    }

    public function deleteExpenseCategory(int $id)
    {

        if (!PermissionHelper::canFeature('manage_expenses')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        ExpenseCategory::where('school_id', $schoolId)->findOrFail($id)->delete();
        return back()->with('success', 'Category deleted.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAYROLL
    // ══════════════════════════════════════════════════════════════════════════

    public function payroll()
    {

        PermissionHelper::denyUnlessFeature('manage_payroll');  // human_resources module feature

        $schoolId = session('LoggedSchool');
        $periods = PayrollPeriod::where('school_id', $schoolId)
            ->orderByDesc('period_start')->get();
        return view('Finance.payroll', compact('periods'));
    }

    public function createPayrollPeriod()
    {

        PermissionHelper::denyUnlessFeature('manage_payroll');

        $schoolId = session('LoggedSchool');

        $teachers = Teacher::where('school_id', $schoolId)
            ->with(['salaryStructure' => fn($q) => $q->where('school_id', $schoolId)])
            ->get();

        $teachersWithStructure = $teachers->filter(fn($t) => $t->salaryStructure !== null);
        $totalGross = $teachersWithStructure->sum(fn($t) => $t->salaryStructure->grossPay());

        return view('Finance.payroll-period-form', compact(
            'teachers',
            'teachersWithStructure',
            'totalGross'
        ));
    }

    public function storePayrollPeriod(Request $request)
    {

        if (!PermissionHelper::canFeature('manage_payroll')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to manage payroll.'], 403);
        }

        $validated = $request->validate([
            'period_name' => 'required|string|max:100',
            'academic_year' => 'required|digits:4',
            'term' => 'nullable|in:1,2,3',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $schoolId = session('LoggedSchool');

        // Fetch all teachers + their salary structures
        $teachers = Teacher::where('school_id', $schoolId)->get();

        DB::beginTransaction();
        try {
            $period = PayrollPeriod::create(array_merge($validated, [
                'school_id' => $schoolId,
                'status' => 'draft',
                'created_by' => session('LoggedUser'),
            ]));

            foreach ($teachers as $teacher) {
                $structure = TeacherSalaryStructure::where('teacher_id', $teacher->id)
                    ->where('school_id', $schoolId)->first();

                if ($structure) {
                    $slip = new PayrollSlip([
                        'payroll_period_id' => $period->id,
                        'school_id' => $schoolId,
                        'teacher_id' => $teacher->id,
                        'payslip_number' => PayrollSlip::generateSlipNumber($schoolId),
                        'loan_deduction' => 0,
                        'other_deductions' => 0,
                        'status' => 'draft',
                    ]);
                    $slip->computeFromStructure($structure);
                    $slip->save();
                }
            }

            $period->recalculateTotals();
            DB::commit();

            return redirect()->route('finance.payroll.show', $period->id)
                ->with('success', 'Payroll period created with ' . $teachers->count() . ' payslips.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function showPayrollPeriod(int $id)
    {
        $schoolId = session('LoggedSchool');
        $period = PayrollPeriod::where('school_id', $schoolId)
            ->with(['slips.teacher'])->findOrFail($id);
        return view('Finance.payroll-period', compact('period'));
    }

    public function approvePayrollPeriod(int $id)
    {

        if (!PermissionHelper::canFeature('manage_payroll')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $period = PayrollPeriod::where('school_id', $schoolId)->findOrFail($id);
        $period->update([
            'status' => 'approved',
            'approved_by' => session('LoggedUser'),
            'approved_at' => now(),
        ]);
        $period->slips()->update(['status' => 'approved']);
        return back()->with('success', 'Payroll approved.');
    }

    public function markPayrollPaid(int $id)
    {

        if (!PermissionHelper::canFeature('manage_payroll')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $period = PayrollPeriod::where('school_id', $schoolId)->findOrFail($id);
        $period->update(['status' => 'paid']);
        $period->slips()->update(['status' => 'paid', 'paid_date' => now()->toDateString()]);

        // Log each slip as a transaction — posted to the Salaries & Wages account
        $salariesAccount = ChartOfAccount::findOrCreateDefault($schoolId, ChartOfAccount::CODE_SALARIES_EXPENSE);
        foreach ($period->slips as $slip) {
            FinanceTransaction::log(
                $schoolId,
                'expense',
                'payroll',
                $slip->id,
                $slip->net_pay,
                "Salary: Teacher #{$slip->teacher_id} — {$period->period_name}",
                now()->toDateString(),
                $period->academic_year,
                $period->term,
                session('LoggedUser'),
                $salariesAccount->id
            );
        }

        return back()->with('success', 'Payroll marked as paid.');
    }

    // ── Teacher Salary Structures ─────────────────────────────────────────

    public function salaryStructures()
    {
        PermissionHelper::denyUnlessFeature('manage_payroll');

        $schoolId = session('LoggedSchool');
        $teachers = Teacher::where('school_id', $schoolId)
            ->with(['salaryStructure' => fn($q) => $q->where('school_id', $schoolId)])
            ->get();
        return view('Finance.salary-structures', compact('teachers'));
    }

    public function storeSalaryStructure(Request $request)
    {

        if (!PermissionHelper::canFeature('manage_payroll')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
            'apply_paye' => 'boolean',
            'apply_nssf' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $schoolId = session('LoggedSchool');

        TeacherSalaryStructure::updateOrCreate(
            ['teacher_id' => $validated['teacher_id'], 'school_id' => $schoolId],
            array_merge($validated, ['school_id' => $schoolId, 'set_by' => session('LoggedUser')])
        );

        return back()->with('success', 'Salary structure saved.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BUDGETS
    // ══════════════════════════════════════════════════════════════════════════

    public function budgets()
    {
        PermissionHelper::denyUnlessFeature('view_finance');

        $schoolId = session('LoggedSchool');
        $budgets = Budget::where('school_id', $schoolId)->orderByDesc('academic_year')->get();
        return view('Finance.budgets', compact('budgets'));
    }

    public function createBudget()
    {
        PermissionHelper::denyUnlessFeature('view_finance');

        $schoolId = session('LoggedSchool');
        $categories = ExpenseCategory::where('school_id', $schoolId)->get();
        return view('Finance.budget-form', compact('categories'));
    }

    public function storeBudget(Request $request)
    {

      if (!PermissionHelper::canFeature('view_finance')) {
        return response()->json(['message' => 'Unauthorized.'], 403);
    }

        $schoolId = session('LoggedSchool');

        // Reshape nested income/expense arrays into a flat items array with type field
        $rawItems = [];
        foreach ($request->input('items.income', []) as $item) {
            if (!empty($item['item_name'])) {
                $rawItems[] = [
                    'item_name' => $item['item_name'],
                    'type' => 'income',
                    'budgeted_amount' => $item['budgeted_amount'] ?? 0,
                    'category_id' => $item['category_id'] ?? null,
                ];
            }
        }
        foreach ($request->input('items.expense', []) as $item) {
            if (!empty($item['item_name'])) {
                $rawItems[] = [
                    'item_name' => $item['item_name'],
                    'type' => 'expense',
                    'budgeted_amount' => $item['budgeted_amount'] ?? 0,
                    'category_id' => $item['category_id'] ?? null,
                ];
            }
        }

        $request->merge(['items' => $rawItems]);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'academic_year' => 'required|digits:4',
            'term' => 'nullable|in:1,2,3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.type' => 'required|in:income,expense',
            'items.*.budgeted_amount' => 'required|numeric|min:0',
            'items.*.category_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $budget = Budget::create([
                'school_id' => $schoolId,
                'title' => $validated['title'],
                'academic_year' => $validated['academic_year'],
                'term' => $validated['term'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => session('LoggedUser'),
            ]);

            $totalIncome = 0;
            $totalExpense = 0;

            foreach ($validated['items'] as $item) {
                $budget->items()->create([
                    'item_name' => $item['item_name'],
                    'type' => $item['type'],
                    'budgeted_amount' => $item['budgeted_amount'],
                    'category_id' => $item['category_id'] ?? null,
                ]);

                if ($item['type'] === 'income') {
                    $totalIncome += $item['budgeted_amount'];
                } else {
                    $totalExpense += $item['budgeted_amount'];
                }
            }

            $budget->update([
                'total_income_budget' => $totalIncome,
                'total_expense_budget' => $totalExpense,
            ]);

            DB::commit();
            return redirect()->route('finance.budgets.index')
                ->with('success', 'Budget created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
    public function showBudget(int $id)
    {
        $schoolId = session('LoggedSchool');
        $budget = Budget::where('school_id', $schoolId)->with('items')->findOrFail($id);
        return view('Finance.budget-detail', compact('budget'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // REPORTS
    // ══════════════════════════════════════════════════════════════════════════

    public function reports()
    {

        PermissionHelper::denyUnlessFeature('financial_reports');

        $schoolId = session('LoggedSchool');
        $year = request('year', date('Y'));
        $term = request('term', '');

        // Income vs Expense summary
        $incomeTotal = FeePayment::where('school_id', $schoolId)
            ->where('academic_year', $year)->where('status', 'confirmed')
            ->when($term, fn($q) => $q->where('term', $term))
            ->sum('amount_paid');

        $expenseTotal = Expense::where('school_id', $schoolId)
            ->where('academic_year', $year)->whereIn('status', ['approved', 'paid'])
            ->when($term, fn($q) => $q->where('term', $term))
            ->sum('amount');

        $payrollTotal = PayrollSlip::where('school_id', $schoolId)
            ->whereHas('period', fn($q) => $q->where('academic_year', $year)
                ->where('status', 'paid')
                ->when($term, fn($q2) => $q2->where('term', $term)))
            ->sum('net_pay');

        // Fee collection by class
        $byClass = DB::table('student_fee_allocations as a')
            ->join('students as s', 's.id', '=', 'a.student_id')
            ->where('a.school_id', $schoolId)
            ->where('a.academic_year', $year)
            ->when($term, fn($q) => $q->where('a.term', $term))
            ->selectRaw("s.senior as class_level,
                COUNT(*) as students,
                SUM(a.allocated_amount - a.discount_amount) as billed,
                SUM(CASE WHEN a.payment_status='paid' THEN (a.allocated_amount - a.discount_amount) ELSE 0 END) as collected,
                SUM(a.balance) as outstanding")
            ->groupBy('s.senior')
            ->get();

        // Monthly payment breakdown
        $monthlyPayments = FeePayment::where('school_id', $schoolId)
            ->where('academic_year', $year)->where('status', 'confirmed')
            ->when($term, fn($q) => $q->where('term', $term))
            ->selectRaw("MONTH(payment_date) as month, SUM(amount_paid) as total, COUNT(*) as count")
            ->groupBy(DB::raw('MONTH(payment_date)'))
            ->orderBy('month')
            ->get();

        // Payment method breakdown
        $byMethod = FeePayment::where('school_id', $schoolId)
            ->where('academic_year', $year)->where('status', 'confirmed')
            ->when($term, fn($q) => $q->where('term', $term))
            ->selectRaw("payment_method, SUM(amount_paid) as total, COUNT(*) as count")
            ->groupBy('payment_method')
            ->get();

        return view('Finance.reports', compact(
            'incomeTotal',
            'expenseTotal',
            'payrollTotal',
            'byClass',
            'monthlyPayments',
            'byMethod',
            'year',
            'term'
        ));
    }

    public function outstandingFees(Request $request)
    {

        PermissionHelper::denyUnlessFeature('financial_reports');

        $schoolId = session('LoggedSchool');
        $filters = $this->outstandingFeesFilters($request);

        $query = $this->outstandingFeesQuery($schoolId, $filters);

        $defaulters = $query->orderByDesc('balance')->paginate(30)->appends($request->query());

        // Stats reflect the SAME filtered set, not just the current page.
        $statsQuery = $this->outstandingFeesQuery($schoolId, $filters);
        $totalOutstanding = (clone $statsQuery)->sum('balance');
        $matchingCount = (clone $statsQuery)->count();
        $fullyUnpaidCount = (clone $statsQuery)->where('payment_status', 'unpaid')->count();

        $classrooms = Classroom::where('school_id', $schoolId)->orderBy('class_name')->get();
        $feeStructures = FeeStructure::where('school_id', $schoolId)->orderBy('name')->get();
        $streams = $filters['class_id']
            ? Stream::where('school_id', $schoolId)->where('class_id', $filters['class_id'])->get()
            : collect();

        return view('Finance.outstanding-fees', array_merge(
            compact('defaulters', 'totalOutstanding', 'matchingCount', 'fullyUnpaidCount', 'classrooms', 'feeStructures', 'streams'),
            $filters
        ));
    }

    /**
     * PDF export — mirrors the on-screen filters exactly (whatever the
     * user has filtered down to is what leaves the system), unpaginated.
     */
    public function outstandingFeesPdf(Request $request)
    {
        PermissionHelper::denyUnlessFeature('financial_reports');

        $schoolId = session('LoggedSchool');
        $filters = $this->outstandingFeesFilters($request);

        $allocations = $this->outstandingFeesQuery($schoolId, $filters)
            ->orderByDesc('balance')
            ->get();

        $totalOutstanding = $allocations->sum('balance');
        $school = \App\Models\School::find($schoolId);

        $pdf = Pdf::loadView('Finance.pdf.outstanding-fees', compact('allocations', 'totalOutstanding', 'filters', 'school'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Outstanding-Fees-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * One-click fix for stale balances: storePayment()/reversePayment() now
     * always resolve an allocation to sync going forward, but any payment
     * recorded before that fix (or any other way balance/payment_status
     * could have drifted from the payments actually on file) needs a
     * one-time recompute. Safe to run any time — syncBalance() is
     * idempotent.
     */
    public function recalculateBalances(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_fees')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');

        $count = 0;
        StudentFeeAllocation::where('school_id', $schoolId)
            ->chunkById(200, function ($allocations) use (&$count) {
                foreach ($allocations as $allocation) {
                    $allocation->syncBalance();
                    $count++;
                }
            });

        return response()->json([
            'success' => true,
            'message' => "Recalculated balances for {$count} fee allocation(s).",
        ]);
    }

    /**
     * Normalises every outstanding-fees filter from the request into one
     * array, shared by the page, the PDF export, and the stats query so
     * all three can never drift out of sync with each other.
     */
    private function outstandingFeesFilters(Request $request): array
    {
        return [
            'year' => $request->query('year', date('Y')),
            'term' => $request->query('term', ''),
            'status' => $request->query('status', ''), // '' = unpaid+partial (defaulters), or unpaid|partial|paid|overpaid
            'min_balance' => $request->query('min_balance', ''),
            'max_balance' => $request->query('max_balance', ''),
            'min_paid' => $request->query('min_paid', ''),
            'max_paid' => $request->query('max_paid', ''),
            'class_id' => $request->query('class_id', ''),
            'stream_id' => $request->query('stream_id', ''),
            'gender' => $request->query('gender', ''),
            'fee_structure_id' => $request->query('fee_structure_id', ''),
            'search' => $request->query('search', ''),
        ];
    }

    private function outstandingFeesQuery($schoolId, array $filters)
    {
        // amount_paid isn't a stored column — it's (allocated - discount) -
        // balance — so a min/max "paid" filter has to be expressed in SQL
        // terms rather than compared against a PHP property.
        $paidExpression = '(allocated_amount - discount_amount - balance)';

        return StudentFeeAllocation::where('school_id', $schoolId)
            ->where('academic_year', $filters['year'])
            ->when($filters['term'], fn($q) => $q->where('term', $filters['term']))
            ->when($filters['status'], fn($q) => $q->where('payment_status', $filters['status']))
            ->when(!$filters['status'], fn($q) => $q->whereIn('payment_status', ['unpaid', 'partial']))
            ->when($filters['min_balance'] !== '', fn($q) => $q->where('balance', '>=', (float) str_replace(',', '', $filters['min_balance'])))
            ->when($filters['max_balance'] !== '', fn($q) => $q->where('balance', '<=', (float) str_replace(',', '', $filters['max_balance'])))
            ->when($filters['min_paid'] !== '', fn($q) => $q->whereRaw("{$paidExpression} >= ?", [(float) str_replace(',', '', $filters['min_paid'])]))
            ->when($filters['max_paid'] !== '', fn($q) => $q->whereRaw("{$paidExpression} <= ?", [(float) str_replace(',', '', $filters['max_paid'])]))
            ->when($filters['fee_structure_id'], fn($q) => $q->where('fee_structure_id', $filters['fee_structure_id']))
            ->when($filters['class_id'] || $filters['stream_id'] || $filters['gender'] || $filters['search'], function ($q) use ($filters) {
                $q->whereHas('student', function ($sq) use ($filters) {
                    $sq->when($filters['class_id'], fn($sq2) => $sq2->where('senior', $filters['class_id']))
                        ->when($filters['stream_id'], fn($sq2) => $sq2->where('stream', $filters['stream_id']))
                        ->when($filters['gender'], fn($sq2) => $sq2->where('gender', $filters['gender']))
                        ->when($filters['search'], function ($sq2) use ($filters) {
                            $needle = $filters['search'];
                            $sq2->where(function ($sq3) use ($needle) {
                                $sq3->where('firstname', 'like', "%{$needle}%")
                                    ->orWhere('lastname', 'like', "%{$needle}%")
                                    ->orWhere('admission_number', 'like', "%{$needle}%");
                            });
                        });
                });
            })
            ->with('student', 'feeStructure');
    }

    public function getStreamsByClass(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $classId = $request->class_id;

        $streams = Stream::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->get()
            ->map(fn($s) => [
                'stream_id' => $s->stream_id,
                'stream_name' => \App\Http\Controllers\Helper::recordMdname($s->stream_id) ?? $s->stream_id,
            ]);

        return response()->json($streams);
    }

    public function getStudentsByStream(Request $request)
    {
        $schoolId = session('LoggedSchool');

        $students = Student::where('school_id', $schoolId)
            ->where('senior', $request->class_id)
            ->where('stream', $request->stream_id)
            ->orderBy('firstname')
            ->get(['id', 'firstname', 'lastname', 'admission_number', 'gender']);

        return response()->json($students);
    }

    /**
     * Get single fee allocation data for editing
     */
    public function getFeeAllocationData(int $id)
    {
        $schoolId = session('LoggedSchool');
        $allocation = StudentFeeAllocation::where('school_id', $schoolId)
            ->with(['student', 'feeStructure'])
            ->findOrFail($id);

        return response()->json([
            'id' => $allocation->id,
            'student_id' => $allocation->student_id,
            'student_name' => ($allocation->student->firstname ?? '') . ' ' . ($allocation->student->lastname ?? ''),
            'student_adm' => $allocation->student->admission_number ?? 'N/A',
            'fee_structure_id' => $allocation->fee_structure_id,
            'fee_structure_name' => $allocation->feeStructure->name ?? '',
            'discount_amount' => $allocation->discount_amount,
            'discount_reason' => $allocation->discount_reason,
            'allocated_amount' => $allocation->allocated_amount,
            'academic_year' => $allocation->academic_year,
            'term' => $allocation->term,
            'class' => $allocation->student->senior ?? '',
            'stream' => $allocation->student->stream ?? '',
        ]);
    }

    /**
     * Update fee allocation
     */
    public function updateFeeAllocation(Request $request, int $id)
    {

        if (!PermissionHelper::canFeature('manage_fees')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $allocation = StudentFeeAllocation::where('school_id', $schoolId)->findOrFail($id);

        $validated = $request->validate([
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
        ]);

        $structure = FeeStructure::findOrFail($validated['fee_structure_id']);
        $discount = $validated['discount_amount'] ?? 0;

        // Server-side validation: discount cannot exceed structure total
        if ($discount > $structure->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount cannot exceed the fee structure amount (UGX ' . number_format($structure->total_amount, 0) . ')'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $structure = FeeStructure::findOrFail($validated['fee_structure_id']);
            $discount = $validated['discount_amount'] ?? 0;
            $net = $structure->total_amount - $discount;

            // Update existing balance based on previous payments
            $previousNet = $allocation->allocated_amount - $allocation->discount_amount;
            $amountPaid = $previousNet - $allocation->balance;
            $newBalance = $net - $amountPaid;

            $allocation->update([
                'fee_structure_id' => $validated['fee_structure_id'],
                'allocated_amount' => $structure->total_amount,
                'discount_amount' => $discount,
                'discount_reason' => $validated['discount_reason'] ?? null,
                'balance' => max(0, $newBalance),
                'payment_status' => $newBalance <= 0 ? 'paid' : ($amountPaid > 0 ? 'partial' : 'unpaid'),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Fee allocation updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete fee allocation
     */
    public function deleteFeeAllocation(int $id)
    {

        if (!PermissionHelper::canFeature('manage_fees')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $allocation = StudentFeeAllocation::where('school_id', $schoolId)->findOrFail($id);

        // Check if there are any payments against this allocation
        $hasPayments = FeePayment::where('allocation_id', $id)->exists();

        if ($hasPayments) {
            return response()->json(['success' => false, 'message' => 'Cannot delete allocation with existing payments.'], 400);
        }

        $allocation->delete();
        return response()->json(['success' => true, 'message' => 'Fee allocation deleted successfully.']);
    }

    public function updateExpenseCategory(Request $request, int $id)
    {

        if (!PermissionHelper::canFeature('manage_expenses')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $category = ExpenseCategory::where('school_id', $schoolId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:10',
            'icon' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'icon' => $validated['icon'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('finance.expense-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function viewPayslip(int $id)
    {
        $schoolId = session('LoggedSchool');
        $slip = PayrollSlip::where('school_id', $schoolId)
            ->with(['teacher', 'period'])
            ->findOrFail($id);

        return response()->json([
            'payslip_number' => $slip->payslip_number,
            'teacher_name' => ($slip->teacher->firstname ?? '') . ' ' . ($slip->teacher->lastname ?? ''),
            'period_name' => $slip->period->period_name ?? '',
            'status' => ucfirst($slip->status),
            'basic_salary' => $slip->basic_salary,
            'housing_allowance' => $slip->housing_allowance,
            'transport_allowance' => $slip->transport_allowance,
            'other_allowances' => $slip->other_allowances,
            'gross_pay' => $slip->gross_pay,
            'paye_tax' => $slip->paye_tax,
            'nssf_employee' => $slip->nssf_employee,
            'loan_deduction' => $slip->loan_deduction,
            'other_deductions' => $slip->other_deductions,
            'total_deductions' => $slip->paye_tax + $slip->nssf_employee + $slip->loan_deduction + $slip->other_deductions,
            'net_pay' => $slip->net_pay,
            'notes' => $slip->notes ?? null,
        ]);
    }

    public function editBudget(int $id)
    {
        $schoolId = session('LoggedSchool');
        $budget = Budget::where('school_id', $schoolId)
            ->with('items')
            ->findOrFail($id);

        $categories = ExpenseCategory::where('school_id', $schoolId)->get();

        return view('Finance.budget-form', compact('budget', 'categories'));
    }

    public function updateBudget(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');
        $budget = Budget::where('school_id', $schoolId)->findOrFail($id);

        // Reshape nested income/expense arrays into flat items array
        $rawItems = [];
        foreach ($request->input('items.income', []) as $item) {
            if (!empty($item['item_name'])) {
                $rawItems[] = [
                    'item_name' => $item['item_name'],
                    'type' => 'income',
                    'budgeted_amount' => $item['budgeted_amount'] ?? 0,
                    'category_id' => $item['category_id'] ?? null,
                ];
            }
        }
        foreach ($request->input('items.expense', []) as $item) {
            if (!empty($item['item_name'])) {
                $rawItems[] = [
                    'item_name' => $item['item_name'],
                    'type' => 'expense',
                    'budgeted_amount' => $item['budgeted_amount'] ?? 0,
                    'category_id' => $item['category_id'] ?? null,
                ];
            }
        }

        $request->merge(['items' => $rawItems]);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'academic_year' => 'required|digits:4',
            'term' => 'nullable|in:1,2,3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.type' => 'required|in:income,expense',
            'items.*.budgeted_amount' => 'required|numeric|min:0',
            'items.*.category_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $budget->update([
                'title' => $validated['title'],
                'academic_year' => $validated['academic_year'],
                'term' => $validated['term'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete old items and recreate
            $budget->items()->delete();

            $totalIncome = 0;
            $totalExpense = 0;

            foreach ($validated['items'] as $item) {
                $budget->items()->create([
                    'item_name' => $item['item_name'],
                    'type' => $item['type'],
                    'budgeted_amount' => $item['budgeted_amount'],
                    'category_id' => $item['category_id'] ?? null,
                ]);

                if ($item['type'] === 'income') {
                    $totalIncome += $item['budgeted_amount'];
                } else {
                    $totalExpense += $item['budgeted_amount'];
                }
            }

            $budget->update([
                'total_income_budget' => $totalIncome,
                'total_expense_budget' => $totalExpense,
            ]);

            DB::commit();
            return redirect()->route('finance.budgets.index')
                ->with('success', 'Budget updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function approveBudget(int $id)
    {

    if (!PermissionHelper::canFeature('financial_reports')) {
        return response()->json(['message' => 'Unauthorized. You do not have permission to approve budgets.'], 403);
    }

        $schoolId = session('LoggedSchool');
        $budget = Budget::where('school_id', $schoolId)->findOrFail($id);
        $budget->update([
            'status' => 'approved',
            'approved_by' => session('LoggedUser'),
        ]);
        return back()->with('success', 'Budget approved successfully.');
    }
}