<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'school_id', 'account_code', 'name', 'type', 'parent_id',
        'description', 'is_active', 'is_system', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    // ── Relationships ───────────────────────────────────────────────────

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id')->orderBy('account_code');
    }

    public function transactions()
    {
        return $this->hasMany(FinanceTransaction::class, 'account_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────────

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootAccounts($query)
    {
        return $query->whereNull('parent_id');
    }

    // ── Accounting helpers ──────────────────────────────────────────────

    /**
     * Asset & Expense accounts naturally grow with debits.
     * Liability, Equity & Income accounts naturally grow with credits.
     */
    public function normalBalance(): string
    {
        return in_array($this->type, ['asset', 'expense']) ? 'debit' : 'credit';
    }

    public function typeBadge(): string
    {
        return match ($this->type) {
            'asset'     => 'primary',
            'liability' => 'danger',
            'income'    => 'success',
            'expense'   => 'warning',
            'equity'    => 'info',
            default     => 'secondary',
        };
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'asset'     => 'fa-building-columns',
            'liability' => 'fa-file-invoice-dollar',
            'income'    => 'fa-arrow-trend-up',
            'expense'   => 'fa-arrow-trend-down',
            'equity'    => 'fa-scale-balanced',
            default     => 'fa-circle',
        };
    }

    /**
     * Net balance of this account only (not including children), for a given
     * school/period. Every transaction posted to an account is treated as
     * moving the account in its "increase" direction, except refunds which
     * reverse an income account. This is a single-entry ledger per account
     * (each transaction points at exactly one account) rather than full
     * double-entry bookkeeping — it is intentionally kept simple for now.
     */
    public function balance(?string $academicYear = null, ?string $term = null): float
    {
        $query = $this->transactions();

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }
        if ($term) {
            $query->where('term', $term);
        }

        return (float) $query->get()->sum(function ($txn) {
            return $txn->type === 'refund' ? -$txn->amount : $txn->amount;
        });
    }

    /**
     * Balance including all descendant accounts (used for parent/summary
     * accounts like "Operating Expenses" that group child accounts).
     */
    public function balanceWithChildren(?string $academicYear = null, ?string $term = null): float
    {
        $total = $this->balance($academicYear, $term);

        foreach ($this->children as $child) {
            $total += $child->balanceWithChildren($academicYear, $term);
        }

        return $total;
    }

    // ── Default Chart of Accounts ────────────────────────────────────────

    /**
     * Fixed codes for accounts the app links to automatically. Keeping
     * these as constants means Finance flows (fee payments, expenses,
     * payroll) can always find/create the right account by code.
     */
    public const CODE_CASH_BANK        = '1000';
    public const CODE_ACCOUNTS_PAYABLE = '2000';
    public const CODE_SCHOOL_FUND      = '3000';
    public const CODE_INCOME_PARENT    = '4000';
    public const CODE_TUITION_INCOME   = '4001';
    public const CODE_OTHER_INCOME     = '4002';
    public const CODE_EXPENSE_PARENT   = '5000';
    public const CODE_SALARIES_EXPENSE = '5001';

    /**
     * The starter Chart of Accounts every school gets. Kept small and
     * standard — schools can add more accounts (or expense categories
     * auto-create their own child accounts under 5000).
     */
    public static function defaultDefinitions(): array
    {
        return [
            ['code' => self::CODE_CASH_BANK,        'name' => 'Cash & Bank',           'type' => 'asset',     'parent' => null],
            ['code' => self::CODE_ACCOUNTS_PAYABLE,  'name' => 'Accounts Payable',       'type' => 'liability', 'parent' => null],
            ['code' => self::CODE_SCHOOL_FUND,       'name' => 'School Fund / Equity',   'type' => 'equity',    'parent' => null],
            ['code' => self::CODE_INCOME_PARENT,     'name' => 'Income',                 'type' => 'income',    'parent' => null],
            ['code' => self::CODE_TUITION_INCOME,    'name' => 'Tuition Fees Income',    'type' => 'income',    'parent' => self::CODE_INCOME_PARENT],
            ['code' => self::CODE_OTHER_INCOME,      'name' => 'Other Income',           'type' => 'income',    'parent' => self::CODE_INCOME_PARENT],
            ['code' => self::CODE_EXPENSE_PARENT,    'name' => 'Operating Expenses',     'type' => 'expense',   'parent' => null],
            ['code' => self::CODE_SALARIES_EXPENSE,  'name' => 'Salaries & Wages',       'type' => 'expense',   'parent' => self::CODE_EXPENSE_PARENT],
        ];
    }

    /**
     * Create the default Chart of Accounts for a school if it doesn't
     * have any accounts yet. Safe to call repeatedly — it's a no-op once
     * accounts exist.
     */
    public static function seedDefaults(int $schoolId, ?int $createdBy = null): void
    {
        if (self::forSchool($schoolId)->exists()) {
            return;
        }

        $codeToId = [];

        foreach (self::defaultDefinitions() as $def) {
            if ($def['parent'] === null) {
                $account = self::create([
                    'school_id'    => $schoolId,
                    'account_code' => $def['code'],
                    'name'         => $def['name'],
                    'type'         => $def['type'],
                    'parent_id'    => null,
                    'is_system'    => true,
                    'created_by'   => $createdBy,
                ]);
                $codeToId[$def['code']] = $account->id;
            }
        }

        foreach (self::defaultDefinitions() as $def) {
            if ($def['parent'] !== null) {
                self::create([
                    'school_id'    => $schoolId,
                    'account_code' => $def['code'],
                    'name'         => $def['name'],
                    'type'         => $def['type'],
                    'parent_id'    => $codeToId[$def['parent']] ?? null,
                    'is_system'    => true,
                    'created_by'   => $createdBy,
                ]);
            }
        }
    }

    /**
     * Get (or lazily create) a default system account by its fixed code.
     * Used by Finance flows to auto-post transactions to the right account.
     */
    public static function findOrCreateDefault(int $schoolId, string $code): self
    {
        $existing = self::forSchool($schoolId)->where('account_code', $code)->first();
        if ($existing) {
            return $existing;
        }

        // Defaults not seeded yet for this school — seed them now, then fetch.
        self::seedDefaults($schoolId);

        return self::forSchool($schoolId)->where('account_code', $code)->firstOrFail();
    }

    /**
     * Find or create a leaf expense account under "Operating Expenses" that
     * mirrors an Expense Category, so every expense category automatically
     * has its own ledger account without any manual setup.
     */
    public static function findOrCreateForExpenseCategory(int $schoolId, int $categoryId, string $categoryName): self
    {
        $code = '5' . str_pad((string) $categoryId, 3, '0', STR_PAD_LEFT);

        $existing = self::forSchool($schoolId)->where('account_code', $code)->first();
        if ($existing) {
            return $existing;
        }

        $parent = self::findOrCreateDefault($schoolId, self::CODE_EXPENSE_PARENT);

        return self::create([
            'school_id'    => $schoolId,
            'account_code' => $code,
            'name'         => $categoryName,
            'type'         => 'expense',
            'parent_id'    => $parent->id,
            'is_system'    => true,
        ]);
    }
}