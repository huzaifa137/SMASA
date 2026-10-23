<?php

namespace App\Services;

use App\Models\BillingInvoice;
use App\Models\BillingInvoiceItem;
use App\Models\BillingLog;
use App\Models\BillingPayment;
use App\Models\BillingSchoolRate;
use App\Models\BillingSetting;
use App\Models\BillingTerm;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * All the money rules of the SaaS billing module live here, so controllers
 * stay thin and every figure is computed in exactly one place.
 *
 *   students  = unique physical students of the school (consolidated rows once)
 *   rate      = per-school override  ->  term rate  ->  default rate (settings)
 *   subtotal  = sum of invoice lines (students line + charges - credits)
 *   discount  = percent or fixed amount of the subtotal
 *   tax       = tax_rate % of (subtotal - discount)
 *   total     = subtotal - discount + tax
 *   balance   = total - valid payments
 */
class BillingService
{
    /** Schools with this school_status are live (Pending Activation = 1). */
    public const ACTIVE_SCHOOL_STATUS = 10;

    // ─── helpers ────────────────────────────────────────────────────────────

    public static function money($value): float
    {
        return round((float) $value, 0); // UGX has no cents
    }

    public static function fmt($value, ?string $currency = null): string
    {
        return ($currency ?? 'UGX') . ' ' . number_format((float) $value, 0);
    }

    public function settings(): BillingSetting
    {
        return BillingSetting::current();
    }

    public function log(string $action, string $description, array $ctx = []): void
    {
        BillingLog::create([
            'action'          => $action,
            'description'     => mb_substr($description, 0, 500),
            'billing_term_id' => $ctx['term_id'] ?? null,
            'invoice_id'      => $ctx['invoice_id'] ?? null,
            'school_id'       => $ctx['school_id'] ?? null,
            'user_id'         => session('LoggedAdmin') ?: null,
        ]);
    }

    /** 1250000 -> "One million two hundred fifty thousand" (whole numbers only). */
    public static function words(int $n): string
    {
        if ($n === 0) {
            return 'Zero';
        }
        $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve',
            'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        $below1000 = function (int $x) use ($ones, $tens): string {
            $out = '';
            if ($x >= 100) {
                $out .= $ones[intdiv($x, 100)] . ' hundred';
                $x %= 100;
                if ($x) {
                    $out .= ' and ';
                }
            }
            if ($x >= 20) {
                $out .= $tens[intdiv($x, 10)] . ($x % 10 ? '-' . $ones[$x % 10] : '');
            } elseif ($x > 0) {
                $out .= $ones[$x];
            }

            return $out;
        };

        $parts = [];
        foreach ([1000000000 => 'billion', 1000000 => 'million', 1000 => 'thousand'] as $div => $label) {
            if ($n >= $div) {
                $parts[] = $below1000(intdiv($n, $div)) . ' ' . $label;
                $n %= $div;
            }
        }
        if ($n > 0) {
            $rest = $below1000($n);
            $parts[] = (count($parts) && $n < 100 ? 'and ' : '') . $rest;
        }

        return ucfirst(trim(implode(' ', $parts)));
    }

    /** "Five hundred thousand Uganda Shillings only" */
    public static function amountInWords(float $amount, string $currency = 'UGX'): string
    {
        $names = ['UGX' => 'Uganda Shillings', 'USD' => 'US Dollars', 'KES' => 'Kenya Shillings'];

        return self::words((int) round($amount)) . ' ' . ($names[$currency] ?? $currency) . ' only';
    }

    // ─── students & pricing ────────────────────────────────────────────────

    /** Unique physical students (a child enrolled twice is counted once). */
    public function countStudents(int $schoolId): int
    {
        return Student::where('school_id', $schoolId)->whereNull('linked_student_id')->count();
    }

    /** Active schools, optionally narrowed to some ids. */
    public function activeSchools(?array $ids = null): Collection
    {
        $q = School::where('school_status', self::ACTIVE_SCHOOL_STATUS)->orderBy('name');
        if ($ids) {
            $q->whereIn('id', $ids);
        }

        return $q->get();
    }

    /**
     * The price a school pays for a term. A term-specific override beats a
     * general (all terms) override, which beats the term rate.
     *
     * @return array{exempt:bool,rate:float,discount_type:string,discount_value:float,reason:?string,source:string}
     */
    public function resolvePricing(int $schoolId, BillingTerm $term): array
    {
        $override = BillingSchoolRate::where('school_id', $schoolId)
            ->where(function ($q) use ($term) {
                $q->where('billing_term_id', $term->id)->orWhereNull('billing_term_id');
            })
            ->orderByRaw('billing_term_id IS NULL') // term-specific first
            ->orderByDesc('id')
            ->first();

        $rate = $term->rate_per_student > 0 ? $term->rate_per_student : $this->settings()->default_rate_per_student;

        return [
            'exempt'         => (bool) ($override->is_exempt ?? false),
            'rate'           => (float) (($override && $override->custom_rate !== null) ? $override->custom_rate : $rate),
            'discount_type'  => $override->discount_type ?? 'none',
            'discount_value' => (float) ($override->discount_value ?? 0),
            'reason'         => $override->reason ?? null,
            'source'         => $override ? ($override->billing_term_id ? 'school-term' : 'school') : 'term',
        ];
    }

    /** What generating invoices for a term would do (no writes). */
    public function preview(BillingTerm $term): array
    {
        $existing = BillingInvoice::where('billing_term_id', $term->id)->pluck('school_id')->all();

        $new = $exempt = $empty = $students = 0;
        $estimate = 0.0;

        foreach ($this->activeSchools() as $school) {
            if (in_array($school->id, $existing, true)) {
                continue;
            }
            $pricing = $this->resolvePricing($school->id, $term);
            if ($pricing['exempt']) {
                $exempt++;
                continue;
            }
            $count = $this->countStudents($school->id);
            if ($count === 0) {
                $empty++;
                continue;
            }
            $new++;
            $students += $count;
            $estimate += self::money($count * $pricing['rate']);
        }

        return [
            'schools_total'    => $this->activeSchools()->count(),
            'already_invoiced' => count($existing),
            'to_create'        => $new,
            'exempt'           => $exempt,
            'no_students'      => $empty,
            'students'         => $students,
            'estimate'         => $estimate,
        ];
    }

    // ─── invoices ──────────────────────────────────────────────────────────

    private function nextNumber(BillingTerm $term, int $sequence): string
    {
        $s = $this->settings();
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $s->invoice_prefix ?: 'INV')) ?: 'INV';
        $year = preg_match('/\d{4}/', $term->year_label, $m) ? $m[0] : now()->format('Y');
        $number = sprintf('%s-%s-T%d-%04d', $prefix, $year, $term->term, $sequence);

        if (BillingInvoice::where('invoice_number', $number)->exists()) {
            $number = sprintf('%s-%s-T%d-%d-%04d', $prefix, $year, $term->term, $term->id, $sequence);
        }

        return $number;
    }

    /**
     * Create the invoices for every active school that doesn't have one yet.
     * Safe to run again: schools already invoiced are left untouched.
     *
     * @return array{created:int,skipped_existing:int,exempt:int,no_students:int,total_expected:float}
     */
    public function generateForTerm(BillingTerm $term, bool $issueNow = true): array
    {
        $result = ['created' => 0, 'skipped_existing' => 0, 'exempt' => 0, 'no_students' => 0, 'total_expected' => 0.0];

        DB::transaction(function () use ($term, $issueNow, &$result) {
            // Serialise concurrent "generate" clicks on the same term.
            BillingTerm::where('id', $term->id)->lockForUpdate()->first();

            $settings = $this->settings();
            $existing = BillingInvoice::where('billing_term_id', $term->id)->pluck('school_id')->all();
            $sequence = (int) BillingInvoice::where('billing_term_id', $term->id)->max('sequence');

            foreach ($this->activeSchools() as $school) {
                if (in_array($school->id, $existing, true)) {
                    $result['skipped_existing']++;
                    continue;
                }

                $pricing = $this->resolvePricing($school->id, $term);
                if ($pricing['exempt']) {
                    $result['exempt']++;
                    continue;
                }

                $count = $this->countStudents($school->id);
                if ($count === 0) {
                    $result['no_students']++;
                    continue;
                }

                $sequence++;
                $invoice = BillingInvoice::create([
                    'invoice_number'  => $this->nextNumber($term, $sequence),
                    'billing_term_id' => $term->id,
                    'school_id'       => $school->id,
                    'sequence'        => $sequence,
                    'student_count'   => $count,
                    'rate_per_student' => $pricing['rate'],
                    'discount_type'   => $pricing['discount_type'],
                    'discount_value'  => $pricing['discount_value'],
                    'discount_reason' => $pricing['reason'],
                    'tax_name'        => $settings->tax_enabled ? $settings->tax_name : null,
                    'tax_rate'        => $settings->tax_enabled ? $settings->tax_rate : 0,
                    'status'          => 'draft',
                    'due_date'        => $term->due_date,
                    'counted_at'      => now(),
                    'created_by'      => session('LoggedAdmin') ?: null,
                ]);

                BillingInvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'type'        => 'students',
                    'description' => 'SMASA platform subscription — ' . $term->name . ' (per enrolled student)',
                    'quantity'    => $count,
                    'unit_price'  => $pricing['rate'],
                    'amount'      => self::money($count * $pricing['rate']),
                    'sort_order'  => 0,
                ]);

                $this->recalculate($invoice);
                if ($issueNow) {
                    $this->issue($invoice, false);
                }

                $result['created']++;
                $result['total_expected'] += $invoice->fresh()->total;
            }

            if ($result['created'] > 0 || !$term->invoices_generated_at) {
                $term->invoices_generated_at = now();
                $term->save();
            }
        });

        $this->log('generate', sprintf(
            'Generated %d invoice(s) for %s%s.',
            $result['created'],
            $term->name,
            $issueNow ? ' and issued them' : ' as drafts'
        ), ['term_id' => $term->id]);

        return $result;
    }

    /** Recompute every figure on an invoice from its lines and valid payments. */
    public function recalculate(BillingInvoice $invoice): BillingInvoice
    {
        $invoice->load('items');

        $subtotal = self::money($invoice->items->sum('amount'));

        $discount = 0.0;
        if ($invoice->discount_type === 'percent') {
            $discount = self::money(max($subtotal, 0) * min(max($invoice->discount_value, 0), 100) / 100);
        } elseif ($invoice->discount_type === 'fixed') {
            $discount = self::money(min(max($invoice->discount_value, 0), max($subtotal, 0)));
        }

        $taxable = max($subtotal - $discount, 0);
        $tax = self::money($taxable * max($invoice->tax_rate, 0) / 100);
        $total = $taxable + $tax;

        $paid = self::money(BillingPayment::where('invoice_id', $invoice->id)->valid()->sum('amount'));

        $invoice->subtotal = $subtotal;
        $invoice->discount_amount = $discount;
        $invoice->tax_amount = $tax;
        $invoice->total = $total;
        $invoice->amount_paid = $paid;
        $invoice->balance = max($total - $paid, 0);

        if (in_array($invoice->status, ['issued', 'partial', 'paid'], true)) {
            if ($invoice->balance <= 0.004) {
                $invoice->status = 'paid';
                $invoice->paid_at = $invoice->paid_at ?? now();
            } else {
                $invoice->status = $paid > 0 ? 'partial' : 'issued';
                $invoice->paid_at = null;
            }
        }

        $invoice->save();

        return $invoice;
    }

    public function issue(BillingInvoice $invoice, bool $log = true): BillingInvoice
    {
        if ($invoice->status !== 'draft') {
            throw new InvalidArgumentException('Only draft invoices can be issued.');
        }

        $settings = $this->settings();
        $invoice->status = 'issued';
        $invoice->issue_date = today();
        $invoice->due_date = $invoice->due_date ?? $invoice->term?->due_date ?? today()->addDays($settings->due_days);
        $invoice->save();

        $this->recalculate($invoice); // a 0-total invoice becomes "paid" straight away

        if ($log) {
            $this->log('issue', "Issued {$invoice->invoice_number}.", [
                'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
            ]);
        }

        return $invoice;
    }

    public function issueAllDrafts(BillingTerm $term): int
    {
        $n = 0;
        BillingInvoice::where('billing_term_id', $term->id)->where('status', 'draft')->get()
            ->each(function ($inv) use (&$n) {
                $this->issue($inv, false);
                $n++;
            });

        if ($n) {
            $this->log('issue', "Issued {$n} draft invoice(s) for {$term->name}.", ['term_id' => $term->id]);
        }

        return $n;
    }

    /** Re-take the student count (draft / unpaid / partially paid invoices only). */
    public function recount(BillingInvoice $invoice): BillingInvoice
    {
        if (!in_array($invoice->status, ['draft', 'issued', 'partial'], true)) {
            throw new InvalidArgumentException('This invoice can no longer be recounted.');
        }

        $count = $this->countStudents($invoice->school_id);
        $old = $invoice->student_count;

        DB::transaction(function () use ($invoice, $count) {
            $invoice->student_count = $count;
            $invoice->counted_at = now();
            $invoice->save();

            $line = $invoice->items()->where('type', 'students')->first();
            if ($line) {
                $line->quantity = $count;
                $line->unit_price = $invoice->rate_per_student;
                $line->amount = self::money($count * $invoice->rate_per_student);
                $line->save();
            }

            $this->recalculate($invoice);
        });

        $this->log('recount', "Recounted {$invoice->invoice_number}: {$old} → {$count} students.", [
            'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
        ]);

        return $invoice->fresh();
    }

    /**
     * Edit price / discount / dates / notes. Throws if the new total would be
     * lower than what the school has already paid.
     */
    public function updateInvoice(BillingInvoice $invoice, array $data): BillingInvoice
    {
        if (in_array($invoice->status, ['cancelled', 'waived'], true)) {
            throw new InvalidArgumentException('A cancelled or written-off invoice cannot be edited.');
        }

        DB::transaction(function () use ($invoice, $data) {
            if (isset($data['rate_per_student'])) {
                $invoice->rate_per_student = (float) $data['rate_per_student'];
                $line = $invoice->items()->where('type', 'students')->first();
                if ($line) {
                    $line->unit_price = $invoice->rate_per_student;
                    $line->amount = self::money($line->quantity * $invoice->rate_per_student);
                    $line->save();
                }
            }

            foreach (['discount_type', 'discount_value', 'discount_reason', 'due_date', 'notes', 'tax_rate'] as $f) {
                if (array_key_exists($f, $data)) {
                    $invoice->{$f} = $data[$f];
                }
            }
            if (($invoice->discount_type ?? 'none') === 'none') {
                $invoice->discount_value = 0;
            }
            $invoice->save();

            $this->recalculate($invoice);

            if ($invoice->total + 0.004 < $invoice->amount_paid) {
                throw new InvalidArgumentException(
                    'These changes would make the total lower than the ' . self::fmt($invoice->amount_paid) .
                    ' already paid. Reverse a payment first.'
                );
            }
        });

        $this->log('edit', "Edited {$invoice->invoice_number} (rate/discount/dates).", [
            'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
        ]);

        return $invoice->fresh();
    }

    public function addAdjustment(BillingInvoice $invoice, string $type, string $description, float $amount): BillingInvoice
    {
        if (!in_array($invoice->status, ['draft', 'issued', 'partial'], true)) {
            throw new InvalidArgumentException('Adjustments can only be added to live invoices.');
        }
        if (!in_array($type, ['charge', 'credit'], true) || $amount <= 0) {
            throw new InvalidArgumentException('Enter a positive amount and choose charge or credit.');
        }

        DB::transaction(function () use ($invoice, $type, $description, $amount) {
            BillingInvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'type'        => $type,
                'description' => $description,
                'quantity'    => 1,
                'unit_price'  => $type === 'credit' ? -$amount : $amount,
                'amount'      => $type === 'credit' ? -self::money($amount) : self::money($amount),
                'sort_order'  => (int) $invoice->items()->max('sort_order') + 1,
            ]);

            $this->recalculate($invoice);

            if ($invoice->total + 0.004 < $invoice->amount_paid) {
                throw new InvalidArgumentException('That credit is larger than what remains payable.');
            }
        });

        $this->log('adjust', ucfirst($type) . ' of ' . self::fmt($amount) . " added to {$invoice->invoice_number}: {$description}", [
            'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
        ]);

        return $invoice->fresh();
    }

    public function removeAdjustment(BillingInvoice $invoice, BillingInvoiceItem $item): BillingInvoice
    {
        if ($item->invoice_id !== $invoice->id || $item->type === 'students') {
            throw new InvalidArgumentException('That line cannot be removed.');
        }

        DB::transaction(function () use ($invoice, $item) {
            $item->delete();
            $this->recalculate($invoice);

            if ($invoice->total + 0.004 < $invoice->amount_paid) {
                throw new InvalidArgumentException('Removing that line would drop the total below what is already paid.');
            }
        });

        $this->log('adjust', "Removed a line from {$invoice->invoice_number}.", [
            'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
        ]);

        return $invoice->fresh();
    }

    public function cancel(BillingInvoice $invoice, string $reason): BillingInvoice
    {
        if ($invoice->payments()->valid()->exists()) {
            throw new InvalidArgumentException('This invoice has payments. Reverse them first, or write off the balance instead.');
        }
        if ($invoice->status === 'cancelled') {
            throw new InvalidArgumentException('Invoice is already cancelled.');
        }

        $invoice->status = 'cancelled';
        $invoice->cancel_reason = $reason;
        $invoice->save();

        $this->log('cancel', "Cancelled {$invoice->invoice_number}: {$reason}", [
            'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
        ]);

        return $invoice;
    }

    /** Write off whatever is still unpaid (kept for the record, no longer "outstanding"). */
    public function writeOff(BillingInvoice $invoice, string $reason): BillingInvoice
    {
        if (!$invoice->isCollectable()) {
            throw new InvalidArgumentException('Only an invoice with an unpaid balance can be written off.');
        }

        $invoice->status = 'waived';
        $invoice->cancel_reason = $reason;
        $invoice->save();

        $this->log('writeoff', "Wrote off {$invoice->invoice_number} (" . self::fmt($invoice->balance) . "): {$reason}", [
            'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
        ]);

        return $invoice;
    }

    /** Bring a cancelled / written-off invoice back as a draft. */
    public function restore(BillingInvoice $invoice): BillingInvoice
    {
        if (!in_array($invoice->status, ['cancelled', 'waived'], true)) {
            throw new InvalidArgumentException('Only cancelled or written-off invoices can be restored.');
        }

        $invoice->status = 'draft';
        $invoice->cancel_reason = null;
        $invoice->save();
        $this->recalculate($invoice);

        $this->log('restore', "Restored {$invoice->invoice_number} to draft.", [
            'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
        ]);

        return $invoice;
    }

    // ─── payments ──────────────────────────────────────────────────────────

    private function nextReceiptNumber(): string
    {
        $prefix = 'RCT-' . now()->format('Y') . '-';
        $last = BillingPayment::where('receipt_number', 'like', $prefix . '%')->max('receipt_number');
        $n = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
    }

    public function recordPayment(BillingInvoice $invoice, array $data): BillingPayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = BillingInvoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if (!$invoice->isCollectable()) {
                throw new InvalidArgumentException('This invoice is not open for payment (status: ' . $invoice->status_label . ').');
            }

            $amount = self::money($data['amount']);
            if ($amount <= 0) {
                throw new InvalidArgumentException('Enter an amount greater than zero.');
            }
            if ($amount > $invoice->balance + 0.004) {
                throw new InvalidArgumentException('Amount is more than the outstanding balance of ' . self::fmt($invoice->balance) . '.');
            }

            $payment = BillingPayment::create([
                'receipt_number'  => $this->nextReceiptNumber(),
                'invoice_id'      => $invoice->id,
                'billing_term_id' => $invoice->billing_term_id,
                'school_id'       => $invoice->school_id,
                'amount'          => $amount,
                'method'          => $data['method'] ?? 'bank',
                'reference'       => $data['reference'] ?? null,
                'paid_on'         => $data['paid_on'] ?? today(),
                'notes'           => $data['notes'] ?? null,
                'status'          => 'valid',
                'recorded_by'     => session('LoggedAdmin') ?: null,
            ]);

            $this->recalculate($invoice);

            $this->log('payment', 'Recorded ' . self::fmt($amount) . " ({$payment->method_label}) on {$invoice->invoice_number} — {$payment->receipt_number}.", [
                'term_id' => $invoice->billing_term_id, 'invoice_id' => $invoice->id, 'school_id' => $invoice->school_id,
            ]);

            return $payment;
        });
    }

    public function reversePayment(BillingPayment $payment, string $reason): BillingPayment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment = BillingPayment::where('id', $payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === 'reversed') {
                throw new InvalidArgumentException('This payment is already reversed.');
            }

            $payment->status = 'reversed';
            $payment->reversed_at = now();
            $payment->reversed_reason = $reason;
            $payment->save();

            $invoice = $payment->invoice;
            // A written-off invoice that gets a payment reversed stays written off.
            $this->recalculate($invoice);

            $this->log('reverse', "Reversed {$payment->receipt_number} (" . self::fmt($payment->amount) . "): {$reason}", [
                'term_id' => $payment->billing_term_id, 'invoice_id' => $payment->invoice_id, 'school_id' => $payment->school_id,
            ]);

            return $payment;
        });
    }

    // ─── reporting ─────────────────────────────────────────────────────────

    /**
     * Headline numbers for one term.
     * "Expected" = every live (issued, partial, paid, written-off) invoice total.
     */
    public function termStats(BillingTerm $term): array
    {
        $invoices = BillingInvoice::where('billing_term_id', $term->id)->get();
        $live = $invoices->whereNotIn('status', ['draft', 'cancelled']);

        $collectable = $live->whereIn('status', ['issued', 'partial']);
        $overdue = $collectable->filter(fn($i) => $i->isOverdue());

        $expected = (float) $live->sum('total');
        $collected = (float) $live->sum('amount_paid');
        $outstanding = (float) $collectable->sum('balance');
        $writtenOff = (float) $live->where('status', 'waived')->sum('balance');

        $invoicedSchoolIds = $invoices->pluck('school_id')->all();
        $active = $this->activeSchools();

        return [
            'expected'        => $expected,
            'collected'       => $collected,
            'outstanding'     => $outstanding,
            'overdue_amount'  => (float) $overdue->sum('balance'),
            'written_off'     => $writtenOff,
            'rate_pct'        => $expected > 0 ? round(($collected / $expected) * 100, 1) : 0.0,
            'subtotal'        => (float) $live->sum('subtotal'),
            'discounts'       => (float) $live->sum('discount_amount'),
            'tax'             => (float) $live->sum('tax_amount'),
            'students'        => (int) $live->sum('student_count'),
            'schools_billed'  => $live->count(),
            'count_paid'      => $live->where('status', 'paid')->count(),
            'count_partial'   => $collectable->where('status', 'partial')->filter(fn($i) => !$i->isOverdue())->count(),
            'count_unpaid'    => $collectable->where('status', 'issued')->filter(fn($i) => !$i->isOverdue())->count(),
            'count_overdue'   => $overdue->count(),
            'count_draft'     => $invoices->where('status', 'draft')->count(),
            'count_cancelled' => $invoices->where('status', 'cancelled')->count(),
            'count_writeoff'  => $live->where('status', 'waived')->count(),
            'not_invoiced'    => $active->reject(fn($s) => in_array($s->id, $invoicedSchoolIds, true))->values(),
            'active_schools'  => $active->count(),
        ];
    }

    /** Expected vs collected for the most recent terms (oldest first), for the trend chart. */
    public function trend(int $limit = 8): Collection
    {
        return BillingTerm::orderByDesc('id')->limit($limit)->get()->reverse()->values()->map(function ($t) {
            $live = BillingInvoice::where('billing_term_id', $t->id)->billable();

            return [
                'label'     => $t->term_label . ' ' . $t->year_label,
                'expected'  => (float) (clone $live)->sum('total'),
                'collected' => (float) (clone $live)->sum('amount_paid'),
            ];
        });
    }

    /**
     * Aged receivables: overdue balances bucketed by how late they are.
     * Optionally limited to one term.
     */
    public function aging(?int $termId = null): array
    {
        $q = BillingInvoice::with(['school:id,name,registration_code', 'term'])->overdue();
        if ($termId) {
            $q->where('billing_term_id', $termId);
        }

        $buckets = [
            '1–30 days'  => ['min' => 1,  'max' => 30,   'amount' => 0.0, 'count' => 0],
            '31–60 days' => ['min' => 31, 'max' => 60,   'amount' => 0.0, 'count' => 0],
            '61–90 days' => ['min' => 61, 'max' => 90,   'amount' => 0.0, 'count' => 0],
            '90+ days'   => ['min' => 91, 'max' => 99999, 'amount' => 0.0, 'count' => 0],
        ];

        $rows = $q->get();
        foreach ($rows as $inv) {
            $days = $inv->daysOverdue();
            foreach ($buckets as $label => $b) {
                if ($days >= $b['min'] && $days <= $b['max']) {
                    $buckets[$label]['amount'] += $inv->balance;
                    $buckets[$label]['count']++;
                    break;
                }
            }
        }

        return ['buckets' => $buckets, 'rows' => $rows->sortByDesc(fn($i) => $i->daysOverdue())->values()];
    }

    /** A school's lifetime position across all terms. */
    public function schoolPosition(int $schoolId): array
    {
        $inv = BillingInvoice::where('school_id', $schoolId)->visibleToSchool()->get();
        $collectable = $inv->whereIn('status', ['issued', 'partial']);

        return [
            'billed'      => (float) $inv->whereNotIn('status', ['cancelled'])->sum('total'),
            'paid'        => (float) $inv->whereNotIn('status', ['cancelled'])->sum('amount_paid'),
            'outstanding' => (float) $collectable->sum('balance'),
            'overdue'     => (float) $collectable->filter(fn($i) => $i->isOverdue())->sum('balance'),
            'invoices'    => $inv->count(),
        ];
    }
}
