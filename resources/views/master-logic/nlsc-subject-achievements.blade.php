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

        .empty-state { text-align: center; padding: 2.5rem 1rem; color: #a3a0c9; }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-bullseye me-1"></i> Subject Achievement</span>
            <div class="hero-title">Subject Achievement — {{ $seniorLabel }}</div>
            <div class="hero-subtitle">
                One achievement statement per Topic — the same Topics already managed from
                Activities of Integration. Type these in from your own copy of the NCDC
                syllabus; nothing here is pre-loaded for you.
            </div>
        </div>

        <div class="nt-card">
            <div class="card-body-custom" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
                <form method="GET" action="{{ route('admin.nlsc-subject-achievements') }}" id="filterForm" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; flex:1;">
                    {{-- Assessment Type — switches to Topics (Activities of
                    Integration) or Projects (Project Work), carrying the
                    current Senior/Subject over. --}}
                    <div style="min-width:220px;">
                        <label class="form-label">Assessment Type</label>
                        <select id="assessmentTypeSelect" class="form-control">
                            <option value="{{ route('admin.nlsc-topics', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Activities of Integration</option>
                            <option value="{{ route('admin.nlsc-projects', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Projects</option>
                            <option value="{{ route('admin.nlsc-subject-achievements', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}" selected>Subject Achievement</option>
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
                                <td>{{ $topic->topic_name }}</td>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const CSRF = '{{ csrf_token() }}';

        document.getElementById('assessmentTypeSelect').addEventListener('change', function () {
            window.location.href = this.value;
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