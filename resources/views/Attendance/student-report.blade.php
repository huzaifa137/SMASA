<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
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

    .filter-title i {
        font-size: 0.9rem;
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
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

    .search-input-sm {
        padding: 0.5rem 1rem 0.5rem 2rem;
        border: 1px solid var(--border-light);
        border-radius: 40px;
        font-size: 0.8rem;
        width: 220px;
        background: white;
    }

    .search-input-sm:focus {
        outline: none;
        border-color: var(--brand);
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

    .badge-excused {
        background: var(--info-muted);
        color: var(--info);
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
    }

    .rate-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.5s ease;
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

    /* Student Avatar in Table */
    .student-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-avatar-sm {
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
        .search-input-sm {
            width: 100%;
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
                        <i class="fas fa-chart-line me-2"></i> Attendance Analytics
                    </span>
                    @if($className)
                    <span class="date-range-badge ms-2">
                        <i class="fas fa-calendar-alt me-2"></i> {{ \Carbon\Carbon::parse($from)->format('M d') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
                    </span>
                    @endif
                </div>
                <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                    <i class="fas fa-chart-bar me-3"></i> Student Attendance Report
                </h1>
                @if($className)
                <p style="font-size: 0.9rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                    <i class="fas fa-school me-2"></i> {{ $className }} {{ $streamName }}
                </p>
                @endif
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex gap-2 justify-content-lg-end">
                    @if($studentSummary->isNotEmpty())
                    <button onclick="window.print()" class="btn" style="background: rgba(255,255,255,0.15); color: white; border-radius: 14px; padding: 0.5rem 1.2rem;">
                        <i class="fas fa-print me-2"></i> Print
                    </button>
                    @endif
                    <a href="{{ route('attendance.students') }}" class="btn" style="background: white; color: var(--brand); border-radius: 14px; padding: 0.5rem 1.2rem; font-weight: 600;">
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
        <form method="GET" action="{{ route('attendance.students.report') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label-modern">Class</label>
                    <select name="class_id" class="form-select-modern" id="classSelect" onchange="loadStreams(this.value)">
                        <option value="">All Classes</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->class_name }}" {{ $classId == $c->class_name ? 'selected' : '' }}>
                                {{ Helper::recordMdname($c->class_name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-modern">Stream</label>
                    <select name="stream_id" class="form-select-modern" id="streamSelect">
                        <option value="">All Streams</option>
                        @foreach($streams as $s)
                            <option value="{{ $s->stream_id }}" {{ $streamId == $s->stream_id ? 'selected' : '' }}>
                                {{ Helper::recordMdname($s->stream_id) }}
                            </option>
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
                        <option value="excused" {{ $status === 'excused' ? 'selected' : '' }}>Excused</option>
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

    @if($studentSummary->isNotEmpty())

    {{-- Summary Cards --}}
    @php
        $totPresent = $records->where('status', 'present')->count();
        $totAbsent = $records->where('status', 'absent')->count();
        $totLate = $records->where('status', 'late')->count();
        $totExcused = $records->where('status', 'excused')->count();
        $avgRate = $studentSummary->avg('attendance_rate');
        $atRisk = $studentSummary->where('attendance_rate', '<', 75)->count();
    @endphp

    <div class="summary-grid">
        <div class="stat-card-premium">
            <i class="fas fa-users stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--text-primary);">{{ $studentSummary->count() }}</div>
            <div class="stat-label-premium">Total Students</div>
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
            <div class="stat-label-premium">Late Records</div>
        </div>
        <div class="stat-card-premium">
            <i class="fas fa-chart-line stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--info);">{{ round($avgRate, 1) }}%</div>
            <div class="stat-label-premium">Avg Attendance Rate</div>
        </div>
        <div class="stat-card-premium">
            <i class="fas fa-exclamation-triangle stat-icon"></i>
            <div class="stat-value-premium" style="color: var(--danger);">{{ $atRisk }}</div>
            <div class="stat-label-premium">At-Risk (&lt;75%)</div>
        </div>
    </div>

    {{-- Per-Student Summary Table --}}
    <div class="data-card">
        <div class="card-header-modern">
            <div class="card-title">
                <i class="fas fa-chalkboard-user" style="color: var(--brand);"></i>
                Per-Student Summary
            </div>
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: var(--text-muted);"></i>
                <input type="text" id="stuSearch" class="search-input-sm" placeholder="Search student..." style="padding-left: 2rem;" onkeyup="filterStuTable()">
            </div>
        </div>
        <div class="table-responsive-custom" style="overflow-x: auto;">
            <table class="premium-table" id="stuTable">
                <thead>
                    <tr>
                        <th width="45">#</th>
                        <th>Student</th>
                        <th>Admission</th>
                        <th>Total Days</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Late</th>
                        <th>Excused</th>
                        <th width="180">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentSummary as $idx => $row)
                        @php 
                            $rColor = $row->attendance_rate >= 80 ? '#10b981' : ($row->attendance_rate >= 60 ? '#f59e0b' : '#ef4444');
                            $initials = strtoupper(substr($row->full_name ?? '?', 0, 1)) . strtoupper(substr(explode(' ', $row->full_name)[1] ?? '', 0, 1));
                        @endphp
                        <tr data-name="{{ strtolower($row->full_name) }}">
                            <td style="color: var(--text-muted); font-size: 0.75rem;">{{ $idx + 1 }}</td>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar-sm">{{ $initials }}</div>
                                    <span style="font-weight: 600;">{{ $row->full_name }}</span>
                                </div>
                            </td>
                            <td style="font-family: monospace; font-size: 0.75rem; color: var(--text-muted);">{{ $row->admission }}</td>
                            <td>{{ $row->total_days }}</td>
                            <td><span class="badge-status badge-present"><i class="fas fa-check-circle"></i> {{ $row->present }}</span></td>
                            <td><span class="badge-status badge-absent"><i class="fas fa-times-circle"></i> {{ $row->absent }}</span></td>
                            <td><span class="badge-status badge-late"><i class="fas fa-clock"></i> {{ $row->late }}</span></td>
                            <td><span class="badge-status badge-excused"><i class="fas fa-shield-alt"></i> {{ $row->excused }}</span></td>
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
                Detailed Records
                <span style="font-size: 0.7rem; font-weight: 400; color: var(--text-muted);">({{ $records->count() }} entries)</span>
            </div>
        </div>
        <div class="table-responsive-custom" style="max-height: 500px; overflow-y: auto;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Arrival Time</th>
                        <th>Session</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $r)
                        @php 
                            $stu = App\Models\Student::find($r->student_id);
                            $initials = strtoupper(substr($stu->firstname ?? '?', 0, 1)) . strtoupper(substr($stu->lastname ?? '', 0, 1));
                        @endphp
                        <tr>
                            <td style="font-size: 0.8rem;">
                                <i class="far fa-calendar-alt me-1" style="color: var(--text-muted);"></i>
                                {{ $r->attendance_date->format('M d, Y') }}
                                <span style="font-size: 0.7rem; color: var(--text-muted);">({{ $r->attendance_date->format('D') }})</span>
                            </td>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar-sm" style="width: 28px; height: 28px; font-size: 0.6rem;">{{ $initials }}</div>
                                    <span style="font-weight: 500;">{{ $stu ? $stu->firstname . ' ' . $stu->lastname : 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                @php 
                                    $badgeClass = match($r->status) {
                                        'present' => 'badge-present',
                                        'absent' => 'badge-absent',
                                        'late' => 'badge-late',
                                        'excused' => 'badge-excused',
                                        default => ''
                                    };
                                    $icon = match($r->status) {
                                        'present' => 'fa-check-circle',
                                        'absent' => 'fa-times-circle',
                                        'late' => 'fa-clock',
                                        'excused' => 'fa-shield-alt',
                                        default => 'fa-circle'
                                    };
                                @endphp
                                <span class="badge-status {{ $badgeClass }}">
                                    <i class="fas {{ $icon }}"></i> {{ ucfirst($r->status) }}
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
                            <td>
                                <span style="font-size: 0.7rem; padding: 0.2rem 0.6rem; background: var(--bg-surface); border-radius: 99px;">
                                    {{ ucfirst($r->session) }}
                                </span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">
                                {{ $r->remarks ?: '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @elseif(request()->has('class_id') || request()->has('from'))
    {{-- No Data Found --}}
    <div class="empty-state-premium">
        <i class="fas fa-search"></i>
        <h5>No Data Found</h5>
        <p>No attendance records match the selected filters. Try adjusting your search criteria.</p>
    </div>

    @else
    {{-- Initial Empty State --}}
    <div class="empty-state-premium">
        <i class="fas fa-chart-line"></i>
        <h5>Select Filters to Generate Report</h5>
        <p>Choose a class, date range, and click <strong>Generate</strong> to view the attendance report.</p>
    </div>
    @endif

</div>
    </div>
    </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function filterStuTable() {
        const query = document.getElementById('stuSearch').value.toLowerCase();
        document.querySelectorAll('#stuTable tbody tr').forEach(row => {
            const name = row.dataset.name || '';
            row.style.display = name.includes(query) ? '' : 'none';
        });
    }

    function loadStreams(classId) {
        if (!classId) return;
        fetch('{{ url('attendance/ajax/streams') }}/' + classId)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('streamSelect');
                select.innerHTML = '<option value="">All Streams</option>';
                data.forEach(stream => {
                    select.innerHTML += `<option value="${stream.stream_id}">${stream.stream_name || stream.stream_id}</option>`;
                });
            })
            .catch(error => console.error('Error loading streams:', error));
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