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
        --gray: #64748b;
        --gray-muted: rgba(100, 116, 139, 0.1);
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
    .hero-section {
        background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%);
        border-radius: 28px;
        padding: 2rem 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 35px -12px rgba(83, 81, 228, 0.3);
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -10%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(108, 63, 197, 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card-modern {
        background: white;
        border-radius: 20px;
        padding: 1rem 0.75rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(83, 81, 228, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .stat-card-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(83, 81, 228, 0.1);
        border-color: rgba(83, 81, 228, 0.15);
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
        font-weight: 500;
    }

    /* Toolbar */
    .toolbar-modern {
        background: white;
        border-radius: 20px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(83, 81, 228, 0.08);
    }

    .search-wrapper {
        flex: 1;
        min-width: 220px;
        position: relative;
    }

    .search-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    .search-wrapper input {
        width: 100%;
        padding: 0.6rem 0.75rem 0.6rem 2.2rem;
        border: 1px solid var(--border-light);
        border-radius: 14px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .search-wrapper input:focus {
        outline: none;
        border-color: var(--brand);
        box-shadow: 0 0 0 3px var(--brand-muted);
    }

    .filter-select {
        padding: 0.6rem 1rem;
        border: 1px solid var(--border-light);
        border-radius: 14px;
        font-size: 0.85rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--brand);
    }

    .btn-mark-all {
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        border: none;
        border-radius: 14px;
        padding: 0.6rem 1.2rem;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-mark-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(83, 81, 228, 0.3);
    }

    .auto-save-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: var(--text-muted);
        background: var(--brand-muted);
        padding: 0.4rem 0.9rem;
        border-radius: 99px;
    }

    .auto-save-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        display: inline-block;
    }

    .auto-saving .auto-save-dot {
        background: var(--warning);
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    /* Teacher Grid */
    .teacher-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1.25rem;
        margin-bottom: 6rem;
    }

    /* Teacher Card */
    .teacher-card-modern {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(83, 81, 228, 0.08);
        position: relative;
    }

    .teacher-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(83, 81, 228, 0.12);
        border-color: rgba(83, 81, 228, 0.2);
    }

    /* Status border accents */
    .teacher-card-modern.status-present { border-left: 4px solid var(--success); }
    .teacher-card-modern.status-absent { border-left: 4px solid var(--danger); }
    .teacher-card-modern.status-late { border-left: 4px solid var(--warning); }
    .teacher-card-modern.status-on_leave { border-left: 4px solid var(--info); }
    .teacher-card-modern.status-half_day { border-left: 4px solid var(--purple); }
    .teacher-card-modern.status-excused { border-left: 4px solid var(--gray); }

    /* Card Content */
    .card-content {
        padding: 1.25rem;
    }

    .teacher-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .teacher-avatar {
        width: 52px;
        height: 52px;
        border-radius: 20px;
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(83, 81, 228, 0.2);
    }

    .teacher-info {
        flex: 1;
    }

    .teacher-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.2rem;
    }

    .teacher-dept {
        font-size: 0.7rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .save-badge {
        font-size: 0.65rem;
        color: var(--success);
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Status Buttons */
    .status-buttons {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .status-btn {
        flex: 1;
        min-width: 55px;
        padding: 0.5rem 0.25rem;
        border: 1.5px solid var(--border-light);
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        background: white;
        transition: all 0.2s ease;
        color: var(--text-secondary);
    }

    .status-btn:hover {
        transform: translateY(-1px);
    }

    .status-btn.active-P { background: var(--success); color: white; border-color: var(--success); }
    .status-btn.active-A { background: var(--danger); color: white; border-color: var(--danger); }
    .status-btn.active-L { background: var(--warning); color: white; border-color: var(--warning); }
    .status-btn.active-OL { background: var(--info); color: white; border-color: var(--info); }
    .status-btn.active-HD { background: var(--purple); color: white; border-color: var(--purple); }
    .status-btn.active-EX { background: var(--gray); color: white; border-color: var(--gray); }

    .status-btn[data-v="present"]:hover { background: var(--success-muted); color: var(--success); border-color: var(--success); }
    .status-btn[data-v="absent"]:hover { background: var(--danger-muted); color: var(--danger); border-color: var(--danger); }
    .status-btn[data-v="late"]:hover { background: var(--warning-muted); color: var(--warning); border-color: var(--warning); }
    .status-btn[data-v="on_leave"]:hover { background: var(--info-muted); color: var(--info); border-color: var(--info); }
    .status-btn[data-v="half_day"]:hover { background: var(--purple-muted); color: var(--purple); border-color: var(--purple); }
    .status-btn[data-v="excused"]:hover { background: var(--gray-muted); color: var(--gray); border-color: var(--gray); }

    /* Time Inputs */
    .time-inputs {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .time-group {
        flex: 1;
    }

    .time-group label {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        display: block;
        margin-bottom: 0.25rem;
    }

    .time-group input {
        width: 100%;
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }

    .time-group input:focus {
        outline: none;
        border-color: var(--brand);
        box-shadow: 0 0 0 3px var(--brand-muted);
    }

    /* Leave Type */
    .leave-type-row {
        margin-top: 0.75rem;
        padding-top: 0.5rem;
        border-top: 1px dashed var(--border-light);
    }

    .leave-type-row label {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        display: block;
        margin-bottom: 0.25rem;
    }

    .leave-type-row select {
        width: 100%;
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        background: white;
        cursor: pointer;
    }

    .leave-type-row select:focus {
        outline: none;
        border-color: var(--brand);
    }

    /* Bottom Save Bar */
    .save-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid var(--border-light);
        padding: 0.9rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        z-index: 100;
        box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.05);
    }

    .save-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .btn-bulk-save {
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        border: none;
        border-radius: 14px;
        padding: 0.7rem 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-bulk-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(83, 81, 228, 0.35);
    }

    /* Toast Notification */
    .toast-notification {
        position: fixed;
        top: 80px;
        right: 20px;
        background: var(--text-primary);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 14px;
        font-size: 0.8rem;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(120%);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .toast-notification.show {
        transform: translateX(0);
    }

    .toast-notification.success {
        border-left: 4px solid var(--success);
    }

    .toast-notification.error {
        border-left: 4px solid var(--danger);
    }

    /* Header Buttons */
    .header-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 14px;
        padding: 0.5rem 1.2rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-glass:hover {
        background: white;
        color: var(--brand);
        border-color: white;
    }

    .date-picker-glass {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 14px;
        padding: 0.5rem 1rem;
        color: white;
        font-size: 0.85rem;
    }

    .date-picker-glass::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .teacher-grid {
            grid-template-columns: 1fr;
        }
        .save-bar {
            flex-direction: column;
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

    {{-- Hero Section --}}
    <div class="hero-section">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="mb-2">
                    <span class="badge" style="background: #FFF; backdrop-filter: blur(4px); padding: 0.4rem 1rem; border-radius: 99px; font-size: 0.7rem;">
                        <i class="fas fa-calendar-alt me-2"></i> {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </span>
                </div>
                <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                    <i class="fas fa-chalkboard-user me-3"></i> Teacher Attendance
                </h1>
                <p style="font-size: 0.9rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                    Record and manage staff attendance for today
                </p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <div class="header-buttons">
                    <input type="date" id="datePicker" value="{{ $date }}"
                           onchange="window.location.href='{{ route('attendance.teachers') }}?date='+this.value"
                           class="date-picker-glass">
                    <a href="{{ route('attendance.teachers.report') }}" class="btn-glass">
                        <i class="fas fa-chart-bar"></i> Report
                    </a>
                    <a href="{{ route('attendance.dashboard') }}" class="btn-glass">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid">
        <div class="stat-card-modern">
            <div class="stat-value" style="color: var(--text-primary);">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Staff</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-value" style="color: var(--success);">{{ $stats['present'] }}</div>
            <div class="stat-label">Present</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-value" style="color: var(--danger);">{{ $stats['absent'] }}</div>
            <div class="stat-label">Absent</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-value" style="color: var(--warning);">{{ $stats['late'] }}</div>
            <div class="stat-label">Late</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-value" style="color: var(--info);">{{ $stats['on_leave'] }}</div>
            <div class="stat-label">On Leave</div>
        </div>
        <div class="stat-card-modern">
            <div class="stat-value" style="color: var(--text-muted);">{{ $stats['not_marked'] }}</div>
            <div class="stat-label">Not Marked</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="toolbar-modern">
        <div class="search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" id="searchTeacher" placeholder="Search teacher name..." onkeyup="filterTeachers()">
        </div>
        <select id="statusFilter" class="filter-select" onchange="filterTeachers()">
            <option value="">All Statuses</option>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="on_leave">On Leave</option>
            <option value="half_day">Half Day</option>
            <option value="not_marked">Not Marked</option>
        </select>
        <div class="auto-save-indicator" id="autoSaveStatus">
            <span class="auto-save-dot"></span>
            <span>Auto-save enabled</span>
        </div>
        <button class="btn-mark-all" onclick="markAllPresent()">
            <i class="fas fa-check-double"></i> Mark All Present
        </button>
    </div>

    {{-- Teacher Grid --}}
    <div class="teacher-grid" id="teacherGrid">
        @foreach($teachers as $teacher)
        @php
            $att = $existing->get($teacher->id);
            $status = $att ? $att->status : '';
            $arrival = $att ? ($att->arrival_time ?? '') : '';
            $departure = $att ? ($att->departure_time ?? '') : '';
            $leaveType = $att ? ($att->leave_type ?? '') : '';
            $initials = strtoupper(substr($teacher->firstname ?? '?', 0, 1)) . strtoupper(substr($teacher->surname ?? '', 0, 1));
        @endphp
        <div class="teacher-card-modern {{ $status ? 'status-'.$status : '' }}"
             data-teacher-id="{{ $teacher->id }}"
             data-name="{{ strtolower($teacher->firstname . ' ' . $teacher->surname) }}"
             data-status="{{ $status ?: 'not_marked' }}"
             id="tc-{{ $teacher->id }}">

            <div class="card-content">
                <div class="teacher-header">
                    <div class="teacher-avatar">{{ $initials }}</div>
                    <div class="teacher-info">
                        <div class="teacher-name">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                        <div class="teacher-dept">
                            <i class="fas fa-phone-alt" style="font-size: 0.6rem;"></i>
                            {{ $teacher->phonenumber ?? 'No phone' }}
                        </div>
                    </div>
                    <div class="save-badge" id="saved-{{ $teacher->id }}" style="display: {{ $att ? 'flex' : 'none' }}">
                        <i class="fas fa-check-circle"></i> Saved
                    </div>
                </div>

                <div class="status-buttons">
                    @foreach([
                        ['v'=>'present', 'l'=>'Present', 'cls'=>'P'],
                        ['v'=>'absent', 'l'=>'Absent', 'cls'=>'A'],
                        ['v'=>'late', 'l'=>'Late', 'cls'=>'L'],
                        ['v'=>'on_leave', 'l'=>'Leave', 'cls'=>'OL'],
                        ['v'=>'half_day', 'l'=>'Half', 'cls'=>'HD'],
                        ['v'=>'excused', 'l'=>'Excused', 'cls'=>'EX'],
                    ] as $s)
                    <button type="button"
                            class="status-btn {{ $status === $s['v'] ? 'active-'.$s['cls'] : '' }}"
                            data-v="{{ $s['v'] }}"
                            data-cls="{{ $s['cls'] }}"
                            onclick="selectStatus({{ $teacher->id }}, '{{ $s['v'] }}', '{{ $s['cls'] }}', this)">
                        {{ $s['l'] }}
                    </button>
                    @endforeach
                </div>

                <div class="time-inputs">
                    <div class="time-group">
                        <label><i class="fas fa-clock"></i> Arrival</label>
                        <input type="time" id="arr-{{ $teacher->id }}" value="{{ $arrival }}" onchange="autoSave({{ $teacher->id }})">
                    </div>
                    <div class="time-group">
                        <label><i class="fas fa-hourglass-end"></i> Departure</label>
                        <input type="time" id="dep-{{ $teacher->id }}" value="{{ $departure }}" onchange="autoSave({{ $teacher->id }})">
                    </div>
                </div>

                <div class="leave-type-row" id="leave-row-{{ $teacher->id }}" style="{{ $status === 'on_leave' ? '' : 'display:none' }}">
                    <label><i class="fas fa-umbrella-beach"></i> Leave Type</label>
                    <select id="leave-{{ $teacher->id }}" onchange="autoSave({{ $teacher->id }})">
                        <option value="">Select type...</option>
                        <option value="sick" {{ $leaveType === 'sick' ? 'selected' : '' }}>🏥 Sick Leave</option>
                        <option value="annual" {{ $leaveType === 'annual' ? 'selected' : '' }}>🌴 Annual Leave</option>
                        <option value="official" {{ $leaveType === 'official' ? 'selected' : '' }}>📋 Official Duty</option>
                        <option value="maternity" {{ $leaveType === 'maternity' ? 'selected' : '' }}>👶 Maternity Leave</option>
                        <option value="other" {{ $leaveType === 'other' ? 'selected' : '' }}>📝 Other</option>
                    </select>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Bottom Save Bar --}}
    <div class="save-bar">
        <div class="save-info">
            <i class="fas fa-info-circle"></i>
            Changes are auto-saved individually. Use bulk save to confirm all changes at once.
        </div>
        <form method="POST" action="{{ route('attendance.teachers.save.bulk') }}" id="bulkForm">
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $date }}">
            <div id="bulkInputs"></div>
            <button type="submit" class="btn-bulk-save">
                <i class="fas fa-cloud-upload-alt"></i> Bulk Save All
            </button>
        </form>
    </div>
</div>
    </div>
</div>
    </div>

{{-- Toast Notification --}}
<div class="toast-notification" id="taToast"></div>
@endsection

@push('scripts')
<script>
const teacherStatuses = {};

// Initialize from existing data
@foreach($teachers as $t)
teacherStatuses[{{ $t->id }}] = {
    status:     '{{ $existing->get($t->id)?->status ?? '' }}',
    arrival:    '{{ $existing->get($t->id)?->arrival_time ?? '' }}',
    departure:  '{{ $existing->get($t->id)?->departure_time ?? '' }}',
    leave_type: '{{ $existing->get($t->id)?->leave_type ?? '' }}',
};
@endforeach

function selectStatus(id, status, cls, btn) {
    const card = document.getElementById('tc-' + id);
    
    // Remove all active classes from status buttons
    card.querySelectorAll('.status-btn').forEach(b => {
        b.classList.remove('active-P', 'active-A', 'active-L', 'active-OL', 'active-HD', 'active-EX');
    });
    btn.classList.add('active-' + cls);

    // Update card status class
    const statusClasses = ['status-present', 'status-absent', 'status-late', 'status-on_leave', 'status-half_day', 'status-excused'];
    statusClasses.forEach(sc => card.classList.remove(sc));
    if (status) card.classList.add('status-' + status);
    
    card.dataset.status = status;
    teacherStatuses[id].status = status;

    // Show/hide leave type row
    const leaveRow = document.getElementById('leave-row-' + id);
    if (leaveRow) leaveRow.style.display = status === 'on_leave' ? 'flex' : 'none';

    // Set default arrival time for late status if not set
    if (status === 'late' && !document.getElementById('arr-' + id).value) {
        const now = new Date();
        document.getElementById('arr-' + id).value = now.toTimeString().slice(0, 5);
    }

    autoSave(id);
}

async function autoSave(id) {
    const status = teacherStatuses[id]?.status;
    if (!status) return;

    teacherStatuses[id].arrival = document.getElementById('arr-' + id)?.value || '';
    teacherStatuses[id].departure = document.getElementById('dep-' + id)?.value || '';
    teacherStatuses[id].leave_type = document.getElementById('leave-' + id)?.value || '';

    // Show saving indicator
    const savedEl = document.getElementById('saved-' + id);
    if (savedEl) {
        savedEl.style.display = 'flex';
        savedEl.style.opacity = '0.5';
        savedEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    }

    try {
        const resp = await fetch('{{ route('attendance.teachers.save') }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify({
                teacher_id: id,
                attendance_date: '{{ $date }}',
                status: teacherStatuses[id].status,
                arrival_time: teacherStatuses[id].arrival || null,
                departure_time: teacherStatuses[id].departure || null,
                leave_type: teacherStatuses[id].leave_type || null,
            })
        });
        
        const data = await resp.json();
        
        if (data.success) {
            if (savedEl) {
                savedEl.style.opacity = '1';
                savedEl.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
                setTimeout(() => {
                    if (savedEl) savedEl.style.display = 'none';
                }, 1500);
            }
            showToast('Saved successfully', 'success');
        } else {
            showToast('Save failed: ' + (data.message || 'Unknown error'), 'error');
            if (savedEl) savedEl.style.display = 'none';
        }
    } catch(e) { 
        showToast('Connection error. Please try again.', 'error');
        if (savedEl) savedEl.style.display = 'none';
    }
}

function markAllPresent() {
    document.querySelectorAll('.teacher-card-modern').forEach(card => {
        if (card.style.display === 'none') return;
        const id = parseInt(card.dataset.teacherId);
        const btn = card.querySelector('[data-v="present"]');
        if (btn) selectStatus(id, 'present', 'P', btn);
    });
}

function filterTeachers() {
    const query = document.getElementById('searchTeacher').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    document.querySelectorAll('.teacher-card-modern').forEach(card => {
        const nameMatch = card.dataset.name.includes(query);
        const statusMatch = !statusFilter || card.dataset.status === statusFilter;
        card.style.display = nameMatch && statusMatch ? '' : 'none';
    });
}

// Bulk form sync
document.getElementById('bulkForm').addEventListener('submit', function(e) {
    const container = document.getElementById('bulkInputs');
    container.innerHTML = '';
    
    Object.entries(teacherStatuses).forEach(([id, data]) => {
        if (!data.status) return;
        
        const fields = { 
            status: data.status, 
            arrival_time: data.arrival, 
            departure_time: data.departure, 
            leave_type: data.leave_type 
        };
        
        Object.entries(fields).forEach(([key, val]) => {
            if (val) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `attendance[${id}][${key}]`;
                input.value = val;
                container.appendChild(input);
            }
        });
    });
});

function showToast(message, type = 'success') {
    const toast = document.getElementById('taToast');
    toast.textContent = message;
    toast.className = `toast-notification show ${type}`;
    
    clearTimeout(window._taTimer);
    window._taTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 2500);
}

// Enter key search
document.getElementById('searchTeacher').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') filterTeachers();
});
</script>
@endpush