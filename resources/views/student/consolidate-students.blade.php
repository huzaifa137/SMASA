@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .10);
            --b2: #2420a8;
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        }

        *, *::before, *::after { box-sizing: border-box; }
        *:not(i):not([class*="fa"]) { font-family: 'DM Sans', sans-serif; }
        body { background: var(--bg); }

        .fin-hero {
            background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }
        .fin-hero::before {
            content: '';
            position: absolute; top: -60px; right: -60px; width: 260px; height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(165, 180, 252, .2) 0%, transparent 70%);
        }
        .fin-hero h1 { color: #fff; font-size: 1.5rem; font-weight: 700; margin: 0; }
        .fin-hero p { color: #c7d2fe; margin: .2rem 0 0; font-size: .88rem; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(47, 44, 203, .25); border: 1px solid rgba(165, 180, 252, .4);
            color: #a5b4fc; padding: .25rem .75rem; border-radius: 20px;
            font-size: .75rem; font-weight: 600; margin-bottom: .6rem;
        }

        .card {
            background: var(--surf); border-radius: var(--rad); border: 1px solid var(--brd);
            box-shadow: var(--sh); margin-bottom: 1.5rem; overflow: visible;
        }
        .card-hd {
            padding: 1rem 1.5rem; border-bottom: 1px solid var(--brd);
            display: flex; align-items: center; justify-content: space-between; background: #fafbff;
            border-radius: var(--rad) var(--rad) 0 0; flex-wrap: wrap; gap: 10px;
        }
        .card-hd h5 { margin: 0; font-size: .98rem; font-weight: 700; color: var(--t1); }
        .card-hd p { margin: 2px 0 0; font-size: .8rem; color: var(--t2); }
        .card-bd { padding: 1.25rem 1.5rem; overflow: visible; }

        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 1.75rem; }
        @media (max-width: 900px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
        .stat-card {
            background: var(--surf); border: 1px solid var(--brd); border-radius: var(--rad);
            padding: 1.1rem 1.25rem; box-shadow: var(--sh);
        }
        .stat-card .val { font-size: 1.6rem; font-weight: 700; color: var(--t1); line-height: 1.1; }
        .stat-card .lbl { font-size: .8rem; color: var(--t2); margin-top: 4px; }
        .stat-card i { float: right; font-size: 1.2rem; }

        .suggestion {
            border: 1px solid var(--brd); border-radius: var(--rads); padding: 1rem 1.1rem; margin-bottom: 1rem;
            transition: opacity .35s ease, max-height .35s ease, margin .35s ease, padding .35s ease; overflow: hidden;
        }
        .suggestion.confidence-high { border-left: 4px solid var(--r); }
        .suggestion.confidence-medium { border-left: 4px solid var(--a); }
        .suggestion.type-duplicate { background: #fef2f2; border-color: #fecaca; }
        .suggestion.collapsing { opacity: 0; max-height: 0 !important; margin: 0 !important; padding-top: 0 !important; padding-bottom: 0 !important; border-width: 0 !important; }

        .badge-conf {
            font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; text-transform: uppercase;
        }
        .badge-conf.high { background: var(--rl); color: var(--r); }
        .badge-conf.medium { background: var(--al); color: var(--a); }
        .badge-type {
            font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-right: 6px;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .badge-type.duplicate { background: #fee2e2; color: #b91c1c; }
        .badge-type.multi { background: var(--bl); color: var(--b2); }

        .student-chip {
            display: flex; align-items: center; gap: 10px; border: 1px solid var(--brd); border-radius: var(--rads);
            padding: .55rem .8rem; margin-bottom: 6px; background: #fafbff;
        }
        .type-duplicate .student-chip { background: #fff; }
        .student-chip input[type=radio] { accent-color: var(--b); }
        .type-duplicate .student-chip input[type=radio] { accent-color: var(--r); }
        .student-chip .name { font-weight: 600; color: var(--t1); font-size: .88rem; }
        .student-chip .meta { font-size: .76rem; color: var(--t2); }

        .btn-link-primary {
            background: var(--b); color: #fff; border: none; border-radius: 8px; padding: 7px 16px;
            font-weight: 600; font-size: .82rem; position: relative;
        }
        .btn-link-primary:hover { background: var(--b2); color: #fff; }
        .btn-danger-delete {
            background: var(--r); color: #fff; border: none; border-radius: 8px; padding: 7px 16px;
            font-weight: 600; font-size: .82rem; position: relative;
        }
        .btn-danger-delete:hover { background: #b91c1c; color: #fff; }
        .btn-dismiss {
            background: #fff; color: var(--t2); border: 1px solid var(--brd); border-radius: 8px; padding: 7px 16px;
            font-weight: 600; font-size: .82rem; position: relative;
        }
        .btn-dismiss:hover { background: #f8fafc; }

        .empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--t2); }
        .empty-state i { font-size: 2.2rem; color: var(--g); margin-bottom: .6rem; }

        /* ── Filter bar (Suggested Matches) ── */
        .filter-bar {
            display: flex; align-items: end; gap: 12px; flex-wrap: wrap;
            background: #fafbff; border: 1px solid var(--brd); border-radius: var(--rads);
            padding: .9rem 1rem; margin-bottom: 1.1rem;
        }
        .filter-bar .fld { min-width: 190px; }
        .filter-bar label { font-size: .74rem; font-weight: 700; color: var(--t2); text-transform: uppercase; letter-spacing: .02em; margin-bottom: 4px; display: block; }
        .filter-bar select {
            width: 100%; border: 1px solid var(--brd); border-radius: 8px; padding: 8px 10px; font-size: .84rem; background: #fff;
        }
        .filter-bar .btn-apply {
            background: var(--b); color: #fff; border: none; border-radius: 8px; padding: 9px 18px; font-weight: 600; font-size: .84rem;
        }
        .filter-bar .btn-apply:hover { background: var(--b2); color: #fff; }
        .filter-bar .btn-clear {
            color: var(--t2); font-size: .82rem; font-weight: 600; text-decoration: none; padding: 9px 6px;
        }
        .filter-bar .btn-clear:hover { color: var(--r); }
        .filter-active-note {
            font-size: .78rem; color: var(--b2); background: var(--bl); border-radius: 20px; padding: 4px 12px;
            display: inline-flex; align-items: center; gap: 6px; margin-bottom: 1rem;
        }

        /* ── Consolidated list search ── */
        .consolidated-search { display: flex; gap: 10px; margin-bottom: 1.1rem; flex-wrap: wrap; }
        .consolidated-search input {
            flex: 1; min-width: 220px; border: 1px solid var(--brd); border-radius: 8px; padding: 8px 12px; font-size: .85rem;
        }
        .consolidated-search button {
            background: var(--t1); color: #fff; border: none; border-radius: 8px; padding: 8px 16px; font-weight: 600; font-size: .84rem;
        }
        .consolidated-search .clear-search { color: var(--t2); font-size: .82rem; font-weight: 600; text-decoration: none; align-self: center; }

        .manual-link-box {
            display: grid; grid-template-columns: 1fr auto 1fr auto; gap: 12px; align-items: end;
        }
        @media (max-width: 800px) { .manual-link-box { grid-template-columns: 1fr; } }
        .manual-link-box label { font-size: .78rem; font-weight: 600; color: var(--t2); margin-bottom: 4px; display: block; }
        .search-wrap { position: relative; overflow: visible; }
        .search-wrap input {
            width: 100%; border: 1px solid var(--brd); border-radius: 8px; padding: 8px 12px; font-size: .85rem;
        }
        .search-results {
            position: absolute; z-index: 50; top: 100%; left: 0; right: 0; background: #fff;
            border: 1px solid var(--brd); border-radius: 8px; box-shadow: var(--sh); max-height: 220px; overflow-y: auto;
            display: none;
        }
        .search-results .res-item { padding: 8px 12px; cursor: pointer; font-size: .84rem; border-bottom: 1px solid #f1f5f9; }
        .search-results .res-item:hover { background: #f8fafc; }
        .search-results .res-item .n { font-weight: 600; color: var(--t1); }
        .search-results .res-item .m { font-size: .74rem; color: var(--t2); }
        .picked-pill {
            display: none; align-items: center; gap: 8px; background: var(--bl); color: var(--b2);
            border-radius: 8px; padding: 8px 12px; font-size: .84rem; font-weight: 600;
        }
        .picked-pill i.remove { cursor: pointer; opacity: .7; }

        .consolidated-row {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: .8rem 0; border-bottom: 1px solid var(--brd); flex-wrap: wrap;
            transition: opacity .35s ease, max-height .35s ease, margin .35s ease, padding .35s ease; overflow: hidden;
        }
        .consolidated-row:last-child { border-bottom: none; }
        .consolidated-row.collapsing { opacity: 0; max-height: 0 !important; margin: 0 !important; padding-top: 0 !important; padding-bottom: 0 !important; border-width: 0 !important; }
        .program-chip {
            display: inline-flex; align-items: center; gap: 6px; background: var(--gl); color: var(--g);
            border-radius: 20px; padding: 3px 10px; font-size: .74rem; font-weight: 600; margin-right: 6px;
            transition: opacity .3s ease, max-width .3s ease;
        }
        .program-chip.collapsing { opacity: 0; }
        .program-chip i.unlink { cursor: pointer; opacity: .7; }

        .btn-loading { opacity: .7; pointer-events: none; }
        .btn-loading .fa-link, .btn-loading .fa-xmark, .btn-loading .fa-trash-can { visibility: hidden; }
        .btn-loading::after {
            content: ''; position: absolute; width: 14px; height: 14px; left: 50%; top: 50%; margin: -7px 0 0 -7px;
            border: 2px solid rgba(255,255,255,.5); border-top-color: #fff; border-radius: 50%;
            animation: spin .6s linear infinite; display: inline-block;
        }
        .btn-dismiss.btn-loading::after { border-color: rgba(71,85,105,.3); border-top-color: var(--t2); }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Pagination ── */
        .pagination { display: flex; gap: .25rem; align-items: center; flex-wrap: wrap; }
        .pagination .page-link {
            display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px;
            padding: 0 .5rem; border-radius: 8px; font-size: .8rem; font-weight: 600; color: var(--t2);
            background: var(--surf); border: 1px solid var(--brd); text-decoration: none; transition: all .15s;
        }
        .pagination .page-link:hover { background: var(--bl); color: var(--b); border-color: rgba(47, 44, 203, .3); }
        .pagination .page-item.active .page-link { background: var(--b); color: #fff; border-color: var(--b); }
        .pagination .page-item.disabled .page-link { opacity: .4; pointer-events: none; }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-4">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-code-branch"></i> Students</div>
            <h1>Consolidate Students</h1>
            <p>Make sure every child is counted once, even when enrolled in both Theology and Secular classes.</p>
        </div>
    </div>
@endsection

@section('content')

    @php
        $csrf = csrf_token();
    @endphp

    <div class="stat-grid">
        <div class="stat-card">
            <span style="color:#2f2ccb"><i class="fas fa-id-card"></i></span>
            <div class="val" id="statTotalRecords">{{ number_format($stats['total_records']) }}</div>
            <div class="lbl">Total Enrollment Records</div>
        </div>
        <div class="stat-card">
            <span style="color:#dc2626"><i class="fas fa-user-graduate"></i></span>
            <div class="val" id="statUniqueStudents">{{ number_format($stats['unique_students']) }}</div>
            <div class="lbl">Unique Physical Students</div>
        </div>
        <div class="stat-card">
            <span style="color:#059669"><i class="fas fa-code-branch"></i></span>
            <div class="val" id="statMultiProgram">{{ number_format($stats['multi_program_students']) }}</div>
            <div class="lbl">Enrolled in Both Programs</div>
        </div>
        <div class="stat-card">
            <span style="color:#d97706"><i class="fas fa-triangle-exclamation"></i></span>
            <div class="val" id="statPendingReview">{{ number_format($stats['pending_review']) }}</div>
            <div class="lbl">Records Awaiting Review</div>
        </div>
    </div>

    {{-- ── Suggested duplicates ── --}}
    <div class="card">
        <div class="card-hd">
            <div>
                <h5><i class="fas fa-wand-magic-sparkles me-1" style="color:var(--b);"></i> Suggested Matches</h5>
                <p>Records that are very likely the same child, found automatically by name, date of birth, gender and guardian contact.</p>
            </div>
        </div>
        <div class="card-bd">

            {{-- Class / stream filters --}}
            @php
                // Map of class ("senior") => distinct streams that exist within it,
                // used to keep the Stream dropdown scoped to whatever Class is selected.
                $streamsByClass = $classStreamOptions
                    ->groupBy('senior')
                    ->map(fn ($rows) => $rows->pluck('stream')->filter()->unique()->values());
            @endphp
            <form method="GET" action="{{ route('students.consolidation') }}" class="filter-bar" id="suggestionFilterForm">
                <div class="fld">
                    <label>Class</label>
                    <select name="class" id="filterClass">
                        <option value="">All classes</option>
                        @foreach($classStreamOptions->pluck('senior')->unique() as $seniorVal)
                            <option value="{{ $seniorVal }}" {{ (string) $classFilter === (string) $seniorVal ? 'selected' : '' }}>
                                {{ \App\Http\Controllers\Helper::recordMdname($seniorVal) ?: $seniorVal }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="fld">
                    <label>Stream</label>
                    <select name="stream" id="filterStream">
                        <option value="">{{ $classFilter ? 'All streams in this class' : 'Select a class first' }}</option>
                        @if($classFilter)
                            @foreach($streamsByClass->get($classFilter, collect()) as $streamVal)
                                <option value="{{ $streamVal }}" {{ (string) $streamFilter === (string) $streamVal ? 'selected' : '' }}>
                                    {{ $streamVal }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                {{-- Preserve the consolidated-list search box when filtering suggestions --}}
                @if($consolidatedSearch)
                    <input type="hidden" name="q" value="{{ $consolidatedSearch }}">
                @endif
                <button type="submit" class="btn-apply"><i class="fas fa-filter me-1"></i> Apply Filters</button>
                @if($classFilter || $streamFilter)
                    <a href="{{ route('students.consolidation', array_filter(['q' => $consolidatedSearch ?: null])) }}" class="btn-clear">
                        <i class="fas fa-xmark me-1"></i> Clear filters
                    </a>
                @endif
            </form>

            @if($classFilter || $streamFilter)
                <div class="filter-active-note">
                    <i class="fas fa-filter"></i>
                    Showing matches touching
                    @if($classFilter) <strong>{{ \App\Http\Controllers\Helper::recordMdname($classFilter) ?: $classFilter }}</strong> @endif
                    @if($streamFilter) stream <strong>{{ $streamFilter }}</strong> @endif
                    only.
                </div>
            @endif

            <div id="suggestionsContainer">
                @forelse($suggestions as $group)
                    <div class="suggestion confidence-{{ $group['confidence'] }} type-{{ $group['type'] === 'duplicate' ? 'duplicate' : 'multi' }}"
                        data-group="{{ $group['key'] }}" data-type="{{ $group['type'] }}">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:6px;">
                            <div>
                                @if($group['type'] === 'duplicate')
                                    <span class="badge-type duplicate"><i class="fas fa-clone"></i> Exact duplicate · same class</span>
                                @else
                                    <span class="badge-type multi"><i class="fas fa-code-branch"></i> Different program</span>
                                @endif
                                <span class="badge-conf {{ $group['confidence'] }}">{{ $group['confidence'] }} confidence</span>
                            </div>
                            <span style="font-size:.78rem;color:var(--t2);">{{ $group['reason'] }}</span>
                        </div>

                        @foreach($group['students'] as $i => $s)
                            <label class="student-chip" data-name="{{ $s->firstname }} {{ $s->lastname }}" data-class="{{ \App\Http\Controllers\Helper::recordMdname($s->senior) ?: $s->senior }}">
                                <input type="radio" name="primary_{{ $group['key'] }}" value="{{ $s->id }}" {{ $i === 0 ? 'checked' : '' }}>
                                <div>
                                    <div class="name">{{ $s->firstname }} {{ $s->lastname }}
                                        <span style="font-weight:400;color:var(--t3);">
                                            — keep as {{ $group['type'] === 'duplicate' ? 'the one record' : 'the primary record' }}
                                        </span>
                                    </div>
                                    <div class="meta">
                                        Class: {{ \App\Http\Controllers\Helper::recordMdname($s->senior) ?: $s->senior }}
                                        &nbsp;·&nbsp; Stream: {{ $s->stream }}
                                        &nbsp;·&nbsp; Gender: {{ $s->gender }}
                                        @if($s->date_of_birth) &nbsp;·&nbsp; DOB: {{ $s->date_of_birth }} @endif
                                        @if($s->admission_number) &nbsp;·&nbsp; Adm#: {{ $s->admission_number }} @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach

                        <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;">
                            @if($group['type'] === 'duplicate')
                                <button type="button" class="btn-danger-delete btn-confirm-delete"
                                    data-ids="{{ $group['students']->pluck('id')->implode(',') }}"
                                    data-group="{{ $group['key'] }}">
                                    <i class="fas fa-trash-can me-1"></i> Keep One &amp; Delete Others
                                </button>
                            @else
                                <button type="button" class="btn-link-primary btn-confirm-link"
                                    data-ids="{{ $group['students']->pluck('id')->implode(',') }}"
                                    data-group="{{ $group['key'] }}">
                                    <i class="fas fa-link me-1"></i> Link as Same Student
                                </button>
                            @endif
                            <button type="button" class="btn-dismiss btn-dismiss-group"
                                data-ids="{{ $group['students']->pluck('id')->implode(',') }}">
                                <i class="fas fa-xmark me-1"></i> {{ $group['type'] === 'duplicate' ? 'Not a Duplicate' : 'Not a Match' }}
                            </button>
                        </div>
                    </div>
                @empty
                @endforelse
                <div class="empty-state" id="suggestionsEmptyState" style="{{ $suggestions->isEmpty() ? '' : 'display:none;' }}">
                    <i class="fas fa-circle-check"></i>
                    <div style="font-weight:600;color:var(--t1);">No likely duplicates found</div>
                    <div style="font-size:.85rem;">
                        @if($classFilter || $streamFilter)
                            Nothing matches this filter right now.
                        @else
                            Every record currently looks like a unique student.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Manual link tool ── --}}
    <div class="card">
        <div class="card-hd">
            <div>
                <h5><i class="fas fa-hand-pointer me-1" style="color:var(--b);"></i> Manually Link Two Records</h5>
                <p>Search and pick the two enrollment rows that belong to the same child (useful when the automatic match misses something).</p>
            </div>
        </div>
        <div class="card-bd">
            <div class="manual-link-box">
                <div>
                    <label>Primary record (keep this one as the master profile)</label>
                    <div class="search-wrap">
                        <input type="text" id="manualSearchA" placeholder="Search by name or admission number…" autocomplete="off">
                        <div class="search-results" id="manualResultsA"></div>
                    </div>
                    <div class="picked-pill mt-2" id="pickedA"></div>
                </div>
                <div style="text-align:center;padding-bottom:10px;"><i class="fas fa-equals" style="color:var(--t3);"></i></div>
                <div>
                    <label>Duplicate enrollment (the other program record)</label>
                    <div class="search-wrap">
                        <input type="text" id="manualSearchB" placeholder="Search by name or admission number…" autocomplete="off">
                        <div class="search-results" id="manualResultsB"></div>
                    </div>
                    <div class="picked-pill mt-2" id="pickedB"></div>
                </div>
                <div style="padding-bottom:10px;">
                    <button type="button" class="btn-link-primary" id="btnManualLink" disabled>
                        <i class="fas fa-link me-1"></i> Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Already consolidated ── --}}
    <div class="card">
        <div class="card-hd">
            <div>
                <h5><i class="fas fa-users-line me-1" style="color:var(--g);"></i> Consolidated Students</h5>
                <p>Students already linked across programs — counted once on the School Dashboard. Search here if you need to undo a mistaken link.</p>
            </div>
        </div>
        <div class="card-bd">

            <form method="GET" action="{{ route('students.consolidation') }}" class="consolidated-search">
                @if($classFilter) <input type="hidden" name="class" value="{{ $classFilter }}"> @endif
                @if($streamFilter) <input type="hidden" name="stream" value="{{ $streamFilter }}"> @endif
                <input type="text" name="q" value="{{ $consolidatedSearch }}" placeholder="Search consolidated students by name or admission number…">
                <button type="submit"><i class="fas fa-search me-1"></i> Search</button>
                @if($consolidatedSearch)
                    <a href="{{ route('students.consolidation', array_filter(['class' => $classFilter, 'stream' => $streamFilter])) }}" class="clear-search">
                        <i class="fas fa-xmark me-1"></i> Clear
                    </a>
                @endif
            </form>

            <div id="consolidatedList">
                @forelse($consolidated as $primary)
                    <div class="consolidated-row" data-primary-id="{{ $primary->id }}">
                        <div>
                            <div style="font-weight:700;color:var(--t1);">{{ $primary->firstname }} {{ $primary->lastname }}</div>
                            <div style="font-size:.78rem;color:var(--t2);">
                                Primary record · {{ \App\Http\Controllers\Helper::recordMdname($primary->senior) ?: $primary->senior }} ({{ $primary->stream }})
                                @if($primary->admission_number) · Adm# {{ $primary->admission_number }} @endif
                            </div>
                        </div>
                        <div>
                            @foreach($primary->linkedRecords as $child)
                                <span class="program-chip">
                                    {{ \App\Http\Controllers\Helper::recordMdname($child->senior) ?: $child->senior }}
                                    <i class="fas fa-link-slash unlink" data-id="{{ $child->id }}" title="Unlink this enrollment"></i>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
            <div class="empty-state" id="consolidatedEmptyState" style="padding:1.5rem 1rem;{{ $consolidated->isEmpty() ? '' : 'display:none;' }}">
                <div style="font-size:.85rem;">
                    @if($consolidatedSearch)
                        No consolidated student matches "{{ $consolidatedSearch }}".
                    @else
                        No students have been consolidated yet.
                    @endif
                </div>
            </div>

            @if($consolidated->total() > 10)
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.85rem;flex-wrap:wrap;gap:.5rem;">
                    <span style="font-size:.78rem;color:var(--t3);">
                        Showing {{ $consolidated->firstItem() }}–{{ $consolidated->lastItem() }} of {{ $consolidated->total() }}
                    </span>
                    {{ $consolidated->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
    </div>
    </div>

    <div class="toast-msg" id="toastMsg"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const CSRF = '{{ $csrf }}';
        const SEARCH_URL = '{{ route('students.consolidation.search') }}';
        const LINK_URL = '{{ route('students.consolidation.link') }}';
        const UNLINK_URL = '{{ route('students.consolidation.unlink') }}';
        const DISMISS_URL = '{{ route('students.consolidation.dismiss') }}';
        const DELETE_URL = '{{ route('students.consolidation.delete') }}';

        // ── Class → Stream dependent filter ──
        const STREAMS_BY_CLASS = @json($streamsByClass);
        const CURRENT_STREAM_FILTER = @json($streamFilter);
        const $filterClass = document.getElementById('filterClass');
        const $filterStream = document.getElementById('filterStream');

        function rebuildStreamOptions(selectedClass, preserveStream) {
            const streams = selectedClass ? (STREAMS_BY_CLASS[selectedClass] || []) : [];
            $filterStream.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = selectedClass
                ? (streams.length ? 'All streams in this class' : 'No streams found for this class')
                : 'Select a class first';
            $filterStream.appendChild(placeholder);

            streams.forEach(stream => {
                const opt = document.createElement('option');
                opt.value = stream;
                opt.textContent = stream;
                if (preserveStream && stream === preserveStream) opt.selected = true;
                $filterStream.appendChild(opt);
            });

            $filterStream.disabled = !selectedClass;
        }

        if ($filterClass && $filterStream) {
            // Initial state on page load — keep whatever stream came back in the URL selected.
            rebuildStreamOptions($filterClass.value, CURRENT_STREAM_FILTER);

            $filterClass.addEventListener('change', function () {
                rebuildStreamOptions(this.value, null);
            });
        }

        // ── SweetAlert2 toast (top-right, auto-dismiss) ──
        const ToastMixin = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#0f172a',
            color: '#fff',
            didOpen: (el) => {
                el.addEventListener('mouseenter', Swal.stopTimer);
                el.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        function toast(msg, type = 'success') {
            ToastMixin.fire({
                iconHtml: type === 'success' ? '✓' : '!',
                iconColor: type === 'success' ? '#34d399' : '#f87171',
                title: msg
            });
        }

        // ── SweetAlert2 confirm dialog (replaces confirm()) ──
        async function confirmDialog({ title, text, confirmText = 'Yes', icon = 'warning', confirmColor = '#dc2626' }) {
            const r = await Swal.fire({
                icon, title, text,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel',
                confirmButtonColor: confirmColor,
                reverseButtons: true
            });
            return r.isConfirmed;
        }

        async function postJson(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify(body)
            });
            return res.json();
        }

        function collapseAndRemove(el, afterRemove) {
            if (!el) { if (afterRemove) afterRemove(); return; }
            el.style.maxHeight = el.scrollHeight + 'px';
            requestAnimationFrame(() => el.classList.add('collapsing'));
            setTimeout(() => { el.remove(); if (afterRemove) afterRemove(); }, 380);
        }

        const stat = {
            el: (id) => document.getElementById(id),
            get: (id) => parseInt(stat.el(id).textContent.replace(/,/g, ''), 10) || 0,
            set: (id, val) => { stat.el(id).textContent = val.toLocaleString(); },
            bump: (id, delta) => stat.set(id, Math.max(0, stat.get(id) + delta)),
        };

        function toggleEmptyState(listSelectorAll, emptyStateId) {
            const remaining = document.querySelectorAll(listSelectorAll).length;
            const emptyEl = document.getElementById(emptyStateId);
            if (emptyEl) emptyEl.style.display = remaining ? 'none' : '';
        }

        function prependConsolidatedRow(primary, children) {
            const list = document.getElementById('consolidatedList');
            const row = document.createElement('div');
            row.className = 'consolidated-row';
            row.dataset.primaryId = primary.id;
            row.innerHTML = `
                <div>
                    <div style="font-weight:700;color:var(--t1);">${primary.name}</div>
                    <div style="font-size:.78rem;color:var(--t2);">Primary record · ${primary.class || ''}</div>
                </div>
                <div>
                    ${children.map(c => `
                        <span class="program-chip">
                            ${c.class || ''}
                            <i class="fas fa-link-slash unlink" data-id="${c.id}" title="Unlink this enrollment"></i>
                        </span>
                    `).join('')}
                </div>`;
            list.prepend(row);
            row.querySelectorAll('.unlink').forEach(wireUnlink);
            toggleEmptyState('#consolidatedList .consolidated-row', 'consolidatedEmptyState');
        }

        // ── Suggested groups: link (different program) ──
        document.querySelectorAll('.btn-confirm-link').forEach(btn => {
            btn.addEventListener('click', async function () {
                const group = this.dataset.group;
                const card = this.closest('.suggestion');
                const primaryInput = document.querySelector(`input[name="primary_${group}"]:checked`);
                if (!primaryInput) { toast('Pick a primary record first.', 'error'); return; }

                const primaryChip = primaryInput.closest('.student-chip');
                const primaryName = primaryChip.dataset.name;
                const primaryClass = primaryChip.dataset.class;

                const duplicateIds = this.dataset.ids.split(',').filter(id => id !== primaryInput.value);
                const linkedChildren = [];

                this.classList.add('btn-loading');
                card.querySelectorAll('.btn-dismiss-group, .btn-confirm-delete').forEach(b => b.setAttribute('disabled', 'disabled'));

                for (const dupId of duplicateIds) {
                    const r = await postJson(LINK_URL, { primary_student_id: primaryInput.value, duplicate_student_id: dupId });
                    if (!r.status) {
                        toast(r.message || 'Could not link records.', 'error');
                        this.classList.remove('btn-loading');
                        card.querySelectorAll('.btn-dismiss-group, .btn-confirm-delete').forEach(b => b.removeAttribute('disabled'));
                        return;
                    }
                    const dupChip = card.querySelector(`.student-chip input[value="${dupId}"]`)?.closest('.student-chip');
                    linkedChildren.push({ id: dupId, class: dupChip?.dataset.class });
                }

                stat.bump('statUniqueStudents', -duplicateIds.length);
                stat.bump('statMultiProgram', duplicateIds.length);
                stat.bump('statPendingReview', -(duplicateIds.length + 1));

                card.innerHTML = `
                    <div style="display:flex;align-items:center;gap:14px;padding:.4rem .2rem;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--gl);color:var(--g);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <strong style="display:block;color:var(--t1);font-size:.92rem;">Linked as one student</strong>
                            <span style="color:var(--t2);font-size:.78rem;">${primaryName} now covers ${duplicateIds.length + 1} enrollment record(s).</span>
                        </div>
                    </div>`;

                toast('Linked — counted as one student from now on.');
                prependConsolidatedRow({ id: primaryInput.value, name: primaryName, class: primaryClass }, linkedChildren);

                setTimeout(() => {
                    collapseAndRemove(card, () => toggleEmptyState('#suggestionsContainer .suggestion', 'suggestionsEmptyState'));
                }, 900);
            });
        });

        // ── Suggested groups: delete exact duplicates (same class) ──
        document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
            btn.addEventListener('click', async function () {
                const group = this.dataset.group;
                const card = this.closest('.suggestion');
                const keepInput = document.querySelector(`input[name="primary_${group}"]:checked`);
                if (!keepInput) { toast('Pick which record to keep first.', 'error'); return; }

                const deleteIds = this.dataset.ids.split(',').filter(id => id !== keepInput.value);
                const keepName = keepInput.closest('.student-chip').dataset.name;

                const ok = await confirmDialog({
                    title: `Delete ${deleteIds.length} duplicate record(s)?`,
                    text: `This permanently removes the extra enrollment row(s) for ${keepName} in this class. This can't be undone.`,
                    confirmText: 'Yes, delete duplicates',
                    icon: 'warning',
                    confirmColor: '#dc2626'
                });
                if (!ok) return;

                this.classList.add('btn-loading');
                card.querySelector('.btn-dismiss-group')?.setAttribute('disabled', 'disabled');

                const r = await postJson(DELETE_URL, { keep_student_id: keepInput.value, delete_student_ids: deleteIds });
                if (!r.status) {
                    toast(r.message || 'Could not delete the duplicate record(s).', 'error');
                    this.classList.remove('btn-loading');
                    card.querySelector('.btn-dismiss-group')?.removeAttribute('disabled');
                    return;
                }

                stat.bump('statTotalRecords', -deleteIds.length);
                stat.bump('statUniqueStudents', -deleteIds.length);
                stat.bump('statPendingReview', -(deleteIds.length + 1));

                card.innerHTML = `
                    <div style="display:flex;align-items:center;gap:14px;padding:.4rem .2rem;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--rl);color:var(--r);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                            <i class="fas fa-trash-can"></i>
                        </div>
                        <div>
                            <strong style="display:block;color:var(--t1);font-size:.92rem;">Duplicate(s) removed</strong>
                            <span style="color:var(--t2);font-size:.78rem;">${keepName} is now the only record for that class.</span>
                        </div>
                    </div>`;

                toast(r.message || 'Duplicate record(s) deleted.');
                setTimeout(() => {
                    collapseAndRemove(card, () => toggleEmptyState('#suggestionsContainer .suggestion', 'suggestionsEmptyState'));
                }, 900);
            });
        });

        // ── Suggested groups: dismiss (works for duplicate or multi-program groups, any size) ──
        document.querySelectorAll('.btn-dismiss-group').forEach(btn => {
            btn.addEventListener('click', async function () {
                const ids = this.dataset.ids.split(',');
                if (ids.length < 2) return;
                const card = this.closest('.suggestion');
                const isDuplicate = card.dataset.type === 'duplicate';

                const ok = await confirmDialog({
                    title: isDuplicate ? 'Not actually duplicates?' : 'Not a match?',
                    text: 'We won\'t suggest linking these records again.',
                    confirmText: 'Confirm',
                    icon: 'question',
                    confirmColor: '#2f2ccb'
                });
                if (!ok) return;

                this.classList.add('btn-loading');
                card.querySelectorAll('.btn-confirm-link, .btn-confirm-delete').forEach(b => b.setAttribute('disabled', 'disabled'));

                const r = await postJson(DISMISS_URL, { student_ids: ids });
                if (!r.status) {
                    toast(r.message || 'Could not dismiss this suggestion.', 'error');
                    this.classList.remove('btn-loading');
                    card.querySelectorAll('.btn-confirm-link, .btn-confirm-delete').forEach(b => b.removeAttribute('disabled'));
                    return;
                }

                stat.bump('statPendingReview', -ids.length);
                toast(r.message || 'Got it — won\'t suggest that match again.');
                collapseAndRemove(card, () => toggleEmptyState('#suggestionsContainer .suggestion', 'suggestionsEmptyState'));
            });
        });

        // ── Unlink from consolidated list ──
        function wireUnlink(icon) {
            if (!icon || icon._wired) return;
            icon._wired = true;
            icon.addEventListener('click', async function () {
                const ok = await confirmDialog({
                    title: 'Unlink this record?',
                    text: 'It will be counted as a separate student again.',
                    confirmText: 'Yes, unlink'
                });
                if (!ok) return;

                const chip = this.closest('.program-chip');
                const row = this.closest('.consolidated-row');

                chip.style.opacity = '.5';
                const r = await postJson(UNLINK_URL, { student_id: this.dataset.id });
                if (!r.status) { toast(r.message || 'Could not unlink.', 'error'); chip.style.opacity = '1'; return; }

                stat.bump('statUniqueStudents', 1);
                stat.bump('statMultiProgram', -1);
                toast(r.message || 'Unlinked.');

                collapseAndRemove(chip, () => {
                    if (row && !row.querySelector('.program-chip')) {
                        collapseAndRemove(row, () => toggleEmptyState('#consolidatedList .consolidated-row', 'consolidatedEmptyState'));
                    }
                });
            });
        }
        document.querySelectorAll('.unlink').forEach(wireUnlink);

        // ── Manual search-and-link ──
        function wireManualSearch(inputId, resultsId, pickedId, onPick) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            let timer = null;

            input.addEventListener('input', function () {
                clearTimeout(timer);
                const term = this.value.trim();
                if (term.length < 2) { results.style.display = 'none'; return; }
                timer = setTimeout(async () => {
                    const res = await fetch(`${SEARCH_URL}?term=${encodeURIComponent(term)}`, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    results.innerHTML = '';
                    (data.results || []).forEach(s => {
                        const div = document.createElement('div');
                        div.className = 'res-item';
                        div.innerHTML = `<div class="n">${s.name}</div><div class="m">${s.class || ''} · ${s.stream || ''} · ${s.gender || ''}${s.already_linked ? ' · already linked' : ''}</div>`;
                        div.addEventListener('click', () => {
                            onPick(s);
                            results.style.display = 'none';
                            input.value = '';
                        });
                        results.appendChild(div);
                    });
                    results.style.display = data.results && data.results.length ? 'block' : 'none';
                }, 250);
            });

            document.addEventListener('click', (e) => {
                if (!results.contains(e.target) && e.target !== input) results.style.display = 'none';
            });
        }

        let pickedStudentA = null;
        let pickedStudentB = null;

        function renderPicked(pillId, student, clearFn) {
            const pill = document.getElementById(pillId);
            if (!student) { pill.style.display = 'none'; pill.innerHTML = ''; return; }
            pill.style.display = 'inline-flex';
            pill.innerHTML = `<i class="fas fa-user"></i> ${student.name} <i class="fas fa-xmark remove"></i>`;
            pill.querySelector('.remove').addEventListener('click', clearFn);
        }

        function refreshLinkButton() {
            const btn = document.getElementById('btnManualLink');
            btn.disabled = !(pickedStudentA && pickedStudentB && pickedStudentA.id !== pickedStudentB.id);
        }

        wireManualSearch('manualSearchA', 'manualResultsA', 'pickedA', (s) => {
            pickedStudentA = s;
            renderPicked('pickedA', s, () => { pickedStudentA = null; renderPicked('pickedA', null); refreshLinkButton(); });
            refreshLinkButton();
        });
        wireManualSearch('manualSearchB', 'manualResultsB', 'pickedB', (s) => {
            pickedStudentB = s;
            renderPicked('pickedB', s, () => { pickedStudentB = null; renderPicked('pickedB', null); refreshLinkButton(); });
            refreshLinkButton();
        });

        document.getElementById('btnManualLink').addEventListener('click', async function () {
            if (!pickedStudentA || !pickedStudentB) return;
            this.classList.add('btn-loading');
            this.disabled = true;

            const r = await postJson(LINK_URL, { primary_student_id: pickedStudentA.id, duplicate_student_id: pickedStudentB.id });
            if (!r.status) {
                toast(r.message || 'Could not link records.', 'error');
                this.classList.remove('btn-loading');
                this.disabled = false;
                return;
            }

            stat.bump('statUniqueStudents', -1);
            stat.bump('statMultiProgram', 1);
            toast('Linked — counted as one student from now on.');
            prependConsolidatedRow(pickedStudentA, [pickedStudentB]);

            pickedStudentA = null;
            pickedStudentB = null;
            renderPicked('pickedA', null);
            renderPicked('pickedB', null);
            this.classList.remove('btn-loading');
            refreshLinkButton();
        });
    </script>

@endsection