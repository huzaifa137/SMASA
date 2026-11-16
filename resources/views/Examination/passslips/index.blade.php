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

{{-- ── Examination picker: server-side filters + pagination (scales to thousands of exams) ── --}}
@php
    $px = $examPicker;
    $pf = $px['filters'];
    $pPage = $px['page'];
    $pLang = request('lang');
    $pStatusPill = fn($st) => match ($st) {
        'results_released' => ['Results released', 'psx-pill-green'],
        'closed' => ['Closed', 'psx-pill-grey'],
        default => ['Class-level release', 'psx-pill-amber'],
    };
@endphp
<div class="psx" id="psExamPicker">
    @if($exam)
        <div class="psx-current">
            <span class="psx-current-icon"><i class="fas fa-file-signature"></i></span>
            <div class="psx-current-text">
                <span class="psx-eyebrow">Selected examination</span>
                <strong>{{ $px['selectedLabel'] }}</strong>
            </div>
            <button type="button" class="psx-btn psx-btn-outline" id="psxToggle" aria-expanded="{{ $px['panelOpen'] ? 'true' : 'false' }}">
                <i class="fas fa-exchange-alt"></i> Change examination
            </button>
        </div>
    @endif

    <div class="psx-panel {{ $px['panelOpen'] ? '' : 'is-collapsed' }}" id="psxPanel">
        <form method="GET" action="{{ route('examination.passslips.hub') }}" id="psxForm" autocomplete="off">
            @if($exam)<input type="hidden" name="exam" value="{{ $exam->id }}">@endif
            @if($pLang)<input type="hidden" name="lang" value="{{ $pLang }}">@endif

            <div class="psx-filters">
                <div class="psx-field psx-field-search">
                    <label for="psxQ">Search</label>
                    <div class="psx-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="psxQ" name="q" value="{{ $pf['q'] }}" placeholder="Examination name or code…">
                    </div>
                </div>

                <div class="psx-field">
                    <label for="psxYear">Academic year</label>
                    <select id="psxYear" name="year" class="psx-auto">
                        <option value="all" @selected($pf['year'] === 'all')>All years</option>
                        @foreach($px['years'] as $y)
                            <option value="{{ $y }}" @selected($pf['year'] === (string) $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="psx-field">
                    <label for="psxTerm">Term</label>
                    <select id="psxTerm" name="term" class="psx-auto">
                        <option value="">All terms</option>
                        @foreach($px['terms'] as $n => $lbl)
                            <option value="{{ $n }}" @selected($pf['term'] === (string) $n)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="psx-field">
                    <label for="psxType">Exam type</label>
                    <select id="psxType" name="type" class="psx-auto">
                        <option value="">All types</option>
                        @foreach($px['types'] as $t)
                            <option value="{{ $t }}" @selected($pf['type'] === $t)>{{ str_replace('-', ' ', $t) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="psx-field">
                    <label for="psxKind">Kind</label>
                    <select id="psxKind" name="kind" class="psx-auto">
                        <option value="">Exams &amp; report cards</option>
                        <option value="standard" @selected($pf['kind'] === 'standard')>Examinations only</option>
                        <option value="report_card" @selected($pf['kind'] === 'report_card')>Report cards only</option>
                    </select>
                </div>

                <div class="psx-field">
                    <label for="psxStatus">Status</label>
                    <select id="psxStatus" name="status" class="psx-auto">
                        <option value="">Any status</option>
                        <option value="results_released" @selected($pf['status'] === 'results_released')>Results released</option>
                        <option value="closed" @selected($pf['status'] === 'closed')>Closed</option>
                        <option value="partial" @selected($pf['status'] === 'partial')>Class-level release</option>
                    </select>
                </div>

                <div class="psx-field">
                    <label for="psxSort">Sort by</label>
                    <select id="psxSort" name="sort" class="psx-auto">
                        <option value="newest" @selected($pf['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($pf['sort'] === 'oldest')>Oldest first</option>
                        <option value="name" @selected($pf['sort'] === 'name')>Name (A–Z)</option>
                    </select>
                </div>

                <div class="psx-field psx-field-actions">
                    <button type="submit" class="psx-btn psx-btn-primary"><i class="fas fa-filter"></i> Apply</button>
                    <a class="psx-btn psx-btn-ghost"
                        href="{{ route('examination.passslips.hub', array_filter(['exam' => $exam?->id, 'lang' => $pLang, 'pick' => 1])) }}">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="psx-summary">
            @if($pPage->total() > 0)
                Showing <strong>{{ $pPage->firstItem() }}–{{ $pPage->lastItem() }}</strong> of <strong>{{ $pPage->total() }}</strong>
                {{ \Illuminate\Support\Str::plural('examination', $pPage->total()) }}
                @if($px['activeCount'] > 0)<span class="psx-chip">{{ $px['activeCount'] }} {{ \Illuminate\Support\Str::plural('filter', $px['activeCount']) }} active</span>@endif
            @else
                No examinations to show.
            @endif
        </div>

        <div class="psx-list">
            @forelse($pPage as $e)
                @php [$stLabel, $stClass] = $pStatusPill($e->status); @endphp
                <a class="psx-row {{ $exam && $exam->id === $e->id ? 'is-selected' : '' }}"
                    href="{{ route('examination.passslips.hub', array_filter(['exam' => $e->id, 'lang' => $pLang])) }}">
                    <span class="psx-row-main">
                        <span class="psx-row-name">{{ $e->exam_name }}</span>
                        <span class="psx-row-meta">
                            @if($e->exam_code)<span>{{ $e->exam_code }}</span>@endif
                            <span>{{ \App\Support\Term::label($e->term) }} {{ $e->academic_year }}</span>
                            @if($e->exam_type)<span>{{ str_replace('-', ' ', $e->exam_type) }}</span>@endif
                        </span>
                    </span>
                    <span class="psx-row-tags">
                        @if($e->isReportCard())<span class="psx-pill psx-pill-blue">Report card</span>@endif
                        <span class="psx-pill {{ $stClass }}">{{ $stLabel }}</span>
                        <span class="psx-row-go">{{ $exam && $exam->id === $e->id ? 'Selected' : 'Open' }} <i class="fas fa-chevron-right"></i></span>
                    </span>
                </a>
            @empty
                <div class="psx-empty">
                    <i class="fas fa-folder-open"></i>
                    @if(!$px['hasEligible'])
                        <div>No examination has been closed or released yet.</div>
                    @else
                        <div>No examination matches these filters.</div>
                        <a href="{{ route('examination.passslips.hub', array_filter(['exam' => $exam?->id, 'lang' => $pLang, 'pick' => 1])) }}">Reset filters</a>
                    @endif
                </div>
            @endforelse
        </div>

        @if($pPage->hasPages())
            <div class="psx-pager">{{ $pPage->links() }}</div>
        @endif
    </div>
</div>

<style>
    .psx { margin-bottom: 1rem; }
    .psx-current { display: flex; align-items: center; gap: .8rem; padding: .7rem .9rem; background: #fff; border: 1px solid #e3e5f5; border-radius: .7rem; margin-bottom: .6rem; }
    .psx-current-icon { width: 34px; height: 34px; border-radius: .55rem; background: #eef0ff; color: var(--brand, #2C29CA); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .psx-current-text { display: flex; flex-direction: column; min-width: 0; flex: 1; }
    .psx-current-text strong { font-size: .92rem; color: #1d1d3a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .psx-eyebrow { font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b6b90; }
    .psx-panel { background: #fff; border: 1px solid #e3e5f5; border-radius: .8rem; padding: .9rem 1rem 1rem; }
    .psx-panel.is-collapsed { display: none; }
    .psx-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .7rem; align-items: end; }
    .psx-field { display: flex; flex-direction: column; gap: .25rem; min-width: 0; }
    .psx-field-search { grid-column: span 2; }
    .psx-field label { margin: 0; font-size: .65rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #5a5a80; }
    .psx-field select, .psx-search input { width: 100%; height: 38px; padding: 0 .7rem; font-size: .85rem; color: #1d1d3a; background: #f7f8ff; border: 1px solid #dcdff3; border-radius: .55rem; outline: none; transition: border-color .15s, box-shadow .15s; }
    .psx-field select:focus, .psx-search input:focus { border-color: var(--brand, #2C29CA); box-shadow: 0 0 0 3px rgba(44, 41, 202, .13); background: #fff; }
    .psx-search { position: relative; }
    .psx-search i { position: absolute; left: .7rem; top: 50%; transform: translateY(-50%); font-size: .75rem; color: #8a8ab0; }
    .psx-search input { padding-left: 2rem; }
    .psx-field-actions { flex-direction: row; gap: .4rem; }
    .psx-btn { display: inline-flex; align-items: center; gap: .4rem; height: 38px; padding: 0 .9rem; font-size: .8rem; font-weight: 700; border-radius: .55rem; border: 1px solid transparent; cursor: pointer; text-decoration: none !important; white-space: nowrap; }
    .psx-btn-primary { background: var(--brand, #2C29CA); color: #fff !important; }
    .psx-btn-outline { background: #fff; border-color: var(--brand, #2C29CA); color: var(--brand, #2C29CA) !important; }
    .psx-btn-ghost { background: #f1f2fb; color: #4a4a70 !important; }
    .psx-summary { margin: .8rem 0 .5rem; font-size: .78rem; color: #5a5a80; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .psx-chip { background: #eef0ff; color: var(--brand, #2C29CA); font-weight: 700; font-size: .68rem; padding: .1rem .5rem; border-radius: 999px; }
    .psx-list { display: flex; flex-direction: column; gap: .4rem; }
    .psx-row { display: flex; align-items: center; justify-content: space-between; gap: .8rem; padding: .6rem .8rem; border: 1px solid #e8eaf7; border-radius: .6rem; background: #fff; text-decoration: none !important; color: inherit; transition: border-color .15s, background .15s; }
    .psx-row:hover { border-color: var(--brand, #2C29CA); background: #fafaff; }
    .psx-row.is-selected { border-color: var(--brand, #2C29CA); background: #eef0ff; }
    .psx-row-main { display: flex; flex-direction: column; min-width: 0; }
    .psx-row-name { font-weight: 700; font-size: .88rem; color: #1d1d3a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .psx-row-meta { display: flex; flex-wrap: wrap; gap: .2rem .7rem; font-size: .72rem; color: #6b6b90; }
    .psx-row-tags { display: flex; align-items: center; gap: .4rem; flex-shrink: 0; }
    .psx-row-go { font-size: .72rem; font-weight: 700; color: var(--brand, #2C29CA); }
    .psx-pill { font-size: .65rem; font-weight: 700; padding: .12rem .5rem; border-radius: 999px; white-space: nowrap; }
    .psx-pill-green { background: #dcfce7; color: #15803d; }
    .psx-pill-grey { background: #eceef5; color: #4a4a70; }
    .psx-pill-amber { background: #fef3c7; color: #a16207; }
    .psx-pill-blue { background: #e0e7ff; color: #3730a3; }
    .psx-empty { text-align: center; padding: 1.6rem 1rem; color: #6b6b90; font-size: .85rem; }
    .psx-empty i { display: block; font-size: 1.6rem; margin-bottom: .4rem; color: #b5b8d6; }
    .psx-pager { margin-top: .8rem; display: flex; justify-content: center; }
    @media (max-width: 767px) {
        .psx-field-search { grid-column: 1 / -1; }
        .psx-row { flex-direction: column; align-items: flex-start; }
        .psx-row-tags { flex-wrap: wrap; }
        .psx-current { flex-wrap: wrap; }
    }
</style>
<script>
    (function () {
        var form = document.getElementById('psxForm');
        if (!form) return;
        form.querySelectorAll('select.psx-auto').forEach(function (el) {
            el.addEventListener('change', function () { form.submit(); });
        });
        var q = document.getElementById('psxQ'), t;
        if (q) q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { form.submit(); }, 600); });
        var toggle = document.getElementById('psxToggle'), panel = document.getElementById('psxPanel');
        if (toggle && panel) toggle.addEventListener('click', function () {
            var collapsed = panel.classList.toggle('is-collapsed');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        });
        if (q && q.value) { q.focus(); q.setSelectionRange(q.value.length, q.value.length); }
    })();
</script>
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