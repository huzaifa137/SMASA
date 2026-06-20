@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --lib-blue: #2c29ca;
            --lib-blue-l: rgba(44, 41, 202, .12);
            --lib-blue-d: #2420a8;
            --lib-rose: #f43f5e;
            --lib-rose-l: rgba(244, 63, 94, .12);
            --lib-green: #10b981;
            --lib-green-l: rgba(16, 185, 129, .12);
            --lib-amber: #f59e0b;
            --lib-amber-l: rgba(245, 158, 11, .12);
            --lib-violet: #7c3aed;
            --lib-violet-l: rgba(124, 58, 237, .12);
            --surface: #fff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 20px rgba(0, 0, 0, .05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        .lib-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2c29ca 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .lib-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%, transparent 70%);
        }

        .lib-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 30%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .05) 0%, transparent 70%);
        }

        .lib-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .lib-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .lib-card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-1);
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .lib-card-header h3 i {
            color: var(--lib-blue);
        }

        .lib-card-body {
            padding: 1.5rem;
        }

        .lib-table {
            width: 100%;
            border-collapse: collapse;
        }

        .lib-table th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            color: #fff;
            background: #2c29ca;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
        }

        .lib-table td {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: .875rem;
            color: var(--text-1);
            vertical-align: middle;
        }

        .lib-table tr:last-child td {
            border-bottom: none;
        }

        .lib-table tr:hover td {
            background: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: .25rem .65rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .lib-back-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--text-2);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }

        .lib-back-link:hover {
            color: var(--lib-blue);
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-3);
        }

        .report-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .fine-summary-chip {
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            flex: 1;
        }

        /* Reports - Stack layout on mobile */

/* Hero section */
.lib-hero {
    padding: 2rem 2.5rem;
}

/* Report grid - 2 columns on large screens */
.report-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Charts containers */
.lib-card .lib-card-body {
    padding: 1.5rem;
}

.lib-card .lib-card-body canvas {
    max-height: 200px;
    width: 100% !important;
}

/* Fines summary chips */
.fine-summary-chip {
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    flex: 1;
    min-width: 100px;
}

[style*="display:flex;gap:1rem;flex-wrap:wrap;"] {
    gap: 1rem;
}

/* Tables responsive */
.lib-table {
    min-width: 500px;
}

.lib-table th,
.lib-table td {
    padding: .75rem 1rem;
}

/* Tablet */
@media (max-width: 992px) {
    .report-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .65rem .85rem;
        font-size: .8rem;
    }
    
    .fine-summary-chip {
        padding: 1rem;
    }
    
    .fine-summary-chip div:first-child {
        font-size: 1.2rem !important;
    }
}

/* Tablet - stack vertically */
@media (max-width: 768px) {
    .report-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .lib-hero {
        padding: 1.25rem 1.5rem;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1.3rem !important;
    }
    
    .lib-hero [style*="font-size:.875rem;"] {
        font-size: .8rem !important;
    }
    
    .lib-card-header {
        padding: 1rem 1.25rem;
    }
    
    .lib-card-header h3 {
        font-size: .9rem;
    }
    
    .lib-card-body {
        padding: 1rem;
    }
    
    .lib-table {
        min-width: 600px;
        font-size: .8rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .5rem .75rem;
    }
    
    /* Fines summary - wrap nicely */
    [style*="display:flex;gap:1rem;flex-wrap:wrap;"] {
        gap: .75rem;
    }
    
    .fine-summary-chip {
        padding: .85rem 1rem;
        min-width: 80px;
        flex: 1 1 calc(33% - .75rem);
    }
    
    .fine-summary-chip div:first-child {
        font-size: 1.1rem !important;
    }
    
    .fine-summary-chip div:last-child {
        font-size: .7rem !important;
    }
    
    /* Overdue books and popular books sections */
    [style*="overflow-x:auto;max-height:380px;overflow-y:auto;"] {
        max-height: 300px;
    }
}

/* Mobile landscape */
@media (max-width: 576px) {
    [style*="padding:1.5rem;"] {
        padding: 0.75rem !important;
    }
    
    .lib-hero {
        padding: 1rem 1.25rem;
        border-radius: 18px;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1.1rem !important;
    }
    
    .lib-hero [style*="font-size:.875rem;"] {
        font-size: .75rem !important;
    }
    
    .lib-card-header {
        padding: .75rem 1rem;
    }
    
    .lib-card-header h3 {
        font-size: .85rem;
    }
    
    .lib-card-body {
        padding: .75rem 1rem;
    }
    
    .lib-card-body canvas {
        max-height: 160px !important;
    }
    
    .lib-table {
        min-width: 520px;
        font-size: .75rem;
    }
    
    .lib-table th {
        font-size: .65rem;
        padding: .4rem .6rem;
    }
    
    .lib-table td {
        padding: .4rem .6rem;
        font-size: .75rem;
    }
    
    .lib-table td:first-child {
        min-width: 40px;
    }
    
    .lib-table td:nth-child(2) {
        min-width: 100px;
        max-width: 120px;
    }
    
    .lib-table td:nth-child(3) {
        min-width: 80px;
    }
    
    .badge {
        font-size: .6rem;
        padding: .15rem .5rem;
    }
    
    /* Fines summary chips on mobile */
    [style*="display:flex;gap:1rem;flex-wrap:wrap;"] {
        gap: .5rem;
    }
    
    .fine-summary-chip {
        padding: .65rem .5rem;
        min-width: 60px;
        flex: 1 1 calc(33% - .5rem);
        border-radius: 10px;
    }
    
    .fine-summary-chip div:first-child {
        font-size: .95rem !important;
    }
    
    .fine-summary-chip div:last-child {
        font-size: .65rem !important;
    }
    
    /* Overdue and popular sections */
    [style*="overflow-x:auto;max-height:380px;overflow-y:auto;"] {
        max-height: 250px;
    }
    
    /* Chart legend on mobile */
    .chartjs-legend {
        font-size: .7rem;
    }
}

/* Very small screens */
@media (max-width: 400px) {
    [style*="padding:1.5rem;"] {
        padding: 0.5rem !important;
    }
    
    .lib-hero {
        padding: .75rem 1rem;
        border-radius: 14px;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1rem !important;
    }
    
    .lib-card-header {
        padding: .6rem .75rem;
    }
    
    .lib-card-body {
        padding: .6rem .75rem;
    }
    
    .lib-card-body canvas {
        max-height: 130px !important;
    }
    
    .lib-table {
        min-width: 450px;
        font-size: .7rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .3rem .5rem;
    }
    
    .lib-table td:first-child {
        min-width: 30px;
    }
    
    .lib-table td:nth-child(2) {
        min-width: 80px;
        max-width: 90px;
    }
    
    .lib-table td:nth-child(3) {
        min-width: 60px;
    }
    
    .lib-table td:nth-child(4) {
        min-width: 50px;
    }
    
    .fine-summary-chip {
        padding: .5rem .3rem;
        min-width: 50px;
        flex: 1 1 calc(33% - .3rem);
        border-radius: 8px;
    }
    
    .fine-summary-chip div:first-child {
        font-size: .8rem !important;
    }
    
    .fine-summary-chip div:last-child {
        font-size: .6rem !important;
    }
    
    [style*="overflow-x:auto;max-height:380px;overflow-y:auto;"] {
        max-height: 200px;
    }
}

/* Fix horizontal scroll on mobile */
@media (max-width: 768px) {
    [style*="overflow-x:auto;"] {
        -webkit-overflow-scrolling: touch;
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }
    
    [style*="overflow-x:auto;max-height:380px;overflow-y:auto;"] {
        -webkit-overflow-scrolling: touch;
    }
}

/* Improve touch targets on mobile */
@media (max-width: 576px) {
    /* Chart containers */
    .lib-card .lib-card-body {
        overflow: hidden;
    }
    
    /* Ensure charts resize properly */
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
}

/* Smooth transitions */
.lib-card,
.lib-hero,
.fine-summary-chip {
    transition: all 0.2s ease;
}

/* Empty state on mobile */
@media (max-width: 576px) {
    .empty-state {
        padding: 1.5rem .5rem;
    }
    
    .empty-state i {
        font-size: 2rem !important;
    }
    
    .empty-state div {
        font-size: .85rem !important;
    }
}

/* Chart.js legend positioning on mobile */
@media (max-width: 576px) {
    #categoryChart {
        max-height: 180px !important;
    }
    
    /* Doughnut chart legend - make it horizontal on mobile */
    .chartjs-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: .3rem;
        padding-top: .5rem;
    }
    
    .chartjs-legend li {
        font-size: .65rem !important;
        padding: .1rem .4rem !important;
    }
}
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem;">

        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;"><i class="fas fa-chart-bar"
                    style="color:var(--lib-blue);margin-right:.5rem;"></i>Reports</div>
            <div style="font-size:.875rem;opacity:.7;">Insights and analytics for your library</div>
        </div>

        {{-- Charts Row --}}
        <div class="report-grid">
            {{-- Monthly Trend --}}
            <div class="lib-card">
                <div class="lib-card-header">
                    <h3><i class="fas fa-chart-line" style="color:var(--lib-blue);"></i> Monthly Borrowings</h3>
                </div>
                <div class="lib-card-body">
                    <canvas id="monthlyChart" height="180"></canvas>
                </div>
            </div>
            {{-- Category Usage --}}
            <div class="lib-card">
                <div class="lib-card-header">
                    <h3><i class="fas fa-chart-pie" style="color:var(--lib-violet);"></i> Category Usage</h3>
                </div>
                <div class="lib-card-body">
                    <canvas id="categoryChart" height="180"></canvas>
                </div>
            </div>
        </div>

        {{-- Fines Summary --}}
        <div class="lib-card" style="margin-bottom:1.5rem;">
            <div class="lib-card-header">
                <h3><i class="fas fa-coins" style="color:var(--lib-amber);"></i> Fines Summary</h3>
            </div>
            <div class="lib-card-body">
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    @foreach($finesReport as $fr)
                        @php
                            $fColors = ['unpaid' => ['var(--lib-rose-l)', 'var(--lib-rose)'], 'paid' => ['var(--lib-green-l)', 'var(--lib-green)'], 'waived' => ['var(--lib-violet-l)', 'var(--lib-violet)']];
                            [$bg, $c] = $fColors[$fr->status] ?? ['var(--bg)', 'var(--text-2)'];
                        @endphp
                        <div class="fine-summary-chip" style="background:{{ $bg }};">
                            <div style="font-size:1.4rem;font-weight:800;color:{{ $c }};">{{ number_format($fr->total, 2) }}
                            </div>
                            <div style="font-size:.75rem;color:{{ $c }};font-weight:600;">{{ ucfirst($fr->status) }}
                                ({{ $fr->count }})</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Two column layout --}}
        <div class="report-grid">
            {{-- Overdue Books --}}
            <div class="lib-card">
                <div class="lib-card-header">
                    <h3><i class="fas fa-exclamation-circle" style="color:var(--lib-rose);"></i> Overdue Books</h3>
                    <span class="badge"
                        style="background:var(--lib-rose-l);color:var(--lib-rose);">{{ $overdueBooks->count() }}</span>
                </div>
                <div style="overflow-x:auto;max-height:380px;overflow-y:auto;">
                    @if($overdueBooks->count())
                        <table class="lib-table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Book</th>
                                    <th>Due</th>
                                    <th>Days Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueBooks as $b)
                                    <tr>
                                        <td style="font-weight:600;">{{ $b->member->name ?? '—' }}</td>
                                        <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $b->book->title ?? '—' }}</td>
                                        <td style="color:var(--lib-rose);font-weight:600;font-size:.8rem;">
                                            {{ \Carbon\Carbon::parse($b->due_date)->format('d M Y') }}</td>
                                        <td><span class="badge"
                                                style="background:var(--lib-rose-l);color:var(--lib-rose);">{{ \Carbon\Carbon::parse($b->due_date)->diffInDays(now()) }}d</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state"><i class="fas fa-check-circle"
                                style="color:var(--lib-green);font-size:2rem;"></i>
                            <div style="color:var(--lib-green);font-weight:600;">No overdue books!</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Popular Books --}}
            <div class="lib-card">
                <div class="lib-card-header">
                    <h3><i class="fas fa-fire" style="color:var(--lib-amber);"></i> Most Borrowed</h3>
                </div>
                <div style="overflow-x:auto;max-height:380px;overflow-y:auto;">
                    @if($popularBooks->count())
                        <table class="lib-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Book</th>
                                    <th>Author</th>
                                    <th>Borrows</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($popularBooks as $i => $book)
                                    <tr>
                                        <td style="color:var(--text-3);font-weight:700;">{{ $i + 1 }}</td>
                                        <td
                                            style="font-weight:500;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $book->title }}</td>
                                        <td style="color:var(--text-2);font-size:.8rem;">{{ $book->author->name ?? '—' }}</td>
                                        <td><span class="badge"
                                                style="background:var(--lib-amber-l);color:var(--lib-amber);">{{ $book->borrowings_count }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">No data yet.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Active Members --}}
        <div class="lib-card" style="margin-top:1.5rem;">
            <div class="lib-card-header">
                <h3><i class="fas fa-users" style="color:var(--lib-blue);"></i> Most Active Members</h3>
            </div>
            <div style="overflow-x:auto;">
                @if($memberActivity->count())
                    <table class="lib-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Total Borrows</th>
                                <th>Active</th>
                                <th>Outstanding Fines</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($memberActivity as $i => $m)
                                <tr>
                                    <td style="color:var(--text-3);font-weight:700;">{{ $i + 1 }}</td>
                                    <td style="font-weight:600;">{{ $m->name }}</td>
                                    <td><span class="badge"
                                            style="background:var(--lib-blue-l);color:var(--lib-blue);">{{ ucfirst($m->member_type) }}</span>
                                    </td>
                                    <td>{{ $m->borrowings_count }}</td>
                                    <td>{{ $m->active_borrowings_count }}</td>
                                    <td
                                        style="color:{{ ($m->total_fines ?? 0) > 0 ? 'var(--lib-rose)' : 'var(--lib-green)' }};font-weight:600;">
                                        {{ number_format($m->total_fines ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">No member activity data.</div>
                @endif
            </div>
        </div>
    </div>
                </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // Monthly borrowing trend
        const monthlyLabels = @json($monthlyStats->pluck('month'));
        const monthlyBorrowed = @json($monthlyStats->pluck('borrowed'));
        const monthlyReturned = @json($monthlyStats->pluck('returned'));

        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [
                    { label: 'Borrowed', data: monthlyBorrowed, backgroundColor: 'rgba(14,165,160,.7)', borderRadius: 6 },
                    { label: 'Returned', data: monthlyReturned, backgroundColor: 'rgba(16,185,129,.5)', borderRadius: 6 }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        // Category usage doughnut
        const catLabels = @json($categoryUsage->pluck('name'));
        const catCounts = @json($categoryUsage->pluck('count'));
        const catColors = @json($categoryUsage->pluck('color'));

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{ data: catCounts, backgroundColor: catColors.length ? catColors : ['#0ea5a0', '#7c3aed', '#f59e0b', '#f43f5e', '#10b981', '#3b82f6', '#ec4899', '#14b8a6'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'right' } }, cutout: '60%' }
        });
    </script>
@endsection