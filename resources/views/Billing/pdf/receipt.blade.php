@php
    use App\Services\BillingService;
    $cur = $settings->currency ?: 'UGX';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $payment->receipt_number }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1e293b; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; }
        .head td { background: #1a1790; color: #fff; padding: 18px 24px; }
        .stripe td { background: #f59e0b; height: 4px; font-size: 1px; line-height: 1px; }
        .wrap { padding: 20px 24px; }
        .lbl { font-size: 8px; letter-spacing: 1.3px; color: #94a3b8; text-transform: uppercase; font-weight: bold; margin-bottom: 3px; }
        .row td { padding: 7px 0; border-bottom: 1px solid #eef2f7; font-size: 10.5px; }
        .row td.k { color: #64748b; width: 38%; }
        .row td.v { font-weight: bold; text-align: right; }
        .amount { background: #ecfdf5; border: 1px solid #a7f3d0; padding: 14px; text-align: center; margin: 16px 0; }
        .amount .n { font-size: 22px; font-weight: bold; color: #047857; }
        .rev { background: #fee2e2; color: #b91c1c; padding: 8px 12px; font-weight: bold; text-align: center; margin-bottom: 12px; }
        .foot { font-size: 8.5px; color: #94a3b8; text-align: center; padding: 10px 24px 16px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <table class="head"><tr>
        <td style="width:60%;"><div style="font-size:16px;font-weight:bold;">{{ $settings->company_name }}</div>
            <div style="font-size:9px;color:#c7c8f5;margin-top:2px;">{{ $settings->company_tagline }}</div></td>
        <td style="text-align:right;"><div style="font-size:20px;font-weight:bold;letter-spacing:2px;">RECEIPT</div>
            <div style="font-size:10px;color:#dfe0fb;margin-top:3px;">{{ $payment->receipt_number }}</div></td>
    </tr></table>
    <table class="stripe"><tr><td>&nbsp;</td></tr></table>

    <div class="wrap">
        @if ($payment->status === 'reversed')
            <div class="rev">THIS PAYMENT HAS BEEN REVERSED — {{ $payment->reversed_reason }}</div>
        @endif

        <div class="lbl">Received from</div>
        <div style="font-size:14px;font-weight:bold;margin-bottom:12px;">{{ $school->name }}</div>

        <div class="amount">
            <div class="lbl" style="color:#047857;">Amount received</div>
            <div class="n">{{ $cur }} {{ number_format($payment->amount, 0) }}</div>
            <div style="font-size:9px;color:#475569;margin-top:4px;font-style:italic;">{{ BillingService::amountInWords($payment->amount, $cur) }}</div>
        </div>

        <table>
            <tr class="row"><td class="k">Payment date</td><td class="v">{{ $payment->paid_on->format('j F Y') }}</td></tr>
            <tr class="row"><td class="k">Method</td><td class="v">{{ $payment->method_label }}</td></tr>
            @if ($payment->reference)<tr class="row"><td class="k">Reference</td><td class="v">{{ $payment->reference }}</td></tr>@endif
            <tr class="row"><td class="k">For invoice</td><td class="v">{{ $invoice->invoice_number }}</td></tr>
            <tr class="row"><td class="k">Billing period</td><td class="v">{{ $invoice->term->name ?? '' }}</td></tr>
            <tr class="row"><td class="k">Invoice total</td><td class="v">{{ $cur }} {{ number_format($invoice->total, 0) }}</td></tr>
            <tr class="row"><td class="k">Balance after this payment</td><td class="v">{{ $cur }} {{ number_format($invoice->balance, 0) }}</td></tr>
        </table>
    </div>
    <div class="foot">Thank you for your payment. This receipt is computer-generated · {{ $settings->company_name }}</div>
</body>
</html>
