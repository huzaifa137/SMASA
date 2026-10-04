<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Receipt {{ $payment->receipt_number }}</title>
<style>
/* 80 mm till-roll receipt (supermarket style). Paper height is measured
   and set by FinanceController so the page is exactly as long as the receipt. */
@page { margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 8.5px;
    line-height: 1.35;
    color: #000;
    background: #fff;
}
#rc { padding: 14px 14px 12px; }

.c { text-align: center; }
.title  { font-size: 13px; font-weight: bold; letter-spacing: 1px; }
.stars  { font-size: 8px; letter-spacing: 1px; }
.school { font-size: 12px; font-weight: bold; margin-top: 2px; }
.small  { font-size: 7.5px; }

table { width: 100%; border-collapse: collapse; }
td { vertical-align: top; padding: 1px 0; }
.k { width: 38%; }
.v { text-align: left; }
.amt { text-align: right; white-space: nowrap; padding-left: 6px; }

.rule-eq   { border-top: 3px double #000; margin: 6px 0; }
.rule-dash { border-top: 1px dashed #000; margin: 6px 0; }

.item-note { font-size: 7px; }
.total td  { font-size: 11px; font-weight: bold; padding-top: 2px; }
.dim       { font-size: 8px; }

.stamp {
    margin: 6px auto 0;
    border: 1.5px solid #000;
    padding: 2px 8px;
    font-weight: bold;
    letter-spacing: 1px;
    text-align: center;
    width: 60%;
}
.thanks  { font-size: 11px; font-weight: bold; margin-top: 10px; letter-spacing: 1px; }
.barcode { margin-top: 8px; text-align: center; }
.barcode img { height: 34px; }
.foot    { font-size: 6.5px; margin-top: 6px; }
</style>
</head>
<body>
<div id="rc">

    <div class="c title">FEE RECEIPT</div>
    <div class="c stars">*****</div>
    <div class="c school">{{ $school->name ?? 'SCHOOL' }}</div>
    @if($school && ($school->phone || $school->email))
        <div class="c small">
            @if($school->phone) Tel: {{ $school->phone }} @endif
            @if($school->phone && $school->email)<br>@endif
            @if($school->email) {{ $school->email }} @endif
        </div>
    @endif
    <div class="c stars">*****</div>

    <table style="margin-top:4px;">
        <tr><td class="k">Receipt No:</td><td class="v">{{ $payment->receipt_number }}</td></tr>
        <tr><td class="k">Date:</td><td class="v">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : now()->format('d/m/Y') }}</td></tr>
        <tr><td class="k">Student:</td><td class="v">{{ $summary['student_name'] }}</td></tr>
        @if($summary['admission_number'])
            <tr><td class="k">ID No:</td><td class="v">{{ $summary['admission_number'] }}</td></tr>
        @endif
        @if($summary['class_name'])
            <tr><td class="k">Class:</td><td class="v">{{ $summary['class_name'] }}{{ $summary['stream_name'] ? ' - ' . $summary['stream_name'] : '' }}</td></tr>
        @endif
        <tr><td class="k">Term:</td><td class="v">{{ \App\Support\Term::label($payment->term) }}, {{ $payment->academic_year }}</td></tr>
        @if($payment->received_by)
            <tr><td class="k">Cashier:</td><td class="v">{{ $payment->received_by }}</td></tr>
        @endif
    </table>

    <div class="rule-eq"></div>

    <table>
        @foreach($summary['lines'] as $line)
            <tr>
                <td>
                    {{ $line['label'] }}
                    @if(!empty($line['note']))<div class="item-note">{{ $line['note'] }}</div>@endif
                </td>
                <td class="amt">{{ number_format($line['amount'], 0) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="rule-dash"></div>

    <table class="total">
        <tr><td>TOTAL (UGX)</td><td class="amt">{{ number_format($payment->amount_paid, 0) }}</td></tr>
    </table>
    @if($summary['amount_due'] !== null)
        <table class="dim">
            <tr><td>Amount due</td><td class="amt">{{ number_format($summary['amount_due'], 0) }}</td></tr>
            <tr><td>Balance</td><td class="amt">{{ number_format($summary['balance'], 0) }}</td></tr>
        </table>
    @endif

    <div class="rule-dash"></div>

    <table>
        <tr>
            <td>{{ $summary['method_label'] }}</td>
            <td class="amt">{{ number_format($payment->amount_paid, 0) }}</td>
        </tr>
    </table>
    @if($payment->bank_name)
        <div class="dim">Bank: {{ $payment->bank_name }}</div>
    @endif
    @if($payment->transaction_reference)
        <div class="dim">Ref: {{ $payment->transaction_reference }}</div>
    @endif
    @if($payment->notes)
        <div class="dim">Note: {{ $payment->notes }}</div>
    @endif

    @if($summary['status_stamp'])
        <div class="stamp">{{ $summary['status_stamp'] }}</div>
    @endif

    <div class="c thanks">THANK YOU!</div>

    @php $barModule = strlen($payment->receipt_number) <= 14 ? 1.2 : 0.9; @endphp
    <div class="barcode"><img src="{{ \App\Support\Code128::dataUri($payment->receipt_number, $barModule, 34) }}"></div>
    <div class="c foot">{{ $payment->receipt_number }}<br>Printed {{ now()->format('d/m/Y H:i') }}</div>

</div>
</body>
</html>
