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
        --text-primary: #1e293b;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: #e2e8f0;
        --bg-surface: #f8fafc;
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

    .subject-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        border-radius: 99px;
        padding: 0.35rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Stats Cards Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(83, 81, 228, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(83, 81, 228, 0.1);
        border-color: rgba(83, 81, 228, 0.15);
    }

    .stat-number {
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
        min-width: 240px;
        position: relative;
    }

    .search-wrapper i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    .search-wrapper input {
        width: 100%;
        padding: 0.7rem 0.75rem 0.7rem 2.5rem;
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
        padding: 0.7rem 1rem;
        border: 1px solid var(--border-light);
        border-radius: 14px;
        font-size: 0.85rem;
        background: white;
        cursor: pointer;
        min-width: 180px;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--brand);
    }

    /* Bulk Actions */
    .bulk-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
        padding: 0.25rem 0.75rem;
        background: var(--bg-surface);
        border-radius: 40px;
    }

    .bulk-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        padding: 0 0.5rem;
    }

    .btn-bulk {
        padding: 0.45rem 1rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-bulk-success {
        background: var(--success-muted);
        color: var(--success);
    }
    .btn-bulk-success:hover {
        background: var(--success);
        color: white;
        transform: translateY(-1px);
    }

    .btn-bulk-danger {
        background: var(--danger-muted);
        color: var(--danger);
    }
    .btn-bulk-danger:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-1px);
    }

    .btn-bulk-secondary {
        background: #e2e8f0;
        color: var(--text-secondary);
    }
    .btn-bulk-secondary:hover {
        background: var(--gray);
        color: white;
        transform: translateY(-1px);
    }

    /* Student Cards Grid */
    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.25rem;
        margin-bottom: 6rem;
    }

    /* Student Card */
    .student-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(83, 81, 228, 0.08);
        position: relative;
    }

    .student-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(83, 81, 228, 0.12);
        border-color: rgba(83, 81, 228, 0.2);
    }

    /* Status border accents */
    .student-card.status-present { border-left: 4px solid var(--success); }
    .student-card.status-absent { border-left: 4px solid var(--danger); }
    .student-card.status-late { border-left: 4px solid var(--warning); }
    .student-card.status-excused { border-left: 4px solid var(--info); }

    .card-content {
        padding: 1.25rem;
    }

    /* Student Header */
    .student-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .student-avatar {
        width: 56px;
        height: 56px;
        border-radius: 20px;
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(83, 81, 228, 0.2);
    }

    .student-details {
        flex: 1;
    }

    .student-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.2rem;
    }

    .student-adm {
        font-size: 0.7rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* History Dots */
    .history-preview {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .history-label {
        font-size: 0.6rem;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .history-dots {
        display: flex;
        gap: 0.3rem;
        flex-wrap: wrap;
    }

    .history-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        cursor: help;
        transition: transform 0.1s ease;
    }

    .history-dot:hover {
        transform: scale(1.4);
    }

    /* Status Pills */
    .status-section {
        margin-bottom: 1rem;
    }

    .section-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .status-pills {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .status-pill {
        flex: 1;
        padding: 0.5rem 0.75rem;
        border-radius: 14px;
        font-size: 0.7rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1.5px solid transparent;
        background: var(--bg-surface);
        color: var(--text-secondary);
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .status-pill-P { background: var(--success-muted); color: var(--success); }
    .status-pill-P.active, .status-pill-P:hover { background: var(--success); color: white; transform: translateY(-1px); }

    .status-pill-A { background: var(--danger-muted); color: var(--danger); }
    .status-pill-A.active, .status-pill-A:hover { background: var(--danger); color: white; transform: translateY(-1px); }

    .status-pill-L { background: var(--warning-muted); color: var(--warning); }
    .status-pill-L.active, .status-pill-L:hover { background: var(--warning); color: white; transform: translateY(-1px); }

    .status-pill-E { background: var(--info-muted); color: var(--info); }
    .status-pill-E.active, .status-pill-E:hover { background: var(--info); color: white; transform: translateY(-1px); }

    /* Time Inputs */
    .time-section {
        margin-bottom: 1rem;
    }

    .time-inputs {
        display: flex;
        gap: 0.75rem;
    }

    .time-group {
        flex: 1;
    }

    .time-group label {
        font-size: 0.6rem;
        font-weight: 600;
        color: var(--text-muted);
        display: block;
        margin-bottom: 0.25rem;
    }

    .time-group input {
        width: 100%;
        padding: 0.5rem 0.7rem;
        border: 1px solid var(--border-light);
        border-radius: 12px;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }

    .time-group input:focus {
        outline: none;
        border-color: var(--brand);
        box-shadow: 0 0 0 3px var(--brand-muted);
    }

    /* Remarks */
    .remarks-section input {
        width: 100%;
        padding: 0.5rem 0.7rem;
        border: 1px solid var(--border-light);
        border-radius: 12px;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .remarks-section input:focus {
        outline: none;
        border-color: var(--brand);
        box-shadow: 0 0 0 3px var(--brand-muted);
    }

    /* Save Bar */
    .save-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid var(--border-light);
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        z-index: 100;
        box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.08);
    }

    .stats-group {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .stat-label-sm {
        font-size: 0.65rem;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 600;
    }

    .save-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .btn-save {
        background: linear-gradient(135deg, var(--brand), var(--brand-light));
        color: white;
        border: none;
        border-radius: 16px;
        padding: 0.75rem 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(83, 81, 228, 0.35);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* Toast */
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

    /* Empty State */
    .empty-state-modern {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 28px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .empty-state-modern i {
        font-size: 4rem;
        color: var(--brand);
        opacity: 0.3;
        margin-bottom: 1rem;
        display: block;
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
    @media (max-width: 1200px) {
        .stats-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
        .students-grid {
            grid-template-columns: 1fr;
        }
        .save-bar {
            flex-direction: column;
            text-align: center;
        }
        .stats-group {
            justify-content: center;
        }
        .glass-header {
            padding: 1.25rem;
        }
    }
</style>

<style>
    /* ========== MODERN SAVE ACTIONS SECTION ========== */
    .save-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-top: 1px solid rgba(83, 81, 228, 0.12);
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        z-index: 1000;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    /* Left side - Status Message Area */
    .save-status-modern {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1.25rem;
        background: var(--bg-surface);
        border-radius: 60px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-secondary);
        border: 1px solid var(--border-light);
        transition: all 0.2s ease;
    }

    .save-status-modern i {
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    /* Status icon color variations (updated dynamically) */
    .save-status-modern i.fa-info-circle {
        color: var(--brand);
    }

    .save-status-modern i.fa-check-circle {
        color: var(--success);
    }

    .save-status-modern i.fa-exclamation-circle {
        color: var(--warning);
    }

    .save-status-modern i.fa-spinner {
        color: var(--brand);
    }

    /* Right side - Save Button */
    .btn-save-modern {
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-light) 100%);
        color: white;
        border: none;
        border-radius: 60px;
        padding: 0.85rem 2.2rem;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        letter-spacing: 0.3px;
        box-shadow: 0 4px 12px rgba(83, 81, 228, 0.25);
        position: relative;
        overflow: hidden;
    }

    .btn-save-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-save-modern:hover::before {
        left: 100%;
    }

    .btn-save-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(83, 81, 228, 0.35);
    }

    .btn-save-modern:active {
        transform: translateY(0);
    }

    .btn-save-modern:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
        filter: grayscale(0.05);
    }

    .btn-save-modern i {
        font-size: 1rem;
        transition: transform 0.2s ease;
    }

    .btn-save-modern:hover i {
        transform: translateY(-1px);
    }

    /* Saving state animation */
    .btn-save-modern.saving {
        background: linear-gradient(135deg, #6c6be8 0%, #4a47c7 100%);
    }

    /* Counter badge (optional enhancement - shows pending changes count) */
    .save-actions-stats {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pending-badge {
        background: var(--brand-muted);
        border-radius: 40px;
        padding: 0.35rem 1rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--brand);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .save-actions {
            padding: 1rem;
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .save-status-modern {
            justify-content: center;
            text-align: center;
        }

        .btn-save-modern {
            justify-content: center;
            width: 100%;
        }

        .save-actions-stats {
            justify-content: space-between;
        }
    }

    /* Animation for status changes */
    @keyframes subtlePulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }

    .save-status-modern i.fa-spinner {
        animation: subtlePulse 1s ease infinite;
    }
</style>

@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

    {{-- Modern Glass Header --}}
<div class="glass-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <span class="subject-badge" style="color: #FFF; font-size: 1.1rem; padding: 0.5rem 1rem; display: inline-block;">
                    <i class="fas fa-calendar-alt me-2"></i> {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                </span>
                @if($subjectId)
                <span class="subject-badge ms-3" style="color: #FFF; font-size: 1.1rem; padding: 0.5rem 1rem; display: inline-block;">
                    <i class="fas fa-book-open me-2"></i> Subject Attendance
                </span>
                @endif
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: #FFF; margin-bottom: 0.5rem;">
                <i class="fas fa-user-graduate me-3"></i> {{ $className }} · {{ $streamName }}
            </h1>
            <p style="font-size: 0.9rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                Record attendance for today's session
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <div class="header-buttons" style="display: flex; gap: 1rem; justify-content: flex-end; align-items: center;">
                <input type="date" id="dateChanger" value="{{ $date }}"
                       onchange="window.location.href='{{ route('attendance.take', [$classId, $streamId]) }}?date='+this.value+'&class_subject_id={{ $subjectId }}'"
                       class="date-picker-glass"
                       style="padding: 0.6rem 1rem; font-size: 1rem; border-radius: 8px; border: none; background: rgba(255,255,255,0.2); color: #FFF; cursor: pointer;">
                <a href="{{ route('attendance.students') }}" class="btn-glass"
                   style="padding: 0.6rem 1.5rem; font-size: 1rem; border-radius: 8px; background: rgba(255,255,255,0.2); color: #FFF; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

    {{-- Stats Row --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number" style="color: var(--text-primary);">{{ $students->count() }}</div>
            <div class="stat-label">Total Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="stat-present" style="color: var(--success);">0</div>
            <div class="stat-label">Present</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="stat-absent" style="color: var(--danger);">0</div>
            <div class="stat-label">Absent</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="stat-late" style="color: var(--warning);">0</div>
            <div class="stat-label">Late</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="stat-excused" style="color: var(--info);">0</div>
            <div class="stat-label">Excused</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="toolbar-modern">
        <div class="search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by name or admission number...">
        </div>

        @if($subjects->isNotEmpty())
        <select id="subjectFilter" class="filter-select" 
                onchange="window.location.href='{{ route('attendance.take', [$classId, $streamId]) }}?date={{ $date }}&class_subject_id='+this.value">
            <!-- <option value="">📚 All / Morning Attendance</option> -->
            @foreach($subjects as $subj)
            <option value="{{ $subj->id }}" {{ $subjectId == $subj->id ? 'selected' : '' }}>{{ $subj->subject_name }}</option>
            @endforeach
        </select>
        @endif

        <div class="bulk-actions">
            <span class="bulk-label"><i class="fas fa-check-double"></i> Bulk</span>
            <button class="btn-bulk btn-bulk-success" onclick="markAll('present')">
                <i class="fas fa-check-circle"></i> All Present
            </button>
            <button class="btn-bulk btn-bulk-danger" onclick="markAll('absent')">
                <i class="fas fa-times-circle"></i> All Absent
            </button>
            <button class="btn-bulk btn-bulk-secondary" onclick="markAll('reset')">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
        </div>
    </div>

    {{-- Students Grid --}}
    @if($students->isEmpty())
    <div class="empty-state-modern">
        <i class="fas fa-users-slash"></i>
        <h5>No Students Found</h5>
        <p style="font-size: 0.85rem;">No students are enrolled in this class-stream.</p>
    </div>
    @else
    <div class="students-grid" id="studentsGrid">
        @foreach($students as $student)
        @php
            $att = $existing->get($student->id);
            $status = $att ? $att->status : null;
            $arrival = $att ? ($att->arrival_time ?? '') : '';
            $remarks = $att ? ($att->remarks ?? '') : '';
            $initials = strtoupper(substr($student->firstname ?? '?', 0, 1)) . strtoupper(substr($student->lastname ?? '', 0, 1));
            $studentHistory = $history->get($student->id, collect());
        @endphp
        <div class="student-card {{ $status ? 'status-'.$status : '' }}"
             data-student-id="{{ $student->id }}"
             data-name="{{ strtolower($student->firstname . ' ' . $student->lastname) }}" 
             data-adm="{{ strtolower($student->admission_number ?? '') }}">
            
            <div class="card-content">
                {{-- Student Header --}}
                <div class="student-header">
                    <div class="student-avatar">{{ $initials }}</div>
                    <div class="student-details">
                        <div class="student-name">{{ $student->firstname }} {{ $student->lastname }}</div>
                        <div class="student-adm">
                            <i class="fas fa-id-card"></i> {{ $student->admission_number ?? 'No admission number' }}
                        </div>
                        @if($studentHistory->isNotEmpty())
                        <div class="history-preview">
                            <span class="history-label">Recent:</span>
                            <div class="history-dots">
                                @foreach($studentHistory->sortByDesc('attendance_date')->take(10) as $h)
                                @php
                                    $hc = match($h->status) {
                                        'present' => '#10b981', 
                                        'absent' => '#ef4444',
                                        'late' => '#f59e0b', 
                                        'excused' => '#6366f1', 
                                        default => '#cbd5e1'
                                    };
                                @endphp
                                <div class="history-dot" style="background: {{ $hc }}"
                                     title="{{ \Carbon\Carbon::parse($h->attendance_date)->format('D, M j') }}: {{ ucfirst($h->status) }}">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Status Selection --}}
                <div class="status-section">
                    <div class="section-label">
                        <i class="fas fa-flag-checkered"></i> Attendance Status
                    </div>
                    <div class="status-pills">
                        <button type="button" class="status-pill status-pill-P {{ $status === 'present' ? 'active' : '' }}"
                                onclick="setStatus(this, {{ $student->id }}, 'present')">
                            <i class="fas fa-check-circle"></i> Present
                        </button>
                        <button type="button" class="status-pill status-pill-A {{ $status === 'absent' ? 'active' : '' }}"
                                onclick="setStatus(this, {{ $student->id }}, 'absent')">
                            <i class="fas fa-times-circle"></i> Absent
                        </button>
                        <button type="button" class="status-pill status-pill-L {{ $status === 'late' ? 'active' : '' }}"
                                onclick="setStatus(this, {{ $student->id }}, 'late')">
                            <i class="fas fa-clock"></i> Late
                        </button>
                        <button type="button" class="status-pill status-pill-E {{ $status === 'excused' ? 'active' : '' }}"
                                onclick="setStatus(this, {{ $student->id }}, 'excused')">
                            <i class="fas fa-shield-alt"></i> Excused
                        </button>
                    </div>
                    <input type="hidden" class="status-input" 
       data-id="{{ $student->id }}"
       data-original="{{ $att ? $att->status : '' }}"
       value="{{ $status ?? '' }}">
                </div>

                {{-- Time Inputs (shown for late status) --}}
                <div class="time-section" id="time-section-{{ $student->id }}" style="{{ $status === 'late' ? '' : 'display:none' }}">
                    <div class="section-label">
                        <i class="fas fa-clock"></i> Arrival Time
                    </div>
                    <div class="time-inputs">
                        <div class="time-group">
                            <input type="time" class="arrival-input" data-id="{{ $student->id }}"
                                   value="{{ $arrival }}" placeholder="Arrival time">
                        </div>
                    </div>
                </div>

                {{-- Remarks --}}
                <div class="remarks-section">
                    <div class="section-label">
                        <i class="fas fa-pen-alt"></i> Remarks (Optional)
                    </div>
                    <input type="text" class="remarks-input" data-id="{{ $student->id }}"
                           value="{{ $remarks }}" placeholder="Add a note...">
                </div>

            <div class="save-actions-stats mt-4">
                <button class="btn-save-modern" onclick="saveAttendance(this, {{ $student->id }})">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Save Attendance</span>
                </button>
            </div>

            </div>
        </div>
        @endforeach

{{-- Replace your current save-actions-stats div with this --}}
<div class="save-actions-stats mt-4" id="saveAllContainer">
    <button class="btn-save-modern" id="saveAllBtn" style="width: 100%; justify-content: center; background: linear-gradient(135deg, #10b981, #059669);">
        <i class="fas fa-cloud-upload-alt"></i>
        <span>Save All Attendance Records</span>
        <span id="pendingCount" class="pending-badge" style="background: rgba(255,255,255,0.2); margin-left: 0.5rem;">0 pending</span>
    </button>
</div>
    </div>
    @endif
    
</div>
</div>
</div>

{{-- Toast Notification --}}
<div class="toast-notification" id="toastEl"></div>
@endsection


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Attendance data store
let attendanceMap = {};
let originalAttendanceMap = {};

// FIRST: Store original state BEFORE any changes
function storeOriginalState() {
    document.querySelectorAll('.student-card').forEach(card => {
        const statusInput = card.querySelector('.status-input');
        if (!statusInput) return;
        
        const id = statusInput.dataset.id;
        const arrivalInput = card.querySelector('.arrival-input');
        const remarksInput = card.querySelector('.remarks-input');
        
        // Store original values from database
        originalAttendanceMap[id] = {
            status: statusInput.value,
            arrival: arrivalInput ? arrivalInput.value : '',
            remarks: remarksInput ? remarksInput.value : ''
        };
        
        // Initialize current map with SAME values as original
        attendanceMap[id] = {
            status: statusInput.value,
            arrival: arrivalInput ? arrivalInput.value : '',
            remarks: remarksInput ? remarksInput.value : ''
        };
    });
    
    console.log('Original state stored:', originalAttendanceMap);
}

// Check for unsaved changes and update pending count
function checkForChanges() {
    let changedCount = 0;
    
    for (let id in attendanceMap) {
        const original = originalAttendanceMap[id];
        const current = attendanceMap[id];
        
        if (!original) continue;
        
        // Debug logging to see what's happening
        const statusChanged = original.status !== current.status;
        const arrivalChanged = original.arrival !== (current.arrival || '');
        const remarksChanged = original.remarks !== (current.remarks || '');
        
        if (statusChanged || arrivalChanged || remarksChanged) {
            changedCount++;
            console.log(`Student ${id} changed:`, {
                original: original.status,
                current: current.status,
                statusChanged,
                arrivalChanged,
                remarksChanged
            });
            
            // Add visual indicator to the card
            const card = document.querySelector(`.student-card[data-student-id="${id}"]`);
            if (card && !card.classList.contains('has-changes')) {
                card.classList.add('has-changes');
            }
        } else {
            // Remove change indicator
            const card = document.querySelector(`.student-card[data-student-id="${id}"]`);
            if (card && card.classList.contains('has-changes')) {
                card.classList.remove('has-changes');
            }
        }
    }
    
    // Update pending count badge
    const pendingCountSpan = document.getElementById('pendingCount');
    if (pendingCountSpan) {
        if (changedCount > 0) {
            pendingCountSpan.textContent = `${changedCount} pending`;
            pendingCountSpan.style.background = 'rgba(245, 158, 11, 0.3)';
            pendingCountSpan.style.color = '#f59e0b';
        } else {
            pendingCountSpan.textContent = 'All saved';
            pendingCountSpan.style.background = 'rgba(16, 185, 129, 0.2)';
            pendingCountSpan.style.color = '#f1eeee';
        }
    }
    
    return changedCount;
}

// Set up event listeners for inputs
function setupEventListeners() {
    // Arrival time changes
    document.querySelectorAll('.arrival-input').forEach(inp => {
        const id = inp.dataset.id;
        inp.removeEventListener('change', handleArrivalChange);
        inp.addEventListener('change', handleArrivalChange);
    });
    
    // Remarks changes
    document.querySelectorAll('.remarks-input').forEach(inp => {
        const id = inp.dataset.id;
        inp.removeEventListener('input', handleRemarksChange);
        inp.addEventListener('input', handleRemarksChange);
    });
}

function handleArrivalChange(e) {
    const inp = e.target;
    const id = inp.dataset.id;
    if (attendanceMap[id]) {
        attendanceMap[id].arrival = inp.value;
        checkForChanges();
    }
}

function handleRemarksChange(e) {
    const inp = e.target;
    const id = inp.dataset.id;
    if (attendanceMap[id]) {
        attendanceMap[id].remarks = inp.value;
        checkForChanges();
    }
}

// IMPROVED setStatus function
function setStatus(btn, studentId, status) {
    const card = btn.closest('.student-card');
    const statusMap = {
        present: { class: 'status-present', pill: 'P', color: '#10b981' },
        absent: { class: 'status-absent', pill: 'A', color: '#ef4444' },
        late: { class: 'status-late', pill: 'L', color: '#f59e0b' },
        excused: { class: 'status-excused', pill: 'E', color: '#3b82f6' }
    };
    
    const statusInfo = statusMap[status];
    if (!statusInfo) return;
    
    // Remove all status classes
    card.classList.remove('status-present', 'status-absent', 'status-late', 'status-excused');
    card.classList.add(statusInfo.class);
    
    // Update active state on pills
    card.querySelectorAll('.status-pill').forEach(pill => {
        pill.classList.remove('active');
        pill.style.opacity = '0.7';
        pill.style.transform = 'scale(0.98)';
    });
    
    // Activate clicked pill
    btn.classList.add('active');
    btn.style.opacity = '1';
    btn.style.transform = 'scale(1.02)';
    btn.style.boxShadow = `0 4px 12px ${statusInfo.color}40`;
    
    // Add pulsing effect
    btn.style.animation = 'pillPop 0.3s ease-out';
    setTimeout(() => {
        btn.style.animation = '';
    }, 300);
    
    // Update hidden input
    const statusInput = card.querySelector('.status-input');
    if (statusInput) {
        statusInput.value = status;
    }
    
    // CRITICAL FIX: Update the attendanceMap with the new status
    if (!attendanceMap[studentId]) {
        attendanceMap[studentId] = {};
    }
    attendanceMap[studentId].status = status;
    
    console.log(`Status changed for student ${studentId}: ${status}`);
    console.log('Current attendanceMap:', attendanceMap);
    
    // Show/hide time section for late status
    const timeSection = document.getElementById('time-section-' + studentId);
    if (timeSection) {
        timeSection.style.display = status === 'late' ? 'block' : 'none';
    }
    
    // If status is late and no arrival time, set default
    if (status === 'late') {
        const arrivalInput = card.querySelector('.arrival-input');
        if (arrivalInput && !arrivalInput.value) {
            const now = new Date();
            const defaultTime = now.toTimeString().slice(0, 5);
            arrivalInput.value = defaultTime;
            if (attendanceMap[studentId]) {
                attendanceMap[studentId].arrival = defaultTime;
            }
        }
    }
    
    updateCounts();
    checkForChanges(); // This should now detect the change
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes pillPop {
        0% { transform: scale(1); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1.02); }
    }
    
    .status-pill {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .status-pill.active {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-weight: 800;
    }
    
    .student-card.has-changes {
        position: relative;
        animation: subtleGlow 1.5s ease-in-out infinite;
        border: 2px solid #f59e0b !important;
    }
    
    @keyframes subtleGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        50% { box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2); }
    }
    
    .student-card.has-changes::before {
        content: '⚠️';
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 1.2rem;
        z-index: 10;
        background: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(style);

function markAll(status) {
    if (status === 'reset') {
        // Restore each student to their original DB state
        document.querySelectorAll('.student-card').forEach(card => {
            if (card.style.display === 'none') return;
            const statusInput = card.querySelector('.status-input');
            if (!statusInput) return;

            const id = statusInput.dataset.id;
            const originalStatus = originalAttendanceMap[id]?.status ?? ''; // '' = never saved

            // Remove all status classes from card
            card.classList.remove('status-present', 'status-absent', 'status-late', 'status-excused');
            if (originalStatus) {
                card.classList.add('status-' + originalStatus);
            }

            // Deactivate all pills first
            card.querySelectorAll('.status-pill').forEach(pill => {
                pill.classList.remove('active');
                pill.style.opacity = '';
                pill.style.transform = '';
                pill.style.boxShadow = '';
            });

            // Reactivate the correct pill if there was a saved status
            if (originalStatus) {
                const pillMap = { present: 'P', absent: 'A', late: 'L', excused: 'E' };
                const pillClass = '.status-pill-' + pillMap[originalStatus];
                const pill = card.querySelector(pillClass);
                if (pill) {
                    pill.classList.add('active');
                }
            }

            // Restore hidden input value
            statusInput.value = originalStatus;

            // Restore arrival and remarks inputs
            const arrivalInput = card.querySelector('.arrival-input');
            const remarksInput = card.querySelector('.remarks-input');
            const origArrival = originalAttendanceMap[id]?.arrival ?? '';
            const origRemarks = originalAttendanceMap[id]?.remarks ?? '';
            if (arrivalInput) arrivalInput.value = origArrival;
            if (remarksInput) remarksInput.value = origRemarks;

            // Show/hide time section
            const timeSection = document.getElementById('time-section-' + id);
            if (timeSection) {
                timeSection.style.display = originalStatus === 'late' ? 'block' : 'none';
            }

            // Restore attendanceMap to match original
            attendanceMap[id] = {
                status: originalStatus,
                arrival: origArrival,
                remarks: origRemarks
            };
        });

        updateCounts();
        checkForChanges();
        showToast('Reset to original saved state', 'info');
        return;
    }

    // Existing logic for 'present' / 'absent' bulk mark
    const statusMap = { present: 'P', absent: 'A', late: 'L', excused: 'E' };
    const pillClass = `.status-pill-${statusMap[status]}`;

    document.querySelectorAll('.student-card').forEach(card => {
        if (card.style.display === 'none') return;
        const statusInput = card.querySelector('.status-input');
        if (!statusInput) return;
        const id = parseInt(statusInput.dataset.id);
        const btn = card.querySelector(pillClass);
        if (btn) setStatus(btn, id, status);
    });
}

function updateCounts() {
    const counts = { present: 0, absent: 0, late: 0, excused: 0 };
    Object.values(attendanceMap).forEach(v => {
        if (counts.hasOwnProperty(v.status)) counts[v.status]++;
    });

    const statPresent = document.getElementById('stat-present');
    const statAbsent = document.getElementById('stat-absent');
    const statLate = document.getElementById('stat-late');
    const statExcused = document.getElementById('stat-excused');

    if (statPresent) statPresent.textContent = counts.present;
    if (statAbsent) statAbsent.textContent = counts.absent;
    if (statLate) statLate.textContent = counts.late;
    if (statExcused) statExcused.textContent = counts.excused;
}

// Search functionality
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.student-card').forEach(card => {
            const name = card.dataset.name || '';
            const adm = card.dataset.adm || '';
            const shouldShow = name.includes(query) || adm.includes(query);
            card.style.display = shouldShow ? '' : 'none';
        });
    });
}

// SAVE ALL FUNCTION
async function saveAllAttendance() {
    const saveBtn = document.getElementById('saveAllBtn');
    if (!saveBtn) return;
    
    const originalHTML = saveBtn.innerHTML;
    
    // Find all changed students
    const changedStudents = [];
    
    for (let id in attendanceMap) {
        const original = originalAttendanceMap[id];
        const current = attendanceMap[id];
        
        if (!original) continue;
        
        const isChanged = original.status !== current.status ||
                         original.arrival !== (current.arrival || '') ||
                         original.remarks !== (current.remarks || '');
        
        if (isChanged) {
            changedStudents.push({
                student_id: parseInt(id),
                status: current.status || 'present',
                arrival_time: current.arrival || null,
                remarks: current.remarks || null
            });
        }
    }
    
    console.log('Changed students found:', changedStudents.length);
    console.log('AttendanceMap:', attendanceMap);
    console.log('OriginalMap:', originalAttendanceMap);
    
    if (changedStudents.length === 0) {
        showToast('No changes to save', 'info');
        return;
    }
    
const result = await Swal.fire({
    title: 'Save Changes?',
    text: `You have ${changedStudents.length} student(s) with unsaved changes.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, Save All',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33'
});

if (!result.isConfirmed) return;

// continue saving here
    
    // Disable button
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving ' + changedStudents.length + ' records...';
    
    try {
        const response = await fetch('{{ route('attendance.students.save') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                class_id: '{{ $classId }}',
                stream_id: '{{ $streamId }}',
                attendance_date: '{{ $date }}',
                class_subject_id: '{{ $subjectId }}' || null,
                session: 'morning',
                period_label: null,
                attendance: changedStudents
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`✓ Successfully saved ${changedStudents.length} attendance record(s)!`, 'success');
            saveBtn.innerHTML = '<i class="fas fa-check-circle"></i> All Saved!';
            
            // Update original map with current values
            changedStudents.forEach(student => {
                const id = student.student_id;
                originalAttendanceMap[id] = {
                    status: student.status,
                    arrival: student.arrival_time || '',
                    remarks: student.remarks || ''
                };
                
                // Remove change indicator from cards
                const card = document.querySelector(`.student-card[data-student-id="${id}"]`);
                if (card) {
                    card.classList.remove('has-changes');
                }
            });
            
            // Update pending count
            const pendingCountSpan = document.getElementById('pendingCount');
            if (pendingCountSpan) {
                pendingCountSpan.textContent = 'All saved';
                pendingCountSpan.style.background = 'rgba(16, 185, 129, 0.2)';
                pendingCountSpan.style.color = '#10b981';
            }
            
            setTimeout(() => {
                saveBtn.innerHTML = originalHTML;
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to save');
        }
        
    } catch(error) {
        console.error('Save error:', error);
        showToast('✗ Error saving attendance: ' + error.message, 'error');
        saveBtn.innerHTML = originalHTML;
    } finally {
        saveBtn.disabled = false;
    }
}

// Individual save function
async function saveAttendance(btn, studentId) {
    const id = String(studentId);
    const entry = attendanceMap[id];

    if (!entry) {
        showToast('No attendance data found for this student.', 'error');
        return;
    }

    // Check if actually changed
    const original = originalAttendanceMap[id];
    const isChanged = original && (original.status !== entry.status ||
                                   original.arrival !== (entry.arrival || '') ||
                                   original.remarks !== (entry.remarks || ''));
    
    console.log(`Save check for student ${id}:`, { isChanged, original: original?.status, current: entry.status });
    
    if (!isChanged) {
        showToast('No changes to save for this student', 'info');
        return;
    }

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
        const response = await fetch('{{ route('attendance.students.save') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                class_id: '{{ $classId }}',
                stream_id: '{{ $streamId }}',
                attendance_date: '{{ $date }}',
                class_subject_id: '{{ $subjectId }}' || null,
                session: 'morning',
                period_label: null,
                attendance: [{
                    student_id: parseInt(id),
                    status: entry.status || 'present',
                    arrival_time: entry.arrival || null,
                    remarks: entry.remarks || null,
                }]
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast('✓ Saved!', 'success');
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Saved!';
            
            // Update original map
            originalAttendanceMap[id] = {
                status: entry.status,
                arrival: entry.arrival || '',
                remarks: entry.remarks || ''
            };
            
            // Remove change indicator from card
            const card = document.querySelector(`.student-card[data-student-id="${id}"]`);
            if (card) {
                card.classList.remove('has-changes');
            }
            
            checkForChanges();
            
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        } else {
            showToast('✗ ' + (data.message || 'Failed to save'), 'error');
            btn.innerHTML = originalText;
        }

    } catch(e) {
        console.error('Save error:', e);
        showToast('✗ Network error. Please try again.', 'error');
        btn.innerHTML = originalText;
    }

    btn.disabled = false;
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toastEl');
    if (!toast) return;
    
    toast.textContent = message;
    toast.className = `toast-notification show ${type}`;
    
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    storeOriginalState();
    setupEventListeners();
    updateCounts();
    checkForChanges();
    
    // Add event listener for Save All button
    const saveAllBtn = document.getElementById('saveAllBtn');
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', saveAllAttendance);
    }
    
    // Warn before leaving
    window.addEventListener('beforeunload', (e) => {
        const pendingSpan = document.getElementById('pendingCount');
        if (pendingSpan && pendingSpan.textContent !== 'All saved' && pendingSpan.textContent !== '0 pending') {
            e.preventDefault();
            e.returnValue = 'You have unsaved attendance changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
});

// Make functions globally available
window.setStatus = setStatus;
window.markAll = markAll;
window.saveAttendance = saveAttendance;
window.saveAllAttendance = saveAllAttendance;
</script>