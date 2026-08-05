<?php use App\Http\Controllers\Helper; use App\Helpers\PermissionHelper; ?>
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

    /* RPT Hero Card Styles */
    .rpt-hero-card {
        background: linear-gradient(135deg, #000000 0%, #070189 100%);
        border-radius: 20px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 24px rgba(7, 1, 137, 0.3);
    }

    .rpt-hero-main {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .rpt-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .rpt-hero-left {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
    }

    .rpt-hero-icon-wrapper {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        margin-top: 0.25rem;
    }

    .rpt-hero-info {
        flex: 1;
    }

    .rpt-hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .rpt-hero-badge {
        display: inline-flex;
        align-items: center;
        backdrop-filter: blur(4px);
        padding: 0.4rem 1rem;
        border-radius: 99px;
        font-size: 0.75rem;
        color: #FFF;
    }

    .rpt-hero-info h4 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 0.25rem;
    }

    .rpt-hero-info p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
        margin: 0;
    }

    .rpt-hero-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .rpt-hero-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1.1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        backdrop-filter: blur(10px);
        text-align: center;
        white-space: nowrap;
    }

    .rpt-hero-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        text-decoration: none;
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    .rpt-hero-meta {
        padding-top: 0.25rem;
    }

    .rpt-meta-items {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.5rem 0.75rem;
        width: 100%;
    }

    .rpt-meta-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        color: rgba(255, 255, 255, 0.65);
        font-size: 0.78rem;
        padding: 0.35rem 0.75rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 99px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        text-align: center;
        transition: all 0.3s ease;
    }

    .rpt-meta-item:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-1px);
    }

    .rpt-meta-item i {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.4);
    }

    .rpt-meta-highlight {
        background: rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.2);
        color: rgba(255, 255, 255, 0.9);
    }

    .rpt-meta-highlight i {
        color: #818cf8;
    }

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
        display:inline-flex;
        align-items:center;
        gap:0.5rem;
        padding:0.6rem 1.4rem;
        border-radius:12px;
        font-size:0.85rem;
        font-weight:700;
        min-height:44px;
    }
    .status-active  { background:rgba(16,185,129,0.1); color:#059669; }
    .status-draft   { background:rgba(245,158,11,0.1);  color:#d97706; }
    .status-archived{ background:rgba(100,116,139,0.1); color:#64748b; }

    @media (max-width: 768px) {
        .rpt-hero-card { padding: 1.25rem; }
        .rpt-hero-top { flex-direction: column; align-items: flex-start; }
        .rpt-hero-left { flex-direction: column; align-items: flex-start; }
        .rpt-hero-info h4 { font-size: 1.2rem; }
        .rpt-hero-info p { font-size: 0.85rem; }
        .rpt-meta-items { grid-template-columns: 1fr 1fr; }
        .rpt-hero-badges { flex-wrap: wrap; }
        .rpt-hero-actions { width: 100%; }
        .rpt-hero-btn-secondary { flex: 1; justify-content: center; }
    }

    @media (max-width: 480px) {
        .rpt-hero-left { width: 100%; }
        .rpt-hero-icon-wrapper { width: 48px; height: 48px; font-size: 1.2rem; }
        .rpt-meta-items { grid-template-columns: 1fr; }
        .rpt-hero-badge { font-size: 0.65rem !important; padding: 0.3rem 0.7rem !important; }
    }

    @media print {
        .rpt-hero-card, .btn-glass, .print-btn, .edit-btn, nav, aside { display:none !important; }
        body { background:white !important; }
        .tt-container { box-shadow:none !important; border:none !important; }
        .tt-meta { padding:0.5rem 0 !important; }
    }
</style>
@endsection

@section('content')
<div class="side-app" style="padding: 1.5rem;">

    {{-- New Dark Gradient Header --}}
    <div class="rpt-hero-card">
        <div class="rpt-hero-main">
            <div class="rpt-hero-top">
                <div class="rpt-hero-left">
                    <div class="rpt-hero-icon-wrapper">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="rpt-hero-info">
                        <div class="rpt-hero-badges">
                            <span class="rpt-hero-badge" style="background: rgba(255,255,255,0.15);">
                                <i class="fas fa-calendar-alt me-2"></i> Timetable Details
                            </span>
                            @if($timetable->status === 'draft')
                            <span class="rpt-hero-badge" style="background: rgba(245,158,11,0.25); color: #fbbf24; border: 1px solid rgba(245,158,11,0.2);">
                                <i class="fas fa-pen-ruler me-1"></i> DRAFT
                            </span>
                            @else
                            <span class="rpt-hero-badge" style="background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.2);">
                                <i class="fas fa-check-circle me-1"></i> ACTIVE
                            </span>
                            @endif
                        </div>
                        <h4>{{ $timetable->name }}</h4>
                        <p>
                            <i class="fas fa-school me-2" style="color: rgba(255,255,255,0.4);"></i>
                            {{ $className }} — {{ $streamName }}
                            @if($timetable->term) · <span style="color: rgba(255,255,255,0.5);">{{ $timetable->term }}</span> @endif
                        </p>
                    </div>
                </div>
                <div class="rpt-hero-actions">
                    <a href="{{ route('timetable.dashboard') }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                    @if(PermissionHelper::canFeature('edit_timetable'))
                    <a href="{{ route('timetable.edit', $timetable->id) }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-pen"></i> Edit Timetable
                    </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="rpt-hero-meta">
            <div class="rpt-meta-items">
                <div class="rpt-meta-item rpt-meta-highlight text-white">
                    <i class="fas fa-calendar-alt text-white"></i>
                    <span>Timetable Details</span>
                </div>
                <div class="rpt-meta-item text-white">
                    <i class="fas fa-tag text-white"></i>
                    <span>{{ $timetable->name }}</span>
                </div>
                <div class="rpt-meta-item text-white">
                    <i class="fas fa-school text-white"></i>
                    <span>{{ $className }} — {{ $streamName }}</span>
                </div>
                @if($timetable->term)
                <div class="rpt-meta-item text-white">
                    <i class="fas fa-calendar text-white"></i>
                    <span>{{ $timetable->term }}</span>
                </div>
                @endif
                <div class="rpt-meta-item" style="{{ $timetable->status === 'draft' ? 'color: #fbbf24;' : 'color: #34d399;' }}">
                    <i class="fas {{ $timetable->status === 'draft' ? 'fa-pen-ruler' : 'fa-check-circle' }}"></i>
                    <span>{{ strtoupper($timetable->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Timetable Content --}}
    <div class="tt-container">
<div class="tt-meta" style="
    background: linear-gradient(135deg, #1a1a3e 0%, #2C29CA 50%, #070189 100%);
    border-bottom: 3px solid rgba(255, 255, 255, 0.15);
    border-radius: 24px 24px 0 0;
    padding: 1.75rem 2rem;
    position: relative;
    overflow: hidden;
">
    <!-- Decorative overlay -->
    <div style="position: absolute; top: -50%; right: -20%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    <div style="position: absolute; bottom: -30%; left: -10%; width: 150px; height: 150px; background: radial-gradient(circle, rgba(102, 126, 234, 0.08) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    
    <div class="tt-meta-info" style="position: relative; z-index: 1;">
        <h5 style="
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        ">
            <i class="fas fa-calendar-check" style="color: #818cf8; font-size: 1.1rem;"></i>
            {{ $timetable->name }}
        </h5>
        <p style="
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        ">
            <span><i class="fas fa-school" style="color: #818cf8; width: 14px;"></i> {{ $className }} &mdash; {{ $streamName }}</span>
            @if($timetable->term)
            <span><i class="fas fa-calendar-alt" style="color: #818cf8; width: 14px;"></i> {{ $timetable->term }}</span>
            @endif
            <span><i class="fas fa-clock" style="color: #818cf8; width: 14px;"></i> Created {{ $timetable->created_at->format('d M Y') }}</span>
        </p>
    </div>
    <div class="d-flex align-items-center flex-wrap" style="gap: 0.75rem; position: relative; z-index: 1;">
        <span class="status-pill status-{{ $timetable->status }}" style="
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.4rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            min-height: 44px;
            backdrop-filter: blur(10px);
            {{ $timetable->status === 'active' ? 'background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);' : '' }}
            {{ $timetable->status === 'draft' ? 'background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);' : '' }}
            {{ $timetable->status === 'archived' ? 'background: rgba(148, 163, 184, 0.2); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3);' : '' }}
        ">
            <i class="fas fa-{{ $timetable->status==='active'?'check-circle':($timetable->status==='draft'?'pen-ruler':'archive') }}" 
               style="{{ $timetable->status === 'active' ? 'color: #34d399;' : ($timetable->status === 'draft' ? 'color: #fbbf24;' : 'color: #94a3b8;') }}"></i>
            {{ ucfirst($timetable->status) }}
        </span>

        @if(PermissionHelper::canFeature('edit_timetable'))
        <a href="{{ route('timetable.edit', $timetable->id) }}" class="edit-btn" style="
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.4rem;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
        "
        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(102, 126, 234, 0.4)';"
        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(102, 126, 234, 0.3)';">
            <i class="fas fa-pen"></i> Edit Timetable
        </a>
        @endif

        <button class="edit-btn" id="orientationToggle" onclick="toggleOrientation()" style="
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 0.6rem 1.4rem;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        "
        onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-2px)'; this.style.borderColor='rgba(255, 255, 255, 0.3)';"
        onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.transform='translateY(0)'; this.style.borderColor='rgba(255, 255, 255, 0.15)';">
            <i class="fas fa-exchange-alt"></i> <span id="orientationLabel">Swap to Days Vertical</span>
        </button>

        <button class="print-btn" onclick="window.print()" style="
            background: rgba(255, 255, 255, 0.12);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 0.6rem 1.4rem;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        "
        onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(-2px)'; this.style.borderColor='rgba(255, 255, 255, 0.35)';"
        onmouseout="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.transform='translateY(0)'; this.style.borderColor='rgba(255, 255, 255, 0.2)';">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

        <div class="tt-wrapper">
            <div class="tt-grid" id="gridPeriodsVertical">
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

            {{-- Transposed grid: days run down the left column, periods run across the top --}}
            <div class="tt-grid" id="gridDaysVertical" style="display:none;">
                <div class="tt-head-row" style="grid-template-columns:120px repeat({{ count($periods) }},1fr);">
                    <div class="tt-head-cell period-col"><i class="fas fa-calendar-day me-1"></i> Day</div>
                    @foreach($periods as $period)
                        <div class="tt-head-cell">{{ $period->name }}</div>
                    @endforeach
                </div>

                @foreach($days as $dayNum => $dayName)
                <div class="tt-body-row" style="grid-template-columns:120px repeat({{ count($periods) }},1fr);">
                    <div class="tt-period-cell">
                        <div class="period-name">{{ $dayName }}</div>
                    </div>

                    @foreach($periods as $period)
                    @php
                        $isLesson  = $period->type === 'lesson';
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
</div>

<script>
    function toggleOrientation() {
        const periodsGrid = document.getElementById('gridPeriodsVertical');
        const daysGrid = document.getElementById('gridDaysVertical');
        const label = document.getElementById('orientationLabel');
        const isPeriodsVertical = periodsGrid.style.display !== 'none';

        periodsGrid.style.display = isPeriodsVertical ? 'none' : 'grid';
        daysGrid.style.display = isPeriodsVertical ? 'grid' : 'none';
        label.textContent = isPeriodsVertical ? 'Swap to Periods Vertical' : 'Swap to Days Vertical';

        localStorage.setItem('ttOrientation', isPeriodsVertical ? 'days_vertical' : 'periods_vertical');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (localStorage.getItem('ttOrientation') === 'days_vertical') {
            toggleOrientation();
        }
    });
</script>
@endsection