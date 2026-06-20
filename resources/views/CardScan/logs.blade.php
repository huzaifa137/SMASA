{{-- resources/views/CardScan/logs.blade.php --}}
<?php use App\Http\Controllers\Helper; use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .1);
            --g: #059669;
            --gl: rgba(5, 150, 105, .1);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .1);
            --a: #d97706;
            --al: rgba(217, 119, 6, .1);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box
        }

        *:not(i):not([class*="fa"]) {
           
        }

        body {
            background: var(--bg)
        }

        .logs-hero {
            background: linear-gradient(135deg, #0d0b5e 0%, #2f2ccb 50%, #5b58e0 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .hero-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 .3rem
        }

        .hero-sub {
            color: rgba(255, 255, 255, .7);
            font-size: .9rem;
            margin: 0
        }

        .hero-badge {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            padding: .35rem .8rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            backdrop-filter: blur(6px)
        }

        .panel {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            padding: 1.5rem;
            margin-bottom: 1.25rem
        }

        .panel-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem
        }

        .panel-title i {
            color: var(--b)
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            align-items: center
        }

        .filter-bar select,
        .filter-bar input[type=date] {
            padding: .5rem .85rem;
            border: 1.5px solid var(--brd);
            border-radius: 8px;
            font-size: .85rem;
            color: var(--t1);
            background: var(--bg);
            cursor: pointer;
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            outline: none;
            border-color: var(--b)
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .5rem 1rem;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .15s
        }

        .btn-primary {
            background: var(--b);
            color: #fff
        }

        .btn-primary:hover {
            background: #2420a8
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--brd);
            color: var(--t2)
        }

        .btn-outline:hover {
            border-color: var(--b);
            color: var(--b)
        }

        .btn-sm {
            padding: .3rem .7rem;
            font-size: .78rem
        }

        /* Summary chips */
        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin-bottom: 1.25rem
        }

        .summary-chip {
            background: var(--surf);
            border: 1.5px solid var(--brd);
            border-radius: 12px;
            padding: .6rem 1rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .82rem;
        }

        .summary-chip .chip-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0
        }

        .summary-chip .chip-val {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--t1);
            line-height: 1
        }

        .summary-chip .chip-lbl {
            font-size: .72rem;
            color: var(--t2);
            margin-top: .1rem
        }

        /* Table */
        .log-table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--brd)
        }

        table.log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .83rem
        }

        table.log-table thead th {
            background: #f8fafc;
            color: var(--t2);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .65rem 1rem;
            border-bottom: 1px solid var(--brd);
            white-space: nowrap;
        }

        table.log-table tbody td {
            padding: .7rem 1rem;
            border-bottom: 1px solid var(--brd);
            color: var(--t1);
            vertical-align: middle
        }

        table.log-table tbody tr:last-child td {
            border-bottom: none
        }

        table.log-table tbody tr:hover {
            background: #f8fafc
        }

        .badge {
            display: inline-block;
            padding: .2rem .55rem;
            border-radius: 6px;
            font-size: .72rem;
            font-weight: 700
        }

        .badge-success {
            background: var(--gl);
            color: var(--g)
        }

        .badge-danger {
            background: var(--rl);
            color: var(--r)
        }

        .badge-warning {
            background: var(--al);
            color: var(--a)
        }

        .badge-info {
            background: rgba(14, 165, 233, .1);
            color: #0284c7
        }

        .badge-secondary {
            background: #f1f5f9;
            color: var(--t2)
        }

        .cat-pill {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .6rem;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 600;
        }

        .card-mono {
            font-family: 'DM Mono', monospace;
            font-size: .8rem;
            background: #f1f5f9;
            padding: .15rem .45rem;
            border-radius: 4px;
            color: var(--t2)
        }

        .msg-cell {
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .empty-state {
            text-align: center;
            padding: 3.5rem 1rem;
            color: var(--t3)
        }

        .empty-state i {
            font-size: 3rem;
            opacity: .3;
            display: block;
            margin-bottom: .75rem
        }

        /* Pagination */
        .pag-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            flex-wrap: wrap;
            gap: .75rem
        }

        .pag-info {
            font-size: .82rem;
            color: var(--t2)
        }

        .pag-links {
            display: flex;
            gap: .3rem
        }

        .pag-links a,
        .pag-links span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 600;
            border: 1.5px solid var(--brd);
            color: var(--t2);
            text-decoration: none;
            transition: .15s;
        }

        .pag-links a:hover {
            border-color: var(--b);
            color: var(--b)
        }

        .pag-links span.active {
            background: var(--b);
            border-color: var(--b);
            color: #fff
        }

        .pag-links span.disabled {
            opacity: .4;
            pointer-events: none
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1400px;">

        {{-- Hero --}}
        <div class="logs-hero">
            <div>
                <h2 class="hero-title"><i class="fas fa-history me-2"></i> Card Scan Logs</h2>
                <p class="hero-sub">Full scan history — filter by category, date, and result.</p>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <span class="hero-badge"><i class="fas fa-calendar-day me-1"></i> {{ $date }}</span>
                @if(PermissionHelper::canFeature('view_hub'))
                <a href="{{ route('card-scan.hub') }}" class="btn btn-outline"
                    style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3)">
                    <i class="fas fa-qrcode"></i> Back to Hub
                </a>
                @endif
            </div>
        </div>

        {{-- Category Summary --}}
        @if($categorySummary->count())
            <div class="panel">
                <div class="panel-title"><i class="fas fa-chart-bar"></i> Today's Summary</div>
                <div class="summary-grid">
                    @php
                        $catMeta = [
                            'attendance_arrival' => ['icon' => 'fa-school', 'color' => '#2f2ccb', 'label' => 'Arrival'],
                            'attendance_class' => ['icon' => 'fa-chalkboard-teacher', 'color' => '#7c3aed', 'label' => 'Class Att.'],
                            'library_issue' => ['icon' => 'fa-book-open', 'color' => '#059669', 'label' => 'Book Issue'],
                            'library_return' => ['icon' => 'fa-undo', 'color' => '#0891b2', 'label' => 'Book Return'],
                            'library_reserve' => ['icon' => 'fa-bookmark', 'color' => '#d97706', 'label' => 'Reserve'],
                            'finance_balance' => ['icon' => 'fa-wallet', 'color' => '#1d4ed8', 'label' => 'Fee Balance'],
                            'finance_payment' => ['icon' => 'fa-cash-register', 'color' => '#15803d', 'label' => 'Fee Payment'],
                            'info' => ['icon' => 'fa-id-card', 'color' => '#64748b', 'label' => 'Card Info'],
                        ];
                        $grouped = $categorySummary->groupBy('scan_category');
                    @endphp
                    @foreach($grouped as $cat => $rows)
                        @php
                            $meta = $catMeta[$cat] ?? ['icon' => 'fa-qrcode', 'color' => '#334155', 'label' => ucfirst($cat)];
                            $total = $rows->sum('cnt');
                            $success = $rows->where('scan_result', 'success')->sum('cnt');
                            $failed = $rows->where('scan_result', 'failed')->sum('cnt');
                        @endphp
                        <div class="summary-chip">
                            <div class="chip-icon" style="background:{{ $meta['color'] }}18;color:{{ $meta['color'] }}">
                                <i class="fas {{ $meta['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="chip-val">{{ $total }}</div>
                                <div class="chip-lbl">{{ $meta['label'] }} &nbsp;
                                    <span style="color:var(--g)">{{ $success }}✓</span>
                                    @if($failed) <span style="color:var(--r)">{{ $failed }}✗</span> @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <div class="panel">
            <div class="panel-title"><i class="fas fa-filter"></i> Filter Logs</div>
            <form method="GET" action="{{ route('card-scan.logs') }}">
                <div class="filter-bar">
                    <input type="date" name="date" value="{{ $date }}">
                    <select name="category">
                        <option value="">All Categories</option>
                        <option value="attendance_arrival" {{ $category === 'attendance_arrival' ? 'selected' : '' }}>School
                            Arrival</option>
                        <option value="attendance_class" {{ $category === 'attendance_class' ? 'selected' : '' }}>Class
                            Attendance
                        </option>
                        <option value="library_issue" {{ $category === 'library_issue' ? 'selected' : '' }}>Book Issue
                        </option>
                        <option value="library_return" {{ $category === 'library_return' ? 'selected' : '' }}>Book Return
                        </option>
                        <option value="library_reserve" {{ $category === 'library_reserve' ? 'selected' : '' }}>Book Reserve
                        </option>
                        <option value="finance_balance" {{ $category === 'finance_balance' ? 'selected' : '' }}>Fee Balance
                        </option>
                        <option value="finance_payment" {{ $category === 'finance_payment' ? 'selected' : '' }}>Fee Payment
                        </option>
                        <option value="info" {{ $category === 'info' ? 'selected' : '' }}>Card Info</option>
                    </select>
                    <select name="result">
                        <option value="">All Results</option>
                        <option value="success" {{ $result === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ $result === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="invalid" {{ $result === 'invalid' ? 'selected' : '' }}>Invalid</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('card-scan.logs') }}" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
                </div>
            </form>
        </div>

        {{-- Logs Table --}}
        <div class="panel">
            <div class="panel-title"><i class="fas fa-list"></i> Scan Records
                <span style="margin-left:auto;font-size:.8rem;font-weight:400;color:var(--t3)">{{ $logs->total() }} total
                    records</span>
            </div>

            @if($logs->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    No scan logs found for the selected filters.
                </div>
            @else
                <div class="log-table-wrap">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Card</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Result</th>
                                <th>Message</th>
                                <th>Scanned By</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $i => $log)
                                @php
                                    $meta = $catMeta[$log->scan_category] ?? ['icon' => 'fa-qrcode', 'color' => '#334155', 'label' => ucfirst(str_replace('_', ' ', $log->scan_category))];
                                @endphp
                                <tr>
                                    <td style="color:var(--t3);font-size:.75rem">{{ $logs->firstItem() + $i }}</td>
                                    <td><span class="card-mono">{{ $log->card_number }}</span></td>
                                    <td>
                                        <span class="badge {{ $log->card_type === 'student' ? 'badge-info' : 'badge-secondary' }}">
                                            {{ strtoupper($log->card_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="cat-pill" style="background:{{ $meta['color'] }}18;color:{{ $meta['color'] }}">
                                            <i class="fas {{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $log->scan_result === 'success' ? 'badge-success' : ($log->scan_result === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                            {{ strtoupper($log->scan_result) }}
                                        </span>
                                    </td>
                                    <td class="msg-cell" title="{{ $log->result_message }}">{{ $log->result_message }}</td>
                                    <td style="color:var(--t2);font-size:.8rem">
                                        @if($log->scanned_by)
                                            <i class="fas fa-user-circle" style="color:var(--t3)"></i>
                                            {{ $log->scanned_by }} <span style="color:var(--t3)">({{ $log->scanned_by_type }})</span>
                                        @else
                                            <span style="color:var(--t3)">—</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap;color:var(--t2);font-size:.8rem">
                                        {{ $log->created_at->format('h:i A') }}
                                        <div style="font-size:.7rem;color:var(--t3)">{{ $log->created_at->format('d M Y') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="pag-wrap">
                    <span class="pag-info">
                        Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
                    </span>
                    <div class="pag-links">
                        @if($logs->onFirstPage())
                            <span class="disabled"><i class="fas fa-chevron-left" style="font-size:.7rem"></i></span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}&date={{ $date }}&category={{ $category }}&result={{ $result }}">
                                <i class="fas fa-chevron-left" style="font-size:.7rem"></i>
                            </a>
                        @endif

                        @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                            @if($page == $logs->currentPage())
                                <span class="active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}&date={{ $date }}&category={{ $category }}&result={{ $result }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}&date={{ $date }}&category={{ $category }}&result={{ $result }}">
                                <i class="fas fa-chevron-right" style="font-size:.7rem"></i>
                            </a>
                        @else
                            <span class="disabled"><i class="fas fa-chevron-right" style="font-size:.7rem"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
    </div>
    </div>
    </div>
@endsection