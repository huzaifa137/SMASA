<?php
use App\Http\Controllers\Helper;
?>
{{-- resources/views/Examination/passslips/index.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <style>
        /* ── Design tokens (unchanged from the previous layout — only the
            structure below changed, not the palette) ── */
        :root {
            --brand: #2C29CA;
            --brand-mid: #5351e4;
            --brand-light: #7c7aec;
            --brand-pale: #ede9ff;
            --brand-ultra: #f5f4ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --ink: #1e1b4b;
            --sub: #6b7280;
            --radius-lg: 1.25rem;
            --radius-md: .875rem;
            --radius-sm: .5rem;
            --shadow-card: 0 4px 24px rgba(44, 41, 202, .10);
        }

        * {
            box-sizing: border-box;
        }

        #ps-bento-app {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--ink);
        }

        /* ── Top line: back link + compact language switch ── */
        .ps-topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
            margin-bottom: 1.1rem;
        }

        .ps-back {
            color: var(--sub);
            text-decoration: none;
            font-size: .82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .ps-back:hover {
            color: var(--brand);
        }

        .ps-lang-switch {
            display: flex;
            background: #fff;
            border: 1px solid var(--brand-pale);
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: var(--shadow-card);
        }

        .ps-lang-btn {
            border: none;
            background: none;
            padding: .4rem .9rem;
            font-size: .75rem;
            font-weight: 700;
            color: var(--sub);
            cursor: pointer;
            font-family: inherit;
        }

        .ps-lang-btn.active {
            background: var(--brand);
            color: #fff;
        }

        /* ── Examination picker ── */
        .ps-picker {
            background: #fff;
            border: 1px solid rgba(44, 41, 202, .08);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            padding: 1rem 1.25rem;
            margin-bottom: 1.1rem;
            display: flex;
            align-items: center;
            gap: .9rem;
            flex-wrap: wrap;
        }

        .ps-picker label {
            font-size: .82rem;
            font-weight: 700;
            margin: 0;
            color: var(--ink);
        }

        .ps-picker select {
            flex: 1 1 320px;
            max-width: 560px;
        }

        .ps-empty {
            text-align: center;
            color: var(--sub);
            padding: 3rem 1rem;
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
        }

        .ps-empty i {
            font-size: 2.2rem;
            color: var(--brand-light);
            margin-bottom: .6rem;
        }

        /* ── Title + KPI strip ── */
        .ps-title-row {
            margin-bottom: 1.1rem;
        }

        .ps-title-row h1 {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0 0 .25rem;
            display: flex;
            align-items: center;
            gap: .55rem;
        }

        .ps-title-row .exam-name {
            font-size: .85rem;
            color: var(--sub);
        }

        .status-pill {
            padding: .3rem .85rem;
            border-radius: 99px;
            font-size: .7rem;
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

        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .9rem;
            margin-bottom: 1.15rem;
        }

        @media (max-width: 900px) {
            .kpi-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .kpi {
            background: #fff;
            border-radius: 1rem;
            padding: 1rem 1.1rem;
            display: flex;
            align-items: center;
            gap: .8rem;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(44, 41, 202, .06);
        }

        .kpi .ic {
            width: 40px;
            height: 40px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .kpi .v {
            font-weight: 800;
            font-size: 1.05rem;
            line-height: 1.1;
        }

        .kpi .l {
            font-size: .66rem;
            color: var(--sub);
            margin-top: .2rem;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        /* ── Bento grid: an asymmetric dashboard instead of a left
            sidebar + right column split — the Student Directory owns the
            visual weight the old page spent across four stacked cards. ── */
        .ps-bento {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            grid-template-rows: auto auto;
            gap: 1.1rem;
        }

        @media (max-width: 992px) {
            .ps-bento {
                grid-template-columns: 1fr;
            }
        }

        .ps-cell {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(44, 41, 202, .08);
            padding: 1.35rem;
        }

        .ps-cell-directory {
            grid-row: 1 / 3;
        }

        .ps-cell h3 {
            margin: 0 0 .2rem;
            font-size: 1rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .ps-cell h3 i {
            color: var(--brand);
        }

        .ps-cell .desc {
            font-size: .78rem;
            color: var(--sub);
            margin: 0 0 1rem;
        }

        /* ── Directory: search + class filter chips + list ── */
        .student-search-wrap {
            position: relative;
            margin-bottom: .85rem;
        }

        .student-search-wrap .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
            pointer-events: none;
        }

        #studentSearch {
            width: 100%;
            padding: .8rem 1rem .8rem 2.7rem;
            border: 2px solid var(--brand-pale);
            border-radius: 1rem;
            font-size: .88rem;
            font-family: inherit;
            background: white;
        }

        #studentSearch:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .1);
        }

        .class-filter-chip {
            padding: .35rem .9rem;
            border-radius: 99px;
            font-size: .74rem;
            font-weight: 600;
            border: 1.5px solid var(--brand-pale);
            background: white;
            color: #4a5568;
            cursor: pointer;
            transition: all .15s ease;
        }

        .class-filter-chip:hover {
            border-color: var(--brand-light);
            background: var(--brand-ultra);
        }

        .class-filter-chip.active {
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            border-color: var(--brand);
            color: white;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1rem;
        }

        .student-list-container {
            border: 1.5px solid var(--brand-pale);
            border-radius: 1rem;
            overflow: hidden;
        }

        .student-list-header {
            background: var(--brand-ultra);
            padding: .7rem 1rem;
            border-bottom: 1.5px solid var(--brand-pale);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: .78rem;
            color: var(--brand);
        }

        .student-count-badge {
            background: var(--brand);
            color: white;
            padding: .2rem .65rem;
            border-radius: 1rem;
            font-size: .68rem;
            font-weight: 700;
        }

        .student-list {
            max-height: 480px;
            overflow-y: auto;
        }

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

        .student-card {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: .8rem 1rem;
            border-bottom: 1px solid #f2f2f7;
            text-decoration: none;
            transition: all .15s ease;
            cursor: pointer;
            background: white;
        }

        .student-card:hover {
            background: var(--brand-ultra);
        }

        .student-card:last-child {
            border-bottom: none;
        }

        .student-card-avatar {
            width: 40px;
            height: 40px;
            border-radius: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .8rem;
            color: white;
            flex-shrink: 0;
        }

        .student-card-info {
            flex: 1;
            min-width: 0;
        }

        .student-card-name {
            font-weight: 700;
            font-size: .86rem;
            color: var(--ink);
        }

        .other-names {
            font-weight: 400;
            color: var(--sub);
            font-size: .78rem;
        }

        .student-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            margin-top: .1rem;
        }

        .meta-tag {
            font-size: .68rem;
            color: var(--sub);
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .meta-tag i {
            font-size: .62rem;
            color: var(--brand-light);
        }

        .student-card-action {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem .75rem;
            background: var(--brand-ultra);
            border-radius: .6rem;
            font-size: .68rem;
            font-weight: 700;
            color: var(--brand);
            flex-shrink: 0;
        }

        .student-card:hover .student-card-action {
            background: var(--brand);
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1.5rem;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: .85rem;
            opacity: .5;
        }

        .empty-state h4 {
            font-size: .92rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: .35rem;
        }

        .empty-state p {
            font-size: .8rem;
            margin: 0;
        }

        /* ── Print by Class: compact card grid ── */
        .class-mini-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: .65rem;
        }

        .class-mini {
            border: 1.5px solid var(--brand-pale);
            border-radius: var(--radius-md);
            padding: .8rem .9rem;
            cursor: pointer;
            transition: all .2s ease;
            background: #fff;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .class-mini:hover,
        .class-mini.selected {
            border-color: var(--brand);
            background: linear-gradient(135deg, #fff, var(--brand-ultra));
            box-shadow: 0 6px 18px rgba(44, 41, 202, .12);
        }

        .class-mini .ic {
            width: 38px;
            height: 38px;
            border-radius: .7rem;
            background: linear-gradient(135deg, var(--brand), var(--brand-mid));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .class-mini .body {
            flex: 1;
            min-width: 0;
        }

        .class-mini .name {
            font-weight: 700;
            font-size: .84rem;
        }

        .class-mini .sub {
            font-size: .7rem;
            color: var(--sub);
            margin-top: .1rem;
        }

        .class-mini .go {
            font-size: .7rem;
            font-weight: 700;
            color: var(--brand);
            display: flex;
            align-items: center;
            gap: .3rem;
            flex-shrink: 0;
        }

        .class-empty {
            text-align: center;
            padding: 1.75rem 1rem;
            color: #9ca3af;
        }

        .class-empty i {
            font-size: 2rem;
            opacity: .5;
            margin-bottom: .6rem;
        }

        .class-empty p {
            font-size: .75rem;
            margin: 0;
        }

        /* ── More Actions: stacked link rows ── */
        .stack-btn {
            display: flex;
            align-items: center;
            gap: .65rem;
            width: 100%;
            border: none;
            text-align: left;
            background: var(--brand-ultra);
            border-radius: .85rem;
            padding: .8rem .9rem;
            margin-bottom: .6rem;
            text-decoration: none;
            color: inherit;
        }

        .stack-btn:last-child {
            margin-bottom: 0;
        }

        .stack-btn:hover {
            background: var(--brand-pale);
        }

        .stack-btn .ic {
            width: 34px;
            height: 34px;
            border-radius: .6rem;
            background: #fff;
            color: var(--brand);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stack-btn .t {
            font-weight: 700;
            font-size: .82rem;
            color: var(--ink);
        }

        .stack-btn .d {
            font-size: .7rem;
            color: var(--sub);
        }

        .stack-btn .chev {
            margin-left: auto;
            color: var(--brand);
            font-size: .78rem;
        }

        /* ── Loading overlay (unchanged behaviour) ── */
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

        #loadingText {
            color: var(--brand) !important;
            font-weight: 600;
        }

        @media print {
            body {
                display: none;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
@endsection

@section('content')
    @php
        // "lang" is the one live print-time option this page still controls
        // directly (Arabic/English pass slips) — everything else (design
        // template, accent colour, show/hide toggles, Combine Examinations)
        // now lives entirely on the "Customize this design" page and is
        // persisted per-class via passslip_settings, so it no longer needs
        // duplicate controls here. Baking ?lang= straight into every href/
        // hidden field server-side (below) means printing works correctly
        // even before any JS on this page has run.
        $currentLang = request('lang', 'en');
    @endphp

    <div class="side-app" id="ps-bento-app">

        {{-- Loading Overlay --}}
        <div id="loadingOverlay">
            <div class="text-center">
                <div class="spinner-ring mx-auto mb-3"></div>
                <p class="text-white fw-semibold" style="font-size:.95rem;" id="loadingText">Generating pass slips…</p>
            </div>
        </div>

        {{-- ── Top line ── --}}
        <div class="ps-topline">
            @if(\App\Helpers\PermissionHelper::canFeature('view_exams'))<a href="{{ route('examination.index') }}" class="ps-back btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>@endif
            <div class="ps-lang-switch">
                <button type="button" class="ps-lang-btn {{ $currentLang === 'en' ? 'active' : '' }}"
                    onclick="setLanguage('en')">
                    <i class="fas fa-flag-usa"></i> English
                </button>
                <!-- <button type="button" class="ps-lang-btn {{ $currentLang === 'ar' ? 'active' : '' }}"
                    onclick="setLanguage('ar')">
                    <i class="fas fa-flag"></i> العربية
                </button> -->
            </div>
        </div>

       {{-- ── Examination picker (no exam is required to open this page) ── --}}
<div class="ps-picker">
    <label for="psExamPicker" class="ps-picker-label">
        <span class="ps-picker-icon"><i class="fas fa-file-signature"></i></span>
        <span>Examination</span>
    </label>

    <div class="ps-picker-search">
        <i class="fas fa-search ps-search-icon"></i>
        <input type="text" id="psExamSearch" placeholder="Type to search examinations…"
            autocomplete="off" oninput="filterExamOptions(this)" onkeydown="examSearchKey(event, this)">
        <button type="button" class="ps-search-clear" onclick="clearExamSearch(this)" title="Clear">
            <i class="fas fa-times"></i>
        </button>
        <div class="ps-search-empty">No examination matches your search.</div>
    </div>

    <div class="ps-input-wrap">
        <i class="fas fa-file-signature ps-input-icon"></i>
        <select id="psExamPicker" class="form-control" onchange="selectPassslipExam(this.value)">
            <option value="">-- Select an examination --</option>
            @foreach($examOptions as $opt)
                <option value="{{ $opt['id'] }}" {{ $exam && $exam->id == $opt['id'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
            @endforeach
        </select>
    </div>
</div>

<style>
    /* ── Examination picker ── */
.ps-picker {
    display: flex;
    flex-direction: column;
    gap: .45rem;
    margin-bottom: 1rem;
}
.ps-picker-label {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #5a5a80;
    margin: 0;
}
.ps-picker-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: .4rem;
    background: #eef0ff;
    color: var(--brand, #2C29CA);
    font-size: .6rem;
    flex-shrink: 0;
}

.ps-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.ps-input-icon {
    position: absolute;
    left: .7rem;
    font-size: .78rem;
    color: var(--brand, #2C29CA);
    opacity: .7;
    pointer-events: none;
    z-index: 1;
}

.ps-input-wrap .form-control {
    padding-left: 2.1rem !important;
    padding-right: 2.25rem !important;
    padding-top: .55rem;
    padding-bottom: .55rem;
    border-radius: .55rem !important;
    border: 1.5px solid #c9cdf0 !important;
    background-color: #f3f4ff !important;
    color: #1a1a3a;
    font-size: .85rem;
    font-weight: 600;
    height: auto;
    box-shadow: inset 0 1px 2px rgba(44, 41, 202, .05);
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%232C29CA' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right .7rem center !important;
    background-size: 12px !important;
}
.ps-input-wrap .form-control:hover {
    border-color: #a8adde !important;
    background-color: #eef0ff !important;
}
.ps-input-wrap .form-control:focus {
    border-color: var(--brand, #2C29CA) !important;
    background-color: #fff !important;
    box-shadow: 0 0 0 3px rgba(44, 41, 202, .15);
    outline: none;
}
</style>

<style>
/* ── Examination picker — full-width override + text fix ── */
.ps-picker {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    gap: .5rem !important;
    width: 100% !important;
    padding: 1rem 1.25rem !important;
    background: #fff !important;
    border: 1px solid rgba(44, 41, 202, .08) !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 4px 24px rgba(44, 41, 202, .10) !important;
    margin-bottom: 1.1rem !important;
    flex-wrap: nowrap !important;
}

.ps-picker-label {
    display: inline-flex !important;
    align-items: center !important;
    gap: .45rem !important;
    font-size: .72rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .05em !important;
    color: #5a5a80 !important;
    margin: 0 !important;
    line-height: 1.2 !important;
}

.ps-picker-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 22px !important;
    height: 22px !important;
    border-radius: .4rem !important;
    background: #eef0ff !important;
    color: var(--brand, #2C29CA) !important;
    font-size: .62rem !important;
    flex-shrink: 0 !important;
}

.ps-input-wrap {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
}

.ps-input-icon {
    position: absolute !important;
    left: .8rem !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    font-size: .82rem !important;
    color: var(--brand, #2C29CA) !important;
    opacity: .7 !important;
    pointer-events: none !important;
    z-index: 2 !important;
}

/* Widen to full section + fix clipping */
.ps-input-wrap .form-control {
    width: 100% !important;
    max-width: none !important;
    min-height: 46px !important;
    height: auto !important;
    padding: .65rem 2.6rem .65rem 2.35rem !important;
    line-height: 1.4 !important;
    border-radius: .6rem !important;
    border: 1.5px solid #c9cdf0 !important;
    background-color: #f3f4ff !important;
    color: #1a1a3a !important;
    font-size: .9rem !important;
    font-weight: 600 !important;
    box-shadow: inset 0 1px 2px rgba(44, 41, 202, .05) !important;
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%232C29CA' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right .9rem center !important;
    background-size: 12px !important;
    text-overflow: ellipsis !important;
}

.ps-input-wrap .form-control:hover {
    border-color: #a8adde !important;
    background-color: #eef0ff !important;
}

.ps-input-wrap .form-control:focus {
    border-color: var(--brand, #2C29CA) !important;
    background-color: #fff !important;
    box-shadow: 0 0 0 3px rgba(44, 41, 202, .15) !important;
    outline: none !important;
}

/* Ensure long option labels don't get clipped on some browsers */
.ps-input-wrap select.form-control option {
    padding: .4rem .6rem !important;
    line-height: 1.4 !important;
}

/* Small screens: keep comfortable height, allow wrap-safe text */
@media (max-width: 575.98px) {
    .ps-picker { padding: .9rem 1rem !important; }
    .ps-input-wrap .form-control {
        font-size: .85rem !important;
        padding: .6rem 2.4rem .6rem 2.2rem !important;
    }
}

/* ── Inline search inside the exam picker ── */
.ps-picker-search {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    margin-top: .15rem;
}
.ps-picker-search .ps-search-icon {
    position: absolute;
    left: .8rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: .8rem;
    color: var(--brand, #2C29CA);
    opacity: .7;
    pointer-events: none;
    z-index: 2;
}
.ps-picker-search input {
    width: 100%;
    padding: .55rem 2.4rem .55rem 2.35rem;
    border-radius: .55rem;
    border: 1.5px solid #dfe2f5;
    background: #fbfbff;
    color: #1a1a3a;
    font-size: .84rem;
    font-weight: 600;
    height: auto;
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}
.ps-picker-search input::placeholder {
    color: #9496b8;
    font-weight: 500;
}
.ps-picker-search input:hover {
    border-color: #a8adde;
    background: #f3f4ff;
}
.ps-picker-search input:focus {
    border-color: var(--brand, #2C29CA);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(44, 41, 202, .15);
    outline: none;
}
.ps-picker-search .ps-search-clear {
    position: absolute;
    right: .6rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: #eef0ff;
    color: #2C29CA;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: .65rem;
    cursor: pointer;
    transition: background .15s ease;
    z-index: 2;
}
.ps-picker-search .ps-search-clear:hover {
    background: #2C29CA;
    color: #fff;
}
.ps-picker-search.has-value .ps-search-clear {
    display: inline-flex;
}
.ps-picker-search .ps-search-empty {
    position: absolute;
    top: calc(100% + .4rem);
    left: 0;
    right: 0;
    padding: .55rem .75rem;
    font-size: .74rem;
    color: #b45309;
    background: #fff8ec;
    border: 1px solid #fbd9a0;
    border-radius: .5rem;
    display: none;
    z-index: 5;
}
.ps-picker-search.no-results .ps-search-empty {
    display: block;
}
</style>
        @if($exam)
        {{-- ── Title ── --}}
        <div class="ps-title-row">
            <h1>
                <i class="fas fa-id-card" style="color:var(--brand);"></i> Pass Slip Generator
                <span class="status-pill status-{{ $exam->status }}">{{ $exam->statusLabel() }}</span>
            </h1>
            <div class="exam-name">{{ $exam->exam_name }}</div>
        </div>

        {{-- ── KPI strip ── --}}
        <div class="kpi-row">
            <div class="kpi">
                <div class="ic" style="background:linear-gradient(135deg,var(--brand),var(--brand-mid));"><i
                        class="fas fa-layer-group"></i></div>
                <div>
                    <div class="v">{{ $examClasses->count() }}</div>
                    <div class="l">Class(es)</div>
                </div>
            </div>
            <div class="kpi">
                <div class="ic" style="background:linear-gradient(135deg,#10b981,#34d399);"><i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="v">{{ count($allStudents) }}</div>
                    <div class="l">Students</div>
                </div>
            </div>
            <div class="kpi">
                <div class="ic" style="background:linear-gradient(135deg,#0a4191,#2563eb);"><i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="v">{{ \App\Support\Term::label($exam->term) }}</div>
                    <div class="l">{{ $exam->academic_year }}</div>
                </div>
            </div>
            <div class="kpi">
                <div class="ic" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);"><i class="fas fa-hashtag"></i>
                </div>
                <div>
                    <div class="v">{{ $exam->exam_code }}</div>
                    <div class="l">Exam Code</div>
                </div>
            </div>
        </div>

        
            {{-- ┌──────────────────────────────────┐
            │ O Level Report Cards │
            └──────────────────────────────────┘ --}}
@if(Helper::schoolHasSecondary($exam->school_id) && \App\Helpers\PermissionHelper::canFeature('view_olevel_report_cards'))
<style>
    .rc-editorial {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 1fr 1.3fr;
        gap: 1rem;
        padding: 1.6rem;
        border-radius: 1.35rem;
        background: #fff;
        border: 1px solid rgba(44,41,202,.1);
        box-shadow: 0 8px 30px rgba(44,41,202,.09);
    }
    @media (max-width: 820px) {
        .rc-editorial { grid-template-columns: 1fr; }
    }
    .rc-editorial-intro {
        display: flex; flex-direction: column;
        justify-content: center;
        padding: .5rem .25rem;
    }
    .rc-editorial-kicker {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .6rem; font-weight: 800; letter-spacing: .1em;
        text-transform: uppercase;
        color: #2C29CA;
        margin-bottom: .65rem;
    }
    .rc-editorial-kicker::before {
        content: "";
        width: 22px; height: 2px; border-radius: 2px;
        background: linear-gradient(90deg, #2C29CA, #7c7aec);
    }
    .rc-editorial-title {
        font-size: 1.35rem; font-weight: 800;
        color: #1e1b4b; line-height: 1.2;
        margin: 0 0 .55rem;
        letter-spacing: -.01em;
    }
    .rc-editorial-title span {
        background: linear-gradient(120deg, #2C29CA, #7c7aec);
        -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .rc-editorial-desc {
        font-size: .82rem; color: #6b7280;
        line-height: 1.55; margin: 0;
    }
    .rc-editorial-actions {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: .75rem;
    }
    @media (max-width: 520px) {
        .rc-editorial-actions { grid-template-columns: 1fr; }
    }
    .rc-editorial-card {
        position: relative;
        display: flex; flex-direction: column;
        padding: 1.15rem 1.15rem 1.05rem;
        border-radius: 1rem;
        text-decoration: none;
        color: inherit;
        border: 1.5px solid #e4e6ff;
        background: #fafbff;
        transition: all .22s ease;
        overflow: hidden;
        min-height: 148px;
    }
    .rc-editorial-card:hover {
        border-color: #2C29CA;
        background: #fff;
        color: inherit;
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(44,41,202,.15);
    }
    .rc-editorial-card.rc-create {
        background: linear-gradient(145deg, #f0fdf7 0%, #fafbff 100%);
        border-color: #b8efd6;
    }
    .rc-editorial-card.rc-create:hover {
        border-color: #10b981;
        box-shadow: 0 14px 30px rgba(16,185,129,.18);
    }
    .rc-editorial-card .rc-num {
        position: absolute; top: .8rem; right: 1rem;
        font-size: 2.4rem; font-weight: 800;
        color: rgba(44,41,202,.08);
        line-height: 1;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .rc-editorial-card.rc-create .rc-num { color: rgba(16,185,129,.1); }
    .rc-editorial-card .rc-ic {
        width: 38px; height: 38px;
        border-radius: .65rem;
        background: #2C29CA;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .85rem;
        margin-bottom: auto;
        box-shadow: 0 4px 12px rgba(44,41,202,.3);
    }
    .rc-editorial-card.rc-create .rc-ic {
        background: linear-gradient(135deg, #10b981, #34d399);
        box-shadow: 0 4px 12px rgba(16,185,129,.35);
    }
    .rc-editorial-card .rc-t {
        font-weight: 800; font-size: .9rem;
        color: #1e1b4b; margin: .85rem 0 .2rem;
    }
    .rc-editorial-card .rc-d {
        font-size: .73rem; color: #6b7280; line-height: 1.4;
    }
    .rc-editorial-card .rc-go {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .7rem; font-weight: 800;
        color: #2C29CA; margin-top: .65rem;
    }
    .rc-editorial-card.rc-create .rc-go { color: #0d9668; }
    .rc-editorial-card .rc-go i { transition: transform .22s ease; }
    .rc-editorial-card:hover .rc-go i { transform: translateX(3px); }

    /* Select becomes a visible list while searching */
.ps-input-wrap select.form-control.is-listbox {
    background-image: none !important;
    padding-right: .8rem !important;
    padding-left: .8rem !important;
    overflow-y: auto !important;
}
.ps-input-wrap select.form-control.is-listbox + .ps-input-icon,
.ps-input-wrap:has(select.is-listbox) .ps-input-icon {
    display: none !important;
}
.ps-input-wrap select.form-control.is-listbox option {
    padding: .5rem .6rem !important;
    border-radius: .4rem;
}
.ps-input-wrap select.form-control.is-listbox option:checked {
    background: var(--brand, #2C29CA) linear-gradient(0deg, var(--brand, #2C29CA), var(--brand, #2C29CA));
    color: #fff;
}
.ps-picker-search { margin-bottom: .1rem; }
</style>
<div class="rc-editorial mb-4">
    <div class="rc-editorial-intro">
        <div class="rc-editorial-kicker">O Level · Senior 1–4</div>
        <h3 class="rc-editorial-title">
            Build <span>Report Cards</span><br>that combine everything.
        </h3>
        <p class="rc-editorial-desc">
            Blend assessments and examinations into a single, professional O‑level report card — scaled exactly the way you want.
        </p>
    </div>
    <div class="rc-editorial-actions">
        <a href="{{ route('olevel-report-cards.index') }}" class="rc-editorial-card rc-view">
            <span class="rc-num">01</span>
            <div class="rc-ic"><i class="fas fa-folder-open"></i></div>
            <div class="rc-t">View Report Cards</div>
            <div class="rc-d">Browse, edit and print your saved cards</div>
            <span class="rc-go">Open <i class="fas fa-arrow-right"></i></span>
        </a>
        @if(\App\Helpers\PermissionHelper::canFeature('create_olevel_report_card'))
        <a href="{{ route('olevel-report-cards.create') }}" class="rc-editorial-card rc-create">
            <span class="rc-num">02</span>
            <div class="rc-ic"><i class="fas fa-plus"></i></div>
            <div class="rc-t">New Report Card</div>
            <div class="rc-d">Build one from an exam that already has marks</div>
            <span class="rc-go">Create <i class="fas fa-arrow-right"></i></span>
        </a>
        @endif
    </div>
</div>
@endif


        {{-- ── Bento grid ── --}}
        <div class="ps-bento">

            {{-- ┌──────────────────────────────────┐
            │ Student Directory (tall left cell) │
            └──────────────────────────────────┘ --}}
            <div class="ps-cell ps-cell-directory">
                <h3><i class="fas fa-user-graduate"></i> Student Directory</h3>
                <div class="desc">Search &amp; print one student's pass slip.</div>

                {{-- Class filter chips — built strictly from $examClasses,
                i.e. only classes actually attached to THIS exam (see
                ExaminationController::passslipIndex()'s
                examination_classes query) — never every class the
                school has. --}}
                <div class="chip-row" id="classFilterContainer">
                    <button class="class-filter-chip active" data-class="all" onclick="filterClass('all', this)">
                        <i class="fas fa-globe" style="margin-right:.3rem;"></i> All Classes
                    </button>
                    @foreach ($examClasses as $ec)
                        <button class="class-filter-chip" data-class="{{ $ec->class_id }}_{{ $ec->stream_id }}"
                            onclick="filterClass('{{ $ec->class_id }}_{{ $ec->stream_id }}', this)">
                            <i class="fas fa-chalkboard-user" style="margin-right:.3rem;"></i>
                            {{ Helper::recordMdname($ec->class_id) }}{{ $ec->stream_id ? ' – ' . $ec->stream_id : '' }}
                        </button>
                    @endforeach
                </div>

                <div class="student-search-wrap">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="studentSearch" placeholder="Search by name, LIN Number, or class…"
                        oninput="filterStudents(this.value)" autocomplete="off">
                </div>

                <div class="student-list-container">
                    <div class="student-list-header">
                        <span><i class="fas fa-users me-1"></i> {{ count($allStudents) }} student(s)</span>
                        <span class="student-count-badge" id="studentCount">{{ count($allStudents) }}</span>
                    </div>

                    <div class="student-list" id="studentList">
                        @php
                            // Same list-building approach as before: one pass per
                            // exam class (never the whole school's students),
                            // each student tagged with the class/stream it
                            // belongs to for search + chip filtering.
                            $listStudents = collect();
                            foreach ($examClasses as $ec) {
                                $batch = DB::table('students')
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
                                $listStudents = $listStudents->merge($batch);
                            }
                            $listStudents = $listStudents->sortBy('lastname')->values();
                        @endphp

                        @forelse ($listStudents as $index => $student)
                            @php
                                $initials = strtoupper(substr($student->lastname, 0, 1) . substr($student->firstname, 0, 1));
                                $fullName = $student->lastname . ' ' . $student->firstname;
                                if (property_exists($student, 'other_names') && $student->other_names) {
                                    $fullName .= ' ' . $student->other_names;
                                }
                            @endphp
                            @if(\App\Helpers\PermissionHelper::canFeature('view_report_cards'))<a href="{{ route('examination.passslips.student', [$exam->id, $student->id]) }}?lang={{ $currentLang }}"
                                class="student-card student-link" data-name="{{ strtolower($fullName) }}"
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
                                        @if (property_exists($student, 'other_names') && $student->other_names)
                                            <span class="other-names">{{ $student->other_names }}</span>
                                        @endif
                                    </div>
                                    <div class="student-card-meta">
                                        <span class="meta-tag">
                                            <i class="fas fa-graduation-cap"></i>
                                            {{ Helper::recordMdname($student->class_id) }}{{ $student->stream_id ? ' – ' . $student->stream_id : '' }}
                                        </span>
                                        @if ($student->adm_no ?? false)
                                            <span class="meta-tag"><i class="fas fa-id-card"></i>{{ $student->adm_no }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="student-card-action">
                                    <i class="fas fa-print"></i> Print
                                </div>
                            </a>@endif
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

            {{-- ┌──────────────────────────────────┐
            │ Print by Class │
            └──────────────────────────────────┘ --}}
            <div class="ps-cell">
                <h3><i class="fas fa-chalkboard-teacher"></i> Print by Class</h3>
                <div class="desc">Generate every pass slip in one class at once.</div>

                @if ($examClasses->count() > 0)
                    <div class="class-mini-grid">
                        @foreach ($examClasses as $ec)
                            @php
                                $className = Helper::recordMdname($ec->class_id);
                                $streamLabel = $ec->stream_id ? ' – ' . $ec->stream_id : '';
                                $studentCount = DB::table('students')
                                    ->where('school_id', Session('LoggedSchool'))
                                    ->where('senior', $ec->class_id)
                                    ->where('stream', $ec->stream_id)
                                    ->count();
                                $safeStream = $ec->stream_id ?? '';
                                $formId = 'classForm_' . $ec->class_id . '_' . $safeStream;
                            @endphp
                            <button type="button" class="class-mini" onclick="printClass('{{ $formId }}', this)">
                                <div class="ic"><i class="fas fa-graduation-cap"></i></div>
                                <div class="body">
                                    <div class="name">{{ $className }}{{ $streamLabel }}</div>
                                    <div class="sub">{{ $studentCount }} student(s)</div>
                                </div>
                                <div class="go"><i class="fas fa-print"></i></div>
                            </button>

                            {{-- Hidden GET form for this class — carries lang
                            straight through server-side, exactly like the
                            student links above; every other setting
                            (template, toggles, combined exams) is resolved
                            per-class server-side from its saved
                            passslip_settings row (applySavedPassslipSettings). --}}
                            @if(\App\Helpers\PermissionHelper::canFeature('view_report_cards'))<form id="{{ $formId }}" action="{{ route('examination.passslips.class', $exam->id) }}" method="GET"
                                target="_blank" style="display:none;">
                                <input type="hidden" name="class_id" value="{{ $ec->class_id }}">
                                <input type="hidden" name="stream_id" value="{{ $safeStream }}">
                                <input type="hidden" name="lang" value="{{ $currentLang }}">
                            </form>@endif
                        @endforeach
                    </div>
                @else
                    <div class="class-empty">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>This examination doesn't have any classes assigned yet.</p>
                    </div>
                @endif
            </div>

            {{-- ┌──────────────────────────────────┐
            │ More Actions │
            └──────────────────────────────────┘ --}}
            <div class="ps-cell">
                <h3><i class="fas fa-th-large"></i> More Actions</h3>
                <div class="desc">Discipline ratings, remarks &amp; design templates.</div>

                @if(\App\Helpers\PermissionHelper::canFeature('view_discipline'))<a href="{{ route('examination.discipline.entry', $exam->id) }}" class="stack-btn">
                    <div class="ic"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <div class="t">Discipline Entry</div>
                        <div class="d">Rate punctuality, behaviour &amp; conduct</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>@endif

                @if(\App\Helpers\PermissionHelper::canFeature('view_remarks'))<a href="{{ route('examination.remarks.entry', $exam->id) }}" class="stack-btn">
                    <div class="ic"><i class="fas fa-comment-alt"></i></div>
                    <div>
                        <div class="t">Remarks Entry</div>
                        <div class="d">Class &amp; Head Teacher remarks per student</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>@endif

                @php $customDesigns = \App\Support\CustomReportCards::activeForSchool($exam->school_id); @endphp
                @if(Helper::schoolHasNonSecondary($exam->school_id))
                @if(\App\Helpers\PermissionHelper::canFeature('customize_report_cards'))<a href="{{ route('examination.passslips.customize', $exam->id) }}?template=classic" class="stack-btn">
                    <div class="ic"><i class="fas fa-graduation-cap"></i></div>
                    <div>
                        <div class="t">Customize Primary</div>
                        <div class="d">{{ $customDesigns['primary']['template']->name ?? '' ? 'Custom design: ' . $customDesigns['primary']['template']->name : 'Classic / Modern / Minimal' }}</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>@endif
                @endif

                @if(Helper::schoolHasNonSecondary($exam->school_id))
                @if(\App\Helpers\PermissionHelper::canFeature('customize_report_cards'))<a href="{{ route('examination.passslips.customize', $exam->id) }}?template=nursery-classic"
                    class="stack-btn">
                    <div class="ic"><i class="fas fa-child"></i></div>
                    <div>
                        <div class="t">Customize Nursery</div>
                        <div class="d">{{ $customDesigns['nursery']['template']->name ?? '' ? 'Custom design: ' . $customDesigns['nursery']['template']->name : 'Baby / Middle / Top Class' }}</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>@endif
                @endif

                @if(Helper::schoolHasSecondary($exam->school_id))
                @if(\App\Helpers\PermissionHelper::canFeature('customize_report_cards'))<a href="{{ route('examination.passslips.customize', $exam->id) }}?template=secondary-classic"
                    class="stack-btn">
                    <div class="ic"><i class="fas fa-user-graduate"></i></div>
                    <div>
                        <div class="t">Customize Secondary</div>
                        <div class="d">{{ $customDesigns['secondary']['template']->name ?? '' ? 'Custom design: ' . $customDesigns['secondary']['template']->name : 'O-Level / A-Level report card' }}</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>@endif
                @endif

                @if(\App\Helpers\PermissionHelper::canFeature('customize_report_cards'))<a href="{{ route('examination.passslips.arabic', $exam->id) }}" class="stack-btn">
                    <div class="ic"><i class="fas fa-language"></i></div>
                    <div>
                        <div class="t">Arabic Report Card</div>
                        <div class="d">Assign classes · العربية / English</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>@endif
            </div>

        </div>{{-- /.ps-bento --}}
        @else
            <div class="ps-empty">
                <div><i class="fas fa-id-card"></i></div>
                <div class="fw-semibold">Select an examination above to generate pass slips.</div>
                @if(empty($examOptions))
                    <div class="mt-1" style="font-size:.82rem;">No examination has been closed or released yet.</div>
                @endif
            </div>
        @endif
    </div>{{-- /.side-app --}}
    </div>
    </div>
    </div>
    
    {{-- ═══════════════════════════════════════════════════════════
    JAVASCRIPT — trimmed down to what this page still actually owns:
    language switching (a real navigation, not a client-side patch),
    the loading overlay, class-print form submission, and the
    directory's search/filter. Template selection, show/hide toggles
    and Combine Examinations no longer live here — see
    "Customize this design" — so there's no buildQS()/injectIntoForm()
    machinery left to keep in sync.
    ═══════════════════════════════════════════════════════════ --}}
    <script>
        const PS_HUB_URL = @json(route('examination.passslips.hub', [], false));

        async function selectPassslipExam(id) {
            const url = new URL(PS_HUB_URL, window.location.origin);
            if (id) url.searchParams.set('exam', id);
            const lang = new URLSearchParams(window.location.search).get('lang');
            if (lang) url.searchParams.set('lang', lang);

            showLoading('Loading pass slips…');
            try {
                const res = await fetch(url.toString(), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                const fresh = doc.getElementById('ps-bento-app');
                if (!res.ok || !fresh) throw new Error('bad response');

                document.getElementById('ps-bento-app').innerHTML = fresh.innerHTML;
                activeClassFilter = 'all';
                window.history.replaceState(null, '', url.toString());
            } catch (e) {
                window.location.href = url.toString(); // fall back to a normal page load
            }
            const ov = document.getElementById('loadingOverlay');
            if (ov) ov.classList.remove('active');
        }

        function filterExamOptions(input) {
    const select = document.getElementById('psExamPicker');
    if (!select) return;
    const wrap = input.closest('.ps-picker-search');

    // Cache the full option list once per select element
    if (!select._allOptions) {
        select._allOptions = Array.from(select.options).map(o => ({
            value: o.value,
            label: o.text,
            selected: o.defaultSelected
        }));
    }

    const q = input.value.toLowerCase().trim();
    const terms = q.split(/\s+/).filter(Boolean);
    const current = select.value;

    const list = q
        ? select._allOptions.filter(o => o.value !== '' && terms.every(t => o.label.toLowerCase().includes(t)))
        : select._allOptions;

    select.innerHTML = '';
    list.forEach(o => {
        const opt = new Option(o.label, o.value, false, false);
        if (!q && o.value === current) opt.selected = true;
        select.add(opt);
    });

    if (q && list.length) {
        select.size = Math.min(Math.max(list.length, 2), 7);
        select.classList.add('is-listbox');
        select.selectedIndex = -1;
    } else {
        select.size = 1;
        select.classList.remove('is-listbox');
        if (!q) select.value = current;
    }

    wrap.classList.toggle('has-value', !!q);
    wrap.classList.toggle('no-results', !!q && list.length === 0);
}

function clearExamSearch(btn) {
    const input = btn.closest('.ps-picker-search').querySelector('input');
    input.value = '';
    filterExamOptions(input);
    input.focus();
}

function examSearchKey(e, input) {
    const select = document.getElementById('psExamPicker');
    if (e.key === 'Escape') {
        input.value = '';
        filterExamOptions(input);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const first = Array.from(select.options).find(o => o.value !== '');
        if (first) {
            select.value = first.value;
            select.dispatchEvent(new Event('change'));
        }
    } else if (e.key === 'ArrowDown' && select.classList.contains('is-listbox')) {
        e.preventDefault();
        select.focus();
        select.selectedIndex = 0;
    }
}

        function setLanguage(lang) {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }

        function showLoading(msg) {
            document.getElementById('loadingText').textContent = msg ?? 'Generating…';
            document.getElementById('loadingOverlay').classList.add('active');
            setTimeout(() => document.getElementById('loadingOverlay').classList.remove('active'), 5000);
        }

        function printClass(formId, btn) {
            document.querySelectorAll('.class-mini').forEach(t => t.classList.remove('selected'));
            if (btn) btn.classList.add('selected');
            showLoading('Generating class pass slips…');
            const form = document.getElementById(formId);
            if (!form) {
                document.getElementById('loadingOverlay').classList.remove('active');
                alert('Error: form not found.');
                return;
            }
            form.submit();
        }

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
                const nameMatch = card.dataset.name.includes(q);
                const admMatch = card.dataset.adm && card.dataset.adm.includes(q);
                const classMatch = activeClassFilter === 'all' || card.dataset.class === activeClassFilter;
                const show = (nameMatch || admMatch) && classMatch;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            const nr = document.getElementById('noResultsMsg');
            if (nr) nr.style.display = visible === 0 ? 'flex' : 'none';

            const sc = document.getElementById('studentCount');
            if (sc) sc.textContent = visible;
        }
    </script>

@endsection