<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\ChartOfAccount;
use App\Models\FinanceTransaction;
use App\Models\Student;
use App\Models\StudentFeeAllocation;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * LedgerController
 *
 * Adds a lightweight General Ledger layer on top of the existing Finance
 * module: a Chart of Accounts, a General Ledger view per account, a
 * per-student Fee Ledger (statement of account), and a Trial Balance /
 * Income & Expenditure statement.
 *
 * NOTE on accounting model: this is a single-entry ledger — each
 * finance_transactions row is posted against exactly one account (see
 * ChartOfAccount::findOrCreateDefault / findOrCreateForExpenseCategory and
 * the three FinanceTransaction::log() call sites in FinanceController).
 * It is not full double-entry bookkeeping (no automatic offsetting Cash/Bank
 * entry yet) — that can be layered on later without breaking this.
 */
class LedgerController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // Chart of Accounts
    // ─────────────────────────────────────────────────────────────────

    public function chartOfAccounts()
    {
        PermissionHelper::denyUnlessFeature('manage_ledger');

        $schoolId = session('LoggedSchool');

        // Auto-seed a starter Chart of Accounts the first time this school
        // opens the page, so it's never empty.
        ChartOfAccount::seedDefaults($schoolId, session('LoggedUser'));

        $accounts = ChartOfAccount::forSchool($schoolId)
            ->orderBy('account_code')
            ->get();

        $rootAccounts = $accounts->whereNull('parent_id')->values();

        $year = request('year', date('Y'));
        $term = request('term', '');

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->id] = $account->balanceWithChildren($year, $term ?: null);
        }

        return view('Finance.Ledger.chart-of-accounts', compact(
            'accounts', 'rootAccounts', 'balances', 'year', 'term'
        ));
    }

    public function storeAccount(Request $request)
    {
        PermissionHelper::denyUnlessFeature('manage_ledger');
        $schoolId = session('LoggedSchool');

        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code,NULL,id,school_id,' . $schoolId,
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:asset,liability,income,expense,equity',
            'parent_id'    => 'nullable|exists:chart_of_accounts,id',
            'description'  => 'nullable|string',
        ]);

        ChartOfAccount::create(array_merge($validated, [
            'school_id'  => $schoolId,
            'is_system'  => false,
            'created_by' => session('LoggedUser'),
        ]));

        return back()->with('success', 'Account created successfully.');
    }

    public function updateAccount(Request $request, int $id)
    {
        PermissionHelper::denyUnlessFeature('manage_ledger');
        $schoolId = session('LoggedSchool');

        $account = ChartOfAccount::forSchool($schoolId)->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:asset,liability,income,expense,equity',
            'parent_id'   => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        if (($validated['parent_id'] ?? null) == $account->id) {
            return back()->with('error', 'An account cannot be its own parent.');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $account->update($validated);

        return back()->with('success', 'Account updated successfully.');
    }

    public function destroyAccount(int $id)
    {
        PermissionHelper::denyUnlessFeature('manage_ledger');
        $schoolId = session('LoggedSchool');

        $account = ChartOfAccount::forSchool($schoolId)->findOrFail($id);

        if ($account->is_system) {
            return back()->with('error', 'System accounts cannot be deleted. You can deactivate them instead.');
        }

        if ($account->children()->exists()) {
            return back()->with('error', 'Cannot delete an account that has sub-accounts.');
        }

        if ($account->transactions()->exists()) {
            return back()->with('error', 'Cannot delete an account that already has transactions posted to it. Deactivate it instead.');
        }

        $account->delete();

        return back()->with('success', 'Account deleted.');
    }

    public function seedDefaultAccounts()
    {
        PermissionHelper::denyUnlessFeature('manage_ledger');
        $schoolId = session('LoggedSchool');

        ChartOfAccount::seedDefaults($schoolId, session('LoggedUser'));

        return back()->with('success', 'Default Chart of Accounts created.');
    }

    // ─────────────────────────────────────────────────────────────────
    // General Ledger
    // ─────────────────────────────────────────────────────────────────

    public function generalLedger(Request $request)
    {
        PermissionHelper::denyUnlessFeature('financial_reports');

        $schoolId = session('LoggedSchool');
        ChartOfAccount::seedDefaults($schoolId, session('LoggedUser'));

        $year = $request->get('year', date('Y'));
        $term = $request->get('term', '');
        $accountId = $request->get('account_id');

        $accounts = ChartOfAccount::forSchool($schoolId)
            ->orderBy('account_code')
            ->get();

        $selectedAccount = null;
        $entries = collect();
        $openingBalance = 0;
        $closingBalance = 0;

        if ($accountId) {
            $selectedAccount = ChartOfAccount::forSchool($schoolId)->findOrFail($accountId);

            $query = FinanceTransaction::where('account_id', $selectedAccount->id)
                ->where('school_id', $schoolId)
                ->where('academic_year', $year)
                ->when($term, fn($q) => $q->where('term', $term))
                ->orderBy('transaction_date')
                ->orderBy('id');

            $running = 0;
            $entries = $query->get()->map(function ($txn) use (&$running) {
                $signed = $txn->type === 'refund' ? -$txn->amount : (float) $txn->amount;
                $running += $signed;
                $txn->signed_amount = $signed;
                $txn->running_balance = $running;
                return $txn;
            });

            $closingBalance = $running;
        }

        return view('Finance.Ledger.general-ledger', compact(
            'accounts', 'selectedAccount', 'entries', 'openingBalance', 'closingBalance', 'year', 'term'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // Student Fee Ledger
    // ─────────────────────────────────────────────────────────────────

    public function studentFeeLedgerSearch(Request $request)
    {
        PermissionHelper::denyUnlessFeature('financial_reports');

        $schoolId = session('LoggedSchool');
        $search = $request->get('q', '');

        $students = collect();
        if (strlen($search) >= 2) {
            $students = Student::where('school_id', $schoolId)
                ->where(function ($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                })
                ->orderBy('firstname')
                ->limit(25)
                ->get(['id', 'firstname', 'lastname', 'admission_number']);
        }

        return view('Finance.Ledger.student-fee-ledger', compact('students', 'search'));
    }

    public function studentFeeLedgerDetail(Request $request, int $studentId)
    {
        PermissionHelper::denyUnlessFeature('financial_reports');

        $schoolId = session('LoggedSchool');
        $student = Student::where('school_id', $schoolId)->findOrFail($studentId);

        $year = $request->get('year', date('Y'));

        // Every fee allocation (charge) raised for the student, each with its
        // linked payments, ordered chronologically by term.
        $allocations = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->when($year, fn($q) => $q->where('academic_year', $year))
            ->with(['feeStructure', 'payments' => function ($q) {
                $q->orderBy('payment_date');
            }])
            ->orderBy('academic_year')
            ->orderBy('term')
            ->get();

        // Build a single chronological statement: charge line, then each
        // payment against it, with a running balance carried forward.
        $statement = collect();
        $runningBalance = 0;

        foreach ($allocations as $allocation) {
            $charge = (float) $allocation->allocated_amount - (float) $allocation->discount_amount;
            $runningBalance += $charge;

            $statement->push([
                'date'        => $allocation->created_at,
                'type'        => 'charge',
                'description' => 'Fee charge — ' . ($allocation->feeStructure->name ?? 'Fee Structure')
                                    . " (Term {$allocation->term}, {$allocation->academic_year})"
                                    . ($allocation->discount_amount > 0 ? ' — discount applied' : ''),
                'debit'       => $charge,
                'credit'      => null,
                'balance'     => $runningBalance,
            ]);

            foreach ($allocation->payments as $payment) {
                if ($payment->status !== 'confirmed') {
                    continue;
                }
                $runningBalance -= (float) $payment->amount_paid;

                $statement->push([
                    'date'        => $payment->payment_date,
                    'type'        => 'payment',
                    'description' => 'Payment received — Receipt ' . $payment->receipt_number . ' (' . $payment->methodLabel() . ')',
                    'debit'       => null,
                    'credit'      => (float) $payment->amount_paid,
                    'balance'     => $runningBalance,
                ]);
            }
        }

        $totalCharges = $allocations->sum(fn($a) => $a->allocated_amount - $a->discount_amount);
        $totalPaid = $allocations->flatMap->payments->where('status', 'confirmed')->sum('amount_paid');
        $arrears = $totalCharges - $totalPaid;

        $availableYears = StudentFeeAllocation::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('Finance.Ledger.student-fee-ledger-detail', compact(
            'student', 'statement', 'totalCharges', 'totalPaid', 'arrears', 'year', 'availableYears'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // Trial Balance / Income & Expenditure Statement
    // ─────────────────────────────────────────────────────────────────

    public function trialBalance(Request $request)
    {
        PermissionHelper::denyUnlessFeature('financial_reports');

        $schoolId = session('LoggedSchool');
        ChartOfAccount::seedDefaults($schoolId, session('LoggedUser'));

        $year = $request->get('year', date('Y'));
        $term = $request->get('term', '');

        // Only leaf-level (posting) accounts appear on the Trial Balance —
        // parent/group accounts (like "Operating Expenses") are summary
        // rows only, to avoid double-counting.
        $accounts = ChartOfAccount::forSchool($schoolId)
            ->active()
            ->orderBy('account_code')
            ->get();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $isLeaf = $account->children()->doesntExist();
            if (!$isLeaf) {
                continue;
            }

            $balance = $account->balance($year, $term ?: null);
            if ($balance == 0) {
                continue;
            }

            $normal = $account->normalBalance();
            // A positive balance sits on the account's normal side; if the
            // balance ever goes negative (e.g. refunds exceeding income for
            // that account), it flips to the opposite column.
            $debit = null;
            $credit = null;

            if (($normal === 'debit' && $balance >= 0) || ($normal === 'credit' && $balance < 0)) {
                $debit = abs($balance);
            } else {
                $credit = abs($balance);
            }

            $rows[] = [
                'account' => $account,
                'debit'   => $debit,
                'credit'  => $credit,
            ];

            $totalDebit += $debit ?? 0;
            $totalCredit += $credit ?? 0;
        }

        // Income & Expenditure summary
        $totalIncome = $accounts->filter(fn($a) => $a->type === 'income' && $a->children()->doesntExist())
            ->sum(fn($a) => $a->balance($year, $term ?: null));

        $totalExpense = $accounts->filter(fn($a) => $a->type === 'expense' && $a->children()->doesntExist())
            ->sum(fn($a) => $a->balance($year, $term ?: null));

        $surplusDeficit = $totalIncome - $totalExpense;

        return view('Finance.Ledger.trial-balance', compact(
            'rows', 'totalDebit', 'totalCredit', 'totalIncome', 'totalExpense', 'surplusDeficit', 'year', 'term'
        ));
    }
}