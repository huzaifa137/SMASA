{{-- resources/views/Attendance/arrival-report.blade.php --}}
<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --b: #2c29ca;
            --bl: rgba(44, 41, 202, .1);
            --bd: #2420a8;
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

        body {
            background: var(--bg)
        }

        .rep-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2c29ca 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .rep-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%, transparent 70%);
        }

        .rep-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 30%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .05) 0%, transparent 70%);
        }

        .hero-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 .3rem
        }

        .hero-sub {
            color: rgba(255, 255, 255, .75);
            font-size: .9rem;
            margin: 0
        }

        .hero-badge {
            background: rgba(255, 255, 255, .2);
            color: #fff;
            padding: .35rem .8rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600
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
        }

        .filter-bar select:focus,
        .filter-bar input[type=date]:focus {
            outline: none;
            border-color: var(--b);
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
            transition: .15s;
            text-decoration: none
        }

        .btn-primary {
            background: var(--b);
            color: #fff
        }

        .btn-primary:hover {
            background: var(--bd);
            color: #fff
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

        .type-tabs {
            display: flex;
            gap: .35rem;
            margin-bottom: 1.25rem
        }

        .type-tab {
            padding: .45rem 1.1rem;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--brd);
            color: var(--t2);
            background: var(--surf);
            transition: .15s;
            text-decoration: none
        }

        .type-tab.active {
            background: var(--b);
            border-color: var(--b);
            color: #fff
        }

        /* Table */
        .rep-table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--brd)
        }

        table.rep-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .83rem
        }

        table.rep-table thead th {
            background: #2c29ca;
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .65rem 1rem;
            border-bottom: none;
            white-space: nowrap;
        }

        table.rep-table tbody td {
            padding: .7rem 1rem;
            border-bottom: 1px solid var(--brd);
            color: var(--t1);
            vertical-align: middle
        }

        table.rep-table tbody tr:last-child td {
            border-bottom: none
        }

        table.rep-table tbody tr:hover {
            background: #f8fafc
        }

        /* Rate bar */
        .rate-wrap {
            display: flex;
            align-items: center;
            gap: .6rem
        }

        .rate-bar-bg {
            flex: 1;
            height: 8px;
            background: var(--brd);
            border-radius: 4px;
            overflow: hidden;
            min-width: 60px
        }

        .rate-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: .3s
        }

        .rate-val {
            font-size: .82rem;
            font-weight: 700;
            min-width: 38px
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
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1400px;">

        {{-- Hero --}}
        <div class="rep-hero">
            <div>
                <h2 class="hero-title"><i class="fas fa-chart-bar me-2"></i> Arrival Attendance Report</h2>
                <p class="hero-sub">Attendance rates from
                    <strong>{{ \Carbon\Carbon::parse($from)->format('d M Y') }}</strong> to
                    <strong>{{ \Carbon\Carbon::parse($to)->format('d M Y') }}</strong></p>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <span class="hero-badge"><i class="fas fa-users me-1"></i> {{ $summary->count() }} {{ $personType }}s</span>
            </div>
        </div>

        {{-- Filter --}}
        <div class="panel">
            <div class="panel-title"><i class="fas fa-filter"></i> Report Filter</div>
            <form method="GET" action="{{ route('card-scan.arrival.report') }}" class="filter-bar">
                <label style="font-size:.82rem;font-weight:600;color:var(--t2)">From:</label>
                <input type="date" name="from" value="{{ $from }}">
                <label style="font-size:.82rem;font-weight:600;color:var(--t2)">To:</label>
                <input type="date" name="to" value="{{ $to }}">
                <select name="person_type">
                    <option value="student" {{ $personType === 'student' ? 'selected' : '' }}>Students</option>
                    <option value="teacher" {{ $personType === 'teacher' ? 'selected' : '' }}>Teachers</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-chart-bar"></i> Generate</button>
            </form>
        </div>

        {{-- Report Table --}}
        <div class="panel">
            <div class="panel-title"><i class="fas fa-table"></i>
                {{ ucfirst($personType) }} Attendance Summary
                <span style="margin-left:auto;font-size:.8rem;font-weight:400;color:var(--t3)">{{ $summary->count() }}
                    records · sorted by attendance rate</span>
            </div>

            @if($summary->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    No arrival data found for the selected period.
                </div>
            @else
                <div class="rep-table-wrap">
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                @if($personType === 'student')
                                <th>Class</th> @endif
                                <th>Days Recorded</th>
                                <th>Present</th>
                                <th>Late</th>
                                <th>Absent</th>
                                <th>Avg Arrival</th>
                                <th>Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary->values() as $i => $row)
                                @php
                                    $rate = $row->attendance_rate ?? 0;
                                    $rateColor = $rate >= 80 ? 'var(--g)' : ($rate >= 60 ? 'var(--a)' : 'var(--r)');
                                    $avgHour = $row->avg_arrival_hour ? sprintf('%02d:%02d', floor($row->avg_arrival_hour), round(($row->avg_arrival_hour - floor($row->avg_arrival_hour)) * 60)) : '—';
                                @endphp
                                <tr>
                                    <td style="color:var(--t3);font-weight:700;font-size:.8rem">
                                        @if($i === 0) 🥇 @elseif($i === 1) 🥈 @elseif($i === 2) 🥉 @else {{ $i + 1 }} @endif
                                    </td>
                                    <td style="font-weight:600;color:var(--t1)">{{ $row->name }}</td>
                                    @if($personType === 'student')
                                        <td style="color:var(--t2);font-size:.82rem">{{ $row->extra }}</td>
                                    @endif
                                    <td style="color:var(--t2)">{{ $row->total_days }}</td>
                                    <td><span class="badge badge-success">{{ $row->present }}</span></td>
                                    <td><span class="badge badge-warning">{{ $row->late }}</span></td>
                                    <td><span class="badge badge-danger">{{ $row->absent }}</span></td>
                                    <td style="font-family:'DM Mono',monospace;font-size:.82rem">{{ $avgHour }}</td>
                                    <td>
                                        <div class="rate-wrap">
                                            <div class="rate-bar-bg">
                                                <div class="rate-bar-fill" style="width:{{ $rate }}%;background:{{ $rateColor }}">
                                                </div>
                                            </div>
                                            <span class="rate-val" style="color:{{ $rateColor }}">{{ $rate }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary footer --}}
                @php
                    $avgRate = $summary->avg('attendance_rate');
                    $perfect = $summary->where('attendance_rate', '>=', 100)->count();
                    $poor = $summary->where('attendance_rate', '<', 60)->count();
                @endphp
                <div style="margin-top:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem">
                    <div
                        style="background:var(--gl);border-radius:10px;padding:.65rem 1.1rem;font-size:.85rem;font-weight:600;color:var(--g)">
                        <i class="fas fa-star"></i> {{ $perfect }} with 100% attendance
                    </div>
                    <div
                        style="background:var(--rl);border-radius:10px;padding:.65rem 1.1rem;font-size:.85rem;font-weight:600;color:var(--r)">
                        <i class="fas fa-exclamation-triangle"></i> {{ $poor }} below 60%
                    </div>
                    <div
                        style="background:var(--bl);border-radius:10px;padding:.65rem 1.1rem;font-size:.85rem;font-weight:600;color:var(--b)">
                        <i class="fas fa-calculator"></i> Average rate: {{ round($avgRate, 1) }}%
                    </div>
                </div>
            @endif
        </div>
    </div>
            </div>
    </div>
@endsection