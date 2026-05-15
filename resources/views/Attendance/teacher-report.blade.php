<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --brand: #5351e4;
        --brand-light: #2C29CA;
        --brand-dark: #2C29CA;
        --brand-muted: rgba(83, 81, 228, 0.08);
        --success: #10b981;
        --success-muted: rgba(16, 185, 129, 0.1);
        --warning: #f59e0b;
        --warning-muted: rgba(245, 158, 11, 0.1);
        --danger: #ef4444;
        --danger-muted: rgba(239, 68, 68, 0.1);
        --info: #3b82f6;
        --info-muted: rgba(59, 130, 246, 0.1);
        --purple: #8b5cf6;
        --purple-muted: rgba(139, 92, 246, 0.1);
        --cyan: #06b6d4;
        --cyan-muted: rgba(6, 182, 212, 0.1);
        --text-primary: #1e293b;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: #e2e8f0;
        --bg-surface: #f8fafc;
    }

    * {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
        background: #f1f5f9;
    }

    /* Modern Glass Header */
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
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
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

    .date-range-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(4px);
        border-radius: 99px;
        padding: 0.35rem 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Filter Panel */
    .filter-card {
        background: white;
        border-radius: 24px;
        padding: 1.5rem;
        margin-bottom: 1.75rem;
        border: 1px solid rgba(83, 81, 228, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .filter-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--brand);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label-modern {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
    }

    .form-control-modern, .form-select-modern {
        border: 1px solid var(--border-light);
        border-radius: 14px;
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        width: 100%;
    }

    .form-control-modern:focus, .form-select-modern:focus {
        outline: none;
        border-color: var(--brand);
        box-shadow: 0 0 0 3px var(--brand-muted);
    }

    .btn-generate {
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        border: none;
        border-radius: 14px;
        padding: 0.6rem 1.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
    }

    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(83, 81, 228, 0.3);
    }

    /* Summary Grid */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .stat-card-premium {
        background: white;
        border-radius: 20px;
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(83, 81, 228, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .stat-card-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(83, 81, 228, 0.1);
        border-color: rgba(83, 81, 228, 0.15);
    }

    .stat-icon {
        position: absolute;
        right: 1rem;
        top: 1rem;
        font-size: 2rem;
        opacity: 0.1;
    }

    .stat-value-premium {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .stat-label-premium {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
        font-weight: 500;
    }

    /* Data Panel */
    .data-card {
        background: white;
        border-radius: 24px;
        margin-bottom: 1.75rem;
        border: 1px solid rgba(83, 81, 228, 0.08);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .card-header-modern {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        background: var(--bg-surface);
    }

    .card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    /* Premium Table */
    .premium-table {
        width: 100%;
        border-collapse: collapse;
    }

    .premium-table th {
        padding: 1rem 1rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-muted);
        font-weight: 700;
        text-align: left;
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border-light);
    }

    .premium-table td {
        padding: 0.9rem 1rem;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-light);
        vertical-align: middle;
    }

    .premium-table tbody tr {
        transition: all 0.2s ease;
    }

    .premium-table tbody tr:hover {
        background: var(--bg-surface);
    }

    .premium-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Status Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.75rem;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .badge-present {
        background: var(--success-muted);
        color: var(--success);
    }
    .badge-absent {
        background: var(--danger-muted);
        color: var(--danger);
    }
    .badge-late {
        background: var(--warning-muted);
        color: var(--warning);
    }
    .badge-on_leave {
        background: var(--info-muted);
        color: var(--info);
    }
    .badge-half_day {
        background: var(--purple-muted);
        color: var(--purple);
    }
    .badge-excused {
        background: var(--cyan-muted);
        color: var(--cyan);
    }

    /* Rate Bar */
    .rate-bar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .rate-percent {
        font-size: 0.85rem;
        font-weight: 700;
        min-width: 45px;
    }

    .rate-track {
        flex: 1;
        height: 6px;
        background: var(--border-light);
        border-radius: 99px;
        overflow: hidden;
        max-width: 100px;
    }

    .rate-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.5s ease;
    }

    /* Teacher Avatar */
    .teacher-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .teacher-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Duration Badge */
    .duration-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.6rem;
        background: var(--bg-surface);
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 500;
        color: var(--text-secondary);
    }

    /* Empty State */
    .empty-state-premium {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(83, 81, 228, 0.08);
    }

    .empty-state-premium i {
        font-size: 4rem;
        color: var(--brand);
        opacity: 0.3;
        margin-bottom: 1rem;
        display: block;
    }

    .empty-state-premium h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .empty-state-premium p {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .glass-header {
            padding: 1.25rem;
        }
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .card-header-modern {
            flex-direction: column;
            align-items: flex-start;
        }
        .premium-table {
            display: block;
            overflow-x: auto;
        }
    }

    /* Scrollbar */
    .table-responsive-custom::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .table-responsive-custom::-webkit-scrollbar-track {
        background: var(--border-light);
        border-radius: 99px;
    }
    .table-responsive-custom::-webkit-scrollbar-thumb {
        background: var(--brand);
        border-radius: 99px;
    }
</style>
@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

    {{-- Modern Glass Header --}}
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="mb-2">
                    <span class="date-range-badge text-white">
                        <i class="fas fa-chalkboard-user me-2"></i> Teacher Attendance Analytics
                    </span>
                    <span class="date-range-badge ms-2 text-white">
                        <i class="fas fa-calendar-alt me-2"></i> {{ \Carbon\Carbon::parse($from)->format('M d') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
                    </span>
                </div>
                <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                    <i class="fas fa-chart-line me-3"></i> Teacher Attendance Report
                </h1>
                <p style="font-size: 0.9rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                    Comprehensive attendance tracking and analytics for faculty members
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex gap-2 justify-content-lg-end">
                    @if($teacherSummary->isNotEmpty())
                    <button onclick="window.print()" class="btn" style="background: rgba(255,255,255,0.15); color: white; border-radius: 14px; padding: 0.5rem 1.2rem;">
                        <i class="fas fa-print me-2"></i> Print
                    </button>
                    @endif
                    <a href="{{ route('attendance.teachers') }}" class="btn" style="background: white; color: var(--brand); border-radius: 14px; padding: 0.5rem 1.2rem; font-weight: 600;">
                        <i class="fas fa-arrow-left me-2"></i> Take Attendance
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="filter-card">
        <div class="filter-title">
            <i class="fas fa-sliders-h"></i> Filter Report
        </div>
        <form method="GET" action="{{ route('attendance.teachers.report') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label-modern">Teacher</label>
                    <select name="teacher_id" class="form-select-modern">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->firstname }} {{ $t->surname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-modern">From Date</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control-modern">
                </div>
                <div class="col-md-2">
                    <label class="form-label-modern">To Date</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control-modern">
                </div>
                <div class="col-md-2">
                    <label class="form-label-modern">Status Filter</label>
                    <select name="status" class="form-select-modern">
                        <option value="">All Statuses</option>
                        <option value="present" {{ $status === 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ $status === 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                        <option value="on_leave" {{ $status === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="half_day" {{ $status === 'half_day' ? 'selected' : '' }}>Half Day</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-modern">Sort By</label>
                    <select name="sort_by" class="form-select-modern">
                        <option value="name">Name</option>
                        <option value="rate" {{ request('sort_by') == 'rate' ? 'selected' : '' }}>Attendance Rate</option>
                        <option value="present">Present Count</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn-generate">
                        <i class="fas fa-chart-line me-1"></i> Fetch
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($teacherSummary->isNotEmpty())

    {{-- Summary Cards --}}
    @php
        $totPresent = $records->where('status','present')->count();
        $totAbsent  = $records->where('status','absent')->count();
        $totLate    = $records->where('status','late')->count();
        $totLeave   = $records->where('status','on_leave')->count();
        $totHalfDay = $records->where('status','half_day')->count();
        $avgRate    = $teacherSummary->avg('attendance_rate');
    @endphp

    <div class="summary-grid">
        <div class="stat-card-premium">
            <i class="fas fa-chalkboard-user stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--text-primary);">{{ $teacherSummary->count() }}</div>
            <div class="stat-label-premium">Faculty Members</div>
        </div>
        <div class="stat-card-premium">
            <i class="fas fa-check-circle stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--success);">{{ $totPresent }}</div>
            <div class="stat-label-premium">Present Records</div>
        </div>
        <div class="stat-card-premium">
            <i class="fas fa-times-circle stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--danger);">{{ $totAbsent }}</div>
            <div class="stat-label-premium">Absent Records</div>
        </div>
        <div class="stat-card-premium">
            <i class="fas fa-clock stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--warning);">{{ $totLate }}</div>
            <div class="stat-label-premium">Late Arrivals</div>
        </div>
        <div class="stat-card-premium">
            <i class="fas fa-umbrella-beach stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--info);">{{ $totLeave }}</div>
            <div class="stat-label-premium">Leave Records</div>
        </div>
        <div class="stat-card-premium">
            <i class="fas fa-chart-line stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--purple);">{{ round($avgRate, 1) }}%</div>
            <div class="stat-label-premium">Average Rate</div>
        </div>
    </div>

    {{-- Per-Teacher Summary Table --}}
    <div class="data-card">
        <div class="card-header-modern">
            <div class="card-title">
                <i class="fas fa-chalkboard" style="color: var(--brand);"></i>
                Per-Faculty Summary
            </div>
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: var(--text-muted);"></i>
                <input type="text" id="teacherSearch" class="form-control-modern" placeholder="Search teacher..." style="padding-left: 2rem; width: 220px;" onkeyup="filterTeacherTable()">
            </div>
        </div>
        <div class="table-responsive-custom" style="overflow-x: auto;">
            <table class="premium-table" id="teacherTable">
                <thead>
                    <tr>
                        <th width="45">#</th>
                        <th>Teacher</th>
                        <th>Contact</th>
                        <th>Days</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Leave</th>
                        <th>Half Day</th>
                        <th width="180">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teacherSummary as $idx => $row)
                    @php 
                        $rColor = $row->attendance_rate >= 85 ? '#10b981' : ($row->attendance_rate >= 70 ? '#f59e0b' : '#ef4444');
                        $initials = strtoupper(substr($row->full_name ?? '?', 0, 1)) . strtoupper(substr(explode(' ', $row->full_name)[1] ?? '', 0, 1));
                    @endphp
                    <tr data-name="{{ strtolower($row->full_name) }}">
                        <td style="color: var(--text-muted); font-size: 0.75rem;">{{ $idx + 1 }}</td>
                        <td>
                            <div class="teacher-cell">
                                <div class="teacher-avatar-sm">{{ $initials }}</div>
                                <span style="font-weight: 600;">{{ $row->full_name }}</span>
                            </div>
                        </td>
                        <td style="font-size: 0.75rem; color: var(--text-muted);">
                            <i class="fas fa-phone-alt me-1" style="font-size: 0.65rem;"></i>
                            {{ $row->phone ?: '—' }}
                        </td>
                        <td style="font-weight: 600;">{{ $row->total_days }}</td>
                        <td><span class="badge-status badge-present"><i class="fas fa-check-circle"></i> {{ $row->present }}</span></td>
                        <td><span class="badge-status badge-absent"><i class="fas fa-times-circle"></i> {{ $row->absent }}</span></td>
                        <td><span class="badge-status badge-late"><i class="fas fa-clock"></i> {{ $row->late }}</span></td>
                        <td><span class="badge-status badge-on_leave"><i class="fas fa-umbrella-beach"></i> {{ $row->on_leave }}</span></td>
                        <td><span class="badge-status badge-half_day"><i class="fas fa-sun"></i> {{ $row->half_day }}</span></td>
                        <td>
                            <div class="rate-bar">
                                <span class="rate-percent" style="color: {{ $rColor }};">{{ $row->attendance_rate }}%</span>
                                <div class="rate-track">
                                    <div class="rate-fill" style="width: {{ $row->attendance_rate }}%; background: {{ $rColor }};"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detailed Records --}}
    @if($records->isNotEmpty())
    <div class="data-card">
        <div class="card-header-modern">
            <div class="card-title">
                <i class="fas fa-list-ul" style="color: var(--brand);"></i>
                Detailed Attendance Records
                <span style="font-size: 0.7rem; font-weight: 400; color: var(--text-muted);">({{ $records->count() }} entries)</span>
            </div>
        </div>
        <div class="table-responsive-custom" style="max-height: 500px; overflow-y: auto;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Teacher</th>
                        <th>Status</th>
                        <th>Arrival</th>
                        <th>Departure</th>
                        <th>Duration</th>
                        <th>Leave Type</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $r)
                    @php
                        $badgeClass = match($r->status) {
                            'present' => 'badge-present',
                            'absent' => 'badge-absent',
                            'late' => 'badge-late',
                            'on_leave' => 'badge-on_leave',
                            'half_day' => 'badge-half_day',
                            default => ''
                        };
                        $icon = match($r->status) {
                            'present' => 'fa-check-circle',
                            'absent' => 'fa-times-circle',
                            'late' => 'fa-clock',
                            'on_leave' => 'fa-umbrella-beach',
                            'half_day' => 'fa-sun',
                            default => 'fa-circle'
                        };
                        $dur = $r->duration ? floor($r->duration/60).'h '.($r->duration%60).'m' : '—';
                        $initials = strtoupper(substr($r->teacher->firstname ?? '?', 0, 1)) . strtoupper(substr($r->teacher->surname ?? '', 0, 1));
                    @endphp
                    <tr>
                        <td style="font-size: 0.8rem;">
                            <i class="far fa-calendar-alt me-1" style="color: var(--text-muted);"></i>
                            {{ $r->attendance_date->format('M d, Y') }}
                            <span style="font-size: 0.7rem; color: var(--text-muted);">({{ $r->attendance_date->format('D') }})</span>
                        </td>
                        <td>
                            <div class="teacher-cell">
                                <div class="teacher-avatar-sm" style="width: 28px; height: 28px; font-size: 0.6rem;">{{ $initials }}</div>
                                <span style="font-weight: 500;">{{ ($r->teacher->firstname ?? '') . ' ' . ($r->teacher->surname ?? '') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-status {{ $badgeClass }}">
                                <i class="fas {{ $icon }}"></i> {{ \App\Models\TeacherAttendance::statusLabel($r->status) }}
                            </span>
                        </td>
                        <td style="font-size: 0.8rem;">
                            @if($r->arrival_time)
                                <i class="fas fa-hourglass-start me-1" style="color: var(--text-muted);"></i>
                                {{ \Carbon\Carbon::parse($r->arrival_time)->format('h:i A') }}
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size: 0.8rem;">
                            @if($r->departure_time)
                                <i class="fas fa-hourglass-end me-1" style="color: var(--text-muted);"></i>
                                {{ \Carbon\Carbon::parse($r->departure_time)->format('h:i A') }}
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="duration-badge">
                                <i class="fas fa-hourglass-half"></i> {{ $dur }}
                            </span>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);">
                            @if($r->leave_type)
                                <i class="fas fa-tag me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $r->leave_type)) }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);">
                            <i class="fas fa-comment me-1"></i>
                            {{ $r->remarks ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @else
    {{-- Empty State --}}
    <div class="empty-state-premium">
        <i class="fas fa-chart-line"></i>
        <h5>Generate Your Report</h5>
        <p>Select filters and click <strong>Generate</strong> to view teacher attendance report.</p>
    </div>
    @endif

</div>
</div>
</div>
@endsection

@push('scripts')
<script>
    function filterTeacherTable() {
        const query = document.getElementById('teacherSearch').value.toLowerCase();
        document.querySelectorAll('#teacherTable tbody tr').forEach(row => {
            const name = row.dataset.name || '';
            row.style.display = name.includes(query) ? '' : 'none';
        });
    }

    // Animate rate bars on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.rate-fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
@endpush