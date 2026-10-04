<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Receipt {{ $payment->receipt_number }}</title>
<style>
/* Horizontal fee-receipt slip: exactly one third of an A4 sheet (210 x 99 mm),
   so three slips cut from one A4 page. One payment = one slip. */
@page { margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Serif', serif;
    font-size: 12px;
    color: #000;
    background: #fff;
}
#rc {
    margin: 8px 10px 0;
    height: 342px;
    border: 2px solid #000;
    padding: 8px 18px 0;
}

table { width: 100%; border-collapse: collapse; }
td { vertical-align: bottom; padding: 0; }

.top-l { width: 30%; font-size: 8.5px; font-family: 'DejaVu Sans', sans-serif; }
.top-c { width: 40%; text-align: center; font-size: 26px; font-weight: bold; line-height: 1.1; }
.top-r { width: 30%; text-align: right; font-size: 8.5px; font-family: 'DejaVu Sans', sans-serif; }

.date-row td { padding-top: 2px; height: 26px; }

.f td { height: 31px; }
.lbl  { white-space: nowrap; padding-right: 6px; }
.lbl-r { text-align: right; }
.val  {
    border-bottom: 1px solid #000;
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 10.5px;
    padding: 0 3px 2px;
    white-space: nowrap;
    overflow: hidden;
}
.gap { width: 18px; }

.stamp {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 9px; font-weight: bold; letter-spacing: 1px;
    border: 1.5px solid #000; padding: 1px 6px;
}
.foot { font-family: 'DejaVu Sans', sans-serif; font-size: 7.5px; text-align: right; padding-top: 6px; }
</style>
</head>
<body>
<div id="rc">

    <table>
        <tr>
            <td class="top-l">{{ $school->name ?? 'SCHOOL' }}</td>
            <td class="top-c">Fee Receipt</td>
            <td class="top-r">
                @if($summary['status_stamp'])<span class="stamp">{{ $summary['status_stamp'] }}</span>&nbsp;@endif
                No. {{ $payment->receipt_number }}
            </td>
        </tr>
    </table>

    <table class="date-row">
        <tr>
            <td></td>
            <td class="lbl lbl-r" style="width:1%;">Date:</td>
            <td class="val" style="width:20%;">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="f">
        <tr>
            <td class="lbl" style="width:1%;">Student Name:</td>
            <td class="val" style="width:41%;">{{ $summary['student_name'] }}</td>
            <td class="gap"></td>
            <td class="lbl lbl-r" style="width:1%;">ID Number:</td>
            <td class="val" style="width:24%;">{{ $summary['admission_number'] }}</td>
        </tr>
        <tr>
            <td class="lbl">Session/Class:</td>
            <td class="val">{{ $summary['class_name'] }}{{ $summary['stream_name'] ? ' - ' . $summary['stream_name'] : '' }}</td>
            <td class="gap"></td>
            <td class="lbl lbl-r">Term:</td>
            <td class="val">{{ \App\Support\Term::label($payment->term) }}, {{ $payment->academic_year }}</td>
        </tr>
        <tr>
            <td class="lbl">Amount Due:</td>
            <td class="val">{{ $summary['amount_due'] !== null ? 'UGX ' . number_format($summary['amount_due'], 0) : '' }}</td>
            <td class="gap"></td>
            <td class="lbl lbl-r">Total Amount:</td>
            <td class="val"><strong>UGX {{ number_format($payment->amount_paid, 0) }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Payment Type:</td>
            <td class="val">{{ $summary['method_label'] }}{{ $payment->transaction_reference ? ' - ' . $payment->transaction_reference : '' }}</td>
            <td class="gap"></td>
            <td class="lbl lbl-r">Balance:</td>
            <td class="val">{{ $summary['balance'] !== null ? 'UGX ' . number_format($summary['balance'], 0) : '' }}</td>
        </tr>
    </table>

    <table class="f">
        <tr>
            <td class="lbl" style="width:1%;">Paid for:</td>
            <td class="val">{{ \Illuminate\Support\Str::limit($summary['paid_for'], 95) }}</td>
        </tr>
    </table>

    <table class="f">
        <tr>
            <td style="width:24%;"></td>
            <td class="lbl lbl-r" style="width:1%;">Prepared by:</td>
            <td class="val" style="width:48%;">{{ $payment->received_by }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td class="lbl lbl-r">Recipient Signature:</td>
            <td class="val"></td>
            <td></td>
        </tr>
    </table>

    <div class="foot">Official computer-generated receipt &middot; Printed {{ now()->format('d/m/Y H:i') }}</div>

</div>
</body>
</html>
