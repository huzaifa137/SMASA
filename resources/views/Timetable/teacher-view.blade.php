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
        --border: #e2e8f0;
    }
    
    .glass-header {
        background:linear-gradient(135deg,#5351e4 0%,#2C29CA 100%);
        border-radius:28px; padding:2rem 2.5rem; margin-bottom:1.5rem;
        box-shadow:0 20px 40px -12px rgba(83,81,228,0.35); position:relative; overflow:hidden;
    }
    .glass-header::before { content:'';position:absolute;top:-40%;right:-10%;width:260px;height:260px;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);border-radius:50%; }
    .btn-glass {
        background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:white;
        border-radius:10px; padding:0.45rem 1rem; font-weight:600; font-size:0.82rem;
        text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.2s;
    }
    .btn-glass:hover { background:rgba(255,255,255,0.25); color:white; }

    /* Stats row */
    .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:1rem; margin-bottom:1.5rem; }
    .stat-card {
        background:white; border-radius:18px; padding:1.2rem 1.4rem;
        box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid rgba(83,81,228,0.08);
        display:flex; align-items:center; gap:1rem;
    }
    .stat-icon { width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
    .stat-num  { font-size:1.6rem;font-weight:800;color:var(--text-primary);line-height:1; }
    .stat-label{ font-size:0.75rem;color:var(--text-muted);font-weight:600; }

    /* Today highlight */
    .today-card {
        background:white; border-radius:20px; padding:1.4rem 1.6rem; margin-bottom:1.5rem;
        box-shadow:0 4px 20px rgba(83,81,228,0.1); border:2px solid rgba(83,81,228,0.15);
    }
    .today-title { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--brand); margin-bottom:0.8rem; }
    .today-slots { display:flex; flex-direction:column; gap:0.5rem; }
    .today-slot {
        display:flex; align-items:center; gap:0.8rem; padding:0.6rem 0.8rem;
        border-radius:12px; border-left:4px solid var(--brand);
        background:#fafbff;
    }
    .today-slot .slot-time { font-size:0.72rem; color:var(--text-muted); font-weight:600; min-width:80px; }
    .today-slot .slot-info { flex:1; }
    .today-slot .slot-subj { font-size:0.85rem; font-weight:800; color:var(--text-primary); }
    .today-slot .slot-class { font-size:0.72rem; color:var(--text-muted); }

    /* Full grid */
    .tt-wrapper { overflow-x:auto; }
    .tt-container { background:white; border-radius:20px; box-shadow:0 4px 24px rgba(0,0,0,0.07); border:1px solid rgba(83,81,228,0.08); overflow:hidden; }
    .tt-head { background:#5351e4; display:grid; grid-template-columns:120px repeat({{ count($days) }},1fr); }
    .tt-head-cell {
        color:white; padding:0.85rem 0.5rem; text-align:center;
        font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em;
        border-right:1px solid rgba(255,255,255,0.12);
    }
    .tt-head-cell:first-child { text-align:left; padding:0.85rem 1rem; background:#2C29CA; }
    .tt-head-cell:last-child  { border-right:none; }
    .tt-head-cell.today-col   { background:rgba(245,158,11,0.7); }

    .tt-row { display:grid; grid-template-columns:120px repeat({{ count($days) }},1fr); border-bottom:1px solid var(--border); }
    .tt-row:last-child { border-bottom:none; }

    .period-label {
        background:#f8fafc; padding:0.6rem 1rem; border-right:1px solid var(--border);
        display:flex; flex-direction:column; justify-content:center; min-height:75px;
    }
    .plabel { font-size:0.75rem; font-weight:700; color:var(--text-primary); }
    .ptime  { font-size:0.65rem; color:var(--text-muted); }
    .period-label.break-lbl .plabel { color:#059669; }
    .period-label.lunch-lbl .plabel { color:#d97706; }

    .slot-cell {
        border-right:1px solid var(--border); padding:5px; min-height:75px;
        display:flex; align-items:stretch;
    }
    .slot-cell:last-child { border-right:none; }
    .slot-cell.today-cell { background:rgba(83,81,228,0.03); }
    .slot-cell.is-break   { background:repeating-linear-gradient(45deg,#f8fafc,#f8fafc 4px,#f1f5f9 4px,#f1f5f9 8px); }

    .my-slot-chip {
        border-radius:10px; padding:0.45rem 0.6rem; flex:1;
        display:flex; flex-direction:column; justify-content:center; gap:2px;
        color:white; min-height:65px;
    }
    .my-slot-subj  { font-size:0.76rem; font-weight:800; line-height:1.2; }
    .my-slot-class { font-size:0.65rem; opacity:0.85; }
    .my-slot-room  { font-size:0.6rem;  opacity:0.7; }
    .empty-slot    { flex:1; display:flex; align-items:center; justify-content:center; color:var(--border); font-size:1rem; }

    .print-btn { background:var(--brand);color:white;border:none;border-radius:12px;padding:0.55rem 1.3rem;font-size:0.82rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:0.4rem; }
    .print-btn:hover { background:var(--brand-light); }

    @media print {
        .btn-glass,.print-btn,.glass-header,nav,aside { display:none!important; }
        body { background:white!important; }
        .tt-container { box-shadow:none!important; border:none!important; }
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
            <h1 style="font-size:1.9rem;font-weight:800;color:white;margin-bottom:0.3rem;">
                <i class="fas fa-calendar-week me-2"></i> My Schedule
            </h1>
            <p style="color:rgba(255,255,255,0.82);margin:0;font-size:0.92rem;">
                Your personal teaching timetable for active sessions
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <button class="btn-glass" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')
@php
    $todayNum = \Carbon\Carbon::today()->dayOfWeekIso; // 1=Mon
    $todaySlots = $slots->filter(fn($s) => $s->day_of_week == $todayNum)->sortBy('period.sort_order');
@endphp

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(83,81,228,0.1);color:var(--brand);">
            <i class="fas fa-book-open"></i>
        </div>
        <div>
            <div class="stat-num">{{ $weeklyHours }}</div>
            <div class="stat-label">Lessons / Week</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:var(--success);">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div>
            <div class="stat-num">{{ $todaySlots->count() }}</div>
            <div class="stat-label">Today's Classes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,0.1);color:var(--info);">
            <i class="fas fa-layer-group"></i>
        </div>
        <div>
            <div class="stat-num">{{ $slots->pluck('timetable.class_id')->unique()->count() }}</div>
            <div class="stat-label">Classes Taught</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:var(--warning);">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div>
            <div class="stat-num">{{ count($days) }}</div>
            <div class="stat-label">School Days</div>
        </div>
    </div>
</div>

<!-- Today's Schedule -->
@if($todaySlots->isNotEmpty())
<div class="today-card">
    <div class="today-title"><i class="fas fa-sun me-1"></i> Today — {{ \Carbon\Carbon::today()->format('l, d F Y') }}</div>
    <div class="today-slots">
        @foreach($todaySlots as $slot)
        <div class="today-slot" style="border-left-color:{{ $slot->color ?? '#5351e4' }};">
            <div class="slot-time">
                {{ \Carbon\Carbon::parse($slot->period->start_time)->format('H:i') }} –
                {{ \Carbon\Carbon::parse($slot->period->end_time)->format('H:i') }}
            </div>
            <div class="slot-info">
                <div class="slot-subj">{{ $slot->subject_name }}</div>
                <div class="slot-class">{{ $slot->class_name }} — {{ $slot->stream_name }}
                    @if($slot->room) · {{ $slot->room }} @endif
                </div>
            </div>
            <span style="background:rgba(83,81,228,0.1);color:var(--brand);border-radius:8px;padding:0.25rem 0.7rem;font-size:0.72rem;font-weight:700;">
                {{ $slot->period->name }}
            </span>
        </div>
        @endforeach
    </div>
</div>
@else
<div style="background:white;border-radius:20px;padding:1.4rem 1.6rem;margin-bottom:1.5rem;text-align:center;color:var(--text-muted);font-size:0.875rem;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
    <i class="fas fa-coffee" style="font-size:1.4rem;margin-bottom:0.5rem;display:block;"></i>
    No classes scheduled for today ({{ \Carbon\Carbon::today()->format('l') }}).
</div>
@endif

<!-- Full Weekly Grid -->
<div class="tt-wrapper">
    <div class="tt-container">
        <div class="tt-head">
            <div class="tt-head-cell"><i class="fas fa-clock me-1"></i> Period</div>
            @foreach($days as $dayNum => $dayName)
                <div class="tt-head-cell {{ $dayNum == $todayNum ? 'today-col' : '' }}">
                    {{ $dayName }}
                    @if($dayNum == $todayNum)
                    <div style="font-size:0.6rem;font-weight:500;opacity:0.85;">Today</div>
                    @endif
                </div>
            @endforeach
        </div>

        @foreach($periods as $period)
        @php
            $isLesson  = $period->type === 'lesson';
            $lblClass  = $period->type === 'break' ? 'break-lbl' : ($period->type === 'lunch' ? 'lunch-lbl' : '');
        @endphp
        <div class="tt-row">
            <div class="period-label {{ $lblClass }}">
                <div class="plabel">{{ $period->name }}</div>
                <div class="ptime">{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</div>
            </div>

            @foreach($days as $dayNum => $dayName)
            @php
                $key  = $dayNum . '_' . $period->id;
                $slot = $slots[$key] ?? null;
            @endphp
            <div class="slot-cell {{ $dayNum == $todayNum ? 'today-cell' : '' }} {{ !$isLesson ? 'is-break' : '' }}">
                @if($slot && $slot->subject_id)
                <div class="my-slot-chip" style="background:{{ $slot->color ?? '#5351e4' }};">
                    <div class="my-slot-subj">{{ $slot->subject_name }}</div>
                    <div class="my-slot-class">{{ $slot->class_name }} — {{ $slot->stream_name }}</div>
                    @if($slot->room)
                    <div class="my-slot-room"><i class="fas fa-map-marker-alt" style="font-size:0.5rem;"></i> {{ $slot->room }}</div>
                    @endif
                </div>
                @elseif(!$isLesson)
                <div class="empty-slot" style="font-size:0.68rem;color:var(--text-muted);">{{ ucfirst($period->type) }}</div>
                @else
                <div class="empty-slot">—</div>
                @endif
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endsection