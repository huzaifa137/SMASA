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
            <a href="{{ route('examination.index') }}" class="ps-back btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div class="ps-lang-switch">
                <button type="button" class="ps-lang-btn {{ $currentLang === 'en' ? 'active' : '' }}"
                    onclick="setLanguage('en')">
                    <i class="fas fa-flag-usa"></i> English
                </button>
                <button type="button" class="ps-lang-btn {{ $currentLang === 'ar' ? 'active' : '' }}"
                    onclick="setLanguage('ar')">
                    <i class="fas fa-flag"></i> العربية
                </button>
            </div>
        </div>

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
                    <div class="v">{{ $exam->term }}</div>
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
                            <a href="{{ route('examination.passslips.student', [$exam->id, $student->id]) }}?lang={{ $currentLang }}"
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
                            <form id="{{ $formId }}" action="{{ route('examination.passslips.class', $exam->id) }}" method="GET"
                                target="_blank" style="display:none;">
                                <input type="hidden" name="class_id" value="{{ $ec->class_id }}">
                                <input type="hidden" name="stream_id" value="{{ $safeStream }}">
                                <input type="hidden" name="lang" value="{{ $currentLang }}">
                            </form>
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

                <a href="{{ route('examination.discipline.entry', $exam->id) }}" class="stack-btn">
                    <div class="ic"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <div class="t">Discipline Entry</div>
                        <div class="d">Rate punctuality, behaviour &amp; conduct</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>

                <a href="{{ route('examination.remarks.entry', $exam->id) }}" class="stack-btn">
                    <div class="ic"><i class="fas fa-comment-alt"></i></div>
                    <div>
                        <div class="t">Remarks Entry</div>
                        <div class="d">Class &amp; Head Teacher remarks per student</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>

                <a href="{{ route('examination.passslips.customize', $exam->id) }}?template=classic" class="stack-btn">
                    <div class="ic"><i class="fas fa-graduation-cap"></i></div>
                    <div>
                        <div class="t">Customize Primary</div>
                        <div class="d">Classic / Modern / Minimal</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>

                <a href="{{ route('examination.passslips.customize', $exam->id) }}?template=nursery-classic"
                    class="stack-btn">
                    <div class="ic"><i class="fas fa-child"></i></div>
                    <div>
                        <div class="t">Customize Nursery</div>
                        <div class="d">Baby / Middle / Top Class</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </a>
            </div>

        </div>{{-- /.ps-bento --}}
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