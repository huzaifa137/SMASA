<?php
use App\Http\Controllers\Helper;
?>
{{-- resources/views/Examination/passslips/index.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <style>
        /* ── Design tokens ── */
        :root {
            --brand: #2C29CA;
            --brand-mid: #5351e4;
            --brand-light: #7c7aec;
            --brand-pale: #ede9ff;
            --brand-ultra: #f5f4ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius-lg: 1.25rem;
            --radius-md: .875rem;
            --radius-sm: .5rem;
            --shadow-card: 0 4px 24px rgba(44, 41, 202, .10);
        }

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* ── Hero ── */
        .ps-hero {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 55%, #7c7aec 100%);
            border-radius: 0 0 2rem 2rem;
            padding: 2rem 2rem 3.5rem;
            margin-bottom: -1.5rem;
            position: relative;
            overflow: hidden;
        }

        .ps-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(255, 255, 255, .12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .ps-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(108, 63, 197, .18) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* ── Section cards ── */
        .ps-section-card {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(44, 41, 202, .08);
            overflow: hidden;
            transition: box-shadow .2s;
        }

        .ps-section-card:hover {
            box-shadow: 0 8px 36px rgba(44, 41, 202, .15);
        }

        .ps-section-header {
            background: var(--brand-ultra);
            border-bottom: 1px solid var(--brand-pale);
            padding: 1.1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .ps-section-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: var(--brand-pale);
            color: var(--brand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* ── Class-stream tiles ── */
        .class-tile {
            border: 2px solid var(--brand-pale);
            border-radius: var(--radius-md);
            padding: 1.1rem 1.25rem;
            cursor: pointer;
            transition: all .18s;
            background: #fff;
            text-align: left;
            width: 100%;
            display: block;
        }

        .class-tile:hover,
        .class-tile.selected {
            border-color: var(--brand);
            background: var(--brand-ultra);
            box-shadow: 0 4px 16px rgba(44, 41, 202, .14);
        }

        .class-tile.selected {
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .18);
        }

        .class-tile-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: .6rem;
        }

        /* ── Student search box ── */
        .student-search-wrap {
            position: relative;
        }

        .student-search-wrap .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: .9rem;
        }

        #studentSearch {
            border: 2px solid var(--brand-pale);
            border-radius: var(--radius-md);
            padding: .7rem 1rem .7rem 2.5rem;
            font-size: .9rem;
            width: 100%;
            transition: border-color .15s;
        }

        #studentSearch:focus {
            border-color: var(--brand-mid);
            box-shadow: 0 0 0 3px rgba(83, 81, 228, .12);
            outline: none;
        }

        /* ── Student list ── */
        .student-list {
            max-height: 360px;
            overflow-y: auto;
        }

        .student-row {
            display: flex;
            align-items: center;
            padding: .65rem 1rem;
            border-bottom: 1px solid #f3f2ff;
            cursor: pointer;
            transition: background .12s;
            text-decoration: none;
            color: inherit;
        }

        .student-row:hover {
            background: var(--brand-ultra);
        }

        .student-row:last-child {
            border-bottom: none;
        }

        .student-avatar-sm {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), var(--brand-light));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            font-weight: 700;
            flex-shrink: 0;
            margin-right: .85rem;
        }

        /* ── Print scope selectors ── */
        .print-btn {
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            padding: .75rem 1.5rem;
            font-weight: 700;
            font-size: .88rem;
            display: flex;
            align-items: center;
            gap: .55rem;
            transition: all .18s;
            text-decoration: none;
        }

        .print-btn:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 41, 202, .3);
        }

        .print-btn.outline {
            background: transparent;
            border: 2px solid var(--brand);
            color: var(--brand);
        }

        .print-btn.outline:hover {
            background: var(--brand-ultra);
            color: var(--brand);
            box-shadow: 0 4px 14px rgba(44, 41, 202, .12);
        }

        /* ── Status pill ── */
        .status-pill {
            padding: .4rem 1rem;
            border-radius: 99px;
            font-size: .8rem;
            font-weight: 700;
            display: inline-block;
        }

        .status-closed {
            background: #fde8e8;
            color: #c0392b;
        }

        .status-results_released {
            background: #d4f5e2;
            color: #1a7a4a;
        }

        .status-marks_entry {
            background: #fff3cd;
            color: #856404;
        }

        .status-active {
            background: #cfe2ff;
            color: #0a4191;
        }

        /* ── Meta pills ── */
        .meta-pill {
            background: rgba(255, 255, 255, .15);
            border-radius: .75rem;
            padding: .55rem 1rem;
            font-size: .88rem;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            backdrop-filter: blur(10px);
            width: 100%;
        }

        /* ── Loader overlay ── */
        #loadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(44, 41, 202, .12);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        #loadingOverlay.active {
            display: flex;
        }

        .spinner-ring {
            width: 56px;
            height: 56px;
            border: 5px solid #fff;
            border-top-color: var(--brand);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media print {
            body {
                display: none;
            }
        }

        /* Enhanced Class Filter Chips */
        .class-filter-chip {
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            border: 2px solid var(--brand-pale);
            background: white;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .class-filter-chip i {
            font-size: 0.75rem;
        }

        .class-filter-chip:hover {
            border-color: var(--brand-light);
            background: var(--brand-ultra);
            transform: translateY(-1px);
        }

        .class-filter-chip.active {
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            border-color: var(--brand);
            color: white;
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.25);
        }

        /* Enhanced Search Box */
        .student-search-wrap {
            position: relative;
        }

        .student-search-wrap .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
            pointer-events: none;
            z-index: 1;
        }

        #studentSearch {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border: 2px solid var(--brand-pale);
            border-radius: 1rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: white;
        }

        #studentSearch:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(44, 41, 202, 0.1);
        }

        .search-stats {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.75rem;
            color: #9ca3af;
            background: var(--brand-ultra);
            padding: 0.25rem 0.6rem;
            border-radius: 1rem;
            font-weight: 600;
        }

        /* Student List Container */
        .student-list-container {
            border: 2px solid var(--brand-pale);
            border-radius: 1rem;
            overflow: hidden;
            background: white;
        }

        .student-list-header {
            background: linear-gradient(135deg, var(--brand-ultra), white);
            padding: 0.9rem 1.25rem;
            border-bottom: 2px solid var(--brand-pale);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--brand);
        }

        .student-list-header i {
            color: var(--brand);
        }

        .student-count-badge {
            background: var(--brand);
            color: white;
            padding: 0.25rem 0.7rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .student-list {
            max-height: 480px;
            overflow-y: auto;
        }

        /* Student Cards */
        .student-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            background: white;
        }

        .student-card:hover {
            background: linear-gradient(90deg, var(--brand-ultra), white);
            transform: translateX(4px);
        }

        .student-card:last-child {
            border-bottom: none;
        }

        .student-card-avatar {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .student-card:hover .student-card-avatar {
            transform: scale(1.05);
        }

        .student-card-info {
            flex: 1;
            min-width: 0;
        }

        .student-card-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e1b4b;
            margin-bottom: 0.3rem;
        }

        .other-names {
            font-weight: 400;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .student-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .meta-tag {
            font-size: 0.7rem;
            color: #6b7280;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .meta-tag i {
            font-size: 0.65rem;
            color: var(--brand-light);
        }

        .student-card-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            background: var(--brand-ultra);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .student-card-action i {
            color: var(--brand);
            font-size: 1rem;
        }

        .student-card-action span {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--brand);
        }

        .student-card:hover .student-card-action {
            background: var(--brand);
        }

        .student-card:hover .student-card-action i,
        .student-card:hover .student-card-action span {
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h4 {
            font-size: 1rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.85rem;
            margin: 0;
        }

        /* Scrollbar Styling */
        .student-list::-webkit-scrollbar {
            width: 6px;
        }

        .student-list::-webkit-scrollbar-track {
            background: var(--brand-ultra);
        }

        .student-list::-webkit-scrollbar-thumb {
            background: var(--brand-light);
            border-radius: 3px;
        }

        .student-list::-webkit-scrollbar-thumb:hover {
            background: var(--brand);
        }

        /* Enhanced Class Tile Styles */
        .class-tile {
            border: 2px solid var(--brand-pale);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .class-tile::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(44, 41, 202, 0.04), transparent);
            transition: left 0.5s ease;
            pointer-events: none;
        }

        .class-tile:hover::before {
            left: 100%;
        }

        .class-tile:hover,
        .class-tile.selected {
            border-color: var(--brand);
            background: linear-gradient(135deg, #fff, var(--brand-ultra));
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(44, 41, 202, 0.12);
        }

        .class-tile.selected {
            box-shadow: 0 0 0 3px rgba(44, 41, 202, 0.2);
        }

        .class-tile:active {
            transform: translateY(0);
        }

        .class-tile-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .class-tile:hover .class-tile-icon {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.2);
        }

        .class-tile .badge {
            transition: all 0.2s ease;
        }

        .class-tile:hover .badge {
            background: var(--brand) !important;
            color: white !important;
            transform: scale(1.05);
        }

        /* Enhanced Stats Cards */
        .quick-stat {
            transition: transform 0.2s ease;
        }

        .quick-stat:hover {
            transform: translateY(-2px);
        }

        /* Info Banner Animation */
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .ps-section-card .d-flex.align-items-start.gap-2 {
            animation: slideInRight 0.3s ease;
        }

        /* Enhanced Class Tile Styles - Cleaner Design */
        .class-tile {
            border: 2px solid var(--brand-pale);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .class-tile:hover {
            border-color: var(--brand);
            background: linear-gradient(135deg, #fff, var(--brand-ultra));
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(44, 41, 202, 0.12);
        }

        .class-tile.selected {
            border-color: var(--brand);
            background: var(--brand-ultra);
            box-shadow: 0 0 0 3px rgba(44, 41, 202, 0.2);
        }

        .class-tile:active {
            transform: translateY(0);
        }

        /* Clean Icon Styling */
        .class-tile-icon {
            transition: all 0.2s ease;
        }

        .class-tile:hover .class-tile-icon {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(44, 41, 202, 0.2);
        }

        /* Badge Styling */
        .class-tile .badge {
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .class-tile:hover .badge {
            background: var(--brand) !important;
            color: white !important;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .class-tile-icon {
                width: 45px !important;
                height: 45px !important;
            }

            .class-tile-icon i {
                font-size: 1.2rem !important;
            }

            .class-tile .fw-bold {
                font-size: 0.9rem !important;
            }

            .class-tile .badge {
                font-size: 0.7rem !important;
                padding: 0.2rem 0.6rem !important;
            }
        }

        #loadingText {
            color: #2C29CA !important;
            font-weight: 600;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
@endsection

@section('content')
    <div class="side-app">

        {{-- Loading Overlay --}}
        <div id="loadingOverlay">
            <div class="text-center">
                <div class="spinner-ring mx-auto mb-3"></div>
                <p class="text-white fw-semibold" style="font-size:.95rem;" id="loadingText">Generating pass slips…</p>
            </div>
        </div>

        {{-- ── Hero ─────────────────────────────────────────────────────────── --}}
        <div class="ps-hero mb-4">
            <div class="row g-3">

                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center"
                        style="gap:12px;">
                        <span class="status-pill status-{{ $exam->status }}">{{ $exam->statusLabel() }}</span>
                        <a href="{{ route('examination.index') }}" class="btn fw-semibold"
                            style="border-radius:1rem; padding:.7rem 1.5rem;
                                                                          background:rgba(255,255,255,.2); backdrop-filter:blur(10px);
                                                                          border:1px solid rgba(255,255,255,.3); color:white;" onmouseover="this.style.background='rgba(255,255,255,.3)'"
                            onmouseout="this.style.background='rgba(255,255,255,.2)'">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="col-12">
                    <h3 class="text-white fw-bold mb-1" style="font-size:1.75rem; line-height:1.2;">
                        <i class="fas fa-id-card me-2 opacity-75"></i> Pass Slip Generator
                    </h3>
                    <p class="mb-0 mb-3" style="color:rgba(255,255,255,.75); font-size:.92rem;">
                        {{ $exam->exam_name }}
                    </p>
                </div>

                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-12 col-sm-4 col-lg-4">
                            <span class="meta-pill">
                                <i class="fas fa-calendar"></i>
                                {{ $exam->term }} | {{ $exam->academic_year }}
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-lg-4">
                            <span class="meta-pill"><i class="fas fa-code"></i>{{ $exam->exam_code }}</span>
                        </div>
                        <div class="col-12 col-sm-4 col-lg-4">
                            <span class="meta-pill"><i class="fas fa-layer-group"></i>{{ $examClasses->count() }}
                                Class(es)</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Body ─────────────────────────────────────────────────────────── --}}
        <div class="row g-4 mt-1">

            {{-- LEFT: Print All + Per Class ─────────────────────────────────── --}}
            <div class="col-lg-4">

                {{-- Print All Section --}}
                <div class="ps-section-card mb-4">
                    <div class="ps-section-header">
                        <div class="ps-section-icon">
                            <i class="fas fa-print"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem; color:#1e1b4b;">Bulk Print</div>
                            <div class="text-muted" style="font-size:.78rem;">Print all students at once</div>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Info Banner --}}
                        <div class="d-flex align-items-start gap-2 mb-4 p-3"
                            style="background: var(--brand-ultra); border-radius: var(--radius-md); border-left: 3px solid var(--brand);">
                            <i class="fas fa-info-circle"
                                style="color: var(--brand); font-size: 0.9rem;margin-top:0.1em;"></i>
                            <div class="small text-muted" style="font-size: 0.8rem; line-height: 1.4;">
                                &nbsp;Generate pass slips for every student across all
                                <strong>{{ $examClasses->count() }}</strong> class(es) in this examination.
                            </div>
                        </div>

                        {{-- Quick Stats --}}
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-1">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-1"
                                    style="background: var(--brand-pale); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-layer-group" style="color: var(--brand); font-size: 0.7rem;"></i>
                                </div>
                                <span class="small fw-semibold text-muted">&nbsp;&nbsp;{{ $examClasses->count() }}
                                    Classes</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-1"
                                    style="background: var(--brand-pale); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-users" style="color: var(--brand); font-size: 0.7rem;"></i>
                                </div>
                                <span class="small fw-semibold text-muted">&nbsp;&nbsp;{{ $allStudents->count() }}
                                    Students</span>
                            </div>
                        </div>

                        {{-- Print Button --}}
                        <a href="{{ route('examination.passslips.all', $exam->id) }}"
                            class="print-btn w-100 justify-content-center"
                            onclick="showLoading('Generating all pass slips…')" style="padding: 0.9rem; font-weight: 600;">
                            <i class="fas fa-print me-2"></i>
                            Print All Pass Slips
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>

                {{-- Per Class Section --}}
                <div class="ps-section-card">
                    <div class="ps-section-header">
                        <div class="ps-section-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem; color:#1e1b4b;">By Class</div>
                            <div class="text-muted" style="font-size:.78rem;">Select a class to print all students</div>
                        </div>
                    </div>

                    <div class="p-3">

                        @if($examClasses->count() > 0)
                            {{-- Header with stats --}}
                            <div class="mb-3 pb-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted" style="font-size: 0.7rem;">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        {{ $examClasses->count() }} Class(es)
                                    </span>
                                    <span class="text-muted" style="font-size: 0.65rem;">
                                        <i class="fas fa-hand-pointer me-1"></i> Click to generate
                                    </span>
                                </div>
                            </div>

                            {{-- Class List Grid - Compact Design --}}
                            <div class="row g-3">
                                @foreach($examClasses as $index => $ec)
                                    @php
                                        $className = Helper::recordMdname($ec->class_id);
                                        $streamLabel = $ec->stream_id ? ' – ' . $ec->stream_id : '';
                                        $studentCount = DB::table('students')
                                            ->where('school_id', Session('LoggedSchool'))
                                            ->where('senior', $ec->class_id)
                                            ->where('stream', $ec->stream_id)
                                            ->count();
                                        $gradientColors = ['brand', 'brand-mid', 'brand-light'];
                                        $gradientIndex = $index % count($gradientColors);
                                        $safeStream = $ec->stream_id ?? '';
                                        $formId = 'classForm_' . $ec->class_id . '_' . $safeStream;
                                    @endphp
                                    <div class="col-12 mb-2">
                                        <button class="class-tile w-100 text-start p-3" onclick="printClass('{{ $formId }}', this)">

                                            <div class="d-flex align-items-center gap-3">
                                                {{-- Class Icon - Smaller --}}
                                                <div class="class-tile-icon flex-shrink-0 d-flex align-items-center justify-content-center"
                                                    style="width: 42px; height: 42px; background: linear-gradient(135deg, var(--{{ $gradientColors[$gradientIndex] }}), var(--brand-mid)); border-radius: 12px;">
                                                    <i class="fas fa-graduation-cap" style="font-size: 1.2rem; color: white;"></i>
                                                </div>

                                                {{-- Class Info --}}
                                                <div class="flex-grow-1">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                                        {{-- Class Name --}}
                                                        <div class="fw-bold"
                                                            style="font-size: 0.95rem; color: #1e1b4b; line-height: 1.3;">
                                                            &nbsp; &nbsp; {{ $className }}{{ $streamLabel }}
                                                        </div>
                                                        {{-- Student Count Badge --}}
                                                        <span class="badge"
                                                            style="background: var(--brand-pale); color: var(--brand); font-size: 0.7rem; padding: 0.25rem 0.8rem; border-radius: 20px;">
                                                            <i class="fas fa-user-graduate me-1"></i>
                                                           <span style="ml-1">&nbsp; &nbsp;{{ $studentCount }}</span> 
                                                        </span>
                                                    </div>

                                                    {{-- Action Text - Smaller --}}
                                                    <div class="mt-2">
                                                        <span class="text-primary d-inline-flex align-items-center gap-1"
                                                            style="font-size: 0.7rem; font-weight: 500; opacity: 0.8;">
                                                            <i class="fas fa-print"></i>
                                                           &nbsp; &nbsp; Print class
                                                            <i class="fas fa-arrow-right mt-1 ml-1" style="font-size: 0.65rem;"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>

                                        {{-- Hidden Form for this class --}}
                                        <form id="{{ $formId }}" action="{{ route('examination.passslips.class', $exam->id) }}"
                                            method="GET" target="_blank" style="display: none;">
                                            <input type="hidden" name="class_id" value="{{ $ec->class_id }}">
                                            <input type="hidden" name="stream_id" value="{{ $safeStream }}">
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Empty State - Compact --}}
                            <div class="text-center py-4">
                                <div class="mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 48px; height: 48px; background: var(--brand-ultra);">
                                        <i class="fas fa-chalkboard-teacher fs-5" style="color: var(--brand-pale);"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-1" style="font-weight: 600; font-size: 0.85rem;">No Classes Assigned
                                </h6>
                                <p class="text-muted small mb-0" style="font-size: 0.7rem;">
                                    This examination doesn't have any classes assigned yet.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- RIGHT: Individual Student ───────────────────────────────────── --}}
            <div class="col-lg-8">
                <div class="ps-section-card h-100">
                    <div class="ps-section-header">
                        <div class="ps-section-icon"><i class="fas fa-user-graduate"></i></div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem; color:#1e1b4b;">Individual Student</div>
                            <div class="text-muted" style="font-size:.78rem;">Search & print one student's slip</div>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Class filter chips --}}
                        <div class="mb-4" style="margin-bottom: 1.5rem;">
                            <label class="fw-semibold mb-3"
                                style="font-size: .85rem; color: #444; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <i class="fas fa-filter" style="color: var(--brand); font-size: 0.8rem;"></i>
                                Filter by Class
                            </label>
                            <div class="d-flex flex-wrap" id="classFilterContainer" style="gap: 0.75rem;">
                                <!-- increased gap between buttons -->
                                <button class="class-filter-chip active" data-class="all" onclick="filterClass('all', this)"
                                    style="padding: 0.4rem 1rem; border: 1px solid #ccc; border-radius: 50px; background-color: #f8f9fa; cursor: pointer; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-globe" style="margin-right: 0.3rem;"></i> All Classes
                                </button>
                                @foreach($examClasses as $ec)
                                    <button class="class-filter-chip" data-class="{{ $ec->class_id }}_{{ $ec->stream_id }}"
                                        onclick="filterClass('{{ $ec->class_id }}_{{ $ec->stream_id }}', this)"
                                        style="padding: 0.4rem 1rem; border: 1px solid #ccc; border-radius: 50px; background-color: #f8f9fa; cursor: pointer; display: flex; align-items: center; gap: 0.3rem; margin-bottom: 0.3rem;">
                                        <i class="fas fa-chalkboard-user" style="margin-right: 0.3rem;"></i>
                                        {{ Helper::recordMdname($ec->class_id) }}
                                        {{ $ec->stream_id ? '– ' . $ec->stream_id : '' }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Enhanced Search Box --}}
                        <div class="student-search-wrap mb-4">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="studentSearch"
                                placeholder="Search by name, admission number, or class..."
                                oninput="filterStudents(this.value)" autocomplete="off">
                            @if(count($allStudents) > 0)
                                <span class="search-stats" id="searchStats">{{ count($allStudents) }} students</span>
                            @endif
                        </div>

                        {{-- Student List with Enhanced Design --}}
                        <div class="student-list-container">
                            <div class="student-list-header">
                                <span><i class="fas fa-users me-1"></i> Student Directory</span>
                                <span class="student-count-badge" id="studentCount">{{ count($allStudents) }}</span>
                            </div>

                            <div class="student-list" id="studentList">
                                @php
                                    // Collect all students for all exam classes
                                    $allStudents = collect();
                                    foreach ($examClasses as $ec) {
                                        $students = DB::table('students')
                                            ->where('school_id', Session('LoggedSchool'))
                                            ->where('senior', $ec->class_id)
                                            ->where('stream', $ec->stream_id)
                                            ->orderBy('lastname')
                                            ->get()
                                            ->map(function ($s) use ($ec) {
                                                $s->class_id = $ec->class_id;
                                                $s->stream_id = $ec->stream_id;
                                                return $s;
                                            });
                                        $allStudents = $allStudents->merge($students);
                                    }
                                    $allStudents = $allStudents->sortBy('lastname');
                                @endphp

                                @forelse($allStudents as $index => $student)
                                    @php
                                        $initials = strtoupper(substr($student->lastname, 0, 1) . substr($student->firstname, 0, 1));
                                        $fullName = $student->lastname . ' ' . $student->firstname;
                                        if (property_exists($student, 'other_names') && $student->other_names) {
                                            $fullName .= ' ' . $student->other_names;
                                        }
                                    @endphp
                                    <a href="{{ route('examination.passslips.student', [$exam->id, $student->id]) }}"
                                        class="student-card" data-name="{{ strtolower($fullName) }}"
                                        data-adm="{{ strtolower($student->adm_no ?? '') }}"
                                        data-class="{{ $student->class_id }}_{{ $student->stream_id }}"
                                        onclick="showLoading('Generating pass slip for {{ addslashes($student->firstname) }}…')"
                                        target="_blank">

                                        <div class="student-card-avatar"
                                            style="background: linear-gradient(135deg, {{ $index % 2 == 0 ? 'var(--brand)' : 'var(--brand-mid)' }}, var(--brand-light));">
                                            {{ $initials }}
                                        </div>

                                        <div class="student-card-info">
                                            <div class="student-card-name">
                                                {{ $student->lastname }} {{ $student->firstname }}
                                                @if(property_exists($student, 'other_names') && $student->other_names)
                                                    <span class="other-names">{{ $student->other_names }}</span>
                                                @endif
                                            </div>
                                            <div class="student-card-meta">
                                                <span class="meta-tag">
                                                    <i class="fas fa-graduation-cap"></i>
                                                    {{ Helper::recordMdname($student->class_id) }}
                                                    {{ $student->stream_id ? '– ' . $student->stream_id : '' }}
                                                </span>
                                                @if($student->adm_no ?? false)
                                                    <span class="meta-tag">
                                                        <i class="fas fa-id-card"></i>
                                                        {{ $student->adm_no }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="student-card-action">
                                            <i class="fas fa-print"></i>
                                            <span>Print Slip</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-user-graduate"></i>
                                        <h4>No Students Found</h4>
                                        <p>No students are enrolled in any class for this examination.</p>
                                    </div>
                                @endforelse

                                <div id="noResultsMsg" class="empty-state" style="display: none;">
                                    <i class="fas fa-search"></i>
                                    <h4>No Matching Students</h4>
                                    <p>Try adjusting your search or class filter to find the student you're looking for.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Hidden forms for class print (POST) --}}
    @foreach($examClasses as $ec)
        <form id="classForm_{{ $ec->class_id }}_{{ $ec->stream_id }}"
            action="{{ route('examination.passslips.class', $exam->id) }}" method="GET" target="_blank" style="display:none;">
            @csrf
            <input type="hidden" name="class_id" value="{{ $ec->class_id }}">
            <input type="hidden" name="stream_id" value="{{ $ec->stream_id }}">
        </form>
    @endforeach

    <script>
        function showLoading(msg) {
            const loadingText = document.getElementById('loadingText');
            loadingText.textContent = msg ?? 'Generating…';
            loadingText.style.color = '#2C29CA';
            document.getElementById('loadingOverlay').classList.add('active');
            // auto-hide after 8s as fallback
            setTimeout(() => document.getElementById('loadingOverlay').classList.remove('active'), 8000);
        }

        // FIXED: printClass now accepts formId instead of classId and streamId
        function printClass(formId, btn) {
            // Visual feedback - highlight selected tile
            document.querySelectorAll('.class-tile').forEach(t => t.classList.remove('selected'));
            if (btn) btn.classList.add('selected');

            showLoading('Generating class pass slips…');

            const form = document.getElementById(formId);
            if (!form) {
                console.error('Form not found:', formId);
                document.getElementById('loadingOverlay').classList.remove('active');
                alert('Error: Could not find form for this class.');
                return;
            }
            form.submit();
        }

        let activeClassFilter = 'all';

        function filterClass(key, btn) {
            activeClassFilter = key;

            // Update active state on chips
            document.querySelectorAll('.class-filter-chip').forEach(b => {
                b.classList.remove('active');
            });
            btn.classList.add('active');

            applyFilters();
        }

        function filterStudents(q) {
            applyFilters(q);
        }

        function applyFilters(q) {
            q = (q ?? document.getElementById('studentSearch').value).toLowerCase().trim();
            const cards = document.querySelectorAll('.student-card');
            let visible = 0;

            cards.forEach(card => {
                const nameMatch = card.dataset.name.includes(q);
                const admMatch = card.dataset.adm && card.dataset.adm.includes(q);
                const classMatch = activeClassFilter === 'all' || card.dataset.class === activeClassFilter;
                const show = (nameMatch || admMatch) && classMatch;

                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            // Update search stats
            const searchStats = document.getElementById('searchStats');
            if (searchStats) {
                if (q) {
                    searchStats.textContent = `${visible} of ${cards.length} students`;
                } else {
                    searchStats.textContent = `${cards.length} students`;
                }
            }

            // Show/hide no results message
            const noResultsMsg = document.getElementById('noResultsMsg');
            if (noResultsMsg) {
                noResultsMsg.style.display = visible === 0 ? 'flex' : 'none';
            }

            // Update student count badge
            const studentCount = document.getElementById('studentCount');
            if (studentCount) {
                studentCount.textContent = visible;
            }
        }

        // Initialize active filter styling
        document.addEventListener('DOMContentLoaded', function () {
            const allFilterBtn = document.querySelector('.class-filter-chip[data-class="all"]');
            if (allFilterBtn) {
                allFilterBtn.classList.add('active');
            }
        });

        // init
        document.querySelector('.class-filter-btn[data-class="all"]').style.background = 'var(--brand)';
        document.querySelector('.class-filter-btn[data-class="all"]').style.color = '#fff';
        document.querySelector('.class-filter-btn[data-class="all"]').style.borderColor = 'var(--brand)';
    </script>
@endsection