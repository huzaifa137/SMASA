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
            background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .ps-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(108,63,197,.18) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* ── Section cards ── */
        .ps-section-card {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(44,41,202,.08);
            overflow: hidden;
            transition: box-shadow .2s;
        }

        .ps-section-card:hover {
            box-shadow: 0 8px 36px rgba(44,41,202,.15);
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
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .class-tile:hover,
        .class-tile.selected {
            border-color: var(--brand);
            background: linear-gradient(135deg, #fff, var(--brand-ultra));
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(44,41,202,.12);
        }

        .class-tile.selected {
            box-shadow: 0 0 0 3px rgba(44,41,202,.2);
        }

        .class-tile-icon {
            transition: all .2s ease;
        }

        .class-tile:hover .class-tile-icon {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(44,41,202,.2);
        }

        .class-tile .badge {
            transition: all .2s ease;
            white-space: nowrap;
        }

        .class-tile:hover .badge {
            background: var(--brand) !important;
            color: white !important;
        }

        /* ── Student search box ── */
        .student-search-wrap { position: relative; }

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
            padding: .85rem 1rem .85rem 2.75rem;
            border: 2px solid var(--brand-pale);
            border-radius: 1rem;
            font-size: .9rem;
            transition: all .2s ease;
            background: white;
        }

        #studentSearch:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(44,41,202,.1);
        }

        .search-stats {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: .75rem;
            color: #9ca3af;
            background: var(--brand-ultra);
            padding: .25rem .6rem;
            border-radius: 1rem;
            font-weight: 600;
        }

        /* ── Student List ── */
        .student-list-container {
            border: 2px solid var(--brand-pale);
            border-radius: 1rem;
            overflow: hidden;
            background: white;
        }

        .student-list-header {
            background: linear-gradient(135deg, var(--brand-ultra), white);
            padding: .9rem 1.25rem;
            border-bottom: 2px solid var(--brand-pale);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: .85rem;
            color: var(--brand);
        }

        .student-count-badge {
            background: var(--brand);
            color: white;
            padding: .25rem .7rem;
            border-radius: 1rem;
            font-size: .7rem;
            font-weight: 600;
        }

        .student-list {
            max-height: 480px;
            overflow-y: auto;
        }

        /* ── Student Cards ── */
        .student-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none;
            transition: all .2s ease;
            cursor: pointer;
            background: white;
        }

        .student-card:hover {
            background: linear-gradient(90deg, var(--brand-ultra), white);
            transform: translateX(4px);
        }

        .student-card:last-child { border-bottom: none; }

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
            transition: transform .2s ease;
        }

        .student-card:hover .student-card-avatar { transform: scale(1.05); }

        .student-card-info { flex: 1; min-width: 0; }

        .student-card-name {
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
            margin-bottom: .3rem;
        }

        .other-names { font-weight: 400; color: #6b7280; font-size: .85rem; }

        .student-card-meta { display: flex; flex-wrap: wrap; gap: .75rem; }

        .meta-tag {
            font-size: .7rem;
            color: #6b7280;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .meta-tag i { font-size: .65rem; color: var(--brand-light); }

        .student-card-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .25rem;
            padding: .5rem 1rem;
            background: var(--brand-ultra);
            border-radius: .75rem;
            transition: all .2s ease;
            flex-shrink: 0;
        }

        .student-card-action i { color: var(--brand); font-size: 1rem; }
        .student-card-action span { font-size: .65rem; font-weight: 600; color: var(--brand); }

        .student-card:hover .student-card-action { background: var(--brand); }
        .student-card:hover .student-card-action i,
        .student-card:hover .student-card-action span { color: white; }

        /* ── Print buttons ── */
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
            box-shadow: 0 6px 20px rgba(44,41,202,.3);
        }

        /* ── Status pill ── */
        .status-pill {
            padding: .4rem 1rem;
            border-radius: 99px;
            font-size: .8rem;
            font-weight: 700;
            display: inline-block;
        }

        .status-closed          { background: #fde8e8; color: #c0392b; }
        .status-results_released{ background: #d4f5e2; color: #1a7a4a; }
        .status-marks_entry     { background: #fff3cd; color: #856404; }
        .status-active          { background: #cfe2ff; color: #0a4191; }

        /* ── Meta pills (hero) ── */
        .meta-pill {
            background: rgba(255,255,255,.15);
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
            background: rgba(44,41,202,.12);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        #loadingOverlay.active { display: flex; }

        .spinner-ring {
            width: 56px;
            height: 56px;
            border: 5px solid #fff;
            border-top-color: var(--brand);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        @media print { body { display: none; } }

        /* ── Class filter chips ── */
        .class-filter-chip {
            padding: .4rem 1rem;
            border-radius: 2rem;
            font-size: .8rem;
            font-weight: 600;
            border: 2px solid var(--brand-pale);
            background: white;
            color: #4a5568;
            cursor: pointer;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
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
            box-shadow: 0 4px 12px rgba(44,41,202,.25);
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #9ca3af;
        }

        .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: .5; }
        .empty-state h4 { font-size: 1rem; font-weight: 600; color: #6b7280; margin-bottom: .5rem; }
        .empty-state p  { font-size: .85rem; margin: 0; }

        /* Scrollbar */
        .student-list::-webkit-scrollbar { width: 6px; }
        .student-list::-webkit-scrollbar-track { background: var(--brand-ultra); }
        .student-list::-webkit-scrollbar-thumb { background: var(--brand-light); border-radius: 3px; }
        .student-list::-webkit-scrollbar-thumb:hover { background: var(--brand); }

        /* ════════════════════════════════════════════════
           CUSTOMISATION PANEL
        ════════════════════════════════════════════════ */
        .custom-panel {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(44,41,202,.08);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .custom-panel-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 60%, #1e1b4b 100%);
            padding: 1rem 1.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }

        .custom-panel-header .cp-title {
            font-size: .9rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: .55rem;
            letter-spacing: .02em;
        }

        .custom-panel-header .cp-badge {
            background: rgba(240,165,0,.25);
            color: #f0a500;
            font-size: .67rem;
            font-weight: 700;
            padding: .2rem .6rem;
            border-radius: .75rem;
            border: 1px solid rgba(240,165,0,.4);
            letter-spacing: .04em;
        }

        .custom-panel-header .cp-toggle {
            color: rgba(255,255,255,.6);
            font-size: .85rem;
            transition: transform .25s;
        }

        .custom-panel-header.collapsed .cp-toggle { transform: rotate(-90deg); }

        .custom-panel-body {
            padding: 1.25rem 1.4rem;
            border-top: 1px solid rgba(44,41,202,.07);
        }

        /* Group headings inside panel */
        .cp-group-label {
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #888;
            margin-bottom: .6rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .cp-group-label:first-child { margin-top: 0; }

        .cp-group-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ebebeb;
        }

        /* ══════════════════════════════════════════════════════
           DESIGN TEMPLATE GALLERY
        ══════════════════════════════════════════════════════ */
        .cp-template-gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .65rem;
            margin-bottom: .3rem;
        }

        .cp-template-card {
            border: 2px solid #e6e6ef;
            border-radius: 12px;
            padding: .55rem .55rem .7rem;
            cursor: pointer;
            transition: all .15s ease;
            background: #fff;
            text-align: center;
        }

        .cp-template-card:hover {
            border-color: #c9c7f5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(44,41,202,.1);
        }

        .cp-template-card.selected {
            border-color: var(--brand, #2f2ccb);
            background: rgba(47,44,203,.045);
            box-shadow: 0 6px 16px rgba(44,41,202,.14);
        }

        .cp-tpl-thumb {
            width: 100%;
            height: 78px;
            border-radius: 6px;
            overflow: hidden;
            background: #f4f4f8;
            border: 1px solid #eaeaf0;
            margin-bottom: .4rem;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .cp-tpl-name {
            font-size: .74rem;
            font-weight: 800;
            color: #222;
        }

        .cp-tpl-desc {
            font-size: .6rem;
            color: #999;
            line-height: 1.3;
            margin-top: .1rem;
        }

        .cp-tpl-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: var(--brand, #2f2ccb);
            color: #fff;
            font-size: .55rem;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 20px;
            display: none;
        }

        .cp-template-card.selected .cp-tpl-badge { display: block; }

        /* ── Classic thumb: centred header, gold band, ornate corners ── */
        .cp-tpl-thumb-classic { border: 2px solid #d9a441; }
        .cp-tpl-thumb-classic .tpl-hdr {
            height: 30px; display: flex; align-items: center; justify-content: center;
            gap: 4px; border-bottom: 2px solid #d9a441;
        }
        .cp-tpl-thumb-classic .tpl-hdr .dot { width: 10px; height: 10px; border-radius: 50%; border: 1.5px solid #d9a441; background: #fff; }
        .cp-tpl-thumb-classic .tpl-hdr .bar { width: 34px; height: 5px; border-radius: 2px; background: #d0d0d8; }
        .cp-tpl-thumb-classic .tpl-band { height: 9px; background: #d9a441; margin: 3px 8px; border-radius: 1px; }
        .cp-tpl-thumb-classic .tpl-rows { flex: 1; margin: 5px 8px 0; display: flex; flex-direction: column; gap: 3px; }
        .cp-tpl-thumb-classic .tpl-rows div { height: 4px; background: #e4e4ea; border-radius: 1px; }

        /* ── Modern thumb: bold colour banner, squared, flat top rule ── */
        .cp-tpl-thumb-modern { border-top: 4px solid #2f2ccb; }
        .cp-tpl-thumb-modern .tpl-hdr {
            height: 34px; background: linear-gradient(120deg,#2f2ccb,#1e1c99);
            display: flex; align-items: center; gap: 4px; padding-left: 7px;
        }
        .cp-tpl-thumb-modern .tpl-hdr .dot { width: 12px; height: 12px; border-radius: 4px; background: #fff; }
        .cp-tpl-thumb-modern .tpl-hdr .bar { width: 40px; height: 5px; border-radius: 2px; background: rgba(255,255,255,.85); }
        .cp-tpl-thumb-modern .tpl-band { height: 8px; background: #1a1a1a; margin: 0; }
        .cp-tpl-thumb-modern .tpl-rows { flex: 1; margin: 5px 8px 0; display: flex; flex-direction: column; gap: 3px; }
        .cp-tpl-thumb-modern .tpl-rows div { height: 4px; background: #e4e4ea; border-radius: 1px; }
        .cp-tpl-thumb-modern .tpl-rows div:first-child { background: #c8c5f2; }

        /* ── Minimal thumb: hairline only, left aligned, quiet ── */
        .cp-tpl-thumb-minimal { border: 1px solid #e2e2e2; }
        .cp-tpl-thumb-minimal .tpl-hdr {
            height: 30px; display: flex; align-items: center; gap: 5px; padding-left: 7px;
            border-bottom: 1px solid #e6e6e6;
        }
        .cp-tpl-thumb-minimal .tpl-hdr .dot { width: 9px; height: 9px; border-radius: 2px; background: #f0f0f0; border: 1px solid #ddd; }
        .cp-tpl-thumb-minimal .tpl-hdr .bar { width: 30px; height: 4px; border-radius: 2px; background: #999; border-bottom: 2px solid #2f2ccb; }
        .cp-tpl-thumb-minimal .tpl-band { height: 4px; width: 40px; margin: 5px 0 0 7px; background: #ccc; }
        .cp-tpl-thumb-minimal .tpl-rows { flex: 1; margin: 6px 7px 0; display: flex; flex-direction: column; gap: 4px; }
        .cp-tpl-thumb-minimal .tpl-rows div { height: 3px; background: #eee; border-radius: 0; }

        /* Checkbox rows */
        .cp-check-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5rem .65rem;
            border-radius: .5rem;
            transition: background .12s;
            cursor: pointer;
        }

        .cp-check-row:hover { background: var(--brand-ultra); }

        .cp-check-row label {
            display: flex;
            align-items: center;
            gap: .55rem;
            font-size: .82rem;
            font-weight: 500;
            color: #333;
            cursor: pointer;
            flex: 1;
        }

        .cp-check-row label i {
            width: 18px;
            text-align: center;
            color: var(--brand-mid);
            font-size: .78rem;
        }

        /* Toggle switch */
        .cp-switch {
            position: relative;
            width: 38px;
            height: 20px;
            flex-shrink: 0;
        }

        .cp-switch input { opacity: 0; width: 0; height: 0; }

        .cp-switch-slider {
            position: absolute;
            inset: 0;
            background: #ddd;
            border-radius: 20px;
            cursor: pointer;
            transition: background .2s;
        }

        .cp-switch-slider::before {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            left: 3px;
            top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: transform .2s;
            box-shadow: 0 1px 4px rgba(0,0,0,.2);
        }

        .cp-switch input:checked + .cp-switch-slider { background: var(--brand); }
        .cp-switch input:checked + .cp-switch-slider::before { transform: translateX(18px); }

        /* Color picker row */
        .cp-color-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .55rem .65rem;
            border-radius: .5rem;
        }

        .cp-color-row label {
            font-size: .82rem;
            font-weight: 500;
            color: #333;
            display: flex;
            align-items: center;
            gap: .55rem;
            flex: 1;
        }

        .cp-color-row label i { width: 18px; text-align: center; color: var(--brand-mid); font-size: .78rem; }

        .cp-color-swatch {
            width: 32px;
            height: 22px;
            border-radius: 6px;
            border: 2px solid #ddd;
            cursor: pointer;
            overflow: hidden;
            padding: 0;
            flex-shrink: 0;
        }

        .cp-color-swatch input[type="color"] {
            width: 200%;
            height: 200%;
            margin: -50%;
            border: none;
            cursor: pointer;
            padding: 0;
            background: none;
        }

        /* Quick presets */
        .cp-presets {
            display: flex;
            gap: .4rem;
            flex-wrap: wrap;
            margin-top: .45rem;
            padding: 0 .65rem;
        }

        .cp-preset-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform .15s, border-color .15s;
            flex-shrink: 0;
        }

        .cp-preset-dot:hover   { transform: scale(1.2); }
        .cp-preset-dot.active  { border-color: #333; transform: scale(1.15); }

        /* Check-all button */
        .cp-check-all-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .55rem .65rem;
            border-radius: .5rem;
            background: var(--brand-ultra);
            border: 1px solid var(--brand-pale);
            margin-bottom: .75rem;
        }

        .cp-check-all-row span {
            font-size: .78rem;
            font-weight: 700;
            color: var(--brand);
        }

        .cp-check-all-btns { display: flex; gap: .4rem; }

        .cp-btn-sm {
            padding: .25rem .75rem;
            border-radius: .5rem;
            border: 1.5px solid var(--brand);
            background: transparent;
            color: var(--brand);
            font-size: .7rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
        }

        .cp-btn-sm:hover,
        .cp-btn-sm.filled { background: var(--brand); color: #fff; }

        /* Live preview swatch (accent color preview) */
        .cp-accent-preview {
            height: 6px;
            border-radius: 3px;
            margin: .5rem .65rem 0;
            transition: background .2s;
        }

        #loadingText { color: #2C29CA !important; font-weight: 600; }

        
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        {{-- ── Hero ──────────────────────────────────────────────────────── --}}
        <div class="ps-hero mb-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center" style="gap:12px;">
                        <span class="status-pill status-{{ $exam->status }}">{{ $exam->statusLabel() }}</span>
                        <a href="{{ route('examination.index') }}" class="btn fw-semibold"
                            style="border-radius:1rem;padding:.7rem 1.5rem;background:rgba(255,255,255,.2);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);color:white;"
                            onmouseover="this.style.background='rgba(255,255,255,.3)'"
                            onmouseout="this.style.background='rgba(255,255,255,.2)'">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <h3 class="text-white fw-bold mb-1" style="font-size:1.75rem;line-height:1.2;">
                        <i class="fas fa-id-card me-2 opacity-75"></i> Pass Slip Generator
                    </h3>
                    <p class="mb-0 mb-3" style="color:rgba(255,255,255,.75);font-size:.92rem;">{{ $exam->exam_name }}</p>
                </div>
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-12 col-sm-4">
                            <span class="meta-pill"><i class="fas fa-calendar"></i>{{ $exam->term }} | {{ $exam->academic_year }}</span>
                        </div>
                        <div class="col-12 col-sm-4">
                            <span class="meta-pill"><i class="fas fa-code"></i>{{ $exam->exam_code }}</span>
                        </div>
                        <div class="col-12 col-sm-4">
                            <span class="meta-pill"><i class="fas fa-layer-group"></i>{{ $examClasses->count() }} Class(es)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Body ─────────────────────────────────────────────────────── --}}
        <div class="row g-4 mt-1">

            {{-- ═══════════ LEFT COLUMN ═══════════ --}}
            <div class="col-lg-4">

                {{-- ┌──────────────────────────────────┐
                     │  CUSTOMISATION PANEL             │
                     └──────────────────────────────────┘ --}}
                <div class="custom-panel">
                    <div class="custom-panel-header" id="cpToggleHeader" onclick="toggleCustomPanel()">
                        <div class="cp-title">
                            <i class="fas fa-sliders"></i>
                            Customise Pass Slip
                            <span style="width: 18px; height: 18px; background: linear-gradient(135deg, #f0a500, #ff6b6b, #4ecdc4, #45b7d1); border-radius: 50%; display: inline-block; animation: rotateColors 3s infinite;"></span>

                            <style>
                            @keyframes rotateColors {
                                0% { filter: hue-rotate(0deg); }
                                100% { filter: hue-rotate(360deg); }
                            }
                            </style>
                        </div>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <small id="cpSummary" style="color:rgba(255,255,255,.5);font-size:.65rem;font-weight:600;letter-spacing:.04em;"></small>
                            <i class="fas fa-chevron-down cp-toggle" id="cpChevron"></i>
                        </div>
                    </div>

                    <div class="custom-panel-body" id="cpBody">

                    {{-- Add this after the custom-panel or in the hero section --}}
<div class="custom-panel mb-4">
    <div class="custom-panel-header" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 60%, #1e1b4b 100%);">
        <div class="cp-title">
            <i class="fas fa-language"></i>
            Language / اللغة
        </div>
    </div>
    <div class="custom-panel-body">
        <div class="d-flex gap-3 align-items-center">
            <button class="lang-toggle-btn {{ request('lang', 'en') == 'en' ? 'active' : '' }}" 
                    data-lang="en" 
                    onclick="setLanguage('en')"
                    style="flex:1; padding: .75rem; border-radius: .75rem; border: 2px solid var(--brand-pale); background: {{ request('lang', 'en') == 'en' ? 'var(--brand)' : 'white' }}; color: {{ request('lang', 'en') == 'en' ? 'white' : '#333' }}; font-weight: 600; transition: all .2s;">
                <i class="fas fa-flag-usa me-2"></i> English
            </button>
            <button class="lang-toggle-btn {{ request('lang') == 'ar' ? 'active' : '' }}" 
                    data-lang="ar" 
                    onclick="setLanguage('ar')"
                    style="flex:1; padding: .75rem; border-radius: .75rem; border: 2px solid var(--brand-pale); background: {{ request('lang') == 'ar' ? 'var(--brand)' : 'white' }}; color: {{ request('lang') == 'ar' ? 'white' : '#333' }}; font-weight: 600; transition: all .2s;">
                <i class="fas fa-flag me-2"></i> العربية
            </button>
        </div>
        <div class="mt-3 text-muted small text-center">
            <i class="fas fa-info-circle"></i> Language preference will be applied to all pass slips
        </div>
    </div>
</div>

<script>
function setLanguage(lang) {
    // Update URL with lang parameter
    const url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}
</script>

<style>
.lang-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44,41,202,.2);
}
.lang-toggle-btn.active {
    border-color: var(--brand);
    background: var(--brand) !important;
    color: white !important;
}
</style>

                        {{-- CHECK ALL / NONE --}}
                        <div class="cp-check-all-row">
                            <span><i class="fas fa-check-square me-1"></i> Quick select</span>
                            <div class="cp-check-all-btns">
                                <button class="cp-btn-sm" onclick="setAllChecks(true)">Check All</button>
                                <button class="cp-btn-sm" onclick="setAllChecks(false)">Uncheck All</button>
                            </div>
                        </div>

                        {{-- ── GROUP: Combine Examinations ── --}}
                        <div class="cp-group-label"><i class="fas fa-layer-group"></i> Combine Examinations</div>
                        <div class="small text-muted" style="font-size:.72rem;line-height:1.4;padding:0 .25rem .5rem;">
                            Add other examinations from this academic year onto the same pass slip
                            (e.g. BOT | MID | END), and choose which of them to average.
                        </div>

                        <div class="cp-check-row" style="opacity:.85;">
                            <label><i class="fas fa-check-circle"></i>&nbsp; {{ $exam->exam_name }} ({{ $exam->term }}) — current</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_avg_base_{{ $exam->id }}" class="exam-avg-cb" value="{{ $exam->id }}" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        @if(isset($siblingExams) && $siblingExams->count() > 0)
                            @foreach($siblingExams as $se)
                                <div class="cp-check-row">
                                    <label for="cb_exam_{{ $se->id }}">
                                        <input type="checkbox" id="cb_exam_{{ $se->id }}" class="exam-combine-cb"
                                               value="{{ $se->id }}" onchange="onExamComboChange(this)"
                                               style="margin-right:.4rem;">
                                        {{ $se->exam_name }} ({{ $se->term }})
                                    </label>
                                    <label class="cp-switch" title="Include in average">
                                        <input type="checkbox" id="cb_avg_{{ $se->id }}" class="exam-avg-cb"
                                               value="{{ $se->id }}" disabled onchange="updateSummary()">
                                        <span class="cp-switch-slider"></span>
                                    </label>
                                </div>
                            @endforeach

                            <div class="cp-check-row">
                                <label for="cb_show_multi_average"><i class="fas fa-percent"></i> Show AVERAGE column</label>
                                <label class="cp-switch">
                                    <input type="checkbox" id="cb_show_multi_average" class="cp-toggle-cb" checked onchange="updateSummary()">
                                    <span class="cp-switch-slider"></span>
                                </label>
                            </div>
                            <div class="small text-muted" style="font-size:.68rem;padding:0 .25rem .5rem;">
                                Tick "Include in average" (the small switch) for at least 2 examinations to
                                show an averaged score. Leave it on 0–1 exam and no average will be printed.
                            </div>
                        @else
                            <div class="small text-muted" style="font-size:.72rem;padding:0 .25rem .5rem;">
                                No other examinations found for {{ $exam->academic_year }} yet.
                            </div>
                        @endif

                        {{-- ── GROUP: Design Template ── --}}
                        <div class="cp-group-label"><i class="fas fa-swatchbook"></i> Design Template</div>
                        <div class="small text-muted" style="font-size:.72rem;line-height:1.4;padding:0 .25rem .5rem;">
                            Pick the overall report card design. Your accent colour, toggles, and every
                            student's data stay exactly the same — only the visual style changes.
                        </div>

                        <div class="cp-template-gallery" id="cpTemplateGallery">
                            <div class="cp-template-card selected" data-template="classic" onclick="selectTemplate('classic', this)">
                                <div class="cp-tpl-thumb cp-tpl-thumb-classic">
                                    <span class="cp-tpl-badge"><i class="fas fa-check"></i> Selected</span>
                                    <div class="tpl-hdr"><div class="dot"></div><div class="bar"></div><div class="dot"></div></div>
                                    <div class="tpl-band"></div>
                                    <div class="tpl-rows"><div></div><div></div><div></div></div>
                                </div>
                                <div class="cp-tpl-name">Classic</div>
                                <div class="cp-tpl-desc">Ornate border, medallion logos, formal &amp; traditional</div>
                            </div>

                            <div class="cp-template-card" data-template="modern" onclick="selectTemplate('modern', this)">
                                <div class="cp-tpl-thumb cp-tpl-thumb-modern">
                                    <span class="cp-tpl-badge"><i class="fas fa-check"></i> Selected</span>
                                    <div class="tpl-hdr"><div class="dot"></div><div class="bar"></div></div>
                                    <div class="tpl-band"></div>
                                    <div class="tpl-rows"><div></div><div></div><div></div></div>
                                </div>
                                <div class="cp-tpl-name">Modern</div>
                                <div class="cp-tpl-desc">Bold colour banner, squared logo, confident &amp; bright</div>
                            </div>

                            <div class="cp-template-card" data-template="minimal" onclick="selectTemplate('minimal', this)">
                                <div class="cp-tpl-thumb cp-tpl-thumb-minimal">
                                    <span class="cp-tpl-badge"><i class="fas fa-check"></i> Selected</span>
                                    <div class="tpl-hdr"><div class="dot"></div><div class="bar"></div></div>
                                    <div class="tpl-band"></div>
                                    <div class="tpl-rows"><div></div><div></div><div></div></div>
                                </div>
                                <div class="cp-tpl-name">Minimal</div>
                                <div class="cp-tpl-desc">Hairline rules, quiet whitespace, editorial &amp; clean</div>
                            </div>
                        </div>

                        {{-- ── GROUP: Appearance ── --}}
                        <div class="cp-group-label"><i class="fas fa-palette"></i> Appearance</div>

                        {{-- Accent / Primary colour --}}
                        <div class="cp-color-row">
                            <label for="cpColorPicker"><i class="fas fa-fill-drip"></i> Accent colour</label>
                            <div class="cp-color-swatch" title="Pick accent colour">
                                <input type="color" id="cpColorPicker" value="#f0a500" onchange="onColorChange(this.value)">
                            </div>
                        </div>

                        {{-- Colour presets --}}
                        <div class="cp-presets" id="cpPresets">
                            @foreach([
['#f0a500','Amber (default)'],
['#c0392b','Ruby Red'],
['#2C29CA','Brand Blue'],
['#10b981','Emerald'],
['#7c3aed','Violet'],
['#0f172a','Midnight'],
['#e11d48','Rose'],
['#0ea5e9','Sky'],

// Additional school-friendly colors
['#1d4ed8','Royal Blue'],
['#2563eb','Academic Blue'],
['#1e3a8a','Navy Blue'],
['#15803d','Forest Green'],
['#166534','Dark Green'],
['#65a30d','Lime Green'],
['#047857','Teal'],
['#0f766e','Deep Teal'],
['#b45309','Golden Brown'],
['#ca8a04','School Gold'],
['#f59e0b','Sunflower'],
['#dc2626','Crimson'],
['#991b1b','Maroon'],
['#be123c','Burgundy'],
['#6d28d9','Deep Purple'],
['#4338ca','Indigo'],
['#0369a1','Ocean Blue'],
['#0891b2','Cyan'],
['#374151','Slate Gray'],
['#4b5563','Charcoal'],
['#111827','Jet Black'],
['#92400e','Chocolate'],
['#78350f','Coffee Brown'],
['#14532d','Hunter Green'],
['#134e4a','Pine Green'],
['#86198f','Plum'],
['#9d174d','Wine'],
['#ea580c','Orange'],
['#fb7185','Soft Pink'],
['#14b8a6','Turquoise'],
['#84cc16','Olive'],
                            ] as [$hex, $label])
                            <div class="cp-preset-dot {{ $hex === '#f0a500' ? 'active' : '' }}"
                                 style="background:{{ $hex }};"
                                 title="{{ $label }}"
                                 data-color="{{ $hex }}"
                                 onclick="applyPreset('{{ $hex }}', this)"></div>
                            @endforeach
                        </div>

                        {{-- Accent preview bar --}}
                        <div class="cp-accent-preview" id="cpAccentPreview" style="background:#f0a500;"></div>

                        {{-- Border toggle --}}
                        <div class="cp-check-row mt-2">
                            <label for="cb_show_border"><i class="fas fa-border-all"></i> Decorative border & corners</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_border" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_watermark"><i class="fas fa-stamp"></i> Watermark (logo / school name)</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_watermark" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        {{-- ── GROUP: Header ── --}}
                        <div class="cp-group-label"><i class="fas fa-school"></i> School Header</div>

                        <div class="cp-check-row">
                            <label for="cb_show_logo"><i class="fas fa-image"></i> School logo</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_logo" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_arabic"><i class="fas fa-language"></i> Arabic school name</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_arabic" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_motto"><i class="fas fa-quote-left"></i> School motto</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_motto" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_contact"><i class="fas fa-phone"></i> Phone / email / location</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_contact" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        {{-- ── GROUP: Student Block ── --}}
                        <div class="cp-group-label"><i class="fas fa-user-graduate"></i> Student Block</div>

                        <div class="cp-check-row">
                            <label for="cb_show_photo"><i class="fas fa-portrait"></i> Student photo</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_photo" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_minichart"><i class="fas fa-chart-line"></i> Subject mini chart (student vs class)</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_minichart" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_qr"><i class="fas fa-qrcode"></i> QR code</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_qr" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_rank"><i class="fas fa-trophy"></i> Class position / rank</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_rank" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        {{-- ── GROUP: Summary Bar ── --}}
                        <div class="cp-group-label"><i class="fas fa-square-poll-horizontal"></i> Summary Bar</div>

                        <div class="cp-check-row">
                            <label for="cb_show_total_score"><i class="fas fa-hashtag"></i> Total Score / Marks box</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_total_score" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_average"><i class="fas fa-percent"></i> Average box</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_average" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_result"><i class="fas fa-flag-checkered"></i> Result box</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_result" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        {{-- ── GROUP: Marks Table ── --}}
                        <div class="cp-group-label"><i class="fas fa-table"></i> Marks Table</div>

                        <div class="cp-check-row">
                            <label for="cb_show_score_col"><i class="fas fa-list-ol"></i> Score / Marks column</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_score_col" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_dev"><i class="fas fa-arrows-alt-v"></i> Development (DEV ↑↓) column</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_dev" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_grade_pill"><i class="fas fa-tag"></i> Grade pills (A / B / C …)</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_grade_pill" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_comment_col"><i class="fas fa-comment-dots"></i> Comment column</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_comment_col" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_teacher_col"><i class="fas fa-chalkboard-teacher"></i> Teacher column</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_teacher_col" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_totals_row"><i class="fas fa-sigma"></i> Totals / average row</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_totals_row" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        {{-- ── GROUP: Bottom Section ── --}}
                        <div class="cp-group-label"><i class="fas fa-chart-bar"></i> Bottom Section</div>

                        <div class="cp-check-row">
                            <label for="cb_show_perf_chart"><i class="fas fa-chart-bar"></i> Performance-over-time chart</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_perf_chart" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_remarks"><i class="fas fa-comment-alt"></i> Remarks section</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_remarks" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_signatures"><i class="fas fa-signature"></i> Signature column</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_signatures" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        {{-- ── GROUP: Footer ── --}}
                        <div class="cp-group-label"><i class="fas fa-shoe-prints"></i> Footer</div>

                        <div class="cp-check-row">
                            <label for="cb_show_footer_timestamp"><i class="fas fa-clock"></i> Generation timestamp</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_footer_timestamp" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        <div class="cp-check-row">
                            <label for="cb_show_confidential"><i class="fas fa-lock"></i> CONFIDENTIAL stamp</label>
                            <label class="cp-switch">
                                <input type="checkbox" id="cb_show_confidential" class="cp-toggle-cb" checked onchange="updateSummary()">
                                <span class="cp-switch-slider"></span>
                            </label>
                        </div>

                        {{-- Save as default for classes --}}
                        {{-- Save as default for classes --}}
<div class="cp-group-label"><i class="fas fa-save"></i> Save Customisation</div>
<div class="px-1 pb-2">
    <div class="text-muted mb-2" style="font-size:.72rem;">
        <i class="fas fa-info-circle me-1"></i>
        Click on classes below to select/unselect them. This setup will be saved and applied automatically every time their passlips are printed.
    </div>
    
    {{-- Class Selection Chips --}}
    <div id="cpClassSelector" class="mb-2" style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem; background: #f8fafc; border-radius: 12px; min-height: 60px; border: 2px solid #e2e8f0; transition: all 0.3s ease;">
        @foreach ($examClasses->unique('class_id') as $ec)
            @php
                $className = Helper::recordMdname($ec->class_id);
                $classId = $ec->class_id;
            @endphp
            <div class="cp-class-chip" 
                 data-class-id="{{ $classId }}"
                 onclick="toggleClassChip(this)"
                 style="
                     padding: 0.5rem 1rem;
                     border-radius: 20px;
                     font-size: .8rem;
                     font-weight: 600;
                     cursor: pointer;
                     transition: all 0.2s ease;
                     background: #fff;
                     border: 2px solid #e2e8f0;
                     color: #475569;
                     user-select: none;
                     display: inline-flex;
                     align-items: center;
                     gap: 0.5rem;
                     box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                 "
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';"
                 onmouseout="if(!this.classList.contains('selected')){this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.04)';}">
                <span>{{ $className }}</span>
                <span class="cp-chip-icon" style="font-size: .6rem; opacity: 0.5; transition: all 0.2s;">+</span>
            </div>
        @endforeach
    </div>

    {{-- Hidden select to store selected classes --}}
    <select id="cpClassSelect" name="selected_classes[]" style="display: none;" multiple>
        @foreach ($examClasses->unique('class_id') as $ec)
            <option value="{{ $ec->class_id }}">{{ Helper::recordMdname($ec->class_id) }}</option>
        @endforeach
    </select>

    {{-- Selection controls --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
        <span id="cpSelectedCount" style="font-size: .75rem; color: #94a3b8;">
            <i class="fas fa-check-circle" style="color: #2f2ccb;"></i>
            <span id="cpSelectedCountText">0</span> class(es) selected
        </span>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" onclick="selectAllClasses()" 
                    style="background: none; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.3rem 0.8rem; font-size: .7rem; color: #475569; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.borderColor='#2f2ccb'; this.style.color='#2f2ccb';"
                    onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569';">
                <i class="fas fa-check-double"></i> Select All
            </button>
            <button type="button" onclick="deselectAllClasses()"
                    style="background: none; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.3rem 0.8rem; font-size: .7rem; color: #475569; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.borderColor='#dc2626'; this.style.color='#dc2626';"
                    onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#475569';">
                <i class="fas fa-times"></i> Clear All
            </button>
        </div>
    </div>

    <button type="button" class="cp-btn-sm w-100" onclick="previewDesign()"
        style="font-size:.75rem;padding:.5rem 1rem;background:#fff;color:#2f2ccb;border:1.5px solid #2f2ccb;border-radius:10px;transition:all 0.2s;font-weight:600;margin-bottom:.5rem;"
        onmouseover="this.style.background='rgba(47,44,203,.06)';"
        onmouseout="this.style.background='#fff';">
        <i class="fas fa-eye me-1"></i> Live preview this design
    </button>

    <button type="button" class="cp-btn-sm w-100" onclick="savePassslipCustomisation()"
        style="font-size:.75rem;padding:.5rem 1rem;background:linear-gradient(135deg, #1e1b4b, #2f2ccb);color:#fff;border:none;border-radius:10px;transition:all 0.2s;font-weight:600;"
        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 16px rgba(47,44,203,0.3)';"
        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
        <i class="fas fa-save me-1"></i> Save for selected class(es)
    </button>
    <div id="cpSaveStatus" class="text-center mt-2" style="font-size:.72rem;"></div>
</div>

<style>
    .cp-class-chip.selected {
        background: linear-gradient(135deg, #2f2ccb 0%, #4338ca 100%) !important;
        color: #fff !important;
        border-color: #2f2ccb !important;
        box-shadow: 0 4px 16px rgba(47, 44, 203, 0.25) !important;
        transform: translateY(-2px);
    }
    
    .cp-class-chip.selected .cp-chip-icon {
        opacity: 1 !important;
        color: #fff !important;
    }
    
    .cp-class-chip.selected .cp-chip-icon::before {
        content: "✓" !important;
        font-weight: 700;
    }
    
    .cp-class-chip .cp-chip-icon::before {
        content: "+";
        font-weight: 700;
    }
    
    .cp-class-chip.selected .cp-chip-icon::before {
        content: "✓";
    }
    
    #cpClassSelector:focus-within {
        border-color: #2f2ccb;
        box-shadow: 0 0 0 3px rgba(47, 44, 203, 0.1);
    }

    .cp-class-chip {
        animation: chipFadeIn 0.2s ease-out;
    }

    @keyframes chipFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

                        {{-- Reset link --}}
                        <div class="text-center mt-3">
                            <button class="cp-btn-sm" onclick="resetCustomisation()" style="font-size:.72rem;padding:.3rem 1rem;">
                                <i class="fas fa-undo me-1"></i> Reset to defaults
                            </button>
                        </div>

                    </div>{{-- /.custom-panel-body --}}
                </div>{{-- /.custom-panel --}}

                {{-- ┌──────────────────────────────────┐
                     │  Bulk Print (all students)       │
                     └──────────────────────────────────┘ --}}
                <div class="ps-section-card mb-4">
                    <div class="ps-section-header">
                        <div class="ps-section-icon"><i class="fas fa-print"></i></div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem;color:#1e1b4b;">Bulk Print</div>
                            <div class="text-muted" style="font-size:.78rem;">Print all students at once</div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="d-flex align-items-start gap-2 mb-4 p-3"
                            style="background:var(--brand-ultra);border-radius:var(--radius-md);border-left:3px solid var(--brand);">
                            <i class="fas fa-info-circle" style="color:var(--brand);font-size:.9rem;margin-top:.1em;"></i>
                            <div class="small text-muted" style="font-size:.8rem;line-height:1.4;">
                                &nbsp;Generate pass slips for every student across all
                                <strong>{{ $examClasses->count() }}</strong> class(es) in this examination.
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4 pb-1">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-1"
                                    style="background:var(--brand-pale);width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-layer-group" style="color:var(--brand);font-size:.7rem;"></i>
                                </div>
                                <span class="small fw-semibold text-muted">&nbsp;&nbsp;{{ $examClasses->count() }} Classes</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-1"
                                    style="background:var(--brand-pale);width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-users" style="color:var(--brand);font-size:.7rem;"></i>
                                </div>
                                <span class="small fw-semibold text-muted">&nbsp;&nbsp;{{ $allStudents->count() }} Students</span>
                            </div>
                        </div>

                        {{-- Print All button — href built by JS --}}
                        <a id="btnPrintAll"
                           href="{{ route('examination.passslips.all', $exam->id) }}"
                           class="print-btn w-100 justify-content-center"
                           onclick="showLoading('Generating all pass slips…')"
                           style="padding:.9rem;font-weight:600;">
                            <i class="fas fa-print me-2"></i>
                            Print All Pass Slips
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>

                {{-- ┌──────────────────────────────────┐
                     │  Per-Class Print                 │
                     └──────────────────────────────────┘ --}}
                <div class="ps-section-card">
                    <div class="ps-section-header">
                        <div class="ps-section-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem;color:#1e1b4b;">By Class</div>
                            <div class="text-muted" style="font-size:.78rem;">Select a class to print all students</div>
                        </div>
                    </div>

                    <div class="p-3">
                        @if($examClasses->count() > 0)
                            <div class="mb-3 pb-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted" style="font-size:.7rem;">
                                        <i class="fas fa-graduation-cap me-1"></i>{{ $examClasses->count() }} Class(es)
                                    </span>
                                    <span class="text-muted" style="font-size:.65rem;">
                                        <i class="fas fa-hand-pointer me-1"></i> Click to generate
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3">
                                @foreach($examClasses as $index => $ec)
                                    @php
                                        $className   = Helper::recordMdname($ec->class_id);
                                        $streamLabel = $ec->stream_id ? ' – ' . $ec->stream_id : '';
                                        $studentCount= DB::table('students')
                                            ->where('school_id', Session('LoggedSchool'))
                                            ->where('senior',    $ec->class_id)
                                            ->where('stream',    $ec->stream_id)
                                            ->count();
                                        $safeStream  = $ec->stream_id ?? '';
                                        $formId      = 'classForm_' . $ec->class_id . '_' . $safeStream;
                                        $gradients   = ['brand','brand-mid','brand-light'];
                                    @endphp
                                    <div class="col-12 mb-2">
                                        <button class="class-tile w-100 text-start p-3"
                                                onclick="printClass('{{ $formId }}', this)">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="class-tile-icon flex-shrink-0 d-flex align-items-center justify-content-center"
                                                    style="width:42px;height:42px;background:linear-gradient(135deg,var(--{{ $gradients[$index % 3] }}),var(--brand-mid));border-radius:12px;">
                                                    <i class="fas fa-graduation-cap" style="font-size:1.2rem;color:white;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                                        <div class="fw-bold" style="font-size:.95rem;color:#1e1b4b;line-height:1.3;">
                                                            &nbsp;&nbsp;{{ $className }}{{ $streamLabel }}
                                                        </div>
                                                        <span class="badge"
                                                            style="background:var(--brand-pale);color:var(--brand);font-size:.7rem;padding:.25rem .8rem;border-radius:20px;">
                                                            <i class="fas fa-user-graduate me-1"></i>&nbsp;&nbsp;{{ $studentCount }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="text-primary d-inline-flex align-items-center gap-1"
                                                            style="font-size:.7rem;font-weight:500;opacity:.8;">
                                                            <i class="fas fa-print"></i>&nbsp;&nbsp; Print class
                                                            <i class="fas fa-arrow-right mt-1 ms-1" style="font-size:.65rem;"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>

                                        {{-- Hidden GET form for this class --}}
                                        <form id="{{ $formId }}"
                                              action="{{ route('examination.passslips.class', $exam->id) }}"
                                              method="GET" target="_blank" style="display:none;">
                                            <input type="hidden" name="class_id"  value="{{ $ec->class_id }}">
                                            <input type="hidden" name="stream_id" value="{{ $safeStream }}">
                                            {{-- customisation fields injected by JS --}}
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                        style="width:48px;height:48px;background:var(--brand-ultra);">
                                        <i class="fas fa-chalkboard-teacher fs-5" style="color:var(--brand-pale);"></i>
                                    </div>
                                </div>
                                <h6 class="text-muted mb-1" style="font-weight:600;font-size:.85rem;">No Classes Assigned</h6>
                                <p class="text-muted small mb-0" style="font-size:.7rem;">
                                    This examination doesn't have any classes assigned yet.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>{{-- /col-lg-4 (left) --}}

            {{-- ═══════════ RIGHT COLUMN ═══════════ --}}
            <div class="col-lg-8">
                <div class="ps-section-card h-100">
                    <div class="ps-section-header">
                        <div class="ps-section-icon"><i class="fas fa-user-graduate"></i></div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem;color:#1e1b4b;">Individual Student</div>
                            <div class="text-muted" style="font-size:.78rem;">Search & print one student's slip</div>
                        </div>
                    </div>

                    <div class="p-4">

                        {{-- Class filter chips --}}
                        <div class="mb-4">
                            <label class="fw-semibold mb-3" style="font-size:.85rem;color:#444;display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
                                <i class="fas fa-filter" style="color:var(--brand);font-size:.8rem;"></i> Filter by Class
                            </label>
                            <div class="d-flex flex-wrap" id="classFilterContainer" style="gap:.75rem;">
                                <button class="class-filter-chip active" data-class="all" onclick="filterClass('all', this)">
                                    <i class="fas fa-globe" style="margin-right:.3rem;"></i> All Classes
                                </button>
                                @foreach($examClasses as $ec)
                                    <button class="class-filter-chip"
                                            data-class="{{ $ec->class_id }}_{{ $ec->stream_id }}"
                                            onclick="filterClass('{{ $ec->class_id }}_{{ $ec->stream_id }}', this)">
                                        <i class="fas fa-chalkboard-user" style="margin-right:.3rem;"></i>
                                        {{ Helper::recordMdname($ec->class_id) }}
                                        {{ $ec->stream_id ? '– ' . $ec->stream_id : '' }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Search box --}}
                        <div class="student-search-wrap mb-4">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="studentSearch"
                                placeholder="Search by name, admission number, or class…"
                                oninput="filterStudents(this.value)" autocomplete="off">
                            @if(count($allStudents) > 0)
                                <span class="search-stats" id="searchStats">{{ count($allStudents) }} students</span>
                            @endif
                        </div>

                        {{-- Student list --}}
                        <div class="student-list-container">
                            <div class="student-list-header">
                                <span><i class="fas fa-users me-1"></i> Student Directory</span>
                                <span class="student-count-badge" id="studentCount">{{ count($allStudents) }}</span>
                            </div>

                            <div class="student-list" id="studentList">
                                @php
                                    // Re-collect allStudents for the list (already passed from controller)
                                    $listStudents = collect();
                                    foreach ($examClasses as $ec) {
                                        $batch = DB::table('students')
                                            ->where('school_id', Session('LoggedSchool'))
                                            ->where('senior', $ec->class_id)
                                            ->where('stream', $ec->stream_id)
                                            ->orderBy('lastname')
                                            ->get()
                                            ->map(function ($s) use ($ec) {
                                                $s->class_id  = $ec->class_id;
                                                $s->stream_id = $ec->stream_id;
                                                return $s;
                                            });
                                        $listStudents = $listStudents->merge($batch);
                                    }
                                    $listStudents = $listStudents->sortBy('lastname');
                                @endphp

                                @forelse($listStudents as $index => $student)
                                    @php
                                        $initials = strtoupper(substr($student->lastname, 0, 1) . substr($student->firstname, 0, 1));
                                        $fullName = $student->lastname . ' ' . $student->firstname;
                                        if (property_exists($student, 'other_names') && $student->other_names) {
                                            $fullName .= ' ' . $student->other_names;
                                        }
                                    @endphp
                                    {{-- href is updated by JS with customisation params --}}
                                    <a href="{{ route('examination.passslips.student', [$exam->id, $student->id]) }}"
                                       class="student-card student-link"
                                       data-base-href="{{ route('examination.passslips.student', [$exam->id, $student->id]) }}"
                                       data-name="{{ strtolower($fullName) }}"
                                       data-adm="{{ strtolower($student->adm_no ?? '') }}"
                                       data-class="{{ $student->class_id }}_{{ $student->stream_id }}"
                                       onclick="showLoading('Generating pass slip for {{ addslashes($student->firstname) }}…')"
                                       target="_blank">

                                        <div class="student-card-avatar"
                                            style="background:linear-gradient(135deg,{{ $index % 2 == 0 ? 'var(--brand)' : 'var(--brand-mid)' }},var(--brand-light));">
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
                                                        <i class="fas fa-id-card"></i>{{ $student->adm_no }}
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

                                <div id="noResultsMsg" class="empty-state" style="display:none;">
                                    <i class="fas fa-search"></i>
                                    <h4>No Matching Students</h4>
                                    <p>Try adjusting your search or class filter to find the student you're looking for.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>{{-- /col-lg-8 --}}

        </div>{{-- /.row --}}
    </div>{{-- /.side-app --}}
     </div>
                    </div>
                </div>

    {{-- ═══════════════════════════════════════════════════════════
         JAVASCRIPT
    ═══════════════════════════════════════════════════════════ --}}
    <script>
    /* ─────────────────────────────────────────────
       CUSTOMISATION STATE
       All settings collected here; serialised to
       URL query-params before every navigation.
    ───────────────────────────────────────────── */
    const DEFAULTS = {
        template:            'classic',
        accent:              '#f0a500',
        exam_ids:            '',
        avg_exam_ids:        '{{ $exam->id }}',
        show_multi_average:  true,
        show_border:         true,
        show_watermark:      true,
        show_logo:           true,
        show_arabic:         true,
        show_motto:          true,
        show_contact:        true,
        show_photo:          true,
        show_minichart:      true,
        show_qr:             true,
        show_rank:           true,
        show_total_score:    true,
        show_average:        true,
        show_result:         true,
        show_score_col:      true,
        show_dev:            true,
        show_grade_pill:     true,
        show_comment_col:    true,
        show_teacher_col:    true,
        show_totals_row:     true,
        show_perf_chart:     true,
        show_remarks:        true,
        show_signatures:     true,
        show_footer_timestamp: true,
        show_confidential:   true,
    };

    let currentSettings = { ...DEFAULTS };

    /* ── Design template selection ──
       Switching template never touches the toggles/accent above it —
       it's a pure presentation swap on top of the same settings. ── */
    function selectTemplate(key, el) {
        currentSettings.template = key;
        document.querySelectorAll('.cp-template-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        updateSummary();
    }

    function setTemplateSelectionUI(key) {
        const card = document.querySelector('.cp-template-card[data-template="' + key + '"]');
        if (!card) return;
        document.querySelectorAll('.cp-template-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
    }

    /* ── Read toggles from DOM → currentSettings ── */
    function readSettings() {
        document.querySelectorAll('.cp-toggle-cb').forEach(cb => {
            currentSettings[cb.id.replace('cb_', '')] = cb.checked;
        });
        currentSettings.accent = document.getElementById('cpColorPicker').value;
        const selectedTplCard = document.querySelector('.cp-template-card.selected');
        currentSettings.template = selectedTplCard ? selectedTplCard.dataset.template : 'classic';

        // Combine-examinations selection: extra exam ids + which ids to average
        const extraExamIds = Array.from(document.querySelectorAll('.exam-combine-cb:checked')).map(cb => cb.value);
        const avgExamIds = Array.from(document.querySelectorAll('.exam-avg-cb:checked')).map(cb => cb.value);
        currentSettings.exam_ids = extraExamIds.join(',');
        currentSettings.avg_exam_ids = avgExamIds.join(',');
    }

    /* ── When an "extra examination" checkbox is toggled, enable/disable
           its paired "include in average" switch and auto-check it ── */
    function onExamComboChange(cb) {
        const avgCb = document.getElementById('cb_avg_' + cb.value);
        if (avgCb) {
            avgCb.disabled = !cb.checked;
            if (cb.checked) {
                avgCb.checked = true;
            } else {
                avgCb.checked = false;
            }
        }
        updateSummary();
    }

    /* ── Build query-string from currentSettings ── */
function buildQS() {
    // Deliberately NOT including currentSettings here anymore. Those
    // toggles reflect whichever class was last loaded into the panel —
    // broadcasting them into every student/class link meant printing P1
    // would silently use whatever was loaded for Baby Class, etc. Each
    // print now resolves its OWN class's saved settings server-side
    // (applySavedPassslipSettings); lang is the only setting that's
    // genuinely a page-wide preference rather than per-class.
    const p = new URLSearchParams();
    const currentLang = new URLSearchParams(window.location.search).get('lang') || 'en';
    p.set('lang', currentLang);
    return p.toString();
}

function injectIntoForm(formEl) {
    // Same reasoning as buildQS() above: don't force the panel's
    // currently-loaded toggle state onto whichever class tile was
    // clicked. Leave settings out entirely so the server resolves that
    // specific class's own saved profile.
    formEl.querySelectorAll('.cp-injected').forEach(i => i.remove());
    // Add lang
    const langInp = document.createElement('input');
    langInp.type = 'hidden';
    langInp.name = 'lang';
    langInp.value = new URLSearchParams(window.location.search).get('lang') || 'en';
    langInp.classList.add('cp-injected');
    formEl.appendChild(langInp);
}

    /* ── Update ALL student links + Print All href ── */
    function updateAllLinks() {
        const qs = buildQS();

        // Print-All button
        const btnAll = document.getElementById('btnPrintAll');
        if (btnAll) {
            const base = btnAll.href.split('?')[0];
            btnAll.href = base + '?' + qs;
        }

        // Student individual links
        document.querySelectorAll('.student-link').forEach(a => {
            const base = a.dataset.baseHref;
            a.href = base + '?' + qs;
        });
    }

    /* ── Colour change ── */
    function onColorChange(val) {
        currentSettings.accent = val;
        document.getElementById('cpAccentPreview').style.background = val;
        // sync preset dots
        document.querySelectorAll('.cp-preset-dot').forEach(d => {
            d.classList.toggle('active', d.dataset.color === val);
        });
        updateAllLinks();
        updateSummary();
    }

    function applyPreset(hex, el) {
        document.getElementById('cpColorPicker').value = hex;
        onColorChange(hex);
    }

    /* ── Check all / none ── */
    function setAllChecks(state) {
        document.querySelectorAll('.cp-toggle-cb').forEach(cb => { cb.checked = state; });
        updateAllLinks();
        updateSummary();
    }

    /* ── Reset to defaults ── */
    function resetCustomisation() {
        currentSettings = { ...DEFAULTS };
        setTemplateSelectionUI(DEFAULTS.template);
        document.getElementById('cpColorPicker').value = DEFAULTS.accent;
        document.getElementById('cpAccentPreview').style.background = DEFAULTS.accent;
        document.querySelectorAll('.cp-toggle-cb').forEach(cb => {
            const key = cb.id.replace('cb_', '');
            cb.checked = DEFAULTS[key] !== false;
        });
        document.querySelectorAll('.cp-preset-dot').forEach(d => {
            d.classList.toggle('active', d.dataset.color === DEFAULTS.accent);
        });
        // Combine-examinations: uncheck all extras, disable their averages
        document.querySelectorAll('.exam-combine-cb').forEach(cb => { cb.checked = false; });
        document.querySelectorAll('.exam-avg-cb').forEach(cb => {
            if (!cb.id.startsWith('cb_avg_base_')) {
                cb.checked = false;
                cb.disabled = true;
            }
        });
        updateAllLinks();
        updateSummary();
    }

    /* ── Open a real, fully-rendered preview of the currently selected
       template + toggles + accent — in a new tab, against live data,
       without saving anything yet. This is the ONE place the full
       currentSettings deliberately IS broadcast via query-string,
       since it's an explicit one-off "show me" action, not a link
       every student/class tile inherits. ── */
    function previewDesign() {
        readSettings();
        const p = new URLSearchParams();
        Object.entries(currentSettings).forEach(([k, v]) => {
            if (k === 'exam_ids' || k === 'avg_exam_ids') return; // panel-local, not a slip toggle
            p.set(k, typeof v === 'boolean' ? (v ? '1' : '0') : v);
        });
        p.set('lang', new URLSearchParams(window.location.search).get('lang') || 'en');
        const url = '{{ route('examination.passslips.all', $exam->id) }}?' + p.toString();
        window.open(url, '_blank');
    }

    /* ── Persist current toggle state for one or more classes ──
       (e.g. Nursery / Kindergarten sharing one saved profile) so it
       survives a page refresh instead of resetting to defaults. */
    function savePassslipCustomisation() {
        const classSelect = document.getElementById('cpClassSelect');
        const classIds = Array.from(classSelect.selectedOptions).map(o => parseInt(o.value, 10));
        const statusEl = document.getElementById('cpSaveStatus');

        if (classIds.length === 0) {
            statusEl.style.color = '#c0392b';
            statusEl.textContent = 'Select at least one class first.';
            return;
        }

        readSettings();
        statusEl.style.color = '#666';
        statusEl.textContent = 'Saving…';

        fetch('{{ route('examination.passslips.settings.save', $exam->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                class_ids: classIds,
                settings: currentSettings,
            }),
        })
            .then(r => r.json())
            .then(res => {
                statusEl.style.color = res.success ? '#1a7a4a' : '#c0392b';
                statusEl.textContent = res.success
                    ? 'Saved for ' + classIds.length + ' class(es). ✓'
                    : (res.message || 'Failed to save.');
            })
            .catch(() => {
                statusEl.style.color = '#c0392b';
                statusEl.textContent = 'Failed to save — check your connection.';
            });
    }

    /* ── Load a class's saved settings into the panel for review ──
       Fires when the teacher/admin picks a class in the "Save
       Customisation" selector, so they can see what's already saved
       (if anything) before adjusting and re-saving. */
    function loadPassslipCustomisation() {
        const classSelect = document.getElementById('cpClassSelect');
        const firstSelected = classSelect.selectedOptions[0];
        if (!firstSelected) return;

        fetch('{{ route('examination.passslips.settings.get', $exam->id) }}?class_id=' + firstSelected.value, {
            headers: { 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.settings || Object.keys(res.settings).length === 0) {
                    return; // nothing saved yet for this class — leave panel as-is
                }
                const saved = res.settings;
                setTemplateSelectionUI(saved.template || 'classic');
                if (saved.accent) {
                    document.getElementById('cpColorPicker').value = saved.accent;
                    document.getElementById('cpAccentPreview').style.background = saved.accent;
                    document.querySelectorAll('.cp-preset-dot').forEach(d => {
                        d.classList.toggle('active', d.dataset.color === saved.accent);
                    });
                }
                document.querySelectorAll('.cp-toggle-cb').forEach(cb => {
                    const key = cb.id.replace('cb_', '');
                    if (key in saved) cb.checked = !!saved[key];
                });

                // Restore combine-examinations selection
                if (typeof saved.exam_ids === 'string') {
                    const extraIds = saved.exam_ids.split(',').filter(Boolean);
                    document.querySelectorAll('.exam-combine-cb').forEach(cb => {
                        cb.checked = extraIds.includes(cb.value);
                        const avgCb = document.getElementById('cb_avg_' + cb.value);
                        if (avgCb) avgCb.disabled = !cb.checked;
                    });
                }
                if (typeof saved.avg_exam_ids === 'string') {
                    const avgIds = saved.avg_exam_ids.split(',').filter(Boolean);
                    document.querySelectorAll('.exam-avg-cb').forEach(cb => {
                        if (!cb.disabled) cb.checked = avgIds.includes(cb.value);
                    });
                }

                updateAllLinks();
                updateSummary();
            })
            .catch(() => { /* silent — keep current panel state */ });
    }
    document.addEventListener('DOMContentLoaded', () => {
        const classSelect = document.getElementById('cpClassSelect');
        if (classSelect) classSelect.addEventListener('change', loadPassslipCustomisation);
    });

    /* ── Summary badge (count enabled features) ── */
    function updateSummary() {
        const total   = document.querySelectorAll('.cp-toggle-cb').length;
        const enabled = document.querySelectorAll('.cp-toggle-cb:checked').length;
        const el      = document.getElementById('cpSummary');
        if (el) el.textContent = enabled + '/' + total + ' on';
        updateAllLinks();
    }

    /* ── Panel collapse/expand ── */
    function toggleCustomPanel() {
        const body    = document.getElementById('cpBody');
        const header  = document.getElementById('cpToggleHeader');
        const chevron = document.getElementById('cpChevron');
        const hidden  = body.style.display === 'none';
        body.style.display  = hidden ? '' : 'none';
        header.classList.toggle('collapsed', !hidden);
        chevron.style.transform = hidden ? '' : 'rotate(-90deg)';
    }

    /* ── printClass: inject settings then submit ── */
    function printClass(formId, btn) {
        document.querySelectorAll('.class-tile').forEach(t => t.classList.remove('selected'));
        if (btn) btn.classList.add('selected');
        showLoading('Generating class pass slips…');
        const form = document.getElementById(formId);
        if (!form) {
            document.getElementById('loadingOverlay').classList.remove('active');
            alert('Error: form not found.');
            return;
        }
        injectIntoForm(form);
        form.submit();
    }

    /* ── Loading overlay ── */
    function showLoading(msg) {
        document.getElementById('loadingText').textContent = msg ?? 'Generating…';
        document.getElementById('loadingOverlay').classList.add('active');
        setTimeout(() => document.getElementById('loadingOverlay').classList.remove('active'), 5000);
    }

    /* ── Student list filter ── */
    let activeClassFilter = 'all';

    function filterClass(key, btn) {
        activeClassFilter = key;
        document.querySelectorAll('.class-filter-chip').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilters();
    }

    function filterStudents(q) { applyFilters(q); }

    function applyFilters(q) {
        q = (q ?? document.getElementById('studentSearch').value).toLowerCase().trim();
        const cards = document.querySelectorAll('.student-card');
        let visible = 0;

        cards.forEach(card => {
            const nameMatch  = card.dataset.name.includes(q);
            const admMatch   = card.dataset.adm && card.dataset.adm.includes(q);
            const classMatch = activeClassFilter === 'all' || card.dataset.class === activeClassFilter;
            const show = (nameMatch || admMatch) && classMatch;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const ss = document.getElementById('searchStats');
        if (ss) ss.textContent = q ? `${visible} of ${cards.length} students` : `${cards.length} students`;

        const nr = document.getElementById('noResultsMsg');
        if (nr) nr.style.display = visible === 0 ? 'flex' : 'none';

        const sc = document.getElementById('studentCount');
        if (sc) sc.textContent = visible;
    }

    /* ── Init ── */
    document.addEventListener('DOMContentLoaded', function () {
        updateSummary();   // sets summary badge & links on first load

        // Whenever any toggle changes → update links
        document.querySelectorAll('.cp-toggle-cb').forEach(cb => {
            cb.addEventListener('change', updateAllLinks);
        });
    });

        document.addEventListener('DOMContentLoaded', function() {
        const body = document.getElementById('cpBody');
        const chevron = document.getElementById('cpChevron');
        if (body && chevron) {
            body.style.display = 'none';
            chevron.style.transform = 'rotate(-90deg)';
        }
    });

/* ─────────────────────────────────────────────
   CLASS CHIP SELECTION
   ───────────────────────────────────────────── */
function toggleClassChip(element) {
    element.classList.toggle('selected');
    updateSelectedCount();
    updateHiddenSelect();
    loadPassslipCustomisation();
    
    // Auto-save selected classes to localStorage
    const selected = getSelectedClassIds();
    localStorage.setItem('cpSelectedClasses', JSON.stringify(selected));
}

function getSelectedClassIds() {
    const selected = [];
    document.querySelectorAll('.cp-class-chip.selected').forEach(chip => {
        selected.push(chip.dataset.classId);
    });
    return selected;
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.cp-class-chip.selected').length;
    const countText = document.getElementById('cpSelectedCountText');
    if (countText) countText.textContent = count;
}

function updateHiddenSelect() {
    const select = document.getElementById('cpClassSelect');
    if (!select) return;
    
    // Clear all selections
    Array.from(select.options).forEach(opt => opt.selected = false);
    
    // Select only the selected chips
    document.querySelectorAll('.cp-class-chip.selected').forEach(chip => {
        Array.from(select.options).forEach(opt => {
            if (opt.value === chip.dataset.classId) {
                opt.selected = true;
            }
        });
    });
}

function selectAllClasses() {
    document.querySelectorAll('.cp-class-chip').forEach(chip => {
        chip.classList.add('selected');
    });
    updateSelectedCount();
    updateHiddenSelect();
    loadPassslipCustomisation();
    const selected = getSelectedClassIds();
    localStorage.setItem('cpSelectedClasses', JSON.stringify(selected));
}

function deselectAllClasses() {
    document.querySelectorAll('.cp-class-chip').forEach(chip => {
        chip.classList.remove('selected');
    });
    updateSelectedCount();
    updateHiddenSelect();
    localStorage.setItem('cpSelectedClasses', JSON.stringify([]));
}

// ─────────────────────────────────────────────
// MODIFIED savePassslipCustomisation
// ─────────────────────────────────────────────
const originalSavePassslip = savePassslipCustomisation;
savePassslipCustomisation = function() {
    const selectedClasses = getSelectedClassIds();
    const statusEl = document.getElementById('cpSaveStatus');
    
    if (selectedClasses.length === 0) {
        if (statusEl) {
            statusEl.style.color = '#c0392b';
            statusEl.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Please select at least one class.';
        }
        return;
    }

    // Update the hidden select before saving
    updateHiddenSelect();
    
    // Call the original save function
    originalSavePassslip.call(this);
};

// ─────────────────────────────────────────────
// INIT - Load saved selections on page load
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    // Load saved selections from localStorage
    const savedClasses = localStorage.getItem('cpSelectedClasses');
    if (savedClasses) {
        try {
            const classIds = JSON.parse(savedClasses);
            document.querySelectorAll('.cp-class-chip').forEach(chip => {
                if (classIds.includes(chip.dataset.classId)) {
                    chip.classList.add('selected');
                }
            });
            updateSelectedCount();
            updateHiddenSelect();
            loadPassslipCustomisation();
        } catch(e) {
            // Silently fail
        }
    }
});
</script>

@endsection