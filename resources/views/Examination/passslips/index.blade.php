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

                        {{-- ── GROUP: Marks Table ── --}}
                        <div class="cp-group-label"><i class="fas fa-table"></i> Marks Table</div>

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
        accent:              '#f0a500',
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
        show_dev:            true,
        show_grade_pill:     true,
        show_teacher_col:    true,
        show_totals_row:     true,
        show_perf_chart:     true,
        show_remarks:        true,
        show_signatures:     true,
        show_footer_timestamp: true,
        show_confidential:   true,
    };

    let currentSettings = { ...DEFAULTS };

    /* ── Read toggles from DOM → currentSettings ── */
    function readSettings() {
        document.querySelectorAll('.cp-toggle-cb').forEach(cb => {
            currentSettings[cb.id.replace('cb_', '')] = cb.checked;
        });
        currentSettings.accent = document.getElementById('cpColorPicker').value;
    }

    /* ── Build query-string from currentSettings ── */
function buildQS() {
    readSettings();
    const p = new URLSearchParams();
    for (const [k, v] of Object.entries(currentSettings)) {
        p.set(k, typeof v === 'boolean' ? (v ? '1' : '0') : v);
    }
    // Preserve the current language
    const currentLang = new URLSearchParams(window.location.search).get('lang') || 'en';
    p.set('lang', currentLang);
    return p.toString();
}

function injectIntoForm(formEl) {
    readSettings();
    formEl.querySelectorAll('.cp-injected').forEach(i => i.remove());
    for (const [k, v] of Object.entries(currentSettings)) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = k;
        inp.value = typeof v === 'boolean' ? (v ? '1' : '0') : v;
        inp.classList.add('cp-injected');
        formEl.appendChild(inp);
    }
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
        document.getElementById('cpColorPicker').value = DEFAULTS.accent;
        document.getElementById('cpAccentPreview').style.background = DEFAULTS.accent;
        document.querySelectorAll('.cp-toggle-cb').forEach(cb => {
            const key = cb.id.replace('cb_', '');
            cb.checked = DEFAULTS[key] !== false;
        });
        document.querySelectorAll('.cp-preset-dot').forEach(d => {
            d.classList.toggle('active', d.dataset.color === DEFAULTS.accent);
        });
        updateAllLinks();
        updateSummary();
    }

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
        setTimeout(() => document.getElementById('loadingOverlay').classList.remove('active'), 10000);
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

</script>

@endsection