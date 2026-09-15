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
            /* NOTE: overflow:hidden removed — it was clipping the searchable
               Subject dropdown. Rounded corners still work fine without it. */
            margin-bottom: 1.5rem;
        }

        /* Only the table card needs the clipping (for the rounded top of the
           header). Scope it so the filter card above stays unclipped. */
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

        .topic-name-text { font-weight: 700; color: #1e1b4b; display: block; }

        /* Count badge — identical to Activities of Integration's
        Competency Areas count, so both screens read the same way: a
        topic can have as many Subject Achievement statements as needed,
        "View" opens the list. */
        .nt-count-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .74rem;
            font-weight: 700;
            padding: .3rem .7rem;
            border-radius: .6rem;
            background: #eef0ff;
            color: #3a37b8;
        }

        .nt-count-badge.is-zero { background: #f1f5f9; color: #64748b; }

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

        .nt-ach-row {
            display: flex; align-items: flex-start; gap: .6rem;
            padding: .7rem .8rem; border: 1.5px solid #e4e2ff; border-radius: .7rem; margin-bottom: .6rem;
        }

        .nt-ach-row .desc { flex: 1; font-size: .84rem; color: #1e1b4b; line-height: 1.45; }

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

        /* The filter card must not clip its overflowing dropdown panel,
           and it needs to sit above the table card while the panel is open. */
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
            </div>
            <div class="hero-subtitle">
                Your school's own Subject Achievement statements per Topic — a topic can have as
                many as needed, the same Topics already managed from Activities of Integration. An
                admin's edit or new achievement reaches your copy automatically; deleting or
                editing yours here only ever affects your school.
            </div>
        </div>

        <div class="nt-card nt-filter-card">
            <div class="card-body-custom nt-filter-bar">
                <form method="GET" action="{{ route('school.nlsc-subject-achievements') }}" id="filterForm" class="nt-filter-form">
                    {{-- Assessment Type — switches to Topics (Activities of
                    Integration) or Projects (Project Work), carrying the
                    current Senior/Subject over. --}}
                    <div class="nt-filter-field">
                        <label class="form-label">Assessment Type</label>
                        <select id="assessmentTypeSelect" class="form-control">
                            <option value="{{ route('school.nlsc-topics', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Activities of Integration</option>
                            <option value="{{ route('school.nlsc-projects', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Projects</option>
                            <option value="{{ route('school.nlsc-subject-achievements', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}" selected>Subject Achievement</option>
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
                            <th style="width:4%;">#</th>
                            <th>Topic</th>
                            <th>{{ $seniorLabel }} — Subject Achievements</th>
                            <th style="width:22%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="achievementsTbody">
                        @forelse ($topics as $i => $topic)
                            <tr data-topic-id="{{ $topic->id }}">
                                <td>{{ $i + 1 }}</td>
                                <td class="topic-name-cell"><span class="topic-name-text">{{ $topic->topic_name }}</span></td>
                                <td>
                                    <span class="nt-count-badge {{ $topic->subject_achievements_count == 0 ? 'is-zero' : '' }}">
                                        <i class="fas fa-bullseye"></i> {{ $topic->subject_achievements_count }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="nt-action-btn btn-view-sm view-topic-btn"><i class="fas fa-eye"></i> View</button>
                                    <button type="button" class="nt-action-btn btn-edit-sm rename-topic-btn"><i class="fas fa-pen"></i> Edit</button>
                                    <button type="button" class="nt-action-btn btn-del-sm delete-topic-btn"><i class="fas fa-trash"></i> Delete</button>
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

    {{-- ===== Add Topic modal ===== — same topic that Activities of
    Integration manages (school_nlsc_topics); adding, renaming or deleting
    a topic here uses the exact same endpoints that screen does, so both
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

    {{-- ===== View Topic — Subject Achievement statements modal =====
    Mirrors Activities of Integration's "View Topic — Competency Areas"
    modal exactly: a topic can carry as many statements as needed, each
    independently addable/editable/deletable here. --}}
    <div class="nt-modal-overlay" id="viewAchievementModal">
        <div class="nt-modal-box" style="max-width:640px;">
            <div class="nt-modal-hd">
                <h4><i class="fas fa-bullseye me-2"></i> Subject Achievements — <span id="viewAchievementTopicName"></span></h4>
                <button class="nt-modal-close" onclick="closeNtModal('viewAchievementModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <input type="hidden" id="viewAchievementTopicIdInput">
                <div id="achievementsList"></div>

                <div style="display:flex; gap:.6rem; margin-top:1rem;">
                    <textarea id="newAchievementInput" class="form-control" rows="3" placeholder="Add a subject achievement statement…"></textarea>
                    <button type="button" id="addAchievementBtnInModal" class="btn btn-primary" style="white-space:nowrap; align-self:flex-start;">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
            <div class="nt-modal-ft" style="justify-content:space-between;">
                <button class="btn btn-outline-danger" id="clearAllAchievementsForTopicBtn">
                    <i class="fas fa-broom me-1"></i> Clear All for this Topic
                </button>
                <button class="btn btn-secondary" onclick="closeNtModal('viewAchievementModal')">Close</button>
            </div>
        </div>
    </div>
    </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const CSRF = '{{ csrf_token() }}';
        const SELECTED_SENIOR = '{{ $selectedSenior }}';
        const SELECTED_SUBJECT = '{{ $selectedSubject }}';

        // Total Subject Achievement statements currently on this page —
        // used only to short-circuit "Delete All" when there's nothing to
        // delete, without another round-trip.
        const TOTAL_ACHIEVEMENTS_COUNT = {{ $topics->sum('subject_achievements_count') }};

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

        // ===== View Topic — Subject Achievement statements =====
        function renderAchievements(achievements) {
            const list = document.getElementById('achievementsList');
            if (!achievements.length) {
                list.innerHTML = '<div class="text-muted" style="font-size:.85rem;">No subject achievement statements added yet.</div>';
                return;
            }
            list.innerHTML = achievements.map(a => `
                <div class="nt-ach-row" data-id="${a.id}">
                    <span class="desc">${a.achievement_text}</span>
                    <button type="button" class="nt-action-btn btn-edit-sm ach-edit-btn" style="padding:.3rem .6rem;"><i class="fas fa-pen"></i></button>
                    <button type="button" class="nt-action-btn btn-del-sm ach-del-btn" style="padding:.3rem .6rem;"><i class="fas fa-trash"></i></button>
                </div>
            `).join('');

            list.querySelectorAll('.ach-edit-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('.nt-ach-row');
                    const id = row.dataset.id;
                    const current = row.querySelector('.desc').textContent;
                    Swal.fire({
                        title: 'Edit Subject Achievement',
                        input: 'textarea',
                        inputValue: current,
                        showCancelButton: true,
                        confirmButtonColor: '#2C29CA',
                        confirmButtonText: 'Save',
                    }).then(result => {
                        if (!result.isConfirmed || !result.value?.trim()) return;

                        fetch(`{{ url('nlsc-subject-achievements') }}/${id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                            body: JSON.stringify({ achievement_text: result.value.trim() }),
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.fire('Error', res.message || 'Failed to update.', 'error');
                                    return;
                                }
                                openViewAchievementModal(document.getElementById('viewAchievementTopicIdInput').value);
                            })
                            .catch(() => Swal.fire('Error', 'Failed to update — check your connection.', 'error'));
                    });
                });
            });

            list.querySelectorAll('.ach-del-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('.nt-ach-row');
                    const id = row.dataset.id;
                    Swal.fire({
                        title: 'Delete this statement?',
                        text: 'This cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Delete',
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        fetch(`{{ url('nlsc-subject-achievements') }}/${id}`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                                    return;
                                }
                                const topicId = document.getElementById('viewAchievementTopicIdInput').value;
                                openViewAchievementModal(topicId);
                                // Keep the row's own count badge in sync without a
                                // full page reload.
                                const mainRow = document.querySelector(`#achievementsTbody tr[data-topic-id="${topicId}"]`);
                                if (mainRow) {
                                    const badge = mainRow.querySelector('.nt-count-badge');
                                    const newCount = Math.max(0, parseInt(badge.textContent.trim(), 10) - 1);
                                    badge.innerHTML = `<i class="fas fa-bullseye"></i> ${newCount}`;
                                    badge.classList.toggle('is-zero', newCount === 0);
                                }
                            })
                            .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
                    });
                });
            });
        }

        function openViewAchievementModal(topicId) {
            fetch(`{{ url('nlsc-topics') }}/${topicId}/subject-achievements`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to load.', 'error');
                        return;
                    }
                    document.getElementById('viewAchievementTopicIdInput').value = topicId;
                    document.getElementById('viewAchievementTopicName').textContent = res.topic.topic_name;
                    document.getElementById('newAchievementInput').value = '';
                    renderAchievements(res.achievements);
                    openNtModal('viewAchievementModal');
                })
                .catch(() => Swal.fire('Error', 'Failed to load — check your connection.', 'error'));
        }

        document.querySelectorAll('.view-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                openViewAchievementModal(this.closest('tr').dataset.topicId);
            });
        });

        document.getElementById('addAchievementBtnInModal').addEventListener('click', function () {
            const topicId = document.getElementById('viewAchievementTopicIdInput').value;
            const input = document.getElementById('newAchievementInput');
            const text = input.value.trim();
            if (!text) return;

            fetch(`{{ route('school.nlsc-subject-achievements.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ school_nlsc_topic_id: topicId, achievement_text: text }),
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to add.', 'error');
                        return;
                    }
                    input.value = '';
                    openViewAchievementModal(topicId);
                    const mainRow = document.querySelector(`#achievementsTbody tr[data-topic-id="${topicId}"]`);
                    if (mainRow) {
                        const badge = mainRow.querySelector('.nt-count-badge');
                        const newCount = parseInt(badge.textContent.trim(), 10) + 1;
                        badge.innerHTML = `<i class="fas fa-bullseye"></i> ${newCount}`;
                        badge.classList.remove('is-zero');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to add — check your connection.', 'error'));
        });

        // ===== Clear ALL Subject Achievements (this topic only) =====
        document.getElementById('clearAllAchievementsForTopicBtn').addEventListener('click', function () {
            const topicId = document.getElementById('viewAchievementTopicIdInput').value;

            Swal.fire({
                title: 'Clear all statements for this topic?',
                text: 'The topic itself stays — only its Subject Achievement statements are removed. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Clear All',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ url('nlsc-topics') }}/${topicId}/subject-achievements-all`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', res.message || 'Failed to clear.', 'error');
                            return;
                        }
                        openViewAchievementModal(topicId);
                        const mainRow = document.querySelector(`#achievementsTbody tr[data-topic-id="${topicId}"]`);
                        if (mainRow) {
                            const badge = mainRow.querySelector('.nt-count-badge');
                            badge.innerHTML = '<i class="fas fa-bullseye"></i> 0';
                            badge.classList.add('is-zero');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Failed to clear — check your connection.', 'error'));
            });
        });

        // ===== Add Topic ===== (same school_nlsc_topics row Activities
        // of Integration manages — see SchoolNlscTopicController::store()).
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

            fetch(`{{ route('school.nlsc-topics.store') }}`, {
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

                    fetch(`{{ url('nlsc-topics') }}/${topicId}`, {
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
        // cascade-delete at the DB level with the topic). No cascade-to-
        // schools option here — a school only ever deletes its OWN copy.
        document.querySelectorAll('.delete-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const topicId = row.dataset.topicId;
                const topicName = row.querySelector('.topic-name-text').textContent.trim();

                Swal.fire({
                    title: `Delete topic "${topicName}"?`,
                    text: 'This also deletes its Subject Achievement statement and any Competency Areas under Activities of Integration. This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('nlsc-topics') }}/${topicId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
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

            fetch(`{{ route('school.nlsc-subject-achievements.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ school_nlsc_topic_id: topicId, achievement_text: text }),
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
        // No cascade-to-schools option here — same as the single-delete
        // button, a school only ever deletes its OWN copy.
        document.getElementById('deleteAllAchievementsBtn').addEventListener('click', function () {
            const setCount = ALL_TOPICS.filter(t => t.hasAchievement).length;
            if (setCount === 0) {
                Swal.fire('Nothing to delete', 'No topics have an achievement statement yet for this Senior/Subject.', 'info');
                return;
            }

            Swal.fire({
                title: `Delete all ${setCount} achievement statement(s)?`,
                text: 'The topics themselves are untouched — only their Subject Achievement text is removed. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete All',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ route('school.nlsc-subject-achievements.delete-all') }}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ senior_class_id: SELECTED_SENIOR, subject_id: SELECTED_SUBJECT }),
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

                fetch(`{{ route('school.nlsc-subject-achievements.store') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ school_nlsc_topic_id: topicId, achievement_text: text }),
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

        // No cascade-to-schools option here — a school only ever deletes its
        // OWN copy (see SchoolNlscSubjectAchievementController::destroy()),
        // that concept only exists on the admin screen.
        document.querySelectorAll('.delete-achievement-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const achievementId = row.dataset.achievementId;
                if (!achievementId) return;

                Swal.fire({
                    title: 'Delete this achievement statement?',
                    text: 'This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('nlsc-subject-achievements') }}/${achievementId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
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