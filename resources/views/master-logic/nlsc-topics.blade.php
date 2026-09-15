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
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        /* Let the searchable dropdown panel escape the filter card */
        .nt-card.nt-card-filters { overflow: visible; }

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

        .nt-table tbody td { vertical-align: middle; padding: .85rem .9rem; border-bottom: 1px solid #f0eeff; }

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

        .btn-view-sm { background: #1e1b4b; color: #fff; }
        .btn-view-sm:hover { background: #14123a; color: #fff; }
        .btn-edit-sm { background: #2C29CA; color: #fff; }
        .btn-edit-sm:hover { background: #211ea3; color: #fff; }
        .btn-del-sm { background: #dc3545; color: #fff; }
        .btn-del-sm:hover { background: #b3212f; color: #fff; }

        .nt-action-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            border: none; border-radius: .55rem; padding: .4rem .8rem;
            font-size: .76rem; font-weight: 700; cursor: pointer;
        }

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

        .nt-comp-row {
            display: flex; align-items: flex-start; gap: .6rem;
            padding: .7rem .8rem; border: 1.5px solid #e4e2ff; border-radius: .7rem; margin-bottom: .6rem;
        }

        .nt-comp-row .desc { flex: 1; font-size: .84rem; color: #1e1b4b; line-height: 1.45; }

        /* ============================================================
           Searchable select (Subject dropdown)
           ============================================================ */
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
            z-index: 1200;
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
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-layer-group me-1"></i> NLSC Topics</span>
            <div class="hero-title">Topics — {{ $seniorLabel }}</div>
            <div class="hero-subtitle">
                Manage the Activity-of-Integration Topics and their Competency Areas for each
                Senior/Subject. Type these in from your own copy of the NCDC syllabus — nothing
                here is pre-loaded for you.
            </div>
        </div>

        <div class="nt-card nt-card-filters">
            <div class="card-body-custom" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
                <form method="GET" action="{{ route('admin.nlsc-topics') }}" id="filterForm" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; flex:1;">
                    {{-- Assessment Type — switches to the Projects (Project
                    Work) screen, carrying the current Senior/Subject over. --}}
                    <div style="min-width:220px;">
                        <label class="form-label">Assessment Type</label>
                        <select id="assessmentTypeSelect" class="form-control">
                            <option value="{{ route('admin.nlsc-topics', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}" selected>Activities of Integration</option>
                            <option value="{{ route('admin.nlsc-projects', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Projects</option>
                        </select>
                    </div>
                    <div style="min-width:200px;">
                        <label class="form-label">Senior</label>
                        <select name="senior" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            @foreach ($seniorOptions as $opt)
                                <option value="{{ $opt->md_id }}" @if((int) $opt->md_id === (int) $selectedSenior) selected @endif>{{ $opt->md_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="min-width:200px;">
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
                <button type="button" id="addTopicBtn" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Topic
                </button>
                <button type="button" id="bulkImportBtn" class="btn btn-outline-primary">
                    <i class="fas fa-file-upload me-1"></i> Bulk Import
                </button>
                <button type="button" id="deleteAllTopicsBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Delete All Topics
                </button>
            </div>
        </div>

        <div class="nt-card">
            <div class="card-header-custom">
                <span><i class="fas fa-list-check me-1"></i> Topics</span>
            </div>
            <div class="table-responsive">
                <table class="table nt-table">
                    <thead>
                        <tr>
                            <th style="width:4%;">#</th>
                            <th>Topic</th>
                            {{-- Count of this topic's own Competency Areas — click
                            "View" to see them individually. Header names the
                            Senior currently filtered to, since that's the
                            context every row in this table shares. --}}
                            <th>{{ $seniorLabel }} — Competency Areas</th>
                            <th style="width:22%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="topicsTbody">
                        @forelse ($topics as $index => $topic)
                            <tr data-id="{{ $topic->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="topic-name-cell">{{ $topic->topic_name }}</td>
                                <td>
                                    <span class="nt-count-badge {{ $topic->competency_areas_count == 0 ? 'is-zero' : '' }}">
                                        <i class="fas fa-list-check"></i> {{ $topic->competency_areas_count }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="nt-action-btn btn-view-sm view-topic-btn"><i class="fas fa-eye"></i> View</button>
                                    <button type="button" class="nt-action-btn btn-edit-sm edit-topic-btn"><i class="fas fa-pen"></i> Edit</button>
                                    <button type="button" class="nt-action-btn btn-del-sm delete-topic-btn"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open d-block mb-2" style="font-size:1.8rem;"></i>
                                        No topics added yet for {{ $seniorLabel }} — click "Add Topic" to add the first one.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Add / Edit Topic modal ===== --}}
    <div class="nt-modal-overlay" id="topicModal">
        <div class="nt-modal-box">
            <div class="nt-modal-hd">
                <h4 id="topicModalTitle"><i class="fas fa-plus me-2"></i>Add Topic</h4>
                <button class="nt-modal-close" onclick="closeNtModal('topicModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <input type="hidden" id="topicIdInput">
                <div class="form-group">
                    <label class="form-label">Topic Name</label>
                    <input type="text" id="topicNameInput" class="form-control" placeholder="e.g. Food">
                </div>
            </div>
            <div class="nt-modal-ft">
                <button class="btn btn-secondary" onclick="closeNtModal('topicModal')">Cancel</button>
                <button class="btn btn-primary" id="saveTopicBtn"><i class="fas fa-save me-1"></i> Save</button>
            </div>
        </div>
    </div>

    {{-- ===== View Topic — Competency Areas modal ===== --}}
    <div class="nt-modal-overlay" id="viewTopicModal">
        <div class="nt-modal-box" style="max-width:640px;">
            <div class="nt-modal-hd">
                <h4><i class="fas fa-list-check me-2"></i> Competency Areas — <span id="viewTopicName"></span></h4>
                <button class="nt-modal-close" onclick="closeNtModal('viewTopicModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <input type="hidden" id="viewTopicIdInput">
                <div id="competencyAreasList"></div>

                <div style="display:flex; gap:.6rem; margin-top:1rem;">
                    <input type="text" id="newCompetencyInput" class="form-control" placeholder="Add a competency area…">
                    <button type="button" id="addCompetencyBtn" class="btn btn-primary" style="white-space:nowrap;">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
            <div class="nt-modal-ft" style="justify-content:space-between;">
                <button class="btn btn-outline-danger" id="clearAllCompetencyBtn">
                    <i class="fas fa-broom me-1"></i> Clear All Competency Areas
                </button>
                <button class="btn btn-secondary" onclick="closeNtModal('viewTopicModal')">Close</button>
            </div>
        </div>
    </div>

    {{-- ===== Bulk Import modal ===== --}}
    <div class="nt-modal-overlay" id="bulkImportModal">
        <div class="nt-modal-box" style="max-width:560px;">
            <div class="nt-modal-hd">
                <h4><i class="fas fa-file-upload me-2"></i> Bulk Import Topics</h4>
                <button class="nt-modal-close" onclick="closeNtModal('bulkImportModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <p style="font-size:.85rem; color:#5a5875; line-height:1.5;">
                    Upload a spreadsheet (.xlsx, .xls or .csv) with these columns:
                </p>
                <div style="background:#f8f7ff; border:1.5px solid #e4e2ff; border-radius:.7rem; padding:.8rem 1rem; font-size:.78rem; margin-bottom:1rem;">
                    <code>senior</code> — e.g. "Senior 1"<br>
                    <code>subject</code> — e.g. "English"<br>
                    <code>topic</code> — the topic name<br>
                    <code>competency_area</code> — optional. Separate more than one with a
                    <strong> | </strong> (pipe) character. Leave blank to add just the topic —
                    competency areas can always be added later from "View".
                </div>
                <p style="font-size:.8rem; color:#8f8cae;">
                    Rows that repeat the same Senior/Subject/Topic reuse the existing topic
                    rather than duplicating it — useful for adding more competency areas to a
                    topic already on the list.
                </p>
                <input type="file" id="bulkImportFileInput" class="form-control" accept=".xlsx,.xls,.csv">
                <div id="bulkImportResult" style="margin-top:1rem; font-size:.82rem;"></div>
            </div>
            <div class="nt-modal-ft">
                <button class="btn btn-secondary" onclick="closeNtModal('bulkImportModal')">Cancel</button>
                <button class="btn btn-primary" id="submitBulkImportBtn"><i class="fas fa-upload me-1"></i> Upload &amp; Import</button>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
    <script>
        const CSRF = '{{ csrf_token() }}';
        const SELECTED_SENIOR = '{{ $selectedSenior }}';
        const SELECTED_SUBJECT = '{{ $selectedSubject }}';

        document.getElementById('assessmentTypeSelect').addEventListener('change', function () {
            window.location.href = this.value;
        });

        function openNtModal(id) { document.getElementById(id).classList.add('open'); }
        function closeNtModal(id) { document.getElementById(id).classList.remove('open'); }
        document.querySelectorAll('.nt-modal-overlay').forEach(m => {
            m.addEventListener('click', e => { if (e.target === m) closeNtModal(m.id); });
        });

        // ===== Searchable Subject dropdown =====
        (function () {
            const root = document.getElementById('subjectSelect');
            if (!root) return;

            const nativeSelect = document.getElementById(root.dataset.native);
            const trigger     = root.querySelector('.nt-select-trigger');
            const valueLabel  = root.querySelector('.nt-select-value');
            const list        = root.querySelector('.nt-select-list');
            const searchInput = root.querySelector('.nt-select-search-input');
            const clearBtn    = root.querySelector('.nt-select-clear');

            // Build the option list from the native <select>
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

                // Keep the currently selected option active if it survived the filter
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
                // Focus search on next frame so the panel transition is smooth
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
                // Submit the filter form — mirrors the previous onchange behaviour
                document.getElementById('filterForm').submit();
            };

            const moveActive = (delta) => {
                if (!filtered.length) return;
                activeIndex = (activeIndex + delta + filtered.length) % filtered.length;
                renderList();
                const activeEl = list.querySelector('.nt-select-option.is-active');
                if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
            };

            // --- Events ---
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

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!root.contains(e.target)) close();
            });

            // Initial render
            syncTrigger();
            applyFilter();
        })();

        // ===== Add / Edit Topic =====
        document.getElementById('addTopicBtn').addEventListener('click', () => {
            document.getElementById('topicModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Add Topic';
            document.getElementById('topicIdInput').value = '';
            document.getElementById('topicNameInput').value = '';
            openNtModal('topicModal');
        });

        document.querySelectorAll('.edit-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                document.getElementById('topicModalTitle').innerHTML = '<i class="fas fa-pen me-2"></i> Edit Topic';
                document.getElementById('topicIdInput').value = row.dataset.id;
                document.getElementById('topicNameInput').value = row.querySelector('.topic-name-cell').textContent.trim();
                openNtModal('topicModal');
            });
        });

        document.getElementById('saveTopicBtn').addEventListener('click', function () {
            const id = document.getElementById('topicIdInput').value;
            const name = document.getElementById('topicNameInput').value.trim();
            if (!name) {
                Swal.fire('Missing name', 'Please type a topic name first.', 'warning');
                return;
            }

            const isEdit = !!id;
            const url = isEdit
                ? `{{ url('admin/nlsc-topics') }}/${id}`
                : `{{ route('admin.nlsc-topics.store') }}`;

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    senior_class_id: SELECTED_SENIOR,
                    subject_id: SELECTED_SUBJECT,
                    topic_name: name,
                }),
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

        // ===== Delete Topic =====
        document.querySelectorAll('.delete-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const id = row.dataset.id;
                Swal.fire({
                    title: 'Delete this topic?',
                    html: 'Its Competency Areas will be deleted too. This cannot be undone.'
                        + '<div style="margin-top:1rem; text-align:left;">'
                        + '<label style="font-size:.85rem; display:flex; align-items:center; gap:.5rem; cursor:pointer;">'
                        + '<input type="checkbox" id="swalCascadeSchools" style="width:16px; height:16px;">'
                        + 'Also remove this from schools that already have it in their own copy'
                        + '</label></div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete',
                    preConfirm: () => ({ cascade: document.getElementById('swalCascadeSchools').checked }),
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('admin/nlsc-topics') }}/${id}`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: JSON.stringify({ cascade_to_schools: result.value.cascade }),
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

        // ===== Delete ALL Topics (this Senior/Subject) =====
        document.getElementById('deleteAllTopicsBtn').addEventListener('click', function () {
            const topicCount = document.querySelectorAll('#topicsTbody tr[data-id]').length;
            if (topicCount === 0) {
                Swal.fire('Nothing to delete', 'There are no topics for this Senior/Subject yet.', 'info');
                return;
            }

            Swal.fire({
                title: `Delete all ${topicCount} topic(s)?`,
                html: 'Every topic AND its competency areas, for this Senior/Subject only, will be permanently deleted. This cannot be undone.'
                    + '<div style="margin-top:1rem; text-align:left;">'
                    + '<label style="font-size:.85rem; display:flex; align-items:center; gap:.5rem; cursor:pointer;">'
                    + '<input type="checkbox" id="swalCascadeSchoolsAll" style="width:16px; height:16px;">'
                    + 'Also remove these from schools that already have them in their own copy'
                    + '</label></div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete All',
                preConfirm: () => ({ cascade: document.getElementById('swalCascadeSchoolsAll').checked }),
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ url('admin/nlsc-topics-all') }}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ senior_class_id: SELECTED_SENIOR, subject_id: SELECTED_SUBJECT, cascade_to_schools: result.value.cascade }),
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

        // ===== View Topic — Competency Areas =====
        function renderCompetencyAreas(areas) {
            const list = document.getElementById('competencyAreasList');
            if (!areas.length) {
                list.innerHTML = '<div class="text-muted" style="font-size:.85rem;">No competency areas added yet.</div>';
                return;
            }
            list.innerHTML = areas.map(a => `
                <div class="nt-comp-row" data-id="${a.id}">
                    <span class="desc">${a.description}</span>
                    <button type="button" class="nt-action-btn btn-edit-sm comp-edit-btn" style="padding:.3rem .6rem;"><i class="fas fa-pen"></i></button>
                    <button type="button" class="nt-action-btn btn-del-sm comp-del-btn" style="padding:.3rem .6rem;"><i class="fas fa-trash"></i></button>
                </div>
            `).join('');

            list.querySelectorAll('.comp-edit-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('.nt-comp-row');
                    const id = row.dataset.id;
                    const current = row.querySelector('.desc').textContent;
                    Swal.fire({
                        title: 'Edit Competency Area',
                        input: 'textarea',
                        inputValue: current,
                        showCancelButton: true,
                        confirmButtonColor: '#2C29CA',
                        confirmButtonText: 'Save',
                    }).then(result => {
                        if (!result.isConfirmed || !result.value?.trim()) return;

                        fetch(`{{ url('admin/nlsc-competency-areas') }}/${id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                            body: JSON.stringify({ description: result.value.trim() }),
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.fire('Error', res.message || 'Failed to update.', 'error');
                                    return;
                                }
                                openViewTopicModal(document.getElementById('viewTopicIdInput').value);
                            })
                            .catch(() => Swal.fire('Error', 'Failed to update — check your connection.', 'error'));
                    });
                });
            });

            list.querySelectorAll('.comp-del-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('.nt-comp-row');
                    const id = row.dataset.id;
                    Swal.fire({
                        title: 'Delete this competency area?',
                        html: '<div style="text-align:left;">'
                            + '<label style="font-size:.85rem; display:flex; align-items:center; gap:.5rem; cursor:pointer;">'
                            + '<input type="checkbox" id="swalCascadeCompArea" style="width:16px; height:16px;">'
                            + 'Also remove this from schools that already have it in their own copy'
                            + '</label></div>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Delete',
                        preConfirm: () => ({ cascade: document.getElementById('swalCascadeCompArea').checked }),
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        fetch(`{{ url('admin/nlsc-competency-areas') }}/${id}`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                            body: JSON.stringify({ cascade_to_schools: result.value.cascade }),
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                                    return;
                                }
                                openViewTopicModal(document.getElementById('viewTopicIdInput').value);
                                // Keep the row's own count badge in sync without a
                                // full page reload.
                                const mainRow = document.querySelector(`#topicsTbody tr[data-id="${document.getElementById('viewTopicIdInput').value}"]`);
                                if (mainRow) {
                                    const badge = mainRow.querySelector('.nt-count-badge');
                                    const newCount = Math.max(0, parseInt(badge.textContent.trim(), 10) - 1);
                                    badge.innerHTML = `<i class="fas fa-list-check"></i> ${newCount}`;
                                    badge.classList.toggle('is-zero', newCount === 0);
                                }
                            })
                            .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
                    });
                });
            });
        }

        function openViewTopicModal(topicId) {
            fetch(`{{ url('admin/nlsc-topics') }}/${topicId}/competency-areas`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to load.', 'error');
                        return;
                    }
                    document.getElementById('viewTopicIdInput').value = topicId;
                    document.getElementById('viewTopicName').textContent = res.topic.topic_name;
                    renderCompetencyAreas(res.competency_areas);
                    openNtModal('viewTopicModal');
                })
                .catch(() => Swal.fire('Error', 'Failed to load — check your connection.', 'error'));
        }

        document.querySelectorAll('.view-topic-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                openViewTopicModal(this.closest('tr').dataset.id);
            });
        });

        document.getElementById('addCompetencyBtn').addEventListener('click', function () {
            const topicId = document.getElementById('viewTopicIdInput').value;
            const input = document.getElementById('newCompetencyInput');
            const description = input.value.trim();
            if (!description) return;

            fetch(`{{ url('admin/nlsc-topics') }}/${topicId}/competency-areas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ description }),
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to add.', 'error');
                        return;
                    }
                    input.value = '';
                    openViewTopicModal(topicId);
                    const mainRow = document.querySelector(`#topicsTbody tr[data-id="${topicId}"]`);
                    if (mainRow) {
                        const badge = mainRow.querySelector('.nt-count-badge');
                        const newCount = parseInt(badge.textContent.trim(), 10) + 1;
                        badge.innerHTML = `<i class="fas fa-list-check"></i> ${newCount}`;
                        badge.classList.remove('is-zero');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to add — check your connection.', 'error'));
        });

        // ===== Clear ALL Competency Areas (this topic) =====
        document.getElementById('clearAllCompetencyBtn').addEventListener('click', function () {
            const topicId = document.getElementById('viewTopicIdInput').value;

            Swal.fire({
                title: 'Clear all competency areas?',
                html: 'The topic itself stays — only its competency areas are removed. This cannot be undone.'
                    + '<div style="margin-top:1rem; text-align:left;">'
                    + '<label style="font-size:.85rem; display:flex; align-items:center; gap:.5rem; cursor:pointer;">'
                    + '<input type="checkbox" id="swalCascadeClearComp" style="width:16px; height:16px;">'
                    + 'Also remove these from schools that already have them in their own copy'
                    + '</label></div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Clear All',
                preConfirm: () => ({ cascade: document.getElementById('swalCascadeClearComp').checked }),
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ url('admin/nlsc-topics') }}/${topicId}/competency-areas-all`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ cascade_to_schools: result.value.cascade }),
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', res.message || 'Failed to clear.', 'error');
                            return;
                        }
                        openViewTopicModal(topicId);
                        const mainRow = document.querySelector(`#topicsTbody tr[data-id="${topicId}"]`);
                        if (mainRow) {
                            const badge = mainRow.querySelector('.nt-count-badge');
                            badge.innerHTML = '<i class="fas fa-list-check"></i> 0';
                            badge.classList.add('is-zero');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Failed to clear — check your connection.', 'error'));
            });
        });

        // ===== Bulk Import =====
        document.getElementById('bulkImportBtn').addEventListener('click', () => {
            document.getElementById('bulkImportFileInput').value = '';
            document.getElementById('bulkImportResult').innerHTML = '';
            openNtModal('bulkImportModal');
        });

        document.getElementById('submitBulkImportBtn').addEventListener('click', function () {
            const fileInput = document.getElementById('bulkImportFileInput');
            const resultBox = document.getElementById('bulkImportResult');

            if (!fileInput.files.length) {
                Swal.fire('No file selected', 'Please choose a spreadsheet first.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importing...';
            resultBox.innerHTML = '';

            fetch(`{{ route('admin.nlsc-topics.bulk-import') }}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: formData,
            })
                .then(r => r.json())
                .then(res => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload &amp; Import';

                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Import failed.', 'error');
                        return;
                    }

                    let html = `<div style="color:#1e8e4f; font-weight:600;">${res.message}</div>`;
                    if (res.errors && res.errors.length) {
                        html += '<div style="margin-top:.6rem; max-height:160px; overflow-y:auto; background:#fff5f5; border:1.5px solid #ffd7d7; border-radius:.6rem; padding:.6rem .8rem;">';
                        html += res.errors.map(e => `<div style="color:#b3212f; font-size:.76rem; margin-bottom:.3rem;">${e}</div>`).join('');
                        html += '</div>';
                    }
                    resultBox.innerHTML = html;

                    if (res.topics_imported > 0 || res.competency_areas_imported > 0) {
                        setTimeout(() => window.location.reload(), 1800);
                    }
                })
                .catch(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload &amp; Import';
                    Swal.fire('Error', 'Import failed — check your connection.', 'error');
                });
        });
    </script>
@endsection