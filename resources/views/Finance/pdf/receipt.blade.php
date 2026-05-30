<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Receipt {{ $payment->receipt_number }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'DejaVu Sans',Arial,sans-serif;
    background:#fff;
    color:#1e293b;
    font-size:9.5px;
    line-height:1.35;
}

.page {
    width:100%;
    max-width:520px;
    margin:0 auto;
}

/* HEADER */
.hdr {
    background:#0f172a;
    padding:14px 22px 12px;
    position:relative;
    overflow:hidden;
}
.hdr::after {
    content:'';
    position:absolute;
    top:-35px; right:-35px;
    width:110px; height:110px;
    border-radius:50%;
    background:rgba(5,150,105,.18);
}
.hdr-inner { position:relative; z-index:1; display:table; width:100%; }
.hdr-left  { display:table-cell; vertical-align:middle; }
.hdr-right { display:table-cell; vertical-align:middle; text-align:right; }

.school-name    { font-size:14px; font-weight:700; color:#fff; letter-spacing:.4px; }
.school-sub     { font-size:8px; color:#94a3b8; margin-top:1px; }
.school-contact { font-size:7.5px; color:#64748b; margin-top:3px; }

.receipt-badge {
    display:inline-block;
    background:#059669; color:#fff;
    font-size:7px; font-weight:700;
    letter-spacing:1.2px; text-transform:uppercase;
    padding:3px 10px; border-radius:20px; margin-bottom:3px;
}
.receipt-num  { font-size:12px; font-weight:700; color:#fff; font-family:'DejaVu Sans Mono',monospace; }
.receipt-date { font-size:7.5px; color:#94a3b8; margin-top:2px; }

/* STATUS STRIPE */
.status-stripe {
    background:#059669;
    padding:5px 22px;
    display:table; width:100%;
}
.ss-left  { display:table-cell; vertical-align:middle; }
.ss-right { display:table-cell; vertical-align:middle; text-align:right; }
.s-lbl { font-size:7px; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.7px; }
.s-val { font-size:10px; font-weight:700; color:#fff; }

/* BODY */
.body { padding:14px 22px; }

.sec-label {
    font-size:7px; font-weight:700;
    letter-spacing:1.1px; text-transform:uppercase;
    color:#059669;
    border-bottom:1.5px solid #e2e8f0;
    padding-bottom:3px; margin-bottom:8px;
}

/* Info grid */
.info-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
.info-table td { padding:3px 5px 3px 0; vertical-align:top; width:50%; }
.info-table td:nth-child(even) { padding-left:10px; }
.ikey { font-size:7px; color:#64748b; text-transform:uppercase; letter-spacing:.4px; margin-bottom:1px; }
.ival { font-size:10px; font-weight:600; color:#0f172a; }
.ival-sm { font-size:9px; font-weight:600; color:#0f172a; }

/* Payment table */
.pay-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
.pay-table thead tr { background:#f1f5f9; }
.pay-table th {
    padding:6px 8px;
    font-size:7px; text-transform:uppercase;
    letter-spacing:.5px; color:#475569; font-weight:700; text-align:left;
}
.pay-table th:last-child { text-align:right; }
.pay-table td {
    padding:6px 8px;
    font-size:9.5px; color:#334155;
    border-bottom:1px solid #f1f5f9;
}
.pay-table td:last-child { text-align:right; font-weight:600; }
.pay-table tfoot tr { background:#f8fafc; }
.pay-table tfoot td { padding:7px 8px; font-size:9px; border-bottom:none; color:#475569; }

/* Total box */
.total-box {
    background:#0f172a;
    border-radius:7px;
    padding:10px 15px;
    margin-bottom:10px;
    display:table; width:100%;
}
.tb-left  { display:table-cell; vertical-align:middle; }
.tb-right { display:table-cell; vertical-align:middle; text-align:right; }
.total-label  { font-size:7.5px; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; }
.total-amount { font-size:17px; font-weight:700; color:#34d399; font-family:'DejaVu Sans Mono',monospace; }
.total-currency { font-size:9px; color:#64748b; margin-right:2px; }
.balance-line { font-size:8px; color:#94a3b8; margin-top:3px; }
.balance-amt  { font-weight:700; }
.balance-ok   { color:#34d399; }
.balance-warn { color:#fbbf24; }

.verified-chip {
    display:inline-block;
    border:1.5px solid #059669; color:#059669;
    font-size:7px; font-weight:700;
    padding:2px 8px; border-radius:20px;
    letter-spacing:.5px; text-transform:uppercase;
}

/* Method row */
.method-row { display:table; width:100%; margin-bottom:10px; }
.method-cell { display:table-cell; vertical-align:top; width:50%; }
.method-pill {
    display:inline-block;
    background:#dbeafe; color:#1e40af;
    font-size:8px; font-weight:700;
    padding:3px 10px; border-radius:20px;
    letter-spacing:.2px;
}

/* Notes */
.notes-box {
    background:#fffbeb;
    border-left:3px solid #f59e0b;
    padding:6px 10px;
    border-radius:0 5px 5px 0;
    margin-bottom:10px;
}
.notes-title { font-size:7px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
.notes-text  { font-size:8.5px; color:#78350f; }

/* Divider */
.divider { border:none; border-top:1.5px dashed #e2e8f0; margin:10px 0; }

/* Signatures */
.sig-table { width:100%; border-collapse:collapse; margin-top:14px; }
.sig-table td { width:33.33%; text-align:center; padding:0 6px; vertical-align:bottom; }
.sig-line {
    border-top:1px solid #cbd5e1;
    padding-top:4px; margin-top:20px;
    font-size:7.5px; color:#64748b;
}

/* FOOTER */
.ftr {
    background:#f8fafc;
    border-top:1px solid #e2e8f0;
    padding:8px 22px;
    text-align:center;
}
.ftr-main      { font-size:7.5px; color:#64748b; margin-bottom:2px; }
.ftr-sub       { font-size:7px; color:#94a3b8; }
.ftr-generated { font-size:7px; color:#cbd5e1; margin-top:3px; }

@page { margin:0; }
</style>
</head>
<body>
<div class="page">

{{-- HEADER --}}
<div class="hdr">
    <div class="hdr-inner">
        <div class="hdr-left">
            <div class="school-name">{{ $school->name ?? 'SMASA SCHOOL' }}</div>
            <div class="school-sub">Excellence in Education &nbsp;·&nbsp; Uganda</div>
            @if($school && ($school->phone || $school->email))
            <div class="school-contact">
                @if($school->phone) Tel: {{ $school->phone }} @endif
                @if($school->email) &nbsp;·&nbsp; {{ $school->email }} @endif
            </div>
            @endif
        </div>
        <div class="hdr-right">
            <div><span class="receipt-badge">Official Receipt</span></div>
            <div class="receipt-num">{{ $payment->receipt_number }}</div>
            <div class="receipt-date">{{ $payment->payment_date ? $payment->payment_date->format('d F Y') : now()->format('d F Y') }}</div>
        </div>
    </div>
</div>

{{-- STATUS STRIPE --}}
<div class="status-stripe">
    <div class="ss-left">
        <div class="s-lbl">Payment Status</div>
        <div class="s-val">&#10003; {{ strtoupper($payment->status ?? 'CONFIRMED') }}</div>
    </div>
    <div class="ss-right">
        <div class="s-lbl">Academic Year &amp; Term</div>
        <div class="s-val">{{ $payment->academic_year }} &nbsp;·&nbsp; Term {{ $payment->term }}</div>
    </div>
</div>

{{-- BODY --}}
<div class="body">

    {{-- Student Info --}}
    <div class="sec-label">Student Information</div>
    <table class="info-table">
        <tr>
            <td>
                <div class="ikey">Full Name</div>
                <div class="ival">{{ $payment->student->firstname ?? 'N/A' }} {{ $payment->student->lastname ?? '' }}</div>
            </td>
            <td>
                <div class="ikey">Admission Number</div>
                <div class="ival">{{ $payment->student->admission_number ?? 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="ikey">Class / Level</div>
                <div class="ival-sm">
                    @php $clsName = $payment->student->senior ?? null;
                    if($clsName) $clsName = \App\Http\Controllers\Helper::recordMdname($clsName) ?? $clsName; @endphp
                    {{ $clsName ?? '—' }}
                </div>
            </td>
            <td>
                <div class="ikey">Stream</div>
                <div class="ival-sm">
                    @php $streamName = $payment->student->stream ?? null;
                    if($streamName) $streamName = \App\Http\Controllers\Helper::recordMdname($streamName) ?? $streamName; @endphp
                    {{ $streamName ?? '—' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Payment Breakdown --}}
    <div class="sec-label">Payment Breakdown</div>
    <table class="pay-table">
        <thead>
            <tr>
                <th style="width:55%">Description</th>
                <th>Category</th>
                <th>Amount (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @if($payment->allocation && $payment->allocation->feeStructure && $payment->allocation->feeStructure->items->count())
                @foreach($payment->allocation->feeStructure->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td style="font-size:8px;color:#64748b;text-transform:capitalize">{{ $item->category }}</td>
                    <td>{{ number_format($item->amount, 0) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>School Fees — Term {{ $payment->term }}, {{ $payment->academic_year }}</td>
                    <td style="font-size:8px;color:#64748b">Tuition</td>
                    <td>{{ number_format($payment->amount_paid, 0) }}</td>
                </tr>
            @endif
        </tbody>
        @if($payment->allocation && $payment->allocation->discount_amount > 0)
        <tfoot>
            <tr>
                <td colspan="2" style="color:#dc2626;font-weight:600">Discount — {{ $payment->allocation->discount_reason ?? 'Special Discount' }}</td>
                <td style="color:#dc2626;font-weight:600;text-align:right">- {{ number_format($payment->allocation->discount_amount, 0) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Grand Total --}}
    @php
        $remaining = $payment->allocation ? $payment->allocation->balance : 0;
    @endphp
    <div class="total-box">
        <div class="tb-left">
            <div class="total-label">Amount Paid This Transaction</div>
            <div class="total-amount"><span class="total-currency">UGX</span>{{ number_format($payment->amount_paid, 0) }}</div>
            @if($payment->allocation)
            <div class="balance-line">
                Remaining Balance:
                <span class="balance-amt {{ $remaining <= 0 ? 'balance-ok' : 'balance-warn' }}">UGX {{ number_format(max(0,$remaining), 0) }}</span>
                @if($remaining <= 0) &nbsp;&#183; <span class="balance-ok">FULLY CLEARED</span> @endif
            </div>
            @endif
        </div>
        <div class="tb-right">
            <div class="verified-chip">&#10003; Verified</div>
        </div>
    </div>

    {{-- Payment Details --}}
    <div class="sec-label">Payment Details</div>
    <div class="method-row">
        <div class="method-cell">
            <div class="ikey">Payment Method</div>
            <div style="margin-top:3px">
                <span class="method-pill">
                    @switch($payment->payment_method)
                        @case('cash') Cash @break
                        @case('bank_transfer') Bank Transfer @break
                        @case('mobile_money') Mobile Money @break
                        @case('cheque') Cheque @break
                        @default Other
                    @endswitch
                </span>
            </div>
        </div>
        <div class="method-cell">
            @if($payment->received_by)
            <div class="ikey">Received By</div>
            <div class="ival-sm">{{ $payment->received_by }}</div>
            @endif
            @if($payment->confirmed_at)
            <div class="ikey" style="margin-top:3px">Confirmed At</div>
            <div class="ival-sm">{{ $payment->confirmed_at->format('d M Y, H:i') }}</div>
            @endif
        </div>
    </div>

    @if($payment->transaction_reference || $payment->bank_name)
    <table class="info-table" style="margin-bottom:10px;margin-top:-4px">
        <tr>
            @if($payment->transaction_reference)
            <td><div class="ikey">Reference No.</div><div class="ival-sm">{{ $payment->transaction_reference }}</div></td>
            @endif
            @if($payment->bank_name)
            <td><div class="ikey">Bank</div><div class="ival-sm">{{ $payment->bank_name }}</div></td>
            @endif
        </tr>
    </table>
    @endif

    {{-- Notes --}}
    @if($payment->notes)
    <div class="notes-box">
        <div class="notes-title">Notes</div>
        <div class="notes-text">{{ $payment->notes }}</div>
    </div>
    @endif

    <hr class="divider">

    {{-- Signatures --}}
    <table class="sig-table">
        <tr>
            <td><div class="sig-line">Student / Parent</div></td>
            <td><div class="sig-line">Finance Officer</div></td>
            <td><div class="sig-line">Head Teacher</div></td>
        </tr>
    </table>

</div>

{{-- FOOTER --}}
<div class="ftr">
    <div class="ftr-main">Official computer-generated receipt — {{ $school->name ?? 'SMASA School' }}</div>
    <div class="ftr-sub">Keep this receipt for your records. For queries, contact the school finance office.</div>
    <div class="ftr-generated">Generated: {{ now()->format('d F Y \a\t H:i:s') }} &nbsp;·&nbsp; SMASA School Management System</div>
</div>

</div>
</body>
</html>
