<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payroll Report</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'DejaVu Sans',Arial,sans-serif;
    background:#fff;
    color:#1e293b;
    font-size:9px;
    line-height:1.35;
}

.hdr {
    background:#0f172a;
    padding:14px 20px;
    position:relative;
    overflow:hidden;
}
.hdr-inner { display:table; width:100%; }
.hdr-left { display:table-cell; vertical-align:middle; }
.hdr-right { display:table-cell; vertical-align:middle; text-align:right; }

.school-name { font-size:15px; font-weight:700; color:#fff; letter-spacing:.4px; }
.school-sub { font-size:8px; color:#94a3b8; margin-top:2px; }

.report-badge {
    display:inline-block;
    background:#7c3aed; color:#fff;
    font-size:7px; font-weight:700;
    letter-spacing:1px; text-transform:uppercase;
    padding:3px 10px; border-radius:20px; margin-bottom:3px;
}
.report-date { font-size:8px; color:#94a3b8; margin-top:2px; }

.filters-bar {
    background:#f8fafc;
    border-bottom:1px solid #e2e8f0;
    padding:8px 20px;
    font-size:8px;
    color:#475569;
}
.filters-bar b { color:#0f172a; }

.stats-row { display:table; width:100%; padding:10px 20px; }
.stat-box { display:table-cell; width:25%; padding:6px 10px; text-align:center; border-right:1px solid #e2e8f0; }
.stat-box:last-child { border-right:none; }
.stat-val { font-size:13px; font-weight:700; color:#0f172a; }
.stat-lbl { font-size:7px; color:#94a3b8; text-transform:uppercase; margin-top:2px; }

table.data { width:calc(100% - 40px); border-collapse:collapse; margin:0 20px; }
table.data th {
    background:#0f172a; color:#fff; font-size:7.5px; text-transform:uppercase;
    padding:6px 5px; text-align:left; letter-spacing:.3px;
}
table.data td { padding:5px; font-size:8px; border-bottom:1px solid #e2e8f0; }
table.data tr:nth-child(even) td { background:#f8fafc; }
.amount { font-family:'DejaVu Sans Mono',monospace; text-align:right; }
.deduction { color:#dc2626; }
.net { color:#059669; font-weight:700; }
.badge { display:inline-block; padding:2px 6px; border-radius:8px; font-size:6.5px; font-weight:700; }
.badge-draft { background:#fef3c7; color:#d97706; }
.badge-approved, .badge-paid { background:#d1fae5; color:#059669; }

.ftr { padding:14px 20px; text-align:center; font-size:7px; color:#94a3b8; border-top:1px solid #e2e8f0; margin-top:10px; }
</style>
</head>
<body>

<div class="hdr">
    <div class="hdr-inner">
        <div class="hdr-left">
            <div class="school-name">{{ $school->name ?? 'SMASA SCHOOL' }}</div>
            <div class="school-sub">Payroll Report</div>
        </div>
        <div class="hdr-right">
            <div class="report-badge">Payroll</div><br>
            <div class="report-date">Generated {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>
</div>

<div class="filters-bar">
    <b>Year:</b> {{ $filters['year'] }}
    &nbsp;·&nbsp; <b>Term:</b> {{ $filters['term'] ?: 'All' }}
    @if($filters['payroll_period_id']) &nbsp;·&nbsp; <b>Period:</b> {{ optional(\App\Models\PayrollPeriod::find($filters['payroll_period_id']))->period_name }} @endif
    @if($filters['date_from']) &nbsp;·&nbsp; <b>From:</b> {{ $filters['date_from'] }} @endif
    @if($filters['date_to']) &nbsp;·&nbsp; <b>To:</b> {{ $filters['date_to'] }} @endif
    @if($filters['status']) &nbsp;·&nbsp; <b>Status:</b> {{ ucfirst($filters['status']) }} @endif
    @if($filters['search']) &nbsp;·&nbsp; <b>Search:</b> "{{ $filters['search'] }}" @endif
</div>

<div class="stats-row">
    <div class="stat-box">
        <div class="stat-val">{{ $rows->count() }}</div>
        <div class="stat-lbl">Payslips</div>
    </div>
    <div class="stat-box">
        <div class="stat-val">UGX {{ number_format($rows->sum('gross_pay'), 0) }}</div>
        <div class="stat-lbl">Gross Pay</div>
    </div>
    <div class="stat-box">
        <div class="stat-val">UGX {{ number_format($rows->sum('total_deductions'), 0) }}</div>
        <div class="stat-lbl">Deductions</div>
    </div>
    <div class="stat-box">
        <div class="stat-val">UGX {{ number_format($total, 0) }}</div>
        <div class="stat-lbl">Net Pay</div>
    </div>
</div>

<table class="data">
    <thead>
        <tr>
            <th>#</th>
            <th>Payslip #</th>
            <th>Teacher</th>
            <th>Period</th>
            <th style="text-align:right;">Gross</th>
            <th style="text-align:right;">Deductions</th>
            <th style="text-align:right;">Net Pay</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->payslip_number }}</td>
                <td>{{ $r->teacher->firstname ?? '' }} {{ $r->teacher->surname ?? '' }}</td>
                <td>{{ $r->period->period_name ?? '—' }}</td>
                <td class="amount">{{ number_format($r->gross_pay, 0) }}</td>
                <td class="amount deduction">{{ number_format($r->total_deductions, 0) }}</td>
                <td class="amount net">{{ number_format($r->net_pay, 0) }}</td>
                <td><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;padding:20px;">No payslips match the selected filters.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="ftr">Generated by SMASA — {{ $school->name ?? 'School' }} — {{ now()->format('d M Y, H:i') }}</div>

</body>
</html>