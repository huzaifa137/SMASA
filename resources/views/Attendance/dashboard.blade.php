<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
<link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --brand: #5351e4;
        --brand-light: #2C29CA;
        --brand-dark: #2C29CA;
        --brand-muted: rgba(83, 81, 228, .12);
        --accent: #5351e4;
        --accent-muted: rgba(83, 81, 228, .12);
        --purple: #6c3fc5;
        --purple-muted: rgba(108, 63, 197, .12);
        --danger: #ef4444;
        --danger-muted: rgba(239, 68, 68, .12);
        --info: #3b82f6;
        --info-muted: rgba(59, 130, 246, .12);
        --success: #10b981;
        --success-muted: rgba(16, 185, 129, .12);
        --warning: #f59e0b;
        --warning-muted: rgba(245, 158, 11, .12);
        --surface: #ffffff;
        --surface-2: #f7f9f7;
        --surface-3: #eef3ef;
        --border: rgba(83, 81, 228, .14);
        --text-primary: #0f1f14;
        --text-secondary: #4b6356;
        --text-muted: #8ca898;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, .06);
        --shadow-md: 0 4px 20px rgba(0, 0, 0, .09);
        --shadow-lg: 0 8px 40px rgba(0, 0, 0, .12);
        --radius-sm: 10px;
        --radius-md: 16px;
        --radius-lg: 24px;
        --radius-xl: 32px;
        --font: 'Sora', sans-serif;
        --mono: 'JetBrains Mono', monospace;
    }

    

    body {
        background: #F8FAFC;
    }

    /* Modern Glassmorphism Header */
    .glass-header {
        background: linear-gradient(135deg, rgba(83, 81, 228, 0.98) 0%, rgba(44, 41, 202, 0.95) 100%);
        backdrop-filter: blur(10px);
        border-radius: 32px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 40px -12px rgba(83, 81, 228, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .glass-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .glass-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(108, 63, 197, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    /* Animated Stats Cards */
    .stat-card-new {
        background: white;
        border-radius: 28px;
        padding: 1.5rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
        border: 1px solid rgba(83, 81, 228, 0.1);
    }

    .stat-card-new::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(83, 81, 228, 0.1), transparent);
        transition: left 0.5s;
    }

    .stat-card-new:hover::before {
        left: 100%;
    }

    .stat-card-new:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px -12px rgba(83, 81, 228, 0.25);
        border-color: var(--brand);
    }

    .stat-gradient-bg {
        position: absolute;
        top: -50%;
        right: -20%;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        opacity: 0.1;
        filter: blur(40px);
    }

    .stat-icon-blue {
        background: linear-gradient(135deg, var(--brand-muted), rgba(83, 81, 228, 0.15));
        color: var(--brand);
    }

    /* Panel Cards */
    .form-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(44, 41, 202, .10);
        background: white;
        overflow: hidden;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-weight: 700;
        font-size: .85rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #2C29CA;
        margin-bottom: 1.2rem;
        padding-bottom: .5rem;
        border-bottom: 2px solid #ede9ff;
    }

    .section-header i {
        font-size: 1rem;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Chart Bars */
    .chart-bars {
        display: flex;
        align-items: flex-end;
        gap: 6px;
        height: 100px;
        padding-top: 8px;
    }
    .chart-bar-wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .chart-bar {
        width: 100%;
        border-radius: 4px 4px 0 0;
        min-height: 4px;
        transition: height .6s ease;
        position: relative;
    }
    .chart-bar:hover::after {
        content: attr(data-tip);
        position: absolute;
        bottom: calc(100% + 4px);
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: #fff;
        font-size: 11px;
        padding: 3px 7px;
        border-radius: 5px;
        white-space: nowrap;
        pointer-events: none;
    }
    .chart-label {
        font-size: 10px;
        color: var(--text-muted);
        text-align: center;
    }

    /* Donut Chart */
    .donut-wrap {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 8px 0;
    }
    .donut-legend {
        flex: 1;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 13px;
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Class Row */
    .class-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border-radius: 12px;
        margin-bottom: 8px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        transition: all 0.2s ease;
    }
    .class-row:hover {
        border-color: var(--brand);
        background: #f5f4ff;
    }
    .cr-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .cr-meta {
        font-size: 11px;
        color: var(--text-muted);
    }
    .cr-rate {
        font-size: 18px;
        font-weight: 800;
    }
    .cr-rate-label {
        font-size: 10px;
        color: var(--text-muted);
    }

    /* Quick Action Cards */
    .quick-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid #E2E8F0;
        margin-bottom: 10px;
        text-decoration: none;
        color: var(--text-primary);
        transition: all 0.2s ease;
    }
    .quick-card:hover {
        border-color: var(--brand);
        background: #f5f4ff;
        transform: translateX(4px);
        text-decoration: none;
        color: var(--text-primary);
    }
    .quick-card .qc-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .qc-title {
        font-size: 14px;
        font-weight: 600;
    }
    .qc-sub {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Status Badges */
    .sbadge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .sb-success {
        background: #D1FAE5;
        color: #059669;
    }
    .sb-danger {
        background: #FEE2E2;
        color: #DC2626;
    }
    .sb-warning {
        background: #FEF3C7;
        color: #D97706;
    }
    .sb-info {
        background: #DBEAFE;
        color: #2563EB;
    }

    /* Teacher Table */
    .arr-table {
        width: 100%;
        border-collapse: collapse;
    }
    .arr-table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .7px;
        color: var(--text-muted);
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #E2E8F0;
    }
    .arr-table td {
        padding: 12px 16px;
        font-size: 13px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .arr-table tr:last-child td {
        border-bottom: none;
    }

    /* Teacher Avatar */
    .teacher-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 12px;
    }

    /* Scrollbar */
    .panel-body::-webkit-scrollbar {
        width: 6px;
    }
    .panel-body::-webkit-scrollbar-track {
        background: #E2E8F0;
        border-radius: 99px;
    }
    .panel-body::-webkit-scrollbar-thumb {
        background: var(--brand);
        border-radius: 99px;
    }
</style>
@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

    {{-- Modern Glass Header with Blue Theme --}}
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="mb-2">
                    <span class="badge" style="background: rgba(255,255,255,0.2); color: white; padding: 0.5rem 1rem; border-radius: 99px; backdrop-filter: blur(8px);">
                        <i class="fas fa-calendar-check me-2"></i> {{ \Carbon\Carbon::today()->format('l, F j, Y') }}
                    </span>
                </div>
                <h1 style="font-size: 2.5rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                    <i class="fas fa-user-check me-2"></i> Attendance Dashboard
                </h1>
                <p class="mb-0" style="font-size: 1.1rem; color: rgba(255,255,255,0.9);">
                    Real-time attendance tracking for students and staff
                </p>
            </div>


<div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
    
    <div class="d-flex flex-wrap justify-content-lg-end" style="gap: 0.9rem;">

        <a href="{{ route('attendance.students') }}"
           class="btn"
           style="
                background: #FFF;
                color: #2C29CA;
                border-radius: 16px;
                padding: 0.5rem 1.5rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                white-space: nowrap;
           ">
            <i class="fas fa-user-graduate"></i>
            Students Attendance
        </a>

        <a href="{{ route('attendance.teachers') }}"
           class="btn"
           style="
                background: rgba(255,255,255,0.2);
                color: white;
                border-radius: 16px;
                padding: 0.5rem 1.5rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                white-space: nowrap;
           ">
            <i class="fas fa-chalkboard-user"></i>
            Teacher Attendance
        </a>

    </div>

</div>
        </div>
    </div>

    {{-- Stats Row --}}
    @php 
        $sRate = $totalEnrolled > 0 ? round(($studentStats->present ?? 0) / $totalEnrolled * 100) : 0;
        $tRate = $totalTeachers > 0 ? round(($teacherStats->present ?? 0) / $totalTeachers * 100) : 0;
    @endphp

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-new">
                <div class="stat-gradient-bg" style="background: var(--brand);"></div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon-blue" style="width: 48px; height: 48px; border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-graduate fs-4" style="color: var(--brand);"></i>
                    </div>
                    <span class="badge" style="background: var(--brand-muted); color: var(--brand);">Today</span>
                </div>
                <h2 class="fw-bold mb-1" style="font-size: 2.5rem; color: var(--brand);">{{ $studentStats->present ?? 0 }}</h2>
                <p class="text-muted mb-0">Students Present Today</p>
                <div class="mt-3">
                    <div class="progress" style="height: 4px; border-radius: 99px;">
                        <div class="progress-bar progress-bar-blue" style="width: {{ $sRate }}%; background: linear-gradient(90deg, var(--brand), var(--brand-light)); border-radius: 99px;"></div>
                    </div>
                    <small class="text-primary mt-1 d-block">{{ $sRate }}% of {{ $totalEnrolled }} enrolled</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card-new">
                <div class="stat-gradient-bg" style="background: var(--danger);"></div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width: 48px; height: 48px; background: var(--danger-muted); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-slash fs-4" style="color: var(--danger);"></i>
                    </div>
                    <span class="badge bg-light">Missing</span>
                </div>
                <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ $studentStats->absent ?? 0 }}</h2>
                <p class="text-muted mb-0">Absent Today</p>
                <div class="mt-3">
                    <div class="progress" style="height: 4px; border-radius: 99px;">
                        <div class="progress-bar" style="width: {{ $totalEnrolled > 0 ? round(($studentStats->absent ?? 0)/$totalEnrolled*100) : 0 }}%; background: var(--danger); border-radius: 99px;"></div>
                    </div>
                    <small class="text-danger mt-1 d-block">{{ $totalEnrolled > 0 ? round(($studentStats->absent ?? 0)/$totalEnrolled*100) : 0 }}% absenteeism</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card-new">
                <div class="stat-gradient-bg" style="background: var(--success);"></div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width: 48px; height: 48px; background: var(--success-muted); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line fs-4" style="color: var(--success);"></i>
                    </div>
                    <span class="badge bg-light">Monthly</span>
                </div>
                <h2 class="fw-bold mb-1" style="font-size: 2.5rem;">{{ $monthlyRate ?? '—' }}%</h2>
                <p class="text-muted mb-0">Monthly Attendance Rate</p>
                <div class="mt-3">
                    <div class="progress" style="height: 4px; border-radius: 99px;">
                        <div class="progress-bar" style="width: {{ $monthlyRate ?? 0 }}%; background: var(--success); border-radius: 99px;"></div>
                    </div>
                    <small class="text-success mt-1 d-block">{{ \Carbon\Carbon::today()->format('F Y') }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card-new">
                <div class="stat-gradient-bg" style="background: var(--purple);"></div>
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width: 48px; height: 48px; background: var(--purple-muted); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chalkboard-user fs-4" style="color: var(--purple);"></i>
                    </div>
                    <span class="badge bg-light">Staff</span>
                </div>
                <h2 class="fw-bold mb-1" style="font-size: 2.5rem; color: var(--purple);">{{ $teacherStats->present ?? 0 }}</h2>
                <p class="text-muted mb-0">Teachers Present Today</p>
                <div class="mt-3">
                    <div class="progress" style="height: 4px; border-radius: 99px;">
                        <div class="progress-bar" style="width: {{ $tRate }}%; background: var(--purple); border-radius: 99px;"></div>
                    </div>
                    <small class="mt-1 d-block" style="color: var(--purple);">{{ $tRate }}% of {{ $totalTeachers }} staff</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Grid Row 1 --}}
    <div class="content-grid">

{{-- Weekly Student Trend --}}
<div class="card form-card">
    <div class="card-body p-4">
        <div class="section-header" style="justify-content: space-between;">
            <div>
                <i class="fas fa-chart-line"></i>
                7-Day Student Trend
            </div>
            <a href="{{ route('attendance.students.report') }}" class="btn btn-sm" style="background: var(--brand-muted); color: var(--brand); border-radius: 12px; font-size: 11px; font-weight: 600;">
                Full Report <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        @php
            $maxTotal = $weeklyStudentTrend->max('total') ?: 1;
        @endphp
        <div class="chart-bars" style="height: 140px; align-items: flex-end; padding-top: 0;">
            @foreach($weeklyStudentTrend as $day)
            @php
                $pct = round(($day->present / ($day->total ?: 1)) * 100);
                $barH = round(($day->total / $maxTotal) * 80);
                $date = \Carbon\Carbon::parse($day->attendance_date);
            @endphp
            <div class="chart-bar-wrap" style="height: 140px; justify-content: flex-end;">
                <div style="display:flex;flex-direction:column;justify-content:flex-end;height:100px;width:100%">
                    <div class="chart-bar"
                         style="height:{{ $barH }}px;max-height:100px;background:linear-gradient(180deg,var(--brand),var(--brand-light))"
                         data-tip="{{ $pct }}% present · {{ $date->format('D') }}">
                    </div>
                </div>
                <div class="chart-label">{{ $date->format('D') }}</div>
                <div class="chart-label fw-bold" style="color:var(--brand)">{{ $pct }}%</div>
            </div>
            @endforeach
        </div>
        @if($weeklyStudentTrend->isEmpty())
        <div class="text-center text-muted py-4" style="font-size:13px">
            <i class="fas fa-chart-simple fa-2x mb-2 d-block opacity-50"></i>
            No data for the past 7 days
        </div>
        @endif
    </div>
</div>

{{-- Teacher Status Donut --}}
<div class="card form-card">
    <div class="card-body p-4">
        <div class="section-header" style="justify-content: space-between;">
            <div>
                <i class="fas fa-chalkboard"></i>
                Teacher Status Today
            </div>
            <a href="{{ route('attendance.teachers.report') }}" class="btn btn-sm" style="background: var(--brand-muted); color: var(--brand); border-radius: 12px; font-size: 11px; font-weight: 600;">
                Report <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        @php
            $tp = $teacherStats->present ?? 0;
            $ta = $teacherStats->absent ?? 0;
            $tl = $teacherStats->late ?? 0;
            $tol = $teacherStats->on_leave ?? 0;
            $tnm = max(0, $totalTeachers - ($tp + $ta + $tl + $tol));
        @endphp
        <div class="donut-wrap" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
            <svg width="140" height="140" viewBox="0 0 120 120" style="flex-shrink: 0;">
                @php
                    $total = $totalTeachers ?: 1;
                    $vals = [
                        ['v'=>$tp,  'c'=>'#10b981', 'label'=>'Present'],
                        ['v'=>$ta,  'c'=>'#ef4444', 'label'=>'Absent'],
                        ['v'=>$tl,  'c'=>'#f59e0b', 'label'=>'Late'],
                        ['v'=>$tol, 'c'=>'#8b5cf6', 'label'=>'On Leave'],
                        ['v'=>$tnm, 'c'=>'#cbd5e1', 'label'=>'Not Marked'],
                    ];
                    $r = 45; $cx = 60; $cy = 60;
                    $offset = 0;
                @endphp
                @foreach($vals as $v)
                @if($v['v'] > 0)
                @php
                    $pct2 = $v['v'] / $total;
                    $dash = $pct2 * 2 * pi() * $r;
                    $gap  = (1 - $pct2) * 2 * pi() * $r;
                    $rotate = $offset * 360 - 90;
                    $offset += $pct2;
                @endphp
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                    fill="none" stroke="{{ $v['c'] }}" stroke-width="14"
                    stroke-dasharray="{{ number_format($dash,2) }} {{ number_format($gap,2) }}"
                    stroke-dashoffset="0"
                    transform="rotate({{ $rotate }} {{ $cx }} {{ $cy }})"/>
                @endif
                @endforeach
                <circle cx="60" cy="60" r="30" fill="white"/>
                <text x="60" y="56" text-anchor="middle" font-size="18" font-weight="800" fill="#1e293b">{{ $totalTeachers }}</text>
                <text x="60" y="70" text-anchor="middle" font-size="9" fill="#64748b">STAFF</text>
            </svg>
            <div class="donut-legend" style="flex: 1; min-width: 140px;">
                <div class="legend-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 13px; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="legend-dot" style="background:#10b981; width: 10px; height: 10px; border-radius: 50%;"></div>
                        <span>Present</span>
                    </div>
                    <strong style="color: #10b981;">{{ $tp }}</strong>
                </div>
                <div class="legend-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 13px; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="legend-dot" style="background:#ef4444; width: 10px; height: 10px; border-radius: 50%;"></div>
                        <span>Absent</span>
                    </div>
                    <strong style="color: #ef4444;">{{ $ta }}</strong>
                </div>
                <div class="legend-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 13px; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="legend-dot" style="background:#f59e0b; width: 10px; height: 10px; border-radius: 50%;"></div>
                        <span>Late</span>
                    </div>
                    <strong style="color: #f59e0b;">{{ $tl }}</strong>
                </div>
                <div class="legend-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 13px; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="legend-dot" style="background:#8b5cf6; width: 10px; height: 10px; border-radius: 50%;"></div>
                        <span>On Leave</span>
                    </div>
                    <strong style="color: #8b5cf6;">{{ $tol }}</strong>
                </div>
                <div class="legend-item" style="display: flex; align-items: center; gap: 8px; margin-bottom: 0; font-size: 13px; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="legend-dot" style="background:#cbd5e1; width: 10px; height: 10px; border-radius: 50%;"></div>
                        <span>Not Marked</span>
                    </div>
                    <strong style="color: #94a3b8;">{{ $tnm }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>


    </div>

    {{-- Content Grid Row 2 --}}
    <div class="content-grid">

        {{-- Classes with attendance today --}}
        <div class="card form-card">
            <div class="card-body p-4">
                <div class="section-header">
                    <i class="fas fa-school"></i>
                    Classes Marked Today
                    <span class="badge ms-auto" style="background: var(--brand); color: white; border-radius: 99px;">{{ $classesToday->count() }}</span>
                </div>
                <div style="max-height: 340px; overflow-y: auto;">
                    @forelse($classesToday as $c)
                    <div class="class-row">
                        <div>
                            <div class="cr-name">{{ $c->class_name }} — {{ $c->stream_name }}</div>
                            <div class="cr-meta">
                                <i class="fas fa-check-circle text-success me-1"></i> {{ $c->present }} present &nbsp;
                                <i class="fas fa-times-circle text-danger ms-2 me-1"></i> {{ $c->absent }} absent
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="cr-rate" style="color: {{ $c->rate >= 80 ? '#10b981' : ($c->rate >= 60 ? '#f59e0b' : '#ef4444') }}">
                                {{ $c->rate }}%
                            </div>
                            <div class="cr-rate-label">Attendance</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-clipboard-list fa-3x mb-3 d-block opacity-50"></i>
                        <p class="mb-0" style="font-size:13px">No attendance marked yet today</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card form-card">
            <div class="card-body p-4">
                <div class="section-header">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </div>

                <a href="{{ route('attendance.students') }}" class="quick-card">
                    <div class="qc-icon" style="background: var(--brand-muted); color: var(--brand);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="qc-title">Take Student Attendance</div>
                        <div class="qc-sub">Mark daily class attendance for your students</div>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>

                                <a href="{{ route('attendance.students.report') }}" class="quick-card">
                    <div class="qc-icon" style="background: var(--success-muted); color: var(--success);">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                    <div>
                        <div class="qc-title">Student Reports</div>
                        <div class="qc-sub">Generate detailed attendance analytics</div>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>

                <a href="{{ route('attendance.teachers') }}" class="quick-card">
                    <div class="qc-icon" style="background: var(--purple-muted); color: var(--purple);">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <div class="qc-title">Teacher Check-In</div>
                        <div class="qc-sub">Record staff arrival & departure times</div>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>

                <a href="{{ route('attendance.teachers.report') }}" class="quick-card">
                    <div class="qc-icon" style="background: var(--warning-muted); color: var(--warning);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="qc-title">Teacher Reports</div>
                        <div class="qc-sub">Staff attendance trends and analytics</div>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>

                {{-- My Classes (Teacher) --}}
                @if(!empty($myClasses) && $myClasses->isNotEmpty())
                <hr class="my-3" style="border-color: #E2E8F0;">
                <div class="section-header mb-3" style="border-bottom: none; margin-bottom: 0.5rem;">
                    <i class="fas fa-book-open"></i>
                    My Classes
                </div>
                @foreach($myClasses->take(3) as $mc)
                <a href="{{ route('attendance.take', [$mc->class_id, $mc->stream_id]) }}" class="quick-card" style="padding: 10px 14px;">
                    <div class="qc-icon" style="width: 36px; height: 36px; background: var(--info-muted); color: var(--info); font-size: 14px;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div class="qc-title" style="font-size: 13px;">{{ $mc->class_name }} {{ $mc->stream_name }}</div>
                        <div class="qc-sub">{{ $mc->subject_name ?? 'No subject' }}</div>
                    </div>
                    <i class="fas fa-chevron-right ms-auto text-muted"></i>
                </a>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Teacher Arrivals --}}
    @if($recentTeacherAttendance->isNotEmpty())
    <div class="card form-card mt-3">
        <div class="card-body p-0">
            <div class="p-4 pb-3 border-bottom">
                <div class="section-header mb-0">
                    <i class="fas fa-clock"></i>
                    Recent Teacher Arrivals Today
                    <a href="{{ route('attendance.teachers') }}" class="btn btn-sm ms-auto" style="background: var(--brand-muted); color: var(--brand); border-radius: 12px; font-size: 11px; font-weight: 600;">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <table class="arr-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Arrival Time</th>
                        <th>Status</th>
                        <th>Departure</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTeacherAttendance as $ta)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="teacher-avatar">
                                    {{ strtoupper(substr($ta->teacher->firstname ?? '?', 0, 1)) }}{{ strtoupper(substr($ta->teacher->surname ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600; font-size:13px;">{{ ($ta->teacher->firstname ?? '') . ' ' . ($ta->teacher->surname ?? '') }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:600;">{{ $ta->arrival_time ? \Carbon\Carbon::parse($ta->arrival_time)->format('H:i') : '—' }}</td>
                        <td>
                            <span class="sbadge {{ $ta->status === 'present' ? 'sb-success' : ($ta->status === 'absent' ? 'sb-danger' : ($ta->status === 'late' ? 'sb-warning' : 'sb-info')) }}">
                                {{ \App\Models\TeacherAttendance::statusLabel($ta->status) }}
                            </span>
                        </td>
                        <td style="color: var(--text-muted); font-size:12px;">{{ $ta->departure_time ? \Carbon\Carbon::parse($ta->departure_time)->format('H:i') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
</div>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
<script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
<script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
@endsection