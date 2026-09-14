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

        .nt-table tbody td { vertical-align: middle; padding: .85rem .9rem; border-bottom: 1px solid #f0eeff; }

        .nt-area-pill {
            display: inline-block;
            background: #fff2e0;
            color: #a15c00;
            border-radius: .5rem;
            padding: .25rem .6rem;
            font-size: .74rem;
            font-weight: 700;
        }

        .nt-desc-preview {
            color: #7a7894;
            font-size: .78rem;
            max-width: 260px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

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
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-diagram-project me-1"></i> Your School's NLSC Projects</span>
            <div class="hero-title">Projects — {{ $seniorLabel }}</div>
            <div class="hero-subtitle">
                This is your school's own copy — starts from the platform's starter set the first
                time you open a Senior/Subject, then it's entirely yours: add or delete
                whatever you like, it never affects other schools or the platform's master list.
            </div>
        </div>

        <div class="nt-card">
            <div class="card-body-custom" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
                <form method="GET" action="{{ route('school.nlsc-projects') }}" id="filterForm" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; flex:1;">
                    {{-- Assessment Type — switches to the Topics (Activities of
                    Integration) screen, carrying the current Senior/Subject over. --}}
                    <div style="min-width:220px;">
                        <label class="form-label">Assessment Type</label>
                        <select id="assessmentTypeSelect" class="form-control">
                            <option value="{{ route('school.nlsc-topics', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}">Activities of Integration</option>
                            <option value="{{ route('school.nlsc-projects', ['senior' => $selectedSenior, 'subject' => $selectedSubject]) }}" selected>Projects</option>
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
                        <select name="subject" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            @foreach ($subjectOptions as $opt)
                                <option value="{{ $opt->md_id }}" @if((int) $opt->md_id === (int) $selectedSubject) selected @endif>{{ $opt->md_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <button type="button" id="addProjectBtn" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Project
                </button>
                <button type="button" id="deleteAllProjectsBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Delete All Projects
                </button>
            </div>
        </div>

        <div class="nt-card">
            <div class="card-header-custom">
                <span><i class="fas fa-diagram-project me-1"></i> Projects</span>
            </div>
            <div class="table-responsive">
                <table class="table nt-table">
                    <thead>
                        <tr>
                            <th style="width:4%;">#</th>
                            <th>Project Area</th>
                            <th>Project</th>
                            <th>Description</th>
                            <th>{{ $seniorLabel }} — Competency Areas</th>
                            <th style="width:22%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="projectsTbody">
                        @forelse ($projects as $index => $project)
                            <tr data-id="{{ $project->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="nt-area-pill area-name-cell">{{ optional($project->area)->area_name }}</span>
                                    <button type="button" class="nt-action-btn btn-edit-sm edit-area-btn"
                                        data-area-id="{{ optional($project->area)->id }}"
                                        title="Rename this Project Area" style="padding:.25rem .5rem;margin-left:.25rem;"><i
                                            class="fas fa-pen" style="font-size:.62rem;"></i></button>
                                </td>
                                <td class="project-name-cell">{{ $project->project_name }}</td>
                                <td><span class="nt-desc-preview description-cell" title="{{ $project->description }}">{{ $project->description ?: '—' }}</span></td>
                                <td>
                                    <span class="nt-count-badge {{ $project->competency_areas_count == 0 ? 'is-zero' : '' }}">
                                        <i class="fas fa-list-check"></i> {{ $project->competency_areas_count }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="nt-action-btn btn-view-sm view-project-btn"><i class="fas fa-eye"></i> View</button>
                                    <button type="button" class="nt-action-btn btn-edit-sm edit-project-btn"><i class="fas fa-pen"></i> Edit</button>
                                    <button type="button" class="nt-action-btn btn-del-sm delete-project-btn"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open d-block mb-2" style="font-size:1.8rem;"></i>
                                        No projects added yet for {{ $seniorLabel }} — click "Add Project" to add the first one.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    </div>
    {{-- ===== Add / Edit Project modal ===== --}}
    <div class="nt-modal-overlay" id="projectModal">
        <div class="nt-modal-box">
            <div class="nt-modal-hd">
                <h4 id="projectModalTitle"><i class="fas fa-plus me-2"></i>Add Project</h4>
                <button class="nt-modal-close" onclick="closeNtModal('projectModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <input type="hidden" id="projectIdInput">
                <div class="form-group">
                    <label class="form-label">Project Area</label>
                    <input type="text" id="projectAreaInput" class="form-control" list="existingAreasList" placeholder="e.g. Communication">
                    <datalist id="existingAreasList">
                        @foreach($existingAreaNames as $areaName)
                            <option value="{{ $areaName }}"></option>
                        @endforeach
                    </datalist>
                    <small class="text-muted">Type an existing Project Area to add to it, or a new name to create one.</small>
                </div>
                <div class="form-group mt-2">
                    <label class="form-label">Project Name</label>
                    <input type="text" id="projectNameInput" class="form-control" placeholder="e.g. School Communication Campaign">
                </div>
                <div class="form-group mt-2">
                    <label class="form-label">Description</label>
                    <textarea id="projectDescriptionInput" class="form-control" rows="3" placeholder="e.g. Learners design a communication campaign addressing an issue affecting learners in their school."></textarea>
                </div>
            </div>
            <div class="nt-modal-ft">
                <button class="btn btn-secondary" onclick="closeNtModal('projectModal')">Cancel</button>
                <button class="btn btn-primary" id="saveProjectBtn"><i class="fas fa-save me-1"></i> Save</button>
            </div>
        </div>
    </div>

    {{-- ===== View Project — Competency Areas modal ===== --}}
    <div class="nt-modal-overlay" id="viewProjectModal">
        <div class="nt-modal-box" style="max-width:640px;">
            <div class="nt-modal-hd">
                <h4><i class="fas fa-list-check me-2"></i> Competency Areas — <span id="viewProjectName"></span></h4>
                <button class="nt-modal-close" onclick="closeNtModal('viewProjectModal')"><i class="fas fa-times"></i></button>
            </div>
            <div class="nt-modal-body">
                <input type="hidden" id="viewProjectIdInput">
                <div id="viewProjectMeta" style="font-size:.82rem; color:#5a5875; margin-bottom:1rem;"></div>
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
                <button class="btn btn-secondary" onclick="closeNtModal('viewProjectModal')">Close</button>
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

        // ===== Add / Edit Project =====
        document.getElementById('addProjectBtn').addEventListener('click', () => {
            document.getElementById('projectModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Add Project';
            document.getElementById('projectIdInput').value = '';
            document.getElementById('projectAreaInput').value = '';
            document.getElementById('projectAreaInput').disabled = false;
            document.getElementById('projectNameInput').value = '';
            document.getElementById('projectDescriptionInput').value = '';
            openNtModal('projectModal');
        });

        document.querySelectorAll('.edit-project-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                document.getElementById('projectModalTitle').innerHTML = '<i class="fas fa-pen me-2"></i> Edit Project';
                document.getElementById('projectIdInput').value = row.dataset.id;
                document.getElementById('projectAreaInput').value = row.querySelector('.area-name-cell').textContent.trim();
                // Editing never moves a project to a different area — only its
                // own name/description change here.
                document.getElementById('projectAreaInput').disabled = true;
                document.getElementById('projectNameInput').value = row.querySelector('.project-name-cell').textContent.trim();
                const desc = row.querySelector('.description-cell').getAttribute('title') || '';
                document.getElementById('projectDescriptionInput').value = desc;
                openNtModal('projectModal');
            });
        });

        document.getElementById('saveProjectBtn').addEventListener('click', function () {
            const id = document.getElementById('projectIdInput').value;
            const areaName = document.getElementById('projectAreaInput').value.trim();
            const name = document.getElementById('projectNameInput').value.trim();
            const description = document.getElementById('projectDescriptionInput').value.trim();

            if (!id && !areaName) {
                Swal.fire('Missing Project Area', 'Please type a Project Area name first.', 'warning');
                return;
            }
            if (!name) {
                Swal.fire('Missing name', 'Please type a project name first.', 'warning');
                return;
            }

            const isEdit = !!id;
            const url = isEdit
                ? `{{ url('nlsc-projects') }}/${id}`
                : `{{ route('school.nlsc-projects.store') }}`;

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    senior_class_id: SELECTED_SENIOR,
                    subject_id: SELECTED_SUBJECT,
                    project_area_name: areaName,
                    project_name: name,
                    description: description,
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

        // ===== Rename Project Area =====
        document.querySelectorAll('.edit-area-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const areaId = this.dataset.areaId;
                if (!areaId) return;
                const current = this.closest('td').querySelector('.area-name-cell').textContent.trim();

                Swal.fire({
                    title: 'Rename Project Area',
                    input: 'text',
                    inputValue: current,
                    inputValidator: (value) => !value?.trim() ? 'Enter a name' : undefined,
                    showCancelButton: true,
                    confirmButtonColor: '#2C29CA',
                    confirmButtonText: 'Save',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('nlsc-project-areas') }}/${areaId}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: JSON.stringify({ area_name: result.value.trim() }),
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

        // ===== Delete Project =====
        document.querySelectorAll('.delete-project-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const id = row.dataset.id;
                Swal.fire({
                    title: 'Delete this project?',
                    text: 'Its Competency Areas will be deleted too. This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ url('nlsc-projects') }}/${id}`, {
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

        // ===== Delete ALL Projects (this Senior/Subject) =====
        document.getElementById('deleteAllProjectsBtn').addEventListener('click', function () {
            const projectCount = document.querySelectorAll('#projectsTbody tr[data-id]').length;
            if (projectCount === 0) {
                Swal.fire('Nothing to delete', 'There are no projects for this Senior/Subject yet.', 'info');
                return;
            }

            Swal.fire({
                title: `Delete all ${projectCount} project(s)?`,
                text: 'Every Project Area, Project AND its competency areas, for this Senior/Subject only, will be permanently deleted. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete All',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ url('nlsc-projects-all') }}`, {
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

        // ===== View Project — Competency Areas =====
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

                        fetch(`{{ url('nlsc-project-competency-areas') }}/${id}`, {
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
                                openViewProjectModal(document.getElementById('viewProjectIdInput').value);
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
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Delete',
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        fetch(`{{ url('nlsc-project-competency-areas') }}/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                                    return;
                                }
                                openViewProjectModal(document.getElementById('viewProjectIdInput').value);
                                const mainRow = document.querySelector(`#projectsTbody tr[data-id="${document.getElementById('viewProjectIdInput').value}"]`);
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

        function openViewProjectModal(projectId) {
            fetch(`{{ url('nlsc-projects') }}/${projectId}/competency-areas`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        Swal.fire('Error', res.message || 'Failed to load.', 'error');
                        return;
                    }
                    document.getElementById('viewProjectIdInput').value = projectId;
                    document.getElementById('viewProjectName').textContent = res.project.project_name;
                    document.getElementById('viewProjectMeta').innerHTML =
                        `<strong>Project Area:</strong> ${res.project.area_name || '—'}` +
                        (res.project.description ? `<br><strong>Description:</strong> ${res.project.description}` : '');
                    renderCompetencyAreas(res.competency_areas);
                    openNtModal('viewProjectModal');
                })
                .catch(() => Swal.fire('Error', 'Failed to load — check your connection.', 'error'));
        }

        document.querySelectorAll('.view-project-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                openViewProjectModal(this.closest('tr').dataset.id);
            });
        });

        document.getElementById('addCompetencyBtn').addEventListener('click', function () {
            const projectId = document.getElementById('viewProjectIdInput').value;
            const input = document.getElementById('newCompetencyInput');
            const description = input.value.trim();
            if (!description) return;

            fetch(`{{ url('nlsc-projects') }}/${projectId}/competency-areas`, {
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
                    openViewProjectModal(projectId);
                    const mainRow = document.querySelector(`#projectsTbody tr[data-id="${projectId}"]`);
                    if (mainRow) {
                        const badge = mainRow.querySelector('.nt-count-badge');
                        const newCount = parseInt(badge.textContent.trim(), 10) + 1;
                        badge.innerHTML = `<i class="fas fa-list-check"></i> ${newCount}`;
                        badge.classList.remove('is-zero');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to add — check your connection.', 'error'));
        });

        // ===== Clear ALL Competency Areas (this project) =====
        document.getElementById('clearAllCompetencyBtn').addEventListener('click', function () {
            const projectId = document.getElementById('viewProjectIdInput').value;

            Swal.fire({
                title: 'Clear all competency areas?',
                text: 'The project itself stays — only its competency areas are removed. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Clear All',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`{{ url('nlsc-projects') }}/${projectId}/competency-areas-all`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', res.message || 'Failed to clear.', 'error');
                            return;
                        }
                        openViewProjectModal(projectId);
                        const mainRow = document.querySelector(`#projectsTbody tr[data-id="${projectId}"]`);
                        if (mainRow) {
                            const badge = mainRow.querySelector('.nt-count-badge');
                            badge.innerHTML = '<i class="fas fa-list-check"></i> 0';
                            badge.classList.add('is-zero');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Failed to clear — check your connection.', 'error'));
            });
        });
    </script>
@endsection