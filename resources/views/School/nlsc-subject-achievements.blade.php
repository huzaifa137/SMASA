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
        .btn-del-sm { background: #dc3545; color: #fff; }
        .btn-del-sm:hover { background: #b3212f; color: #fff; }
        .btn-save-sm { background: #16a34a; color: #fff; }
        .btn-save-sm:hover { background: #128040; color: #fff; }

        .nt-action-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            border: none; border-radius: .55rem; padding: .4rem .8rem;
            font-size: .76rem; font-weight: 700; cursor: pointer;
        }

        .topic-name-text { font-weight: 700; color: #1e1b4b; }

        .nt-icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 24px; height: 24px; border: none; border-radius: .4rem;
            background: #f1f0ff; color: #5a57c9; font-size: .68rem; cursor: pointer;
            margin-left: .35rem; vertical-align: middle;
        }

        .nt-icon-btn.icon-btn-danger { background: #fdecec; color: #dc3545; }
        .nt-icon-btn:hover { filter: brightness(0.95); }

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
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-bullseye me-1"></i> Subject Achievement</span>
            <div class="hero-title">Subject Achievement — {{ $seniorLabel }}</div>
            <div class="hero-subtitle">
                Your school's own achievement statement per Topic — the same Topics already
                managed from Activities of Integration. An admin's edit or new achievement
                reaches your copy automatically; deleting or editing yours here only ever
                affects your school.
            </div>
        </div>

        <div class="nt-card">
            <div class="card-body-custom" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
                <form method="GET" action="{{ route('school.nlsc-subject-achievements') }}" id="filterForm" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; flex:1;">
                    {{-- Assessment Type — switches to Topics (Activities of
                    Integration) or Projects (Project Work), carrying the
                    current Senior/Subject over. --}}
                    <div style="min-width:220px;">
                        <label class="form-label">Assessment Type</label>
                        <select id="assessmentTypeSelect" class="form-control">
                            <option value="{{ route('school.nlsc-topics', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Activities of Integration</option>
                            <option value="{{ route('school.nlsc-projects', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Projects</option>
                            <option value="{{ route('school.nlsc-subject-achievements', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}" selected>Subject Achievement</option>
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
                    <div style="min-width:220px;">
                        <label class="form-label">Subject</label>
                        <select name="subject" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            @foreach ($subjectOptions as $opt)
                                <option value="{{ $opt->md_id }}" @if((int) $opt->md_id === (int) $selectedSubject) selected @endif>{{ $opt->md_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <button type="button" id="addTopicBtn" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-1"></i> Add Topic
                </button>
                <button type="button" id="addAchievementBtn" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Subject Achievement
                </button>
                <button type="button" id="deleteAllAchievementsBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Delete All Subject Achievements
                </button>
            </div>
        </div>

        <div class="nt-card">
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
                                    <button type="button" class="nt-icon-btn rename-topic-btn" title="Rename topic"><i class="fas fa-pen"></i></button>
                                    <button type="button" class="nt-icon-btn icon-btn-danger delete-topic-btn" title="Delete topic"><i class="fas fa-trash"></i></button>
                                </td>
                                <td>
                                    <div class="achievement-view" @if(!$topic->subjectAchievement) style="display:none;" @endif>
                                        <div class="achievement-text">{{ $topic->subjectAchievement->achievement_text ?? '' }}</div>
                                    </div>
                                    <div class="achievement-empty" @if($topic->subjectAchievement) style="display:none;" @endif>
                                        Not set yet.
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