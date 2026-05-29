<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
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
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════════════

    // public function dashboard()
    // {
    //     $schoolId = session('LoggedSchool');
    //     $year     = date('Y');

    //     // ── Income (fee payments confirmed this year) ─────────────────────
    //     $totalIncome = FeePayment::where('school_id', $schoolId)
    //         ->where('academic_year', $year)
    //         ->where('status', 'confirmed')
    //         ->sum('amount_paid');

    //     // ── Total Expenses this year ──────────────────────────────────────
    //     $totalExpenses = Expense::where('school_id', $schoolId)
    //         ->where('academic_year', $year)
    //         ->whereIn('status', ['approved', 'paid'])
    //         ->sum('amount');

    //     // ── Payroll this year ─────────────────────────────────────────────
    //     $totalPayroll = PayrollSlip::where('school_id', $schoolId)
    //         ->whereHas('period', fn($q) => $q->where('academic_year', $year)->where('status', 'paid'))
    //         ->sum('net_pay');

    //     // ── Fee collection stats ──────────────────────────────────────────
    //     $feeStats = StudentFeeAllocation::where('school_id', $schoolId)
    //         ->where('academic_year', $year)
    //         ->selectRaw("
    //             COUNT(*) as total_students,
    //             SUM(allocated_amount - discount_amount) as total_billed,
    //             SUM(CASE WHEN payment_status='paid' THEN 1 ELSE 0 END) as fully_paid,
    //             SUM(CASE WHEN payment_status='partial' THEN 1 ELSE 0 END) as partial,
    //             SUM(CASE WHEN payment_status='unpaid' THEN 1 ELSE 0 END) as unpaid
    //         ")->first();

    //     $totalBilled = $feeStats->total_billed ?? 0;
    //     $outstanding = $totalBilled - $totalIncome;

    //     // ── Monthly income trend (last 6 months) ─────────────────────────
    //     $monthlyTrend = FeePayment::where('school_id', $schoolId)
    //         ->where('status', 'confirmed')
    //         ->where('payment_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
    //         ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount_paid) as total")
    //         ->groupBy('month')
    //         ->orderBy('month')
    //         ->get();

    //     // ── Expense breakdown by category ────────────────────────────────
    //     $expenseBreakdown = Expense::where('school_id', $schoolId)
    //         ->where('academic_year', $year)
    //         ->whereIn('status', ['approved', 'paid'])
    //         ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
    //         ->selectRaw('expense_categories.name as cat_name, expense_categories.color as cat_color, SUM(expenses.amount) as total')
    //         ->groupBy('expense_categories.id', 'expense_categories.name', 'expense_categories.color')
    //         ->orderByDesc('total')
    //         ->get();

    //     // ── Recent transactions ───────────────────────────────────────────
    //     $recentPayments = FeePayment::where('school_id', $schoolId)
    //         ->where('status', 'confirmed')
    //         ->with('student')
    //         ->orderByDesc('payment_date')
    //         ->limit(8)
    //         ->get();

    //     // ── Pending approvals ─────────────────────────────────────────────
    //     $pendingExpenses = Expense::where('school_id', $schoolId)->where('status', 'draft')->count();
    //     $pendingPayroll  = PayrollPeriod::where('school_id', $schoolId)->where('status', 'processing')->count();

    //     return view('Finance.dashboard', compact(
    //         'totalIncome', 'totalExpenses', 'totalPayroll', 'outstanding',
    //         'feeStats', 'totalBilled', 'monthlyTrend', 'expenseBreakdown',
    //         'recentPayments', 'pendingExpenses', 'pendingPayroll', 'year'
    //     ));
    // }

    public function dashboard()
{
    $schoolId = session('LoggedSchool');
    $year     = date('Y');

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
        $schoolId   = session('LoggedSchool');
        $structures = FeeStructure::where('school_id', $schoolId)
            ->orderByDesc('academic_year')
            ->orderBy('term')
            ->get();

        return view('Finance.fee-structures', compact('structures'));
    }

    public function createFeeStructure()
    {
        return view('Finance.fee-structure-form');
    }

    public function storeFeeStructure(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:200',
            'academic_year'=> 'required|digits:4',
            'term'         => 'required|in:1,2,3',
            'class_level'  => 'nullable|string|max:50',
            'student_type' => 'required|in:boarding,day,all',
            'notes'        => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.category'  => 'required|string|max:100',
            'items.*.amount'    => 'required|numeric|min:0',
            'items.*.is_mandatory' => 'boolean',
        ]);

        $schoolId = session('LoggedSchool');

        DB::beginTransaction();
        try {
            $structure = FeeStructure::create([
                'school_id'    => $schoolId,
                'name'         => $validated['name'],
                'academic_year'=> $validated['academic_year'],
                'term'         => $validated['term'],
                'class_level'  => $validated['class_level'] ?? null,
                'student_type' => $validated['student_type'],
                'notes'        => $validated['notes'] ?? null,
                'created_by'   => session('LoggedUser'),
            ]);

            $total = 0;
            foreach ($validated['items'] as $i => $item) {
                $structure->items()->create([
                    'item_name'    => $item['item_name'],
                    'category'     => $item['category'],
                    'amount'       => $item['amount'],
                    'is_mandatory' => $item['is_mandatory'] ?? true,
                    'sort_order'   => $i,
                ]);
                $total += $item['amount'];
            }

            $structure->update(['total_amount' => $total]);
            DB::commit();

            return redirect()->route('finance.fee-structures')
                ->with('success', 'Fee structure created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create fee structure: ' . $e->getMessage());
        }
    }

    public function editFeeStructure(int $id)
    {
        $schoolId  = session('LoggedSchool');
        $structure = FeeStructure::where('school_id', $schoolId)->with('items')->findOrFail($id);
        return view('Finance.fee-structure-form', compact('structure'));
    }

    public function updateFeeStructure(Request $request, int $id)
    {
        $schoolId  = session('LoggedSchool');
        $structure = FeeStructure::where('school_id', $schoolId)->findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:200',
            'class_level'  => 'nullable|string|max:50',
            'student_type' => 'required|in:boarding,day,all',
            'notes'        => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.category'  => 'required|string|max:100',
            'items.*.amount'    => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $structure->items()->delete();
            $total = 0;
            foreach ($validated['items'] as $i => $item) {
                $structure->items()->create([
                    'item_name'    => $item['item_name'],
                    'category'     => $item['category'],
                    'amount'       => $item['amount'],
                    'is_mandatory' => $item['is_mandatory'] ?? true,
                    'sort_order'   => $i,
                ]);
                $total += $item['amount'];
            }

            $structure->update([
                'name'         => $validated['name'],
                'class_level'  => $validated['class_level'] ?? null,
                'student_type' => $validated['student_type'],
                'notes'        => $validated['notes'] ?? null,
                'total_amount' => $total,
            ]);

            DB::commit();
            return redirect()->route('finance.fee-structures')
                ->with('success', 'Fee structure updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function deleteFeeStructure(int $id)
    {
        $schoolId  = session('LoggedSchool');
        $structure = FeeStructure::where('school_id', $schoolId)->findOrFail($id);
        $structure->delete();
        return back()->with('success', 'Fee structure deleted.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FEE ALLOCATION TO STUDENTS
    // ══════════════════════════════════════════════════════════════════════════

    public function feeAllocations()
    {
        $schoolId   = session('LoggedSchool');
        $year       = request('year', date('Y'));
        $term       = request('term', '');

        $query = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->with(['student', 'feeStructure']);

        if ($term) $query->where('term', $term);

        $allocations = $query->orderByDesc('created_at')->paginate(30);

        $structures = FeeStructure::where('school_id', $schoolId)
            ->where('academic_year', $year)->get();
        $students = Student::where('school_id', $schoolId)->orderBy('firstname')->get();

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

        return view('Finance.fee-allocations', compact('allocations', 'structures', 'students', 'summary', 'year', 'term'));
    }

    public function allocateFees(Request $request)
    {
        $validated = $request->validate([
            'student_ids'      => 'required|array|min:1',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'discount_amount'  => 'nullable|numeric|min:0',
            'discount_reason'  => 'nullable|string|max:255',
        ]);

        $schoolId  = session('LoggedSchool');
        $structure = FeeStructure::findOrFail($validated['fee_structure_id']);
        $discount  = $validated['discount_amount'] ?? 0;

        $created = 0;
        foreach ($validated['student_ids'] as $studentId) {
            $existing = StudentFeeAllocation::where([
                'student_id'       => $studentId,
                'fee_structure_id' => $structure->id,
                'academic_year'    => $structure->academic_year,
                'term'             => $structure->term,
            ])->first();

            if (!$existing) {
                $net = $structure->total_amount - $discount;
                StudentFeeAllocation::create([
                    'school_id'        => $schoolId,
                    'student_id'       => $studentId,
                    'fee_structure_id' => $structure->id,
                    'academic_year'    => $structure->academic_year,
                    'term'             => $structure->term,
                    'allocated_amount' => $structure->total_amount,
                    'discount_amount'  => $discount,
                    'discount_reason'  => $validated['discount_reason'] ?? null,
                    'balance'          => $net,
                    'payment_status'   => 'unpaid',
                    'allocated_by'     => session('LoggedUser'),
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
        $schoolId = session('LoggedSchool');
        $year     = request('year', date('Y'));
        $term     = request('term', '');
        $search   = request('search', '');

        $query = FeePayment::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->with('student')
            ->orderByDesc('payment_date');

        if ($term) $query->where('term', $term);
        if ($search) {
            $query->whereHas('student', fn($q) =>
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
        $schoolId   = session('LoggedSchool');
        $students   = Student::where('school_id', $schoolId)->orderBy('firstname')->get();
        $receiptNum = FeePayment::generateReceiptNumber($schoolId);
        return view('Finance.payment-form', compact('students', 'receiptNum'));
    }

    public function getStudentAllocations(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $student  = Student::where('school_id', $schoolId)->findOrFail($request->student_id);

        $allocations = StudentFeeAllocation::where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->where('academic_year', $request->year ?? date('Y'))
            ->with('feeStructure')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'label'        => "Term {$a->term} — Balance: UGX " . number_format($a->balance, 0),
                'balance'      => $a->balance,
                'term'         => $a->term,
                'status'       => $a->payment_status,
            ]);

        return response()->json(['student' => $student, 'allocations' => $allocations]);
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'student_id'            => 'required|exists:students,id',
            'allocation_id'         => 'nullable|exists:student_fee_allocations,id',
            'academic_year'         => 'required|digits:4',
            'term'                  => 'required|in:1,2,3',
            'amount_paid'           => 'required|numeric|min:1',
            'payment_date'          => 'required|date',
            'payment_method'        => 'required|in:cash,bank_transfer,mobile_money,cheque,other',
            'transaction_reference' => 'nullable|string|max:100',
            'bank_name'             => 'nullable|string|max:100',
            'notes'                 => 'nullable|string',
        ]);

        $schoolId   = session('LoggedSchool');
        $receiptNum = FeePayment::generateReceiptNumber($schoolId);

        DB::beginTransaction();
        try {
            $payment = FeePayment::create([
                'receipt_number'        => $receiptNum,
                'school_id'             => $schoolId,
                'student_id'            => $validated['student_id'],
                'allocation_id'         => $validated['allocation_id'] ?? null,
                'academic_year'         => $validated['academic_year'],
                'term'                  => $validated['term'],
                'amount_paid'           => $validated['amount_paid'],
                'payment_date'          => $validated['payment_date'],
                'payment_method'        => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'bank_name'             => $validated['bank_name'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'status'                => 'confirmed',
                'received_by'           => session('LoggedUser'),
                'confirmed_by'          => session('LoggedUser'),
                'confirmed_at'          => now(),
            ]);

            // Update allocation balance
            if ($validated['allocation_id']) {
                $allocation = StudentFeeAllocation::find($validated['allocation_id']);
                $allocation?->syncBalance();
            }

            // Log transaction
            FinanceTransaction::log(
                $schoolId, 'income', 'fee_payment', $payment->id,
                $validated['amount_paid'],
                "Fee payment by student #" . $validated['student_id'] . " — Receipt {$receiptNum}",
                $validated['payment_date'], $validated['academic_year'], $validated['term'],
                session('LoggedUser')
            );

            DB::commit();
            return redirect()->route('finance.payments')
                ->with('success', "Payment recorded. Receipt: {$receiptNum}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function receiptPdf(int $id)
    {
        $schoolId = session('LoggedSchool');
        $payment  = FeePayment::where('school_id', $schoolId)->with('student')->findOrFail($id);
        $pdf = Pdf::loadView('Finance.pdf.receipt', compact('payment'))->setPaper('a5', 'portrait');
        return $pdf->stream("Receipt-{$payment->receipt_number}.pdf");
    }

    public function reversePayment(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');
        $payment  = FeePayment::where('school_id', $schoolId)->findOrFail($id);
        $payment->update(['status' => 'reversed']);
        if ($payment->allocation_id) {
            StudentFeeAllocation::find($payment->allocation_id)?->syncBalance();
        }
        return back()->with('success', 'Payment reversed successfully.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPENSES
    // ══════════════════════════════════════════════════════════════════════════

    public function expenses()
    {
        $schoolId   = session('LoggedSchool');
        $year       = request('year', date('Y'));
        $categoryId = request('category_id', '');
        $search     = request('search', '');

        $query = Expense::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->with('category')
            ->orderByDesc('expense_date');

        if ($categoryId) $query->where('category_id', $categoryId);
        if ($search) $query->where(fn($q) => $q->where('title', 'like', "%$search%")
            ->orWhere('payee_name', 'like', "%$search%")
            ->orWhere('expense_number', 'like', "%$search%"));

        $expenses   = $query->paginate(25);
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
        $schoolId   = session('LoggedSchool');
        $categories = ExpenseCategory::where('school_id', $schoolId)->where('is_active', true)->get();
        $expNum     = Expense::generateExpenseNumber($schoolId);
        return view('Finance.expense-form', compact('categories', 'expNum'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category_id'           => 'required|exists:expense_categories,id',
            'title'                 => 'required|string|max:255',
            'description'           => 'nullable|string',
            'amount'                => 'required|numeric|min:0',
            'expense_date'          => 'required|date',
            'academic_year'         => 'required|digits:4',
            'term'                  => 'nullable|in:1,2,3',
            'payment_method'        => 'required|in:cash,bank_transfer,mobile_money,cheque,other',
            'payee_name'            => 'nullable|string|max:200',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $schoolId = session('LoggedSchool');

        DB::beginTransaction();
        try {
            $expense = Expense::create(array_merge($validated, [
                'expense_number' => Expense::generateExpenseNumber($schoolId),
                'school_id'      => $schoolId,
                'status'         => 'paid',
                'submitted_by'   => session('LoggedUser'),
                'approved_by'    => session('LoggedUser'),
                'approved_at'    => now(),
            ]));

            FinanceTransaction::log(
                $schoolId, 'expense', 'expense', $expense->id,
                $validated['amount'],
                "Expense: {$validated['title']}",
                $validated['expense_date'], $validated['academic_year'],
                $validated['term'] ?? null, session('LoggedUser')
            );

            DB::commit();
            return redirect()->route('finance.expenses')
                ->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function editExpense(int $id)
    {
        $schoolId   = session('LoggedSchool');
        $expense    = Expense::where('school_id', $schoolId)->findOrFail($id);
        $categories = ExpenseCategory::where('school_id', $schoolId)->where('is_active', true)->get();
        return view('Finance.expense-form', compact('expense', 'categories'));
    }

    public function updateExpense(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');
        $expense  = Expense::where('school_id', $schoolId)->findOrFail($id);

        $validated = $request->validate([
            'category_id'  => 'required|exists:expense_categories,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payee_name'   => 'nullable|string|max:200',
        ]);

        $expense->update($validated);
        return redirect()->route('finance.expenses')->with('success', 'Expense updated.');
    }

    public function deleteExpense(int $id)
    {
        $schoolId = session('LoggedSchool');
        Expense::where('school_id', $schoolId)->findOrFail($id)->delete();
        return back()->with('success', 'Expense deleted.');
    }

    // ── Expense Categories ─────────────────────────────────────────────────

    public function expenseCategories()
    {
        $schoolId   = session('LoggedSchool');
        $categories = ExpenseCategory::where('school_id', $schoolId)
            ->withCount('expenses')
            ->withSum('expenses', 'amount')
            ->get();
        return view('Finance.expense-categories', compact('categories'));
    }

    public function storeExpenseCategory(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|max:10',
            'icon'  => 'required|string|max:50',
        ]);

        ExpenseCategory::create(array_merge($validated, [
            'school_id' => session('LoggedSchool'),
        ]));

        return back()->with('success', 'Category added.');
    }

    public function deleteExpenseCategory(int $id)
    {
        $schoolId = session('LoggedSchool');
        ExpenseCategory::where('school_id', $schoolId)->findOrFail($id)->delete();
        return back()->with('success', 'Category deleted.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PAYROLL
    // ══════════════════════════════════════════════════════════════════════════

    public function payroll()
    {
        $schoolId = session('LoggedSchool');
        $periods  = PayrollPeriod::where('school_id', $schoolId)
            ->orderByDesc('period_start')->get();
        return view('Finance.payroll', compact('periods'));
    }

    public function createPayrollPeriod()
    {
        return view('Finance.payroll-period-form');
    }

    public function storePayrollPeriod(Request $request)
    {
        $validated = $request->validate([
            'period_name'   => 'required|string|max:100',
            'academic_year' => 'required|digits:4',
            'term'          => 'nullable|in:1,2,3',
            'period_start'  => 'required|date',
            'period_end'    => 'required|date|after_or_equal:period_start',
        ]);

        $schoolId = session('LoggedSchool');

        // Fetch all teachers + their salary structures
        $teachers = Teacher::where('school_id', $schoolId)->get();

        DB::beginTransaction();
        try {
            $period = PayrollPeriod::create(array_merge($validated, [
                'school_id'  => $schoolId,
                'status'     => 'draft',
                'created_by' => session('LoggedUser'),
            ]));

            foreach ($teachers as $teacher) {
                $structure = TeacherSalaryStructure::where('teacher_id', $teacher->id)
                    ->where('school_id', $schoolId)->first();

                if ($structure) {
                    $slip = new PayrollSlip([
                        'payroll_period_id' => $period->id,
                        'school_id'         => $schoolId,
                        'teacher_id'        => $teacher->id,
                        'payslip_number'    => PayrollSlip::generateSlipNumber($schoolId),
                        'loan_deduction'    => 0,
                        'other_deductions'  => 0,
                        'status'            => 'draft',
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
        $period   = PayrollPeriod::where('school_id', $schoolId)
            ->with(['slips.teacher'])->findOrFail($id);
        return view('Finance.payroll-period', compact('period'));
    }

    public function approvePayrollPeriod(int $id)
    {
        $schoolId = session('LoggedSchool');
        $period   = PayrollPeriod::where('school_id', $schoolId)->findOrFail($id);
        $period->update([
            'status'      => 'approved',
            'approved_by' => session('LoggedUser'),
            'approved_at' => now(),
        ]);
        $period->slips()->update(['status' => 'approved']);
        return back()->with('success', 'Payroll approved.');
    }

    public function markPayrollPaid(int $id)
    {
        $schoolId = session('LoggedSchool');
        $period   = PayrollPeriod::where('school_id', $schoolId)->findOrFail($id);
        $period->update(['status' => 'paid']);
        $period->slips()->update(['status' => 'paid', 'paid_date' => now()->toDateString()]);

        // Log each slip as a transaction
        foreach ($period->slips as $slip) {
            FinanceTransaction::log(
                $schoolId, 'expense', 'payroll', $slip->id,
                $slip->net_pay,
                "Salary: Teacher #{$slip->teacher_id} — {$period->period_name}",
                now()->toDateString(), $period->academic_year, $period->term,
                session('LoggedUser')
            );
        }

        return back()->with('success', 'Payroll marked as paid.');
    }

    // ── Teacher Salary Structures ─────────────────────────────────────────

    public function salaryStructures()
    {
        $schoolId   = session('LoggedSchool');
        $teachers   = Teacher::where('school_id', $schoolId)
            ->with(['salaryStructure' => fn($q) => $q->where('school_id', $schoolId)])
            ->get();
        return view('Finance.salary-structures', compact('teachers'));
    }

    public function storeSalaryStructure(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'          => 'required|exists:teachers,id',
            'basic_salary'        => 'required|numeric|min:0',
            'housing_allowance'   => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'other_allowances'    => 'nullable|numeric|min:0',
            'apply_paye'          => 'boolean',
            'apply_nssf'          => 'boolean',
            'notes'               => 'nullable|string',
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
        $schoolId = session('LoggedSchool');
        $budgets  = Budget::where('school_id', $schoolId)->orderByDesc('academic_year')->get();
        return view('Finance.budgets', compact('budgets'));
    }

    public function createBudget()
    {
        $schoolId   = session('LoggedSchool');
        $categories = ExpenseCategory::where('school_id', $schoolId)->get();
        return view('Finance.budget-form', compact('categories'));
    }

    public function storeBudget(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:200',
            'academic_year' => 'required|digits:4',
            'term'          => 'nullable|in:1,2,3',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.item_name'        => 'required|string|max:200',
            'items.*.type'             => 'required|in:income,expense',
            'items.*.budgeted_amount'  => 'required|numeric|min:0',
            'items.*.category_id'      => 'nullable|integer',
        ]);

        $schoolId = session('LoggedSchool');

        DB::beginTransaction();
        try {
            $budget = Budget::create(array_merge($validated, [
                'school_id'  => $schoolId,
                'status'     => 'draft',
                'created_by' => session('LoggedUser'),
            ]));

            $totalIncome  = 0;
            $totalExpense = 0;
            foreach ($validated['items'] as $item) {
                $budget->items()->create($item);
                if ($item['type'] === 'income') $totalIncome  += $item['budgeted_amount'];
                else                            $totalExpense += $item['budgeted_amount'];
            }

            $budget->update([
                'total_income_budget'  => $totalIncome,
                'total_expense_budget' => $totalExpense,
            ]);

            DB::commit();
            return redirect()->route('finance.budgets')
                ->with('success', 'Budget created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function showBudget(int $id)
    {
        $schoolId = session('LoggedSchool');
        $budget   = Budget::where('school_id', $schoolId)->with('items')->findOrFail($id);
        return view('Finance.budget-detail', compact('budget'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // REPORTS
    // ══════════════════════════════════════════════════════════════════════════

    public function reports()
    {
        $schoolId = session('LoggedSchool');
        $year     = request('year', date('Y'));
        $term     = request('term', '');

        // Income vs Expense summary
        $incomeTotal  = FeePayment::where('school_id', $schoolId)
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
            'incomeTotal', 'expenseTotal', 'payrollTotal',
            'byClass', 'monthlyPayments', 'byMethod', 'year', 'term'
        ));
    }

    public function outstandingFees()
    {
        $schoolId = session('LoggedSchool');
        $year     = request('year', date('Y'));
        $term     = request('term', '');

        $defaulters = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->with('student', 'feeStructure')
            ->when($term, fn($q) => $q->where('term', $term))
            ->orderByDesc('balance')
            ->paginate(30);

        $totalOutstanding = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('academic_year', $year)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->when($term, fn($q) => $q->where('term', $term))
            ->sum('balance');

        return view('Finance.outstanding-fees', compact('defaulters', 'totalOutstanding', 'year', 'term'));
    }
}