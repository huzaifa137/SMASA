<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --brand: #5351e4; --brand-light: #2C29CA;
        --success: #10b981; --danger: #ef4444; --warning: #f59e0b; --info: #3b82f6;
        --text-primary: #1e293b; --text-secondary: #475569; --text-muted: #94a3b8;
        --border: #e2e8f0; --surface: #ffffff;
    }
    
    .glass-header {
        background:linear-gradient(135deg,#5351e4 0%,#2C29CA 100%);
        border-radius:28px; padding:1.5rem 2rem; margin-bottom:1.5rem;
        box-shadow:0 20px 40px -12px rgba(83,81,228,0.35);
        position:relative; overflow:hidden;
    }
    .glass-header::before { content:'';position:absolute;top:-40%;right:-10%;width:250px;height:250px;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);border-radius:50%; }
    .btn-glass {
        background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3);
        color:white; border-radius:10px; padding:0.45rem 1rem; font-weight:600;
        font-size:0.82rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.2s;
    }
    .btn-glass:hover { background:rgba(255,255,255,0.25); color:white; }
    .btn-glass-success { background:rgba(16,185,129,0.7); border-color:rgba(16,185,129,0.4); }
    .btn-glass-warning { background:rgba(245,158,11,0.7); border-color:rgba(245,158,11,0.4); }

    /* ── Timetable Grid ───────────────────────────────────────────── */
    .tt-wrapper { overflow-x:auto; padding-bottom:1rem; }
    .tt-grid {
        display:grid;
        min-width:900px;
        border-radius:20px; overflow:hidden;
        box-shadow:0 4px 24px rgba(0,0,0,0.07);
        background:white;
    }
    .tt-head-row, .tt-body-row {
        display:grid;
        grid-template-columns: 130px repeat({{ count($days) }}, 1fr);
    }
    .tt-head-cell {
        background:#5351e4; color:white;
        padding:0.85rem 0.5rem; text-align:center;
        font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;
        border-right:1px solid rgba(255,255,255,0.12);
    }
    .tt-head-cell:last-child { border-right:none; }
    .tt-head-cell.period-col { background:#2C29CA; text-align:left; padding:0.85rem 1rem; }

    .tt-period-cell {
        background:#f8fafc; padding:0.6rem 1rem;
        border-bottom:1px solid var(--border); border-right:1px solid var(--border);
        display:flex; flex-direction:column; justify-content:center; min-height:80px;
    }
    .period-name { font-size:0.78rem; font-weight:700; color:var(--text-primary); }
    .period-time { font-size:0.68rem; color:var(--text-muted); margin-top:2px; }
    .period-dur  { font-size:0.65rem; color:var(--brand); font-weight:600; margin-top:1px; }
    .period-type-break  .period-name { color:#059669; }
    .period-type-lunch  .period-name { color:#d97706; }
    .period-type-assembly .period-name { color:#2563eb; }

    /* Slot cells */
    .tt-slot {
        border-bottom:1px solid var(--border); border-right:1px solid var(--border);
        min-height:80px; cursor:pointer; transition:background 0.15s;
        position:relative; padding:6px;
    }
    .tt-slot:hover { background:#f0f4ff; }
    .tt-slot:last-child { border-right:none; }
    .tt-body-row:last-child .tt-slot,
    .tt-body-row:last-child .tt-period-cell { border-bottom:none; }

    /* Break/non-lesson slots */
    .tt-slot.is-break {
        background:repeating-linear-gradient(45deg,#f8fafc,#f8fafc 4px,#f1f5f9 4px,#f1f5f9 8px);
        cursor:default; pointer-events:none;
    }

    /* Filled slot chip */
    .slot-chip {
        border-radius:10px; padding:0.45rem 0.6rem; height:100%; min-height:68px;
        display:flex; flex-direction:column; justify-content:center; gap:2px;
        transition:all 0.2s; border:none; width:100%; text-align:left; cursor:pointer;
        background:#5351e4; color:white;
    }
    .slot-chip:hover { filter:brightness(1.07); transform:scale(1.01); }
    .slot-subject { font-size:0.78rem; font-weight:800; line-height:1.2; }
    .slot-teacher { font-size:0.68rem; opacity:0.85; margin-top:2px; }
    .slot-room    { font-size:0.62rem; opacity:0.7; }

    /* Empty slot btn */
    .slot-empty {
        width:100%; height:100%; min-height:68px;
        display:flex; align-items:center; justify-content:center;
        border:2px dashed var(--border); border-radius:10px;
        color:var(--text-muted); font-size:0.75rem; font-weight:600;
        background:transparent; cursor:pointer; transition:all 0.2s;
    }
    .slot-empty:hover { border-color:var(--brand); color:var(--brand); background:rgba(83,81,228,0.04); }

    /* Subject palette sidebar */
    .side-panel { background:white; border-radius:20px; box-shadow:0 4px 24px rgba(0,0,0,0.07); border:1px solid rgba(83,81,228,0.08); }
    .side-section { padding:1.2rem 1.4rem; border-bottom:1px solid var(--border); }
    .side-section:last-child { border-bottom:none; }
    .side-section-title { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--brand); margin-bottom:0.8rem; }
    .subject-item {
        display:flex; align-items:center; gap:0.6rem; padding:0.5rem 0.6rem;
        border-radius:10px; cursor:pointer; transition:all 0.15s; margin-bottom:4px;
        border:1.5px solid transparent; font-size:0.8rem; font-weight:600; color:var(--text-primary);
    }
    .subject-item:hover { background:#f0f4ff; border-color:rgba(83,81,228,0.2); }
    .subject-item.selected { background:rgba(83,81,228,0.1); border-color:var(--brand); color:var(--brand); }
    .subject-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
    .subject-count { font-size:0.65rem; color:var(--text-muted); margin-left:auto; font-weight:500; }

    /* Slot Modal */
    .modal-overlay { display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .slot-modal { background:white; border-radius:24px; width:min(480px,95vw); box-shadow:0 25px 60px rgba(0,0,0,0.2); animation:slideUp 0.25s ease; }
    .slot-modal-header { padding:1.5rem 1.8rem 0; display:flex; align-items:center; justify-content:space-between; }
    .slot-modal-body { padding:1.2rem 1.8rem; }
    .slot-modal-footer { padding:1rem 1.8rem 1.5rem; display:flex; gap:0.7rem; justify-content:flex-end; border-top:1px solid var(--border); margin-top:0.5rem; }
    @keyframes slideUp { from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);} }
    .modal-title { font-size:1rem; font-weight:800; color:var(--text-primary); }
    .modal-subtitle { font-size:0.78rem; color:var(--text-muted); }
    .form-label { font-size:0.78rem; font-weight:700; color:var(--text-secondary); display:block; margin-bottom:0.35rem; }
    .form-control-sm {
        width:100%; padding:0.6rem 0.9rem; border:1.5px solid var(--border); border-radius:12px;
        font-size:0.85rem; color:var(--text-primary); background:#f8fafc; box-sizing:border-box;
        transition:all 0.2s; margin-bottom:0.9rem;
    }
    .form-control-sm:focus { outline:none; border-color:var(--brand); background:white; box-shadow:0 0 0 3px rgba(83,81,228,0.12); }
    .btn-save-slot { background:var(--brand); color:white; border:none; border-radius:12px; padding:0.65rem 1.6rem; font-weight:700; cursor:pointer; }
    .btn-save-slot:hover { background:var(--brand-light); }
    .btn-clear-slot { background:#fef2f2; color:var(--danger); border:none; border-radius:12px; padding:0.65rem 1.2rem; font-weight:700; cursor:pointer; }
    .btn-clear-slot:hover { background:var(--danger); color:white; }
    .btn-cancel-slot { background:#f1f5f9; color:var(--text-secondary); border:none; border-radius:12px; padding:0.65rem 1.2rem; font-weight:700; cursor:pointer; }
    .conflict-banner { background:#fef3cd; border:1px solid #fcd34d; border-radius:10px; padding:0.7rem 1rem; font-size:0.78rem; color:#92400e; margin-bottom:0.8rem; display:none; }
    .conflict-banner.show { display:flex; gap:0.5rem; align-items:flex-start; }

    .status-bar { background:white; border-radius:16px; padding:0.85rem 1.5rem; margin-bottom:1rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap; }
    .stat-item { font-size:0.8rem; display:flex; align-items:center; gap:0.4rem; }
    .stat-num { font-weight:800; color:var(--brand); }

    .toast-notif { position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;background:#1e293b;color:white;padding:0.85rem 1.5rem;border-radius:14px;font-size:0.875rem;font-weight:600;transform:translateY(80px);opacity:0;transition:all 0.3s;box-shadow:0 8px 24px rgba(0,0,0,0.2); }
    .toast-notif.show { transform:translateY(0);opacity:1; }
    .toast-notif.success { background:#059669; }
    .toast-notif.error   { background:#dc2626; }

    .color-row { display:flex; gap:0.4rem; flex-wrap:wrap; margin-top:0.3rem; }
    .color-swatch { width:24px;height:24px;border-radius:8px;cursor:pointer;border:2px solid transparent;transition:all 0.15s; }
    .color-swatch:hover,.color-swatch.selected { border-color:#1e293b; transform:scale(1.15); }

    /* Fix for modal dropdowns getting cut off */
.modal-overlay {
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 1rem;
}

.slot-modal {
    max-height: 90vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.slot-modal-body {
    overflow-y: auto;
    flex: 1;
}

/* Fix for select dropdowns z-index and overflow */
.form-control-sm {
    position: relative;
    z-index: 1;
}

/* Ensure selects appear above modal content when expanded */
select.form-control-sm {
    cursor: pointer;
}

/* For browsers that render native select dropdowns */
select.form-control-sm option {
    padding: 0.5rem;
    background: white;
    color: var(--text-primary);
}

/* Fix for select dropdown positioning in modal */
.slot-modal select.form-control-sm {
    width: 100%;
}

/* Improve select focus state */
select.form-control-sm:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.12);
}

/* Ensure dropdown lists aren't cut off by modal boundaries */
.slot-modal select.form-control-sm[size],
.slot-modal select.form-control-sm[multiple] {
    max-height: 200px;
}

/* For select dropdowns that open upward/downward properly */
.slot-modal select.form-control-sm {
    transform: translateZ(0);
}

/* Additional fix for webkit browsers (Chrome, Edge, Safari) */
@media screen and (-webkit-min-device-pixel-ratio:0) {
    select.form-control-sm {
        -webkit-appearance: menulist;
        appearance: menulist;
    }
}

/* Ensure modal has enough z-index and overflow visible for dropdowns */
.slot-modal {
    position: relative;
    z-index: 1001;
    overflow: visible;
}

.slot-modal-body {
    overflow: visible;
}

/* Fix for select dropdown height */
select.form-control-sm {
    min-height: 42px;
    line-height: 1.5;
}

/* Improve select arrow styling */
select.form-control-sm {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%235351e4' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.9rem center;
    background-size: 14px;
    padding-right: 2rem;
}

/* Custom scrollbar for modal body */
.slot-modal-body::-webkit-scrollbar {
    width: 6px;
}

.slot-modal-body::-webkit-scrollbar-track {
    background: var(--border);
    border-radius: 99px;
}

.slot-modal-body::-webkit-scrollbar-thumb {
    background: var(--brand);
    border-radius: 99px;
}
</style>
@endsection

@section('page-header')
<div class="glass-header" style="position:relative;z-index:1;">
    <div class="row align-items-center" style="position:relative;z-index:1;">
        <div class="col-lg-7">
            <div class="mb-4">
                <span class="badge" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 0.5rem 1rem; border-radius: 99px; font-size: 1rem; color: #FFF; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-calendar-alt me-1"></i> Timetable Management
                    @if($timetable->status === 'draft')
                    <span style="background: rgba(245,158,11,0.3); padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem; margin-left: 0.3rem;">
                        <i class="fas fa-pen-ruler"></i> DRAFT
                    </span>
                    @else
                    <span style="background: rgba(16,185,129,0.3); padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem; margin-left: 0.3rem;">
                        <i class="fas fa-check-circle"></i> ACTIVE
                    </span>
                    @endif
                </span>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
                <i class="fas fa-table me-3"></i> {{ $timetable->name }}
            </h1>
            <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
                {{ $className }} — {{ $streamName }}
                @if($timetable->term) · {{ $timetable->term }} @endif
            </p>
        </div>
        <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('timetable.dashboard') }}" class="btn"
                   style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; white-space: nowrap;">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
                <a href="{{ route('timetable.view', $timetable->id) }}" class="btn"
                   style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; white-space: nowrap;">
                    <i class="fas fa-eye"></i> Preview
                </a>
                @if($timetable->status === 'draft')
                <button onclick="setStatus('active')" class="btn"
                        style="background: rgba(16,185,129,0.8); border: 1px solid rgba(16,185,129,0.4); color: white; border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; white-space: nowrap;">
                    <i class="fas fa-check"></i> Activate
                </button>
                @else
                <button onclick="setStatus('draft')" class="btn"
                        style="background: rgba(245,158,11,0.8); border: 1px solid rgba(245,158,11,0.4); color: white; border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; white-space: nowrap;">
                    <i class="fas fa-pencil-alt"></i> Back to Draft
                </button>
                @endif
                <button onclick="duplicateTT()" class="btn"
                        style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 8px; padding: 0.6rem 1.5rem; font-size: 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; white-space: nowrap;">
                    <i class="fas fa-copy"></i> Duplicate
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row g-3">
    <!-- Main Grid -->
    <div class="col-lg-9">

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="stat-item"><i class="fas fa-calendar-check" style="color:var(--brand);"></i> <span class="stat-num" id="filledSlots">0</span> Lessons</div>
            <div class="stat-item"><i class="fas fa-user-tie" style="color:var(--success);"></i> <span class="stat-num" id="teachersAssigned">0</span> Teachers Assigned</div>
            <div class="stat-item"><i class="fas fa-book" style="color:var(--warning);"></i> <span class="stat-num">{{ $classSubjects->count() }}</span> Subjects Available</div>
            <div class="stat-item" style="margin-left:auto;">
                @if($timetable->status === 'draft')
                    <span style="background:rgba(245,158,11,0.12);color:#d97706;padding:0.25rem 0.8rem;border-radius:99px;font-size:0.72rem;font-weight:700;">
                        <i class="fas fa-pen-ruler"></i> DRAFT — Click any cell to assign
                    </span>
                @else
                    <span style="background:rgba(16,185,129,0.12);color:#059669;padding:0.25rem 0.8rem;border-radius:99px;font-size:0.72rem;font-weight:700;">
                        <i class="fas fa-check-circle"></i> ACTIVE
                    </span>
                @endif
            </div>
        </div>

        <!-- Timetable Grid -->
        <div class="tt-wrapper">
            <div class="tt-grid" id="ttGrid">
                <!-- Header Row -->
                <div class="tt-head-row">
                    <div class="tt-head-cell period-col"><i class="fas fa-clock me-1"></i> Period</div>
                    @foreach($days as $dayNum => $dayName)
                        <div class="tt-head-cell">{{ $dayName }}</div>
                    @endforeach
                </div>

                <!-- Body Rows -->
                @foreach($periods as $period)
                @php
                    $isLesson = $period->type === 'lesson';
                    $dur      = \Carbon\Carbon::parse($period->start_time)->diffInMinutes(\Carbon\Carbon::parse($period->end_time));
                    $typeClass = 'period-type-' . $period->type;
                @endphp
                <div class="tt-body-row">
                    <!-- Period label -->
                    <div class="tt-period-cell {{ $typeClass }}">
                        <div class="period-name">{{ $period->name }}</div>
                        <div class="period-time">{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</div>
                        <div class="period-dur">{{ $dur }} min</div>
                    </div>
                    <!-- Day Slots -->
                    @foreach($days as $dayNum => $dayName)
                    @php
                        $slotKey  = $dayNum . '_' . $period->id;
                        $slot     = $slots[$slotKey] ?? null;
                    @endphp
                    <div class="tt-slot {{ !$isLesson ? 'is-break' : '' }}"
                         data-day="{{ $dayNum }}" data-period="{{ $period->id }}"
                         data-period-name="{{ $period->name }}" data-day-name="{{ $dayName }}"
                         onclick="{{ $isLesson ? 'openSlotModal(' . $dayNum . ',' . $period->id . ',`' . $dayName . '`,`' . $period->name . '`)' : '' }}">

                        @if($slot && $slot->subject_id)
                        @php
                            $color = $slot->color ?? '#5351e4';
                            $subjectName = Helper::recordMdname($slot->subject_id);
                            $teacherName = $slot->teacher_id ? Helper::get_teacher_name($slot->teacher_id) : null;
                        @endphp
                        <button class="slot-chip" style="background:{{ $color }};"
                                onclick="event.stopPropagation(); openSlotModal({{ $dayNum }}, {{ $period->id }}, '{{ $dayName }}', '{{ $period->name }}')">
                            <div class="slot-subject">{{ $subjectName }}</div>
                            @if($teacherName)
                            <div class="slot-teacher"><i class="fas fa-user-tie" style="font-size:0.6rem;"></i> {{ $teacherName }}</div>
                            @endif
                            @if($slot->room)
                            <div class="slot-room"><i class="fas fa-map-marker-alt" style="font-size:0.55rem;"></i> {{ $slot->room }}</div>
                            @endif
                        </button>
                        @elseif($isLesson)
                        <div class="slot-empty">
                            <i class="fas fa-plus" style="font-size:0.7rem;margin-right:4px;"></i> Assign
                        </div>
                        @else
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);font-size:0.7rem;">{{ ucfirst($period->type) }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-3">
        <div class="side-panel">
            <!-- Subjects -->
            <div class="side-section">
                <div class="side-section-title"><i class="fas fa-book me-1"></i> Subjects</div>
                <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.8rem;">Click a subject to quick-select, then click a cell</p>
                @foreach($classSubjects as $cs)
                @php $color = $subjectColors[$cs->subject_id] ?? '#5351e4'; @endphp
                <div class="subject-item" id="subj-{{ $cs->id }}"
                     onclick="selectSubject('{{ $cs->subject_id }}', '{{ $cs->id }}', '{{ addslashes($cs->subject_name) }}', '{{ $color }}', {{ $cs->subject_teacher_1 ?? 'null' }})">
                    <div class="subject-dot" style="background:{{ $color }};"></div>
                    <span>{{ $cs->subject_name }}</span>
                    @php
                        $slotCount = $slots->filter(fn($s) => $s->subject_id == $cs->subject_id)->count();
                    @endphp
                    <span class="subject-count">{{ $slotCount }}x</span>
                </div>
                @endforeach
                @if($classSubjects->isEmpty())
                    <p style="font-size:0.78rem;color:var(--text-muted);text-align:center;">No subjects found for this class stream.</p>
                @endif
            </div>

            <!-- Teachers -->
            <div class="side-section">
                <div class="side-section-title"><i class="fas fa-user-tie me-1"></i> Quick Info</div>
                <div style="font-size:0.78rem;color:var(--text-secondary);">
                    <p style="margin:0 0 0.4rem;"><i class="fas fa-info-circle me-1" style="color:var(--brand);"></i> Click any lesson cell to open the slot editor.</p>
                    <p style="margin:0 0 0.4rem;"><i class="fas fa-exclamation-triangle me-1" style="color:var(--warning);"></i> Teacher conflicts are automatically detected.</p>
                    <p style="margin:0;"><i class="fas fa-check-circle me-1" style="color:var(--success);"></i> Activate when the timetable is complete.</p>
                </div>
            </div>

            <!-- Period legend -->
            <div class="side-section">
                <div class="side-section-title"><i class="fas fa-clock me-1"></i> Periods</div>
                @foreach($periods as $p)
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;font-size:0.77rem;">
                    <span style="color:var(--text-primary);font-weight:600;">{{ $p->name }}</span>
                    <span style="color:var(--text-muted);">{{ \Carbon\Carbon::parse($p->start_time)->format('H:i') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Slot Assignment Modal -->
<div class="modal-overlay" id="slotModal">
    <div class="slot-modal">
        <div class="slot-modal-header">
            <div>
                <div class="modal-title" id="slotModalTitle">Assign Slot</div>
                <div class="modal-subtitle" id="slotModalSub"></div>
            </div>
            <button onclick="closeSlotModal()" style="background:none;border:none;color:var(--text-muted);font-size:1.1rem;cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="slot-modal-body">
            <div class="conflict-banner" id="conflictBanner">
                <i class="fas fa-exclamation-triangle" style="flex-shrink:0;margin-top:1px;"></i>
                <span id="conflictMsg"></span>
            </div>

            <label class="form-label">Subject *</label>
            <select id="mSubject" class="form-control-sm" onchange="onSubjectChange(this.value)">
                <option value="">— Select Subject —</option>
                @foreach($classSubjects as $cs)
                <option value="{{ $cs->subject_id }}" data-cs="{{ $cs->id }}" data-t1="{{ $cs->subject_teacher_1 ?? '' }}">
                    {{ $cs->subject_name }}
                </option>
                @endforeach
            </select>

            <label class="form-label">Teacher</label>
            <select id="mTeacher" class="form-control-sm">
                <option value="">— No Teacher Assigned —</option>
                @foreach($teachers as $t)
                <option value="{{ $t->id }}">{{ $t->firstname }} {{ $t->surname }}</option>
                @endforeach
            </select>

            <div class="form-row-2" style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem;">
                <div>
                    <label class="form-label">Room / Location</label>
                    <input type="text" id="mRoom" class="form-control-sm" placeholder="e.g. Room 12, Lab A" style="margin-bottom:0;">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <input type="text" id="mNotes" class="form-control-sm" placeholder="Optional notes" style="margin-bottom:0;">
                </div>
            </div>

            <div style="margin-top:0.9rem;">
                <label class="form-label">Colour</label>
                <div class="color-row" id="colorPicker">
                    @php $palette = ['#5351e4','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316']; @endphp
                    @foreach($palette as $col)
                    <div class="color-swatch" style="background:{{ $col }};" data-color="{{ $col }}" onclick="selectColor('{{ $col }}')"></div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="slot-modal-footer">
            <button class="btn-clear-slot" id="clearBtn" onclick="clearSlot()" style="display:none;">
                <i class="fas fa-trash"></i> Clear
            </button>
            <button class="btn-cancel-slot" onclick="closeSlotModal()">Cancel</button>
            <button class="btn-save-slot" onclick="saveSlot()">
                <i class="fas fa-check"></i> Save
            </button>
        </div>
    </div>
</div>
</div>
    </div>
</div>

<div class="toast-notif" id="toast"></div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const csrfToken   = '{{ csrf_token() }}';
const timetableId = {{ $timetable->id }};
const slotSaveUrl = '{{ route('timetable.slot.save') }}';
const slotClrUrl  = '{{ route('timetable.slot.clear') }}';
const statusUrl   = '{{ route('timetable.status', $timetable->id) }}';
const dupUrl      = '{{ route('timetable.duplicate', $timetable->id) }}';

// Current modal state
let curDay = null, curPeriod = null, curColor = '#5351e4', selectedCsId = null;

// Pre-populate slots data from server
const serverSlots = {
    @foreach($slots as $key => $slot)
    '{{ $key }}': {
        subject_id: '{{ $slot->subject_id }}',
        subject_name: '{{ addslashes(Helper::recordMdname($slot->subject_id)) }}',
        teacher_id: '{{ $slot->teacher_id ?? '' }}',
        teacher_name: '{{ $slot->teacher_id ? addslashes(Helper::get_teacher_name($slot->teacher_id)) : '' }}',
        room: '{{ addslashes($slot->room ?? '') }}',
        notes: '{{ addslashes($slot->notes ?? '') }}',
        color: '{{ $slot->color ?? '#5351e4' }}',
        class_subject_id: '{{ $slot->class_subject_id ?? '' }}'
    },
    @endforeach
};

// Build slot counts
function updateStatusBar() {
    let filled = 0, teachers = new Set();
    document.querySelectorAll('.tt-slot .slot-chip').forEach(c => { filled++; });
    document.querySelectorAll('.tt-slot .slot-teacher').forEach(el => {
        const t = el.textContent.trim();
        if (t) teachers.add(t);
    });
    document.getElementById('filledSlots').textContent = filled;
    document.getElementById('teachersAssigned').textContent = teachers.size;
}
updateStatusBar();

function openSlotModal(day, period, dayName, periodName) {
    curDay    = day;
    curPeriod = period;
    curColor  = '#5351e4';

    document.getElementById('slotModalTitle').textContent = `${dayName} — ${periodName}`;
    document.getElementById('slotModalSub').textContent   = 'Assign a subject and teacher to this slot';
    document.getElementById('conflictBanner').classList.remove('show');
    document.getElementById('mRoom').value  = '';
    document.getElementById('mNotes').value = '';
    document.getElementById('mSubject').value  = '';
    document.getElementById('mTeacher').value  = '';
    document.getElementById('clearBtn').style.display = 'none';

    // Reset colors
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    document.querySelector(`.color-swatch[data-color="${curColor}"]`)?.classList.add('selected');

    // Load existing data
    const key = `${day}_${period}`;
    if (serverSlots[key]) {
        const s = serverSlots[key];
        document.getElementById('mSubject').value  = s.subject_id;
        document.getElementById('mTeacher').value  = s.teacher_id;
        document.getElementById('mRoom').value     = s.room;
        document.getElementById('mNotes').value    = s.notes;
        curColor = s.color || '#5351e4';
        selectedCsId = s.class_subject_id;
        document.querySelectorAll('.color-swatch').forEach(sw => sw.classList.remove('selected'));
        document.querySelector(`.color-swatch[data-color="${curColor}"]`)?.classList.add('selected');
        document.getElementById('clearBtn').style.display = 'block';
    } else {
        selectedCsId = null;
    }

    document.getElementById('slotModal').classList.add('open');
}

function onSubjectChange(subjectId) {
    const opt = document.querySelector(`#mSubject option[value="${subjectId}"]`);
    if (!opt) return;
    selectedCsId = opt.dataset.cs || null;
    const t1 = opt.dataset.t1;
    if (t1) document.getElementById('mTeacher').value = t1;

    // Set color from palette
    @foreach($subjectColors as $sid => $color)
    if (subjectId === '{{ $sid }}') {
        selectColor('{{ $color }}');
    }
    @endforeach
}

function selectColor(color) {
    curColor = color;
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    document.querySelector(`.color-swatch[data-color="${color}"]`)?.classList.add('selected');
}

function closeSlotModal() {
    document.getElementById('slotModal').classList.remove('open');
}

async function saveSlot() {
    const subjectId = document.getElementById('mSubject').value;
    const teacherId = document.getElementById('mTeacher').value;
    const room      = document.getElementById('mRoom').value.trim();
    const notes     = document.getElementById('mNotes').value.trim();

    if (!subjectId) { showToast('Please select a subject.', 'error'); return; }

    const btn = document.querySelector('.btn-save-slot');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const res = await fetch(slotSaveUrl, {
            method: 'POST',
            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
            body: JSON.stringify({
                timetable_id: timetableId,
                day_of_week: curDay,
                period_id: curPeriod,
                subject_id: subjectId,
                class_subject_id: selectedCsId || null,
                teacher_id: teacherId || null,
                room: room || null,
                notes: notes || null,
                color: curColor
            })
        });
        const data = await res.json();

        if (data.success) {
            showToast('Slot saved!', 'success');
            // Update server slots cache
            const key = `${curDay}_${curPeriod}`;
            serverSlots[key] = {
                subject_id: subjectId,
                subject_name: data.subject_name,
                teacher_id: teacherId,
                teacher_name: data.teacher_name || '',
                room: room, notes: notes, color: curColor,
                class_subject_id: selectedCsId
            };
            // Update cell UI
            updateCell(curDay, curPeriod, data.subject_name, data.teacher_name, room, curColor);
            updateStatusBar();
            closeSlotModal();
        } else {
            // Conflict or error
            document.getElementById('conflictBanner').classList.add('show');
            document.getElementById('conflictMsg').textContent = data.message;
        }
    } catch(e) { showToast('Connection error.', 'error'); }
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Save';
}

async function clearSlot() {
    const result = await Swal.fire({
        title: 'Clear this slot?',
        text: 'This action will remove the assigned slot.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, clear it',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(slotClrUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                timetable_id: timetableId,
                day_of_week: curDay,
                period_id: curPeriod
            })
        });

        const data = await res.json();

        if (data.success) {
            const key = `${curDay}_${curPeriod}`;
            delete serverSlots[key];

            const cell = document.querySelector(
                `.tt-slot[data-day="${curDay}"][data-period="${curPeriod}"]`
            );

            if (cell) {
                cell.innerHTML = `
                    <div class="slot-empty">
                        <i class="fas fa-plus" style="font-size:0.7rem;margin-right:4px;"></i>
                        Assign
                    </div>
                `;
            }

            updateStatusBar();
            closeSlotModal();

            showToast('Slot cleared.', 'success');

            // Optional SweetAlert success popup
            // await Swal.fire({
            //     title: 'Cleared!',
            //     text: 'The slot has been cleared successfully.',
            //     icon: 'success',
            //     timer: 1500,
            //     showConfirmButton: false
            // });
        }
    } catch (error) {
        console.error(error);

        Swal.fire({
            title: 'Error',
            text: 'Failed to clear the slot.',
            icon: 'error'
        });
    }
}

function updateCell(day, period, subjectName, teacherName, room, color) {
    const cell = document.querySelector(`.tt-slot[data-day="${day}"][data-period="${period}"]`);
    if (!cell) return;
    const dayName    = cell.dataset.dayName;
    const periodName = cell.dataset.periodName;
    cell.innerHTML = `
        <button class="slot-chip" style="background:${color};"
                onclick="event.stopPropagation();openSlotModal(${day},${period},'${dayName}','${periodName}')">
            <div class="slot-subject">${subjectName}</div>
            ${teacherName ? `<div class="slot-teacher"><i class="fas fa-user-tie" style="font-size:0.6rem;"></i> ${teacherName}</div>` : ''}
            ${room ? `<div class="slot-room"><i class="fas fa-map-marker-alt" style="font-size:0.55rem;"></i> ${room}</div>` : ''}
        </button>`;
}

async function setStatus(status) {
    const res = await fetch(statusUrl, {
        method: 'PATCH',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken },
        body: JSON.stringify({ status })
    });
    const data = await res.json();
    if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 700); }
    else { showToast(data.message || 'Error.', 'error'); }
}

async function duplicateTT() {
    const result = await Swal.fire({
        title: 'Duplicate timetable?',
        text: 'A copy of this timetable will be created.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, duplicate it',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(dupUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await res.json();

        if (data.success) {
            showToast('Duplicated!', 'success');

            // Optional SweetAlert success popup
            // await Swal.fire({
            //     title: 'Success!',
            //     text: 'Timetable duplicated successfully.',
            //     icon: 'success',
            //     timer: 1500,
            //     showConfirmButton: false
            // });

            setTimeout(() => {
                window.location.href = data.redirect;
            }, 700);

        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'An error occurred.',
                icon: 'error'
            });
        }

    } catch (error) {
        console.error(error);

        Swal.fire({
            title: 'Error',
            text: 'Failed to duplicate timetable.',
            icon: 'error'
        });
    }
}

function selectSubject(subjectId, csId, subjectName, color, teacher1) {
    // Quick-select mode: highlight and pre-fill modal subject on next click
    document.querySelectorAll('.subject-item').forEach(el => el.classList.remove('selected'));
    document.getElementById(`subj-${csId}`).classList.add('selected');
    window._quickSubject = { subjectId, csId, subjectName, color, teacher1 };
    showToast(`Selected: ${subjectName} — click a cell to assign.`, 'success');
}

// Override openSlotModal to use quick subject if selected
const _origOpenSlotModal = openSlotModal;
window.openSlotModal = function(day, period, dayName, periodName) {
    _origOpenSlotModal(day, period, dayName, periodName);
    if (window._quickSubject && !serverSlots[`${day}_${period}`]) {
        const qs = window._quickSubject;
        document.getElementById('mSubject').value = qs.subjectId;
        selectedCsId = qs.csId;
        if (qs.teacher1) document.getElementById('mTeacher').value = qs.teacher1;
        selectColor(qs.color);
    }
};

document.getElementById('slotModal').addEventListener('click', function(e) { if(e.target===this) closeSlotModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') closeSlotModal(); });

function showToast(msg, type='success') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = `toast-notif show ${type}`;
    clearTimeout(window._tt); window._tt = setTimeout(()=>t.classList.remove('show'), 3000);
}
</script>
