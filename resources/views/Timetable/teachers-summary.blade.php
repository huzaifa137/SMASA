<?php use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand: #5351e4;
            --brand-light: #2C29CA;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
        }

        .glass-header {
            background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%);
            border-radius: 28px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 20px 40px -12px rgba(83, 81, 228, 0.35);
            position: relative;
            overflow: hidden;
        }

        .glass-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .summary-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-pill {
            flex: 1;
            min-width: 170px;
            background: white;
            border-radius: 16px;
            padding: 1rem 1.2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(83, 81, 228, 0.08);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .summary-pill i {
            font-size: 1.1rem;
            color: var(--brand);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(83, 81, 228, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .summary-pill-num {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        .summary-pill-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .search-box {
            position: relative;
            max-width: 320px;
            margin-bottom: 1.2rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.4rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.85rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .search-box i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .teacher-card {
            background: white;
            border-radius: 16px;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(83, 81, 228, 0.08);
            overflow: hidden;
        }

        .teacher-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.3rem;
            cursor: pointer;
            flex-wrap: wrap;
        }

        .teacher-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #5351e4, #2C29CA);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .teacher-main {
            flex: 1 1 220px;
            min-width: 0;
        }

        .teacher-name {
            font-weight: 800;
            color: var(--text-primary);
            font-size: 0.92rem;
        }

        .teacher-meta {
            font-size: 0.74rem;
            color: var(--text-muted);
            margin-top: 0.1rem;
        }

        .day-badges {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
        }

        .day-badge {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 800;
            background: #f5f5fb;
            color: var(--text-muted);
        }

        .day-badge.active {
            background: rgba(83, 81, 228, 0.12);
            color: var(--brand-light);
        }

        .load-num {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--brand-light);
            text-align: center;
            min-width: 60px;
        }

        .load-label {
            font-size: 0.63rem;
            color: var(--text-muted);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .chevron {
            color: #b0adec;
            font-size: 0.8rem;
            transition: transform .2s;
        }

        .teacher-card.open .chevron {
            transform: rotate(90deg);
        }

        .teacher-detail {
            display: none;
            border-top: 1px solid var(--border);
            background: #fafbff;
            padding: 1rem 1.3rem;
        }

        .teacher-card.open .teacher-detail {
            display: block;
        }

        .detail-day-block {
            margin-bottom: 0.9rem;
        }

        .detail-day-block:last-child {
            margin-bottom: 0;
        }

        .detail-day-title {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--brand);
            margin-bottom: 0.5rem;
        }

        .detail-slot {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.7rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 0.4rem;
            border: 1px solid var(--border);
        }

        .detail-slot:last-child {
            margin-bottom: 0;
        }

        .detail-slot-time {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--text-muted);
            min-width: 60px;
        }

        .detail-slot-subj {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-primary);
            flex: 1;
        }

        .detail-slot-class {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            opacity: 0.3;
            margin-bottom: 1rem;
            display: block;
        }

        .print-btn {
            background: var(--brand);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.55rem 1.3rem;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .print-btn:hover {
            background: var(--brand-light);
        }

        @media print {

            .search-box,
            .print-btn,
            .glass-header,
            nav,
            aside {
                display: none !important;
            }

            .teacher-detail {
                display: block !important;
            }

            .chevron {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="rpt-hero-card" style="margin-bottom: 1.5rem;margin-top: 2.5rem;">
        <div class="rpt-hero-main" style="margin-bottom: 0.75rem; padding-bottom: 0.75rem;">
            <div class="rpt-hero-top">
                <div class="rpt-hero-left">
                    <div class="rpt-hero-icon-wrapper">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="rpt-hero-info">
                        <div class="rpt-hero-badges" style="margin-bottom: 0.5rem;">
                            <span class="rpt-hero-badge"
                                style="background: rgba(255,255,255,0.12); backdrop-filter: blur(4px); padding: 0.4rem 1rem; border-radius: 99px; font-size: 0.75rem; color: #FFF; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-user-tie me-1" style="color: #818CF8 !important;"></i>
                                <span style="color: #ffffff !important;">Staffing Overview</span>
                            </span>
                        </div>
                        <h4 style="font-size: 1.5rem; font-weight: 800; color: #ffffff; margin-bottom: 0.25rem;">
                            <i class="fas fa-chalkboard-teacher me-3" style="color: rgba(255,255,255,0.6);"></i> Teacher
                            Teaching-Days Summary
                        </h4>
                        <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.95rem; margin: 0;">
                            Weekly workload for every teacher, drawn from active timetables.
                        </p>
                    </div>
                </div>
                <div class="rpt-hero-actions">
                    <a href="{{ route('timetable.dashboard') }}" class="rpt-hero-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
        <div class="rpt-hero-meta">
            <div class="rpt-meta-items">
                <div class="rpt-meta-item rpt-meta-highlight">
                    <i class="fas fa-user-tie" style="color: #818CF8 !important;"></i>
                    <span style="color: #ffffff !important;">Staffing Overview</span>
                </div>
                <div class="rpt-meta-item">
                    <i class="fas fa-chalkboard-teacher" style="color: #818CF8 !important;"></i>
                    <span style="color: #ffffff !important;">Teacher Teaching-Days</span>
                </div>
                <div class="rpt-meta-item">
                    <i class="fas fa-calendar-alt" style="color: #818CF8 !important;"></i>
                    <span style="color: #ffffff !important;">Weekly Workload</span>
                </div>
                <div class="rpt-meta-item" style="color: rgba(255,255,255,0.65);">
                    <i class="fas fa-users" style="color: #818CF8 !important;"></i>
                    <span style="color: #ffffff !important;">All Active Teachers</span>
                </div>
            </div>
        </div>
    </div>

    <style>
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
        }

        .rpt-meta-highlight {
            background: rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.2);
            color: rgba(255, 255, 255, 0.9);
        }

        @media (max-width: 768px) {
            .rpt-hero-card {
                padding: 1.25rem;
            }

            .rpt-hero-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .rpt-hero-left {
                flex-direction: column;
                align-items: flex-start;
            }

            .rpt-hero-info h4 {
                font-size: 1.2rem;
            }

            .rpt-hero-info p {
                font-size: 0.85rem;
            }

            .rpt-meta-items {
                grid-template-columns: 1fr 1fr;
            }

            .rpt-hero-badges {
                flex-wrap: wrap;
            }

            .rpt-hero-actions {
                width: 100%;
            }

            .rpt-hero-btn-secondary {
                flex: 1;
                justify-content: center;
                font-size: 0.75rem;
                padding: 0.5rem 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .rpt-hero-left {
                width: 100%;
            }

            .rpt-hero-icon-wrapper {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
            }

            .rpt-meta-items {
                grid-template-columns: 1fr;
            }

            .rpt-hero-badge {
                font-size: 0.65rem !important;
                padding: 0.3rem 0.7rem !important;
            }

            .rpt-hero-actions {
                flex-direction: column;
            }

            .rpt-hero-btn-secondary {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')

    <div class="summary-strip">
        <div class="summary-pill">
            <i class="fas fa-user-check"></i>
            <div>
                <div class="summary-pill-num">{{ $summary->count() }}</div>
                <div class="summary-pill-label">Teachers scheduled</div>
            </div>
        </div>
        <div class="summary-pill">
            <i class="fas fa-calculator"></i>
            <div>
                <div class="summary-pill-num">{{ $schoolAverage }}</div>
                <div class="summary-pill-label">Avg. periods / week</div>
            </div>
        </div>
        <div class="summary-pill">
            <i class="fas fa-user-slash"></i>
            <div>
                <div class="summary-pill-num">{{ $unscheduledCount }}</div>
                <div class="summary-pill-label">Not yet on any timetable</div>
            </div>
        </div>
    </div>

    @if($summary->isEmpty())
        <div style="background:white;border-radius:20px;">
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h5 style="color:var(--text-primary);font-weight:700;">No teaching data yet</h5>
                <p>Once class timetables are active and have teachers assigned to slots, they'll show up here.</p>
            </div>
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:0.75rem;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="teacherSearch" placeholder="Search teacher name..." onkeyup="filterTeachers()">
            </div>
            <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>

        <div id="teacherList">
            @foreach($summary as $row)
                @php
                    $teacher = $row->teacher;
                    $initials = strtoupper(substr($teacher->firstname ?? 'T', 0, 1) . substr($teacher->surname ?? '', 0, 1));
                @endphp
                <div class="teacher-card"
                    data-name="{{ strtolower(($teacher->firstname ?? '') . ' ' . ($teacher->surname ?? '')) }}">
                    <div class="teacher-row" onclick="this.closest('.teacher-card').classList.toggle('open')">
                        <div class="teacher-avatar">{{ $initials }}</div>
                        <div class="teacher-main">
                            <div class="teacher-name">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                            <div class="teacher-meta">{{ $row->classes_count }} class{{ $row->classes_count == 1 ? '' : 'es' }}
                                &middot; {{ $row->subjects_count }} subject{{ $row->subjects_count == 1 ? '' : 's' }}</div>
                        </div>
                        <div class="day-badges">
                            @foreach($days as $dayNum => $dayLabel)
                                <div class="day-badge {{ $row->teaching_days->contains($dayNum) ? 'active' : '' }}"
                                    title="{{ $row->teaching_days->contains($dayNum) ? 'Teaching' : 'Free' }}">{{ $dayLabel }}</div>
                            @endforeach
                        </div>
                        <div>
                            <div class="load-num">{{ $row->total_periods }}</div>
                            <div class="load-label">periods/wk</div>
                        </div>
                        <i class="fas fa-chevron-right chevron"></i>
                    </div>

                    <div class="teacher-detail">
                        @foreach($row->by_day as $dayNum => $daySlots)
                            @if($daySlots->count() > 0)
                                <div class="detail-day-block">
                                    <div class="detail-day-title">{{ \App\Models\Timetable::dayName($dayNum) }} ({{ $daySlots->count() }}
                                        period{{ $daySlots->count() == 1 ? '' : 's' }})</div>
                                    @foreach($daySlots as $slot)
                                        <div class="detail-slot">
                                            <div class="detail-slot-time">
                                                {{ $slot->period ? \Carbon\Carbon::parse($slot->period->start_time)->format('H:i') : '' }}</div>
                                            <div class="detail-slot-subj">{{ $slot->subject_name }}</div>
                                            <div class="detail-slot-class">{{ $slot->class_name }}
                                                @if($slot->stream_name && $slot->stream_name !== 'NO_STREAM') — {{ $slot->stream_name }} @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        </div>
        </div>
    @endif

    <script>
        function filterTeachers() {
            const q = document.getElementById('teacherSearch').value.toLowerCase().trim();
            document.querySelectorAll('#teacherList .teacher-card').forEach(card => {
                card.style.display = card.dataset.name.includes(q) ? '' : 'none';
            });
        }
    </script>
@endsection