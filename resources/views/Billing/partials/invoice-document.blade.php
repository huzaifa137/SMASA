{{--
    The invoice itself. Used by the on-screen pages AND the PDF (dompdf), so it is
    deliberately table-based: no flexbox, no grid.

    Expects: $invoice (items, payments, term, school loaded), $school, $profile (nullable), $settings
--}}
@php
    use App\Services\BillingService;

    $cur      = $settings->currency ?: 'UGX';
    $money    = fn ($v) => number_format((float) $v, 0);
    $status   = $invoice->display_status;
    $palette  = [
        'paid'      => ['#047857', '#d1fae5'],
        'issued'    => ['#b45309', '#fef3c7'],
        'partial'   => ['#2f2ccb', '#e0e7ff'],
        'overdue'   => ['#b91c1c', '#fee2e2'],
        'draft'     => ['#475569', '#e2e8f0'],
        'cancelled' => ['#475569', '#e2e8f0'],
        'waived'    => ['#6d28d9', '#ede9fe'],
    ];
    [$stFg, $stBg] = $palette[$status] ?? $palette['draft'];
    $validPayments = $invoice->payments->where('status', 'valid');
    // Round logo with the same indigo ring as the sidebar (dompdf cannot clip an <img>, so it is baked into a PNG).
    $companyLogo   = \App\Support\BrandLogo::circleDataUri(180);
    $schoolLogo    = $profile ? $profile->logoDataUri() : null;
    $term          = $invoice->term;
    $period        = $term ? $term->name : '';
    if ($term && $term->start_date && $term->end_date) {
        $period .= '  (' . $term->start_date->format('j M Y') . ' – ' . $term->end_date->format('j M Y') . ')';
    }
@endphp

<style>
    .inv-sheet { font-family: 'DejaVu Sans', 'DM Sans', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #ffffff; }
    .inv-sheet table { border-collapse: collapse; width: 100%; }
    .inv-sheet td, .inv-sheet th { vertical-align: top; }
    .inv-head td { background: #1a1790; color: #ffffff; padding: 16px 28px; }
    .inv-brand-name { font-size: 20px; font-weight: bold; color: #ffffff; letter-spacing: .5px; }
    .inv-brand-sub { font-size: 10px; color: #c7c8f5; margin-top: 2px; }
    .inv-brand-meta { font-size: 9.5px; color: #dfe0fb; line-height: 1.55; margin-top: 8px; }
    .inv-title { font-size: 30px; font-weight: bold; color: #ffffff; letter-spacing: 3px; text-align: right; }
    .inv-no { font-size: 12px; color: #dfe0fb; text-align: right; margin-top: 4px; }
    .inv-stripe td { height: 5px; background: #f59e0b; font-size: 1px; line-height: 1px; }
    .inv-body { padding: 16px 28px 2px 28px; }
    .inv-label { font-size: 8.5px; font-weight: bold; letter-spacing: 1.4px; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
    .inv-school { font-size: 15px; font-weight: bold; color: #0f172a; }
    .inv-small { font-size: 10px; color: #475569; line-height: 1.6; }
    .inv-meta td { padding: 4px 0; font-size: 10.5px; }
    .inv-meta td.k { color: #64748b; width: 42%; }
    .inv-meta td.v { text-align: right; font-weight: bold; color: #0f172a; }
    .inv-status { display: inline-block; padding: 3px 11px; border-radius: 10px; font-size: 9.5px; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; }
    .inv-period { background: #f1f5ff; border-left: 4px solid #2f2ccb; padding: 8px 12px; font-size: 10.5px; margin: 12px 0 10px 0; color: #1e293b; }
    .inv-items th { background: #f1f5f9; color: #475569; font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; padding: 8px 10px; text-align: left; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
    .inv-items td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10.5px; }
    .inv-items .r, .inv-totals .r { text-align: right; }
    .inv-totals td { padding: 5px 10px; font-size: 10.5px; }
    .inv-totals .k { color: #475569; }
    .inv-totals .grand td { background: #eef2ff; font-size: 12px; font-weight: bold; color: #1a1790; border-top: 1px solid #c7d2fe; border-bottom: 1px solid #c7d2fe; padding: 8px 10px; }
    .inv-totals .due td { background: #1a1790; color: #ffffff; font-size: 13px; font-weight: bold; padding: 10px; }
    .inv-words { font-size: 9.5px; color: #475569; font-style: italic; margin-top: 10px; }
    .inv-h2 { font-size: 9px; font-weight: bold; letter-spacing: 1.3px; text-transform: uppercase; color: #1a1790; margin: 12px 0 5px 0; }
    .inv-pay th { background: #f8fafc; font-size: 8.5px; color: #64748b; text-transform: uppercase; letter-spacing: .8px; padding: 6px 8px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    .inv-pay td { padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #f1f5f9; }
    .inv-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; font-size: 10px; color: #334155; line-height: 1.5; }
    .inv-kv { width: 100%; border-collapse: collapse; }
    .inv-kv td { padding: 4px 0; font-size: 10px; color: #334155; vertical-align: bottom; }
    .inv-kv td.k { width: 118px; white-space: nowrap; font-weight: bold; }
    .inv-kv td.c { width: 14px; text-align: center; }
    .inv-kv td.v { border-bottom: 1px solid #94a3b8; height: 15px; }
    .inv-kv td.t { padding: 3px 0; }
    .inv-foot td { padding: 8px 28px 10px 28px; font-size: 8.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    .inv-paidmark { font-size: 9px; color: #047857; font-weight: bold; }

    .inv-items th {
    background: #2C29CA;
    color: #ffffff;
    font-size: 8.5px;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 8px 10px;
    text-align: left;
    border-top: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
}

.inv-pay th {
    background: #2C29CA !important;
    color: #ffffff !important;
    font-size: 8.5px;
    letter-spacing: .8px;
    text-transform: uppercase;
    padding: 6px 8px;
    text-align: left;
    border-bottom: 1px solid #cbd5e1;
}
</style>

<div class="inv-sheet">
    {{-- ── Header band ─────────────────────────────────────────────── --}}
    <table class="inv-head">
        <tr>
            <td style="width:58%;">
                <table style="width:auto;"><tr>
                    @if ($companyLogo)
                        <td style="background:transparent; padding:0; width:60px;"><img src="{{ $companyLogo }}" style="width:56px; height:56px;" alt=""></td>
                        <td style="background:transparent; padding:0 0 0 12px;">
                    @else
                        <td style="background:transparent; padding:0;">
                    @endif
                        <div class="inv-brand-name">{{ $settings->company_name }}</div>
                        @if ($settings->company_tagline)<div class="inv-brand-sub">{{ $settings->company_tagline }}</div>@endif
                    </td>
                </tr></table>
                <div class="inv-brand-meta">
                    @if ($settings->company_address){{ $settings->company_address }}<br>@endif
                    @if ($settings->company_phone)Tel: {{ $settings->company_phone }}@endif
                    @if ($settings->company_phone && $settings->company_email) &nbsp;|&nbsp; @endif
                    @if ($settings->company_email){{ $settings->company_email }}@endif
                    @if ($settings->company_website)<br>{{ $settings->company_website }}@endif
                    @if ($settings->company_tin)<br>TIN: {{ $settings->company_tin }}@endif
                </div>
            </td>
            <td style="width:42%;">
                <div class="inv-title">INVOICE</div>
                <div class="inv-no">{{ $invoice->invoice_number }}</div>
            </td>
        </tr>
    </table>
    <table class="inv-stripe"><tr><td>&nbsp;</td></tr></table>

    <div class="inv-body">
        {{-- ── Bill to / details ────────────────────────────────────── --}}
        <table>
            <tr>
                <td style="width:55%; padding-right:20px;">
                    <div class="inv-label">Billed to</div>
                    <table style="width:auto;"><tr>
                        @if ($schoolLogo)<td style="padding:0 10px 0 0;"><img src="{{ $schoolLogo }}" style="height:42px;" alt=""></td>@endif
                        <td style="padding:0;">
                            <div class="inv-school">{{ $school->name }}</div>
                            <div class="inv-small">
                                @if ($school->registration_code)School code: {{ $school->registration_code }}<br>@endif
                                @if ($school->email){{ $school->email }}<br>@endif
                                @if ($school->phone)Tel: {{ $school->phone }}<br>@endif
                                @if ($profile && $profile->head_teacher_name)Attn: {{ $profile->head_teacher_name }}@endif
                            </div>
                        </td>
                    </tr></table>
                </td>
                <td style="width:45%;">
                    <table class="inv-meta">
                        <tr><td class="k">Invoice no.</td><td class="v">{{ $invoice->invoice_number }}</td></tr>
                        <tr><td class="k">Issue date</td><td class="v">{{ $invoice->issue_date ? $invoice->issue_date->format('j M Y') : 'Not issued' }}</td></tr>
                        <tr><td class="k">Due date</td><td class="v">{{ $invoice->due_date ? $invoice->due_date->format('j M Y') : '—' }}</td></tr>
                        <tr><td class="k">Status</td><td class="v"><span class="inv-status" style="background:{{ $stBg }}; color:{{ $stFg }};">{{ $invoice->status_label }}</span></td></tr>
                        @if ($invoice->isOverdue())
                            <tr><td class="k">Days overdue</td><td class="v" style="color:#b91c1c;">{{ $invoice->daysOverdue() }}</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <div class="inv-period"><strong>Billing period:</strong> {{ $period }}</div>

        {{-- ── Lines ─────────────────────────────────────────────── --}}
        <table class="inv-items">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th>Description</th>
                    <th class="r" style="width:12%;">Qty</th>
                    <th class="r" style="width:18%;">Unit price ({{ $cur }})</th>
                    <th class="r" style="width:19%;">Amount ({{ $cur }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $item->description }}
                            @if ($item->type === 'students')
                                <div style="font-size:9px; color:#94a3b8; margin-top:2px;">Students enrolled at time of billing: {{ number_format($invoice->student_count) }}</div>
                            @endif
                        </td>
                        <td class="r">{{ $item->type === 'students' ? number_format($item->quantity) : '1' }}</td>
                        <td class="r">{{ $item->unit_price < 0 ? '(' . $money(abs($item->unit_price)) . ')' : $money($item->unit_price) }}</td>
                        <td class="r">{{ $item->amount < 0 ? '(' . $money(abs($item->amount)) . ')' : $money($item->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Totals ────────────────────────────────────────────── --}}
        <table style="margin-top:12px;">
            <tr>
                <td style="width:50%; padding-right:18px;">
                    <div class="inv-words"><strong style="font-style:normal; color:#1e293b;">Amount in words:</strong><br>{{ BillingService::amountInWords($invoice->total, $cur) }}</div>
                    @if ($invoice->notes)
                        <div class="inv-label" style="margin-top:14px;">Note</div>
                        <div class="inv-small">{!! nl2br(e($invoice->notes)) !!}</div>
                    @endif
                </td>
                <td style="width:50%;">
                    <table class="inv-totals">
                        <tr><td class="k">Subtotal</td><td class="r">{{ $cur }} {{ $money($invoice->subtotal) }}</td></tr>
                        @if ($invoice->discount_amount > 0)
                            <tr>
                                <td class="k">Discount
                                    @if ($invoice->discount_type === 'percent') ({{ rtrim(rtrim(number_format($invoice->discount_value, 2), '0'), '.') }}%)@endif
                                    @if ($invoice->discount_reason)<div style="font-size:8.5px; color:#94a3b8;">{{ $invoice->discount_reason }}</div>@endif
                                </td>
                                <td class="r" style="color:#047857;">– {{ $cur }} {{ $money($invoice->discount_amount) }}</td>
                            </tr>
                            <tr><td class="k">Amount before tax</td><td class="r">{{ $cur }} {{ $money($invoice->subtotal - $invoice->discount_amount) }}</td></tr>
                        @endif
                        @if ($invoice->tax_rate > 0)
                            <tr><td class="k">{{ $invoice->tax_name ?: 'Tax' }} ({{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}%)</td><td class="r">{{ $cur }} {{ $money($invoice->tax_amount) }}</td></tr>
                        @endif
                        <tr class="grand"><td>Total</td><td class="r">{{ $cur }} {{ $money($invoice->total) }}</td></tr>
                        @if ($invoice->amount_paid > 0)
                            <tr><td class="k">Amount paid</td><td class="r" style="color:#047857;">– {{ $cur }} {{ $money($invoice->amount_paid) }}</td></tr>
                        @endif
                        @if (in_array($invoice->status, ['cancelled', 'waived'], true))
                            <tr class="due"><td>{{ $invoice->status === 'cancelled' ? 'Cancelled' : 'Written off' }}</td><td class="r">{{ $cur }} {{ $money($invoice->balance) }}</td></tr>
                        @elseif ($invoice->status === 'draft')
                            <tr class="due"><td>Amount due (draft)</td><td class="r">{{ $cur }} {{ $money($invoice->balance) }}</td></tr>
                        @else
                            <tr class="due"><td>{{ $invoice->balance <= 0 ? 'Balance' : 'Balance due' }}</td><td class="r">{{ $cur }} {{ $money($invoice->balance) }}</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        {{-- ── Payments received ─────────────────────────────────── --}}
        @if ($validPayments->count())
            <div class="inv-h2">Payments received</div>
            <table class="inv-pay">
                <thead><tr><th>Date</th><th>Receipt</th><th>Method</th><th>Reference</th><th style="text-align:right;">Amount ({{ $cur }})</th></tr></thead>
                <tbody>
                    @foreach ($validPayments as $p)
                        <tr>
                            <td>{{ $p->paid_on->format('j M Y') }}</td>
                            <td>{{ $p->receipt_number }}</td>
                            <td>{{ $p->method_label }}</td>
                            <td>{{ $p->reference ?: '—' }}</td>
                            <td style="text-align:right;">{{ $money($p->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- ── How to pay ────────────────────────────────────────── --}}
        @if ($settings->payment_instructions && !in_array($invoice->status, ['paid', 'cancelled', 'waived'], true))
            <div class="inv-h2">How to pay</div>
            @php
                // "Label: value" lines become aligned rows (label | colon | value); a value that is blank or only
                // underscores is drawn as a fill-in line that runs to the right edge of the box.
                $payLines = preg_split('/\r\n|\r|\n/', (string) $settings->payment_instructions);
            @endphp
            <div class="inv-box">
                <table class="inv-kv">
                    @foreach ($payLines as $line)
                        @if (trim($line) === '') @continue @endif
                        @if (preg_match('/^\s*([^:]{1,40}?)\s*:\s*(.*)$/u', $line, $m))
                            <tr>
                                <td class="k">{{ $m[1] }}</td>
                                <td class="c">:</td>
                                <td class="v">{{ preg_match('/^[\s_.\-]*$/', $m[2]) ? '' : trim($m[2]) }}</td>
                            </tr>
                        @else
                            <tr><td class="t" colspan="3">{{ trim($line) }}</td></tr>
                        @endif
                    @endforeach
                    <tr>
                        <td class="k">Reference</td>
                        <td class="c">:</td>
                        <td class="v"><strong>{{ $invoice->invoice_number }}</strong></td>
                    </tr>
                </table>
            </div>
        @endif

        @if ($settings->invoice_notes)
            <div class="inv-h2">Terms &amp; notes</div>
            <div class="inv-small">{!! nl2br(e($settings->invoice_notes)) !!}</div>
        @endif
    </div>

    <table class="inv-foot"><tr>
        <td>This is a computer-generated invoice and is valid without a signature. &nbsp;·&nbsp; {{ $settings->company_name }} &nbsp;·&nbsp; Generated {{ now()->format('j M Y, H:i') }}</td>
    </tr></table>
</div>
