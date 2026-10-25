{{-- Full-page "access suspended" screen. Standalone on purpose: no sidebar/menu, so no module is reachable.
     Shown by SchoolAuth when a school has an overdue invoice; disappears as soon as the admin clears it. --}}
@php
    $cur = $settings->currency ?: 'UGX';
    $f = fn ($v) => number_format($v, 0);
    $oldest = $invoices->first();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Payment required — {{ $settings->company_name ?: 'SMASA' }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f1f3f9;color:#0f172a;min-height:100vh}
        .top{background:linear-gradient(135deg,#000 0%,#070189 100%);color:#fff;padding:1.1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
        .top .brand{font-weight:700;font-size:1.1rem;letter-spacing:.3px}
        .top .who{font-size:.85rem;color:rgba(255,255,255,.75)}
        .top a{color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.35);border-radius:99px;padding:.4rem 1rem;font-size:.82rem;font-weight:600}
        .top a:hover{background:rgba(255,255,255,.15)}
        .wrap{max-width:900px;margin:2rem auto;padding:0 1rem 3rem}
        .hero{background:#fff;border:1px solid #fecaca;border-left:6px solid #dc2626;border-radius:16px;padding:1.6rem 1.8rem;display:flex;gap:1.2rem;align-items:flex-start;box-shadow:0 4px 24px rgba(15,23,42,.06)}
        .hero .ico{flex:0 0 auto;width:56px;height:56px;border-radius:14px;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.6rem}
        .hero h1{margin:0 0 .35rem;font-size:1.4rem}
        .hero p{margin:0;color:#475569;line-height:1.55;font-size:.95rem}
        .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:1.2rem 0}
        .stat{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem 1.2rem}
        .stat .n{font-size:1.5rem;font-weight:800}
        .stat .n.red{color:#dc2626}
        .stat .l{font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;color:#64748b;font-weight:600;margin-top:2px}
        .panel{background:#fff;border:1px solid #e5e7eb;border-radius:14px;margin-bottom:1.2rem;overflow:hidden}
        .panel>.hd{padding:.9rem 1.2rem;border-bottom:1px solid #e5e7eb;font-weight:700}
        .panel>.bd{padding:1.1rem 1.2rem;font-size:.92rem;line-height:1.6;color:#334155}
        .scroll{overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:.88rem}
        th{background:#2C29CA;color:#fff;text-align:left;padding:.65rem 1rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
        td{padding:.7rem 1rem;border-bottom:1px solid #f1f5f9;white-space:nowrap}
        .badge{display:inline-block;background:#fee2e2;color:#991b1b;border-radius:99px;padding:2px 10px;font-size:.72rem;font-weight:700}
        .steps{margin:0;padding-left:1.2rem}
        .steps li{margin-bottom:.4rem}
        .pay{white-space:pre-line;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:.9rem 1rem;color:#0f172a}
        .actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1rem}
        .btn{display:inline-block;border-radius:10px;padding:.6rem 1.2rem;font-weight:600;font-size:.88rem;text-decoration:none;border:1px solid #2C29CA;background:#2C29CA;color:#fff;cursor:pointer}
        .btn.ghost{background:#fff;color:#2C29CA}
        .foot{text-align:center;color:#94a3b8;font-size:.78rem;margin-top:1.5rem}
        @media(max-width:700px){.grid{grid-template-columns:1fr}.hero{flex-direction:column}}
    </style>
</head>
<body>
    <div class="top">
        <div><div class="brand">{{ $settings->company_name ?: 'SMASA' }}</div><div class="who">{{ $schoolName }}</div></div>
        <a href="{{ route('logout') }}">Sign out</a>
    </div>

    <div class="wrap">
        <div class="hero" role="alert">
            <div class="ico">&#9888;</div>
            <div>
                <h1>Access suspended — payment overdue</h1>
                <p>Your school's SMASA subscription has an unpaid invoice that is past its due date.
                    Access to all modules is paused until the balance is cleared. Your data is safe and
                    nothing has been deleted — everything returns exactly as you left it once payment is confirmed.</p>
            </div>
        </div>

        <div class="grid">
            <div class="stat"><div class="n red">{{ $cur }} {{ $f($total) }}</div><div class="l">Total outstanding</div></div>
            <div class="stat"><div class="n">{{ $invoices->count() }}</div><div class="l">Overdue {{ \Illuminate\Support\Str::plural('invoice', $invoices->count()) }}</div></div>
            <div class="stat"><div class="n">{{ $oldest->daysOverdue() }} {{ \Illuminate\Support\Str::plural('day', $oldest->daysOverdue()) }}</div><div class="l">Oldest overdue</div></div>
        </div>

        <div class="panel">
            <div class="hd">Invoices requiring payment</div>
            <div class="scroll">
                <table>
                    <thead><tr><th>Invoice</th><th>Term</th><th>Due date</th><th>Overdue</th><th>Balance</th>@if($canBilling)<th></th>@endif</tr></thead>
                    <tbody>
                    @foreach($invoices as $i)
                        <tr>
                            <td><strong>{{ $i->invoice_number }}</strong>@if($i->status === 'partial') <span class="badge" style="background:#fef3c7;color:#92400e">Part paid</span>@endif</td>
                            <td>{{ $i->term->name ?? 'Subscription' }}</td>
                            <td>{{ $i->due_date->format('j M Y') }}</td>
                            <td><span class="badge">{{ $i->daysOverdue() }} {{ \Illuminate\Support\Str::plural('day', $i->daysOverdue()) }}</span></td>
                            <td><strong>{{ $cur }} {{ $f($i->balance) }}</strong></td>
                            @if($canBilling)<td><a href="{{ route('school.billing.pdf', $i->id) }}" target="_blank">Download PDF</a></td>@endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="hd">How to restore access</div>
            <div class="bd">
                <ol class="steps">
                    <li>Pay the outstanding balance using the details below, quoting your invoice number as the reference.</li>
                    <li>Send your proof of payment to {{ $settings->company_email ?: 'the SMASA billing team' }}@if($settings->company_phone) or call {{ $settings->company_phone }}@endif.</li>
                    <li>Once our team records the payment, access is restored automatically — no further action is needed.</li>
                </ol>
                @if($settings->payment_instructions)
                    <div class="pay">{{ $settings->payment_instructions }}</div>
                @endif
                <div class="actions">
                    <a class="btn" href="{{ route('school.billing.locked') }}">I have paid — check status</a>
                    @if($settings->company_email)<a class="btn ghost" href="mailto:{{ $settings->company_email }}?subject={{ rawurlencode('Payment for ' . $oldest->invoice_number) }}">Email billing</a>@endif
                </div>
                @unless($canBilling)
                    <p style="margin:1rem 0 0;color:#64748b;font-size:.85rem">Only your school administrator can download invoices. Please let them know about this notice.</p>
                @endunless
            </div>
        </div>

        <div class="foot">{{ $settings->company_name ?: 'SMASA' }} · Secure billing notice · Reference {{ $oldest->invoice_number }}</div>
    </div>
</body>
</html>
