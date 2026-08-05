<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Outstanding Fees Report</title>
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
    background:#dc2626; color:#fff;
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
.stat-box { display:table-cell; width:33.33%; padding:6px 10px; text-align:center; border-right:1px solid #e2e8f0; }
.stat-box:last-child { border-right:none; }
.stat-val { font-size:13px; font-weight:700; color:#0f172a; }
.stat-lbl { font-size:7px; color:#94a3b8; text-transform:uppercase; margin-top:2px; }

table.data { width:100%; border-collapse:collapse; margin:0 20px; width:calc(100% - 40px); }
table.data th {
    background:#0f172a; color:#fff; font-size:7.5px; text-transform:uppercase;
    padding:6px 5px; text-align:left; letter-spacing:.3px;
}
table.data td { padding:5px; font-size:8px; border-bottom:1px solid #e2e8f0; }
table.data tr:nth-child(even) td { background:#f8fafc; }
.amount { font-family:'DejaVu Sans Mono',monospace; text-align:right; }
.paid { color:#059669; }
.balance { color:#dc2626; font-weight:700; }
.badge { display:inline-block; padding:2px 6px; border-radius:8px; font-size:6.5px; font-weight:700; }
.badge-unpaid { background:#fee2e2; color:#dc2626; }
.badge-partial { background:#fef3c7; color:#d97706; }
.badge-paid { background:#d1fae5; color:#059669; }
.badge-overpaid { background:#dbeafe; color:#2563eb; }

.ftr { padding:14px 20px; text-align:center; font-size:7px; color:#94a3b8; border-top:1px solid #e2e8f0; margin-top:10px; }
</style>
</head>
<body>

<div class="hdr">
    <div class="hdr-inner">
        <div class="hdr-left">
            <div class="school-name">{{ $school->name ?? 'SMASA SCHOOL' }}</div>
            <div class="school-sub">Outstanding Fees Report</div>
        </div>
        <div class="hdr-right">
            <div class="report-badge">Fee Report</div><br>
            <div class="report-date">Generated {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>
</div>

<div class="filters-bar">
    <b>Year:</b> {{ $filters['year'] }}
    &nbsp;·&nbsp; <b>Term:</b> {{ $filters['term'] ?: 'All' }}
    &nbsp;·&nbsp; <b>Status:</b> {{ $filters['status'] ? ucfirst($filters['status']) : 'Unpaid + Partial (defaulters)' }}
    @if($filters['class_id']) &nbsp;·&nbsp; <b>Class:</b> {{ \App\Http\Controllers\Helper::recordMdname($filters['class_id']) }} @endif
    @if($filters['stream_id']) &nbsp;·&nbsp; <b>Stream:</b> {{ \App\Http\Controllers\Helper::recordMdname($filters['stream_id']) }} @endif
    @if($filters['gender']) &nbsp;·&nbsp; <b>Gender:</b> {{ $filters['gender'] }} @endif
    @if($filters['fee_structure_id']) &nbsp;·&nbsp; <b>Fee Structure:</b> {{ optional(\App\Models\FeeStructure::find($filters['fee_structure_id']))->name }} @endif
    @if($filters['min_balance'] !== '') &nbsp;·&nbsp; <b>Min Balance:</b> {{ number_format((float)str_replace(',', '', $filters['min_balance'])) }} @endif
    @if($filters['max_balance'] !== '') &nbsp;·&nbsp; <b>Max Balance:</b> {{ number_format((float)str_replace(',', '', $filters['max_balance'])) }} @endif
    @if($filters['min_paid'] !== '') &nbsp;·&nbsp; <b>Min Paid:</b> {{ number_format((float)str_replace(',', '', $filters['min_paid'])) }} @endif
    @if($filters['max_paid'] !== '') &nbsp;·&nbsp; <b>Max Paid:</b> {{ number_format((float)str_replace(',', '', $filters['max_paid'])) }} @endif
    @if($filters['search']) &nbsp;·&nbsp; <b>Search:</b> "{{ $filters['search'] }}" @endif
</div>

<div class="stats-row">
    <div class="stat-box">
        <div class="stat-val">{{ $allocations->count() }}</div>
        <div class="stat-lbl">Students Matching</div>
    </div>
    <div class="stat-box">
        <div class="stat-val">UGX {{ number_format($totalOutstanding, 0) }}</div>
        <div class="stat-lbl">Total Outstanding</div>
    </div>
    <div class="stat-box">
        <div class="stat-val">UGX {{ number_format($allocations->sum(fn($a) => $a->allocated_amount - $a->discount_amount - $a->balance), 0) }}</div>
        <div class="stat-lbl">Total Paid</div>
    </div>
</div>

<table class="data">
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            <th>Adm #</th>
            <th>Class</th>
            <th>Fee Structure</th>
            <th>Term/Year</th>
            <th style="text-align:right;">Billed</th>
            <th style="text-align:right;">Paid</th>
            <th style="text-align:right;">Balance</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($allocations as $i => $alloc)
            @php
                $net = $alloc->allocated_amount - $alloc->discount_amount;
                $paid = $net - $alloc->balance;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $alloc->student->firstname ?? 'N/A' }} {{ $alloc->student->lastname ?? '' }}</td>
                <td>{{ $alloc->student->admission_number ?? '—' }}</td>
                <td>{{ \App\Http\Controllers\Helper::recordMdname($alloc->student->senior ?? null) ?? '—' }}</td>
                <td>{{ $alloc->feeStructure->name ?? '—' }}</td>
                <td>T{{ $alloc->term }} / {{ $alloc->academic_year }}</td>
                <td class="amount">{{ number_format($net, 0) }}</td>
                <td class="amount paid">{{ number_format($paid, 0) }}</td>
                <td class="amount balance">{{ number_format($alloc->balance, 0) }}</td>
                <td><span class="badge badge-{{ $alloc->payment_status }}">{{ ucfirst($alloc->payment_status) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="10" style="text-align:center;padding:20px;">No students match the selected filters.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="ftr">Generated by SMASA — {{ $school->name ?? 'School' }} — {{ now()->format('d M Y, H:i') }}</div>

</body>
</html>
