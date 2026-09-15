@extends('layouts-side-bar.master')

@section('css')
    <style>
        .nt-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: 1.5rem;
        }

        .nt-hero .hero-badge {
            background: rgba(44, 41, 202, 0.25);
            border: 1px solid rgba(107, 105, 232, 0.5);
            color: #c7c5ff;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .nt-hero .hero-title { font-size: 1.4rem; font-weight: 800; color: #fff; margin: .25rem 0; }
        .nt-hero .hero-subtitle { color: rgba(255, 255, 255, .68); font-size: .85rem; max-width: 760px; }

        .nt-card {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            /* NOTE: overflow:hidden intentionally NOT set here — it was
               clipping the searchable Subject dropdown. Scoped below. */
            margin-bottom: 1.5rem;
        }

        /* Only cards that don't contain overflowing dropdowns get clipped
           (e.g. the table card, for its rounded header top). */
        .nt-card.nt-card--clip {
            overflow: hidden;
        }

        .nt-card .card-header-custom {
            padding: 1.1rem 1.6rem;
            border-bottom: 2px solid #f0eeff;
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .nt-card .card-body-custom { padding: 1.4rem 1.6rem; }

        .nt-table { margin-bottom: 0; font-size: .87rem; }

        .nt-table thead th {
            background: #2C29CA;
            color: #fff;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            padding: .85rem .9rem;
            border: none;
            white-space: nowrap;
        }

        .nt-table tbody td { vertical-align: top; padding: .85rem .9rem; border-bottom: 1px solid #f0eeff; }
        .nt-table tbody td:first-child { font-weight: 700; color: #1e1b4b; white-space: nowrap; }

        .achievement-text { font-size: .84rem; color: #34325c; line-height: 1.5; }
        .achievement-empty { font-size: .82rem; color: #a3a0c9; font-style: italic; }

        .achievement-textarea {
            width: 100%; min-height: 70px; border: 1.5px solid #e4e2ff; border-radius: .6rem;
            padding: .5rem .7rem; font-size: .84rem; resize: vertical;
        }

        .btn-edit-sm { background: #2C29CA; color: #fff; }
        .btn-edit-sm:hover { background: #211ea3; color: #fff; }
        .btn-view-sm { background: #1e1b4b; color: #fff; }
        .btn-view-sm:hover { background: #14123a; color: #fff; }
        .btn-del-sm { background: #dc3545; color: #fff; }
        .btn-del-sm:hover { background: #b3212f; color: #fff; }
        .btn-save-sm { background: #16a34a; color: #fff; }
        .btn-save-sm:hover { background: #128040; color: #fff; }

        .nt-action-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            border: none; border-radius: .55rem; padding: .4rem .8rem;
            font-size: .76rem; font-weight: 700; cursor: pointer;
            margin: .15rem .3rem .15rem 0;
        }

        .topic-name-text { font-weight: 700; color: #1e1b4b; display: block; margin-bottom: .4rem; }

        /* Every Topic needs exactly one Subject Achievement statement —
        this replaces the old plain-italic "Not set yet." with an actual
        warning pill, the same visual language as the "incomplete" flag on
        the A-Level combinations screen, so a gap here reads as something
        to fix rather than just an FYI. */
        .achievement-flag {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .74rem; font-weight: 700; color: #b3261e;
            background: #fdecea; border: 1px solid #f6c4c0; border-radius: 999px;
            padding: .35rem .75rem;
        }

        .achievement-summary-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .72rem; font-weight: 700; border-radius: 999px;
            padding: .3rem .8rem; margin-left: .5rem;
        }

        .achievement-summary-pill.is-complete { background: #eafaf0; color: #16a34a; }
        .achievement-summary-pill.is-incomplete { background: #fdecea; color: #b3261e; }

        .empty-state { text-align: center; padding: 2.5rem 1rem; color: #a3a0c9; }

        .nt-modal-overlay {
            position: fixed; inset: 0; background: rgba(15, 23, 42, .55);
            z-index: 9000; display: none; align-items: center; justify-content: center; padding: 1rem;
        }

        .nt-modal-overlay.open { display: flex; }

        .nt-modal-box {
            background: #fff; border-radius: 1.25rem; width: 100%; max-width: 560px;
            max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;
        }

        .nt-modal-hd {
            padding: 1.1rem 1.4rem; background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            display: flex; align-items: center; justify-content: space-between;
        }

        .nt-modal-hd h4 { margin: 0; font-size: .98rem; font-weight: 700; color: #fff; }

        .nt-modal-close {
            width: 30px; height: 30px; border-radius: .5rem; background: rgba(255,255,255,.15);
            border: none; color: #fff; cursor: pointer;
        }

        .nt-modal-body { padding: 1.4rem; overflow-y: auto; flex: 1; }
        .nt-modal-ft { padding: 1rem 1.4rem; border-top: 1px solid #f0eeff; display: flex; gap: .6rem; justify-content: flex-end; }

        /* ===== Filter bar: keeps Assessment Type | Senior | Subject on one line ===== */
        .nt-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .nt-filter-form {
            display: flex;
            flex-wrap: nowrap;
            gap: 1rem;
            align-items: flex-end;
            flex: 1 1 auto;
            min-width: 0;
        }

        .nt-filter-field {
            flex: 1 1 0;
            min-width: 0;
        }

        .nt-filter-field .form-label {
            display: block;
            margin-bottom: .35rem;
            font-size: .8rem;
            font-weight: 600;
            color: #1e1b4b;
            white-space: nowrap;
        }

        /* Filter card: must not clip its overflowing dropdown panel, and
           needs to paint above the table card below while it's open. */
        .nt-filter-card {
            position: relative;
            z-index: 50;
        }

        /* ===== Searchable Subject dropdown — identical to the one on
        Activities of Integration / Projects, copied verbatim so all three
        Assessment Type screens look and behave the same. ===== */
        .nt-select { position: relative; }

        .nt-select-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            background: #fff;
            border: 1.5px solid #e4e2ff;
            border-radius: .65rem;
            padding: .55rem .85rem;
            font-size: .875rem;
            color: #1e1b4b;
            text-align: left;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .nt-select-trigger:hover { border-color: #b9b6f0; }

        .nt-select-trigger:focus-visible {
            outline: none;
            border-color: #2C29CA;
            box-shadow: 0 0 0 3.5px rgba(44, 41, 202, .14);
        }

        .nt-select.open .nt-select-trigger {
            border-color: #2C29CA;
            box-shadow: 0 0 0 3.5px rgba(44, 41, 202, .14);
        }

        .nt-select-value {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nt-select-value.is-placeholder { color: #a3a0c9; }

        .nt-select-caret {
            color: #7a7894;
            font-size: .72rem;
            transition: transform .22s ease, color .22s ease;
        }

        .nt-select.open .nt-select-caret { transform: rotate(180deg); color: #2C29CA; }

        .nt-select-panel {
            position: absolute;
            top: calc(100% + .45rem);
            left: 0;
            right: 0;
            min-width: 220px;
            background: #fff;
            border: 1.5px solid #e4e2ff;
            border-radius: .9rem;
            box-shadow: 0 14px 40px rgba(30, 27, 75, .16), 0 2px 8px rgba(30, 27, 75, .06);
            z-index: 2000;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-6px) scale(.98);
            transform-origin: top center;
            pointer-events: none;
            transition: opacity .16s ease, transform .16s ease;
        }

        .nt-select.open .nt-select-panel {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .nt-select-search {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .6rem .85rem;
            background: #fbfaff;
            border-bottom: 1px solid #f0eeff;
        }

        .nt-select-search > i.fa-search { color: #a3a0c9; font-size: .78rem; }

        .nt-select-search-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-size: .85rem;
            color: #1e1b4b;
            min-width: 0;
        }

        .nt-select-search-input::placeholder { color: #b3b0d4; }

        .nt-select-clear {
            display: none;
            border: none;
            background: transparent;
            color: #b3b0d4;
            cursor: pointer;
            padding: 0 .15rem;
            font-size: .78rem;
            line-height: 1;
        }

        .nt-select.has-query .nt-select-clear { display: block; }
        .nt-select-clear:hover { color: #dc3545; }

        .nt-select-list {
            list-style: none;
            margin: 0;
            padding: .35rem;
            max-height: 230px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .nt-select-list::-webkit-scrollbar { width: 6px; }
        .nt-select-list::-webkit-scrollbar-thumb { background: #d9d6f5; border-radius: 99px; }
        .nt-select-list::-webkit-scrollbar-thumb:hover { background: #b9b6f0; }

        .nt-select-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding: .5rem .7rem;
            border-radius: .55rem;
            font-size: .85rem;
            color: #34325c;
            cursor: pointer;
            transition: background .12s ease, color .12s ease;
        }

        .nt-select-option:hover,
        .nt-select-option.is-active { background: #eef0ff; color: #1e1b4b; }

        .nt-select-option.is-selected {
            background: #2C29CA;
            color: #fff;
            font-weight: 600;
        }

        .nt-select-option.is-selected.is-active { background: #211ea3; }

        .nt-select-tick { opacity: 0; font-size: .72rem; }
        .nt-select-option.is-selected .nt-select-tick { opacity: 1; }

        .nt-select-option mark {
            background: rgba(255, 214, 0, .45);
            color: inherit;
            padding: 0 1px;
            border-radius: 3px;
        }

        .nt-select-option.is-selected mark { background: rgba(255, 255, 255, .3); }

        .nt-select-empty {
            display: none;
            padding: 1.1rem 1rem;
            text-align: center;
            font-size: .8rem;
            color: #a3a0c9;
        }

        .nt-select.no-match .nt-select-list { display: none; }
        .nt-select.no-match .nt-select-empty { display: block; }

        @media (max-width: 768px) {
            .nt-filter-form { flex-wrap: wrap; }
            .nt-filter-field { flex: 1 1 100%; }
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-bullseye me-1"></i> Subject Achievement</span>
            <div class="hero-title">
                Subject Achievement — {{ $seniorLabel }}
                @php
                    $achievedCount = $topics->filter(fn($t) => $t->subjectAchievement)->count();
                    $totalTopics = $topics->count();
                @endphp
                @if($totalTopics > 0)
                    <span class="achievement-summary-pill {{ $achievedCount === $totalTopics ? 'is-complete' : 'is-incomplete' }}">
                        @if($achievedCount === $totalTopics)
                            <i class="fas fa-circle-check"></i> All {{ $totalTopics }} topics set
                        @else
                            <i class="fas fa-triangle-exclamation"></i> {{ $achievedCount }}/{{ $totalTopics }} topics set
                        @endif
                    </span>
                @endif
            </div>
            <div class="hero-subtitle">
                Every Topic must have exactly one Subject Achievement statement — the same Topics
                already managed from Activities of Integration. Type these in from your own copy
                of the NCDC syllabus; nothing here is pre-loaded for you.
            </div>
        </div>

        <div class="nt-card nt-filter-card">
            <div class="card-body-custom nt-filter-bar">
                <form method="GET" action="{{ route('admin.nlsc-subject-achievements') }}" id="filterForm" class="nt-filter-form">
                    {{-- Assessment Type — switches to Topics (Activities of
                    Integration) or Projects (Project Work), carrying the
                    current Senior/Subject over. --}}
                    <div class="nt-filter-field">
                        <label class="form-label">Assessment Type</label>
                        <select id="assessmentTypeSelect" class="form-control">
                            <option value="{{ route('admin.nlsc-topics', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Activities of Integration</option>
                            <option value="{{ route('admin.nlsc-projects', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Projects</option>
                            <option value="{{ route('admin.nlsc-subject-achievements', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}" selected>Subject Achievement</option>
                        </select>
                    </div>
                    <div class="nt-filter-field">
                        <label class="form-label">Senior</label>
                        <select name="senior" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            @foreach ($seniorOptions as $opt)
                                <option value="{{ $opt->md_id }}" @if((int) $opt->md_id === (int) $selectedSenior) selected @endif>{{ $opt->md_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nt-filter-field">
                        <label class="form-label">Subject</label>
                        {{-- Real <select> is hidden but still submitted with the form,
                             so the backend and onchange-submit behaviour are unchanged. --}}
                        <select name="subject" id="subjectNativeSelect" class="d-none" tabindex="-1" aria-hidden="true">
                            @foreach ($subjectOptions as $opt)
                                <option value="{{ $opt->md_id }}" @if((int) $opt->md_id === (int) $selectedSubject) selected @endif>{{ $opt->md_name }}</option>
                            @endforeach
                        </select>

                        {{-- Searchable Subject dropdown --}}
                        <div class="nt-select" id="subjectSelect" data-native="subjectNativeSelect">
                            <button type="button" class="nt-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="nt-select-value is-placeholder">Select a subject…</span>
                                <i class="fas fa-chevron-down nt-select-caret"></i>
                            </button>

                            <div class="nt-select-panel" role="dialog">
                                <div class="nt-select-search">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="nt-select-search-input" placeholder="Search subject…" autocomplete="off">
                                    <button type="button" class="nt-select-clear" aria-label="Clear search">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>

                                <ul class="nt-select-list" role="listbox"></ul>
                                <div class="nt-select-empty">
                                    <i class="fas fa-search-minus d-block mb-1"></i>
                                    No subject matches that search.
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <button type="button" id="addTopicBtn" class="btn btn-outline-primary flex-shrink-0">
                    <i class="fas fa-plus me-1"></i> Add Topic
                </button>
                <button type="button" id="addAchievementBtn" class="btn btn-primary flex-shrink-0">
                    <i class="fas fa-plus me-1"></i> Add Subject Achievement
                </button>
                <button type="button" id="deleteAllAchievementsBtn" class="btn btn-danger flex-shrink-0">
                    <i class="fas fa-trash me-1"></i> Delete All Subject Achievements
                </button>
            </div>
        </div>

        <div class="nt-card nt-card--clip">
            <div class="card-header-custom">
                <span><i class="fas fa-bullseye me-2"></i> Topics ({{ $topics->count() }})</span>
            </div>
            <div class="table-responsive">
                <table class="table nt-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th style="width:220px;">Topic</th>
                            <th>Subject Achievement</th>
                            <th style="width:180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="achievementsTbody">
                        @forelse ($topics as $i => $topic)
                            <tr data-topic-id="{{ $topic->id }}" data-achievement-id="{{ $topic->subjectAchievement->id ?? '' }}">
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <span class="topic-name-text">{{ $topic->topic_name }}</span>
                                    <button type="button" class="nt-action-btn btn-view-sm view-topic-btn"><i class="fas fa-eye"></i> View</button>
                                    <button type="button" class="nt-action-btn btn-edit-sm rename-topic-btn"><i class="fas fa-pen"></i> Edit</button>
                                    <button type="button" class="nt-action-btn btn-del-sm delete-topic-btn"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                                <td>
                                    <div class="achievement-view" @if(!$topic->subjectAchievement) style="display:none;" @endif>
                                        <div class="achievement-text">{{ $topic->subjectAchievement->achievement_text ?? '' }}</div>
                                    </div>
                                    <div class="achievement-empty" @if($topic->subjectAchievement) style="display:none;" @endif>
                                        <span class="achievement-flag"><i class="fas fa-triangle-exclamation"></i> Missing — every topic needs one</span>
                                    </div>
                                    <textarea class="achievement-textarea achievement-edit" style="display:none;">{{ $topic->subjectAchievement->achievement_text ?? '' }}</textarea>
                                </td>
                                <td>
                                    <button type="button" class="nt-action-btn btn-edit-sm edit-achievement-btn"><i class="fas fa-pen"></i> Edit</button>
                                    <button type="button" class="nt-action-btn btn-save-sm save-achievement-btn" style="display:none;"><i class="fas fa-check"></i> Save</button>
                                    <button type="button" class="nt-action-btn btn-del-sm delete-achievement-btn" @if(!$topic->subjectAchievement) style="display:none;" @endif><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state">No topics yet for {{ $seniorLabel }} — add topics first from Activities of Integration.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
     </div>
        </div>
    </div>


    {{-- ===== Add Topic modal ===== — same topic that Activities of
    Integration manages (nlsc_topics); adding, renaming or deleting a
    topic here uses the exact same endpoints that screen does, so both
    stay in sync automatically (it's the same row). --}}
    <div class="nt-modal-overlay" id="addTopicModal">
        <div class="nt-modal-box">
            <div class="nt-modal-hd">
                <h4><i class="fas fa-plus me-2"></i>Add Topic</h4>
                <button class="nt-modal-close" onclick="closeNtModal('addTopicModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <div class="form-group">
                    <label class="form-label">Topic Name</label>
                    <input type="text" id="newTopicNameInput" class="form-control" placeholder="e.g. Personal Life and Family">
                </div>
            </div>
            <div class="nt-modal-ft">
                <button class="btn btn-secondary" onclick="closeNtModal('addTopicModal')">Cancel</button>
                <button class="btn btn-primary" id="saveNewTopicBtn"><i class="fas fa-save me-1"></i> Save</button>
            </div>
        </div>
    </div>

    {{-- ===== Add Subject Achievement modal ===== —a quicker entry point
    matching Add Topic/Add Project's modal pattern, picking from whichever
    topics don't have a statement yet (topics that already have one are
    edited inline in the table instead). --}}
    <div class="nt-modal-overlay" id="addAchievementModal">
        <div class="nt-modal-box">
            <div class="nt-modal-hd">
                <h4><i class="fas fa-plus me-2"></i>Add Subject Achievement</h4>
                <button class="nt-modal-close" onclick="closeNtModal('addAchievementModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <div class="form-group">
                    <label class="form-label">Topic</label>
                    <select id="addAchievementTopicSelect" class="form-control"></select>
                </div>
                <div class="form-group mt-2">
                    <label class="form-label">Subject Achievement</label>
                    <textarea id="addAchievementTextInput" class="form-control" rows="4" placeholder="e.g. Communicates confidently about personal identity, family members, relationships, routines and responsibilities using appropriate spoken and written English."></textarea>
                </div>
            </div>
            <div class="nt-modal-ft">
                <button class="btn btn-secondary" onclick="closeNtModal('addAchievementModal')">Cancel</button>
                <button class="btn btn-primary" id="saveNewAchievementBtn"><i class="fas fa-save me-1"></i> Save</button>
            </div>
        </div>
    </div>
    {{-- ===== View Topic modal ===== — read-only: topic name + its
    Subject Achievement statement (or a prompt to add one). Competency
    Areas aren't shown here — those belong to Activities of Integration's
    own View modal for this same topic. --}}
    <div class="nt-modal-overlay" id="viewAchievementModal">
        <div class="nt-modal-box">
            <div class="nt-modal-hd">
                <h4><i class="fas fa-eye me-2"></i><span id="viewAchievementTopicName">Topic</span></h4>
                <button class="nt-modal-close" onclick="closeNtModal('viewAchievementModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <label class="form-label">Subject Achievement</label>
                <div id="viewAchievementText" class="achievement-text"></div>
                <div id="viewAchievementMissing" class="achievement-flag" style="display:none;">
                    <i class="fas fa-triangle-exclamation"></i> Missing — every topic needs one
                </div>
            </div>
            <div class="nt-modal-ft">
                <button class="btn btn-secondary" onclick="closeNtModal('viewAchievementModal')">Close</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const CSRF = '{{ csrf_token() }}';
        const SELECTED_SENIOR = '{{ $selectedSenior }}';
        const SELECTED_SUBJECT = '{{ $selectedSubject }}';

        // {id, name, hasAchievement} for every topic currently on this
        // page — drives the Add modal's "only topics without one yet"
        // dropdown without another round-trip.
        const ALL_TOPICS = [
            @foreach ($topics as $topic)
                { id: '{{ $topic->id }}', name: @json($topic->topic_name), hasAchievement: {{ $topic->subjectAchievement ? 'true' : 'false' }} },
            @endforeach
        ];

        function openNtModal(id) { document.getElementById(id).classList.add('open'); }
        function closeNtModal(id) { document.getElementById(id).classList.remove('open'); }
        document.querySelectorAll('.nt-modal-overlay').forEach(m => {
            m.addEventListener('click', e => { if (e.target === m) closeNtModal(m.id); });
        });

        document.getElementById('assessmentTypeSelect').addEventListener('change', function () {
            window.location.href = this.value;
        });

        // ===== Searchable Subject dropdown ===== (identical widget to
        // Activities of Integration / Projects)
        (function () {
            const root = document.getElementById('subjectSelect');
            if (!root) return;

            const nativeSelect = document.getElementById(root.dataset.native);
            const trigger     = root.querySelector('.nt-select-trigger');
            const valueLabel  = root.querySelector('.nt-select-value');
            const list        = root.querySelector('.nt-select-list');
            const searchInput = root.querySelector('.nt-select-search-input');
            const clearBtn    = root.querySelector('.nt-select-clear');

            const options = Array.from(nativeSelect.options).map(o => ({
                value: o.value,
                label: o.textContent.trim(),
                selected: o.selected,
            }));

            let activeIndex = options.findIndex(o => o.selected);
            let filtered    = options.slice();

            const escapeHtml = s => s.replace(/[&<>"']/g, c => (
                { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
            ));

            const highlight = (label, query) => {
                if (!query) return escapeHtml(label);
                const idx = label.toLowerCase().indexOf(query.toLowerCase());
                if (idx === -1) return escapeHtml(label);
                return escapeHtml(label.slice(0, idx))
                    + '<mark>' + escapeHtml(label.slice(idx, idx + query.length)) + '</mark>'
                    + escapeHtml(label.slice(idx + query.length));
            };

            const renderList = () => {
                const query = searchInput.value.trim();

                list.innerHTML = filtered.map((opt, i) => {
                    const isSelected = opt.value === nativeSelect.value;
                    const isActive   = i === activeIndex;
                    return `<li class="nt-select-option${isSelected ? ' is-selected' : ''}${isActive ? ' is-active' : ''}"
                                role="option" data-value="${escapeHtml(opt.value)}" data-index="${i}"
                                aria-selected="${isSelected}">
                                <span>${highlight(opt.label, query)}</span>
                                <i class="fas fa-check nt-select-tick"></i>
                            </li>`;
                }).join('');

                root.classList.toggle('no-match', filtered.length === 0);
            };

            const syncTrigger = () => {
                const sel = options.find(o => o.value === nativeSelect.value);
                if (sel) {
                    valueLabel.textContent = sel.label;
                    valueLabel.classList.remove('is-placeholder');
                } else {
                    valueLabel.textContent = 'Select a subject…';
                    valueLabel.classList.add('is-placeholder');
                }
            };

            const applyFilter = () => {
                const q = searchInput.value.trim().toLowerCase();
                filtered = q
                    ? options.filter(o => o.label.toLowerCase().includes(q))
                    : options.slice();

                const selIdx = filtered.findIndex(o => o.value === nativeSelect.value);
                activeIndex = selIdx !== -1 ? selIdx : (filtered.length ? 0 : -1);

                renderList();
            };

            const open = () => {
                root.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
                searchInput.value = '';
                root.classList.remove('has-query');
                applyFilter();
                requestAnimationFrame(() => searchInput.focus());
            };

            const close = () => {
                root.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            };

            const choose = (value) => {
                nativeSelect.value = value;
                syncTrigger();
                close();
                document.getElementById('filterForm').submit();
            };

            const moveActive = (delta) => {
                if (!filtered.length) return;
                activeIndex = (activeIndex + delta + filtered.length) % filtered.length;
                renderList();
                const activeEl = list.querySelector('.nt-select-option.is-active');
                if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
            };

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                root.classList.contains('open') ? close() : open();
            });

            searchInput.addEventListener('input', () => {
                root.classList.toggle('has-query', searchInput.value.length > 0);
                applyFilter();
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown') { e.preventDefault(); moveActive(1); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); moveActive(-1); }
                else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && filtered[activeIndex]) {
                        choose(filtered[activeIndex].value);
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    close();
                    trigger.focus();
                }
            });

            clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                searchInput.value = '';
                root.classList.remove('has-query');
                applyFilter();
                searchInput.focus();
            });

            list.addEventListener('click', (e) => {
                const opt = e.target.closest('.nt-select-option');
                if (!opt) return;
                choose(opt.dataset.value);
            });

            list.addEventListener('mousemove', (e) => {
                const opt = e.target.closest('.nt-select-option');
                if (!opt) return;
                const idx = parseInt(opt.dataset.index, 10);
                if (idx !== activeIndex) {
                    activeIndex = idx;
                    renderList();
                }
            });

            document.addEventListener('click', (e) => {
                if (!root.contains(e.target)) close();
            });

            syncTrigger();
            applyFilter();
        })();

        // ===== View Topic (name + its Subject Achievement statement) =====
        document.querySelectorAll('.view-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const name = row.querySelector('.topic-name-text').textContent.trim();
                const achievementEl = row.querySelector('.achievement-view .achievement-text');
                const hasAchievement = row.querySelector('.achievement-view').style.display !== 'none';

                document.getElementById('viewAchievementTopicName').textContent = name;
                document.getElementById('viewAchievementText').style.display = hasAchievement ? 'block' : 'none';
                document.getElementById('viewAchievementText').textContent = hasAchievement ? achievementEl.textContent.trim() : '';
                document.getElementById('viewAchievementMissing').style.display = hasAchievement ? 'none' : 'inline-flex';
                openNtModal('viewAchievementModal');
            });
        });

        // ===== Add Topic ===== (same nlsc_topics row Activities of
        // Integration manages — see NlscTopicController::store()).
        document.getElementById('addTopicBtn').addEventListener('click', () => {
            document.getElementById('newTopicNameInput').value = '';
            openNtModal('addTopicModal');
        });

        document.getElementById('saveNewTopicBtn').addEventListener('click', function () {
            const name = document.getElementById('newTopicNameInput').value.trim();
            if (!name) {
                Swal.fire('Missing name', 'Please type a topic name first.', 'warning');
                return;
            }

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            fetch(`{{ route('admin.nlsc-topics.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ senior_class_id: SELECTED_SENIOR, subject_id: SELECTED_SUBJECT, topic_name: name }),
            })
                .then(r => r.json())
                .then(res => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to add.', 'error');
                        return;
                    }
                    window.location.reload();
                })
                .catch(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
                    Swal.fire('Error', 'Failed to add — check your connection.', 'error');
                });
        });

        // ===== Rename Topic =====
        document.querySelectorAll('.rename-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const topicId = row.dataset.topicId;
                const currentName = row.querySelector('.topic-name-text').textContent.trim();

                Swal.fire({
                    title: 'Rename topic',
                    input: 'text',
                    inputValue: currentName,
                    showCancelButton: true,
                    confirmButtonColor: '#2C29CA',
                    confirmButtonText: 'Save',
                    inputValidator: (value) => (!value || !value.trim()) ? 'Please enter a topic name.' : undefined,
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('admin/nlsc-topics') }}/${topicId}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: JSON.stringify({ topic_name: result.value.trim() }),
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success) {
                                Swal.fire('Error', res.message || 'Failed to rename.', 'error');
                                return;
                            }
                            window.location.reload();
                        })
                        .catch(() => Swal.fire('Error', 'Failed to rename — check your connection.', 'error'));
                });
            });
        });

        // ===== Delete Topic ===== (also removes its Subject Achievement,
        // and any Competency Areas under Activities of Integration — both
        // cascade-delete at the DB level with the topic).
        document.querySelectorAll('.delete-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const topicId = row.dataset.topicId;
                const topicName = row.querySelector('.topic-name-text').textContent.trim();

                Swal.fire({
                    title: `Delete topic "${topicName}"?`,
                    html: 'This also deletes its Subject Achievement statement and any Competency Areas under Activities of Integration. This cannot be undone.'
                        + '<div style="margin-top:1rem; text-align:left;">'
                        + '<label style="font-size:.85rem; display:flex; align-items:center; gap:.5rem; cursor:pointer;">'
                        + '<input type="checkbox" id="swalCascadeSchoolsTopic" style="width:16px; height:16px;">'
                        + 'Also remove this from schools that already have it in their own copy'
                        + '</label></div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete',
                    preConfirm: () => document.getElementById('swalCascadeSchoolsTopic').checked,
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('admin/nlsc-topics') }}/${topicId}`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: JSON.stringify({ cascade_to_schools: result.value }),
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success) {
                                Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                                return;
                            }
                            window.location.reload();
                        })
                        .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
                });
            });
        });

        // ===== Add Subject Achievement =====
        document.getElementById('addAchievementBtn').addEventListener('click', () => {
            if (ALL_TOPICS.length === 0) {
                Swal.fire('No topics yet', '{{ addslashes($seniorLabel) }} has no topics yet for this subject — add some first from Activities of Integration, then come back here to add their achievement statements.', 'info');
                return;
            }

            const available = ALL_TOPICS.filter(t => !t.hasAchievement);
            if (available.length === 0) {
                Swal.fire('All set', 'Every topic already has an achievement statement — edit any row directly to change it.', 'info');
                return;
            }

            const select = document.getElementById('addAchievementTopicSelect');
            select.innerHTML = available.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
            document.getElementById('addAchievementTextInput').value = '';
            openNtModal('addAchievementModal');
        });

        document.getElementById('saveNewAchievementBtn').addEventListener('click', function () {
            const topicId = document.getElementById('addAchievementTopicSelect').value;
            const text = document.getElementById('addAchievementTextInput').value.trim();

            if (!text) {
                Swal.fire('Missing text', 'Please type an achievement statement first.', 'warning');
                return;
            }

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            fetch(`{{ route('admin.nlsc-subject-achievements.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ nlsc_topic_id: topicId, achievement_text: text }),
            })
                .then(r => r.json())
                .then(res => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to save.', 'error');
                        return;
                    }
                    window.location.reload();
                })
                .catch(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
                    Swal.fire('Error', 'Failed to save — check your connection.', 'error');
                });
        });

        // ===== Delete ALL Subject Achievements (this Senior/Subject) =====
        document.getElementById('deleteAllAchievementsBtn').addEventListener('click', function () {
            const setCount = ALL_TOPICS.filter(t => t.hasAchievement).length;
            if (setCount === 0) {
                Swal.fire('Nothing to delete', 'No topics have an achievement statement yet for this Senior/Subject.', 'info');
                return;
            }

            Swal.fire({
                title: `Delete all ${setCount} achievement statement(s)?`,
                html: 'The topics themselves are untouched — only their Subject Achievement text is removed. This cannot be undone.'
                    + '<div style="margin-top:1rem; text-align:left;">'
                    + '<label style="font-size:.85rem; display:flex; align-items:center; gap:.5rem; cursor:pointer;">'
                    + '<input type="checkbox" id="swalCascadeSchoolsAll" style="width:16px; height:16px;">'
                    + 'Also remove these from schools that already have them in their own copy'
                    + '</label></div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete All',
                preConfirm: () => document.getElementById('swalCascadeSchoolsAll').checked,
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ route('admin.nlsc-subject-achievements.delete-all') }}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        senior_class_id: SELECTED_SENIOR,
                        subject_id: SELECTED_SUBJECT,
                        cascade_to_schools: result.value,
                    }),
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                            return;
                        }
                        window.location.reload();
                    })
                    .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
            });
        });

        document.querySelectorAll('.edit-achievement-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                row.querySelector('.achievement-view').style.display = 'none';
                row.querySelector('.achievement-empty').style.display = 'none';
                row.querySelector('.achievement-edit').style.display = 'block';
                row.querySelector('.edit-achievement-btn').style.display = 'none';
                row.querySelector('.save-achievement-btn').style.display = 'inline-flex';
            });
        });

        document.querySelectorAll('.save-achievement-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const topicId = row.dataset.topicId;
                const text = row.querySelector('.achievement-edit').value.trim();

                if (!text) {
                    Swal.fire('Required', 'Please type an achievement statement, or use Delete instead.', 'warning');
                    return;
                }

                fetch(`{{ route('admin.nlsc-subject-achievements.store') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ nlsc_topic_id: topicId, achievement_text: text }),
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', res.message || 'Failed to save.', 'error');
                            return;
                        }
                        window.location.reload();
                    })
                    .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'));
            });
        });

        document.querySelectorAll('.delete-achievement-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const achievementId = row.dataset.achievementId;
                if (!achievementId) return;

                Swal.fire({
                    title: 'Delete this achievement statement?',
                    html: 'This cannot be undone.'
                        + '<div style="margin-top:1rem; text-align:left;">'
                        + '<label style="font-size:.85rem; display:flex; align-items:center; gap:.5rem; cursor:pointer;">'
                        + '<input type="checkbox" id="swalCascadeSchools" style="width:16px; height:16px;">'
                        + 'Also remove this from schools that already have it in their own copy'
                        + '</label></div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete',
                    preConfirm: () => document.getElementById('swalCascadeSchools').checked,
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('admin/nlsc-subject-achievements') }}/${achievementId}`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: JSON.stringify({ cascade_to_schools: result.value }),
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success) {
                                Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                                return;
                            }
                            window.location.reload();
                        })
                        .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
                });
            });
        });
    </script>
@endsection