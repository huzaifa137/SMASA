<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --brand: #5351e4; --brand-light: #2C29CA;
        --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
        --text-primary: #1e293b; --text-secondary: #475569; --text-muted: #94a3b8;
        --border: #e2e8f0;
    }

    .glass-header {
        background:linear-gradient(135deg,#5351e4 0%,#2C29CA 100%);
        border-radius:28px; padding:1.5rem 2rem; margin-bottom:1.5rem;
        box-shadow:0 20px 40px -12px rgba(83,81,228,0.35); position:relative; overflow:hidden;
    }
    .glass-header::before { content:'';position:absolute;top:-40%;right:-10%;width:250px;height:250px;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);border-radius:50%; }
    .btn-glass {
        background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:white;
        border-radius:10px; padding:0.45rem 1rem; font-weight:600; font-size:0.82rem;
        text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.2s;
    }
    .btn-glass:hover { background:rgba(255,255,255,0.25); color:white; }

    /* Print-ready grid */
    .tt-container { background:white; border-radius:24px; box-shadow:0 4px 24px rgba(0,0,0,0.07); border:1px solid rgba(83,81,228,0.08); overflow:hidden; }
    .tt-meta { padding:1.5rem 2rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
    .tt-meta-info h5 { font-size:1.05rem; font-weight:800; color:var(--text-primary); margin:0 0 0.2rem; }
    .tt-meta-info p  { font-size:0.82rem; color:var(--text-muted); margin:0; }

    .tt-wrapper { overflow-x:auto; }
    .tt-grid {
        display:grid;
        min-width:800px;
        border-collapse:collapse;
    }
    .tt-head-row, .tt-body-row {
        display:grid;
        grid-template-columns:120px repeat({{ count($days) }},1fr);
    }
    .tt-head-cell {
        background:#5351e4; color:white; padding:0.8rem 0.5rem; text-align:center;
        font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;
        border-right:1px solid rgba(255,255,255,0.12);
    }
    .tt-head-cell:last-child { border-right:none; }
    .tt-head-cell.period-col { background:#2C29CA; text-align:left; padding:0.8rem 1rem; }

    .tt-period-cell {
        background:#f8fafc; padding:0.6rem 1rem;
        border-bottom:1px solid var(--border); border-right:1px solid var(--border);
        display:flex; flex-direction:column; justify-content:center; min-height:72px;
    }
    .period-name { font-size:0.75rem; font-weight:700; color:var(--text-primary); }
    .period-time { font-size:0.65rem; color:var(--text-muted); margin-top:1px; }
    .period-type-break .period-name   { color:#059669; }
    .period-type-lunch .period-name   { color:#d97706; }
    .period-type-assembly .period-name{ color:#2563eb; }

    .tt-slot {
        border-bottom:1px solid var(--border); border-right:1px solid var(--border);
        min-height:72px; padding:6px;
    }
    .tt-slot:last-child { border-right:none; }
    .tt-body-row:last-child .tt-slot,
    .tt-body-row:last-child .tt-period-cell { border-bottom:none; }
    .tt-slot.is-break {
        background:repeating-linear-gradient(45deg,#f8fafc,#f8fafc 4px,#f1f5f9 4px,#f1f5f9 8px);
    }

    .slot-chip {
        border-radius:10px; padding:0.4rem 0.55rem; height:100%; min-height:60px;
        display:flex; flex-direction:column; justify-content:center; gap:2px;
        color:white;
    }
    .slot-subject { font-size:0.76rem; font-weight:800; line-height:1.2; }
    .slot-teacher { font-size:0.65rem; opacity:0.85; margin-top:2px; }
    .slot-room    { font-size:0.6rem; opacity:0.7; }

    .empty-cell { width:100%; height:100%; min-height:60px; display:flex; align-items:center; justify-content:center; }
    .empty-dash  { color:var(--border); font-size:1.2rem; }

    .print-btn {
        background:var(--brand); color:white; border:none; border-radius:12px;
        padding:0.6rem 1.4rem; font-size:0.85rem; font-weight:700; cursor:pointer;
        display:inline-flex; align-items:center; gap:0.5rem;
    }
    .print-btn:hover { background:var(--brand-light); }
    .edit-btn {
        background:rgba(83,81,228,0.1); color:var(--brand); border:none; border-radius:12px;
        padding:0.6rem 1.4rem; font-size:0.85rem; font-weight:700; cursor:pointer; text-decoration:none;
        display:inline-flex; align-items:center; gap:0.5rem;
    }
    .edit-btn:hover { background:var(--brand); color:white; }

    .status-pill {
        display:inline-flex; align-items:center; gap:0.3rem;
        padding:0.3rem 0.85rem; border-radius:99px; font-size:0.72rem; font-weight:700;
    }
    .status-active  { background:rgba(16,185,129,0.1); color:#059669; }
    .status-draft   { background:rgba(245,158,11,0.1);  color:#d97706; }
    .status-archived{ background:rgba(100,116,139,0.1); color:#64748b; }

    @media print {
        .btn-glass, .print-btn, .edit-btn, .glass-header, nav, aside { display:none !important; }
        body { background:white !important; }
        .tt-container { box-shadow:none !important; border:none !important; }
        .tt-meta { padding:0.5rem 0 !important; }
    }
</style>
@endsection

@section('page-header')
<div class="glass-header">
    <div class="row align-items-center" style="position:relative;z-index:1;">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('timetable.dashboard') }}" class="btn-glass" style="padding:0.35rem 0.9rem;font-size:0.78rem;">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
            <h1 style="font-size:1.7rem;font-weight:800;color:white;margin-bottom:0.25rem;">
                <i class="fas fa-table me-2"></i> {{ $timetable->name }}
            </h1>
            <p style="color:rgba(255,255,255,0.82);margin:0;font-size:0.88rem;">
                {{ $className }} — {{ $streamName }}
                @if($timetable->term) · {{ $timetable->term }} @endif
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('timetable.edit', $timetable->id) }}" class="btn-glass">
                <i class="fas fa-pen"></i> Edit
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="tt-container">
    <div class="tt-meta">
        <div class="tt-meta-info">
            <h5>{{ $timetable->name }}</h5>
            <p>{{ $className }} &mdash; {{ $streamName }}
                @if($timetable->term) · {{ $timetable->term }} @endif
                · Created {{ $timetable->created_at->format('d M Y') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="status-pill status-{{ $timetable->status }}">
                <i class="fas fa-{{ $timetable->status==='active'?'check-circle':($timetable->status==='draft'?'pen-ruler':'archive') }}"></i>
                {{ ucfirst($timetable->status) }}
            </span>
            <a href="{{ route('timetable.edit', $timetable->id) }}" class="edit-btn">
                <i class="fas fa-pen"></i> Edit Timetable
            </a>
            <button class="print-btn" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="tt-wrapper">
        <div class="tt-grid">
            <!-- Header -->
            <div class="tt-head-row">
                <div class="tt-head-cell period-col"><i class="fas fa-clock me-1"></i> Period</div>
                @foreach($days as $dayNum => $dayName)
                    <div class="tt-head-cell">{{ $dayName }}</div>
                @endforeach
            </div>

            <!-- Body -->
            @foreach($periods as $period)
            @php
                $isLesson  = $period->type === 'lesson';
                $typeClass = 'period-type-' . $period->type;
            @endphp
            <div class="tt-body-row">
                <div class="tt-period-cell {{ $typeClass }}">
                    <div class="period-name">{{ $period->name }}</div>
                    <div class="period-time">
                        {{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} –
                        {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}
                    </div>
                </div>

                @foreach($days as $dayNum => $dayName)
                @php
                    $key  = $dayNum . '_' . $period->id;
                    $slot = $slots[$key] ?? null;
                @endphp
                <div class="tt-slot {{ !$isLesson ? 'is-break' : '' }}">
                    @if($slot && $slot->subject_id)
                    @php
                        $color       = $slot->color ?? '#5351e4';
                        $subjName    = Helper::recordMdname($slot->subject_id);
                        $teacherName = $slot->teacher_id ? Helper::get_teacher_name($slot->teacher_id) : null;
                    @endphp
                    <div class="slot-chip" style="background:{{ $color }};">
                        <div class="slot-subject">{{ $subjName }}</div>
                        @if($teacherName)
                        <div class="slot-teacher"><i class="fas fa-user-tie" style="font-size:0.55rem;"></i> {{ $teacherName }}</div>
                        @endif
                        @if($slot->room)
                        <div class="slot-room"><i class="fas fa-map-marker-alt" style="font-size:0.5rem;"></i> {{ $slot->room }}</div>
                        @endif
                    </div>
                    @elseif($isLesson)
                    <div class="empty-cell"><span class="empty-dash">—</span></div>
                    @else
                    <div class="empty-cell" style="color:var(--text-muted);font-size:0.7rem;">{{ ucfirst($period->type) }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection