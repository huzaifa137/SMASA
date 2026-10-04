@extends('layouts-side-bar.master')

@section('css')
    <style>
        .sos-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: 1.5rem;
        }

        .sos-hero .hero-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
        }

        .sos-hero .hero-subtitle {
            color: rgba(255, 255, 255, .7);
            font-size: .85rem;
            max-width: 760px;
        }

        .sos-card {
            border: none;
            border-radius: 1.1rem;
            box-shadow: 0 4px 24px rgba(44, 41, 202, .08);
            background: #fff;
            margin-bottom: 1.5rem;
        }

        .sos-card .card-header-custom {
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #f0eeff;
            font-weight: 700;
            color: #1e1b4b;
        }

        .sos-card .card-header-custom small {
            display: block;
            font-weight: 500;
            color: #6b6a8f;
            margin-top: .15rem;
        }

        .sos-card .card-body-custom {
            padding: 1.25rem 1.5rem;
        }

        .sos-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .6rem .85rem;
            margin-bottom: .6rem;
            border: 1px solid #eef0ff;
            border-radius: .75rem;
            background: linear-gradient(135deg, #fafaff 0%, #f6f7ff 100%);
            transition: box-shadow .2s ease, border-color .2s ease;
        }

        .sos-item:hover {
            border-color: rgba(44, 41, 202, .22);
            box-shadow: 0 6px 18px rgba(44, 41, 202, .10);
        }

        .sos-item input.sos-name-input {
            flex: 1;
            min-width: 0;
            border: 1px solid transparent;
            border-radius: .55rem;
            background: transparent;
            padding: .5rem .65rem;
            font-size: .9rem;
            font-weight: 600;
            color: #1e1b4b;
        }

        .sos-item input.sos-name-input:hover {
            background: #fff;
            border-color: #e4e3ff;
        }

        .sos-item input.sos-name-input:focus {
            outline: none;
            background: #fff;
            border-color: rgba(44, 41, 202, .45);
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .08);
        }

        .sos-group-select {
            border: 1px solid #e2e3f5;
            border-radius: .55rem;
            background: #fff;
            padding: .4rem .6rem;
            font-size: .8rem;
            font-weight: 600;
            color: #3a37b8;
        }

        .sos-actions {
            display: flex;
            align-items: center;
            gap: .35rem;
            flex-shrink: 0;
        }

        .sos-actions button {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: .55rem;
            background: transparent;
            padding: 0;
            transition: background .2s ease, transform .2s ease;
        }

        .sos-actions button.sos-save,
        .sos-actions button.sos-save i {
            color: #2C29CA !important;
        }

        .sos-actions button.sos-delete,
        .sos-actions button.sos-delete i {
            color: #dc3545 !important;
        }

        .sos-actions button.sos-save:hover {
            background: rgba(44, 41, 202, .09);
            transform: translateY(-2px);
        }

        .sos-actions button.sos-delete:hover {
            background: rgba(220, 53, 69, .09);
            transform: translateY(-2px);
        }

        .sos-new-name {
            height: 42px;
            border: 1px solid #e2e3f5 !important;
            border-radius: .7rem !important;
            background: #fafaff !important;
            padding: .55rem .85rem !important;
            font-size: .88rem;
        }

        .sos-new-name:focus {
            outline: none !important;
            background: #fff !important;
            border-color: #2C29CA !important;
            box-shadow: 0 0 0 3px rgba(44, 41, 202, .10) !important;
        }

        .sos-add {
            height: 42px;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            padding: .55rem 1rem !important;
            border: none !important;
            border-radius: .7rem !important;
            background: linear-gradient(135deg, #2C29CA 0%, #4542df 100%) !important;
            color: #fff !important;
            font-size: .82rem;
            font-weight: 700;
            box-shadow: 0 5px 14px rgba(44, 41, 202, .22);
        }

        .sos-add:hover {
            filter: brightness(1.05);
            transform: translateY(-2px);
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="row px-3 px-md-4">
            <div class="col-12">

                <div class="sos-hero">
                    <span class="hero-badge"
                        style="background:rgba(44,41,202,.25);border:1px solid rgba(107,105,232,.5);color:#c7c5ff;padding:.3rem .9rem;border-radius:999px;font-size:.65rem;font-weight:700;text-transform:uppercase;">
                        <i class="fas fa-graduation-cap me-1"></i> Global Master Data
                    </span>
                    <h1 class="hero-title mt-2 mb-1">Secondary O-Level Subjects</h1>
                    <p class="hero-subtitle mb-0">
                        Decide which O-Level subjects are <strong>Compulsory</strong> (every student in a class that offers
                        the subject takes it) and which are <strong>Elective</strong> (each student chooses, up to 2, on the
                        O-Level Electives page). An elective that no student has picked has no students at all — move it to
                        Compulsory to fix that. Changes here apply to <strong>every school</strong>; a school can still add
                        its own electives from its O-Level Electives page.
                    </p>
                </div>

                @foreach($groups as $groupKey => $groupLabel)
                    <div class="sos-card" data-group="{{ $groupKey }}">
                        <div class="card-header-custom">
                            <i class="fas fa-layer-group"></i> {{ $groupLabel }} subjects
                            <small>
                                @if($groupKey === 'Core')
                                    Taken by every student in a class that offers the subject.
                                @else
                                    Chosen by each student individually (max. 2 per student).
                                @endif
                            </small>
                        </div>
                        <div class="card-body-custom">
                            <div class="sos-item-list" data-group-list="{{ $groupKey }}">
                                @forelse($groupedSubjects[$groupKey] ?? [] as $subject)
                                    <div class="sos-item" data-md-id="{{ $subject->md_id }}">
                                        <input type="text" class="sos-name-input" value="{{ $subject->md_name }}"
                                            data-original="{{ $subject->md_name }}">
                                        <select class="sos-group-select" data-original="{{ $groupKey }}"
                                            title="Compulsory or elective">
                                            @foreach($groups as $gk => $gl)
                                                <option value="{{ $gk }}" {{ $gk === $groupKey ? 'selected' : '' }}>{{ $gl }}</option>
                                            @endforeach
                                        </select>
                                        <div class="sos-actions">
                                            <button type="button" class="sos-save" title="Save"><i
                                                    class="fas fa-check"></i></button>
                                            <button type="button" class="sos-delete" title="Delete"><i
                                                    class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted" data-empty-placeholder>No subjects in this group yet.</div>
                                @endforelse
                            </div>

                            <div class="d-flex gap-2 mt-3" style="gap:.75rem;">
                                <input type="text" class="form-control form-control-sm sos-new-name"
                                    placeholder="e.g. {{ $groupKey === 'Core' ? 'Physical Education' : 'Literature in English' }}">
                                <button type="button" class="btn btn-sm sos-add"><i class="fas fa-plus"></i> Add</button>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const baseUrl = `{{ url('admin/secondary-olevel-subjects') }}`;
        const GROUPS = @json($groups);
        const lists = {};
        document.querySelectorAll('.sos-card[data-group]').forEach(c => {
            lists[c.dataset.group] = c.querySelector('[data-group-list]');
        });

        function apiCall(url, method, body) {
            return fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: body ? JSON.stringify(body) : undefined,
            }).then(r => r.json());
        }

        function escapeAttr(v) {
            return String(v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        }

        function refreshPlaceholders() {
            Object.entries(lists).forEach(([group, list]) => {
                const hasItems = !!list.querySelector('.sos-item');
                const ph = list.querySelector('[data-empty-placeholder]');
                if (hasItems && ph) ph.remove();
                if (!hasItems && !ph) {
                    const d = document.createElement('div');
                    d.className = 'text-muted';
                    d.setAttribute('data-empty-placeholder', '');
                    d.textContent = 'No subjects in this group yet.';
                    list.appendChild(d);
                }
            });
        }

        function makeItem(mdId, name, group) {
            const options = Object.entries(GROUPS)
                .map(([k, l]) => `<option value="${k}"${k === group ? ' selected' : ''}>${l}</option>`).join('');
            const item = document.createElement('div');
            item.className = 'sos-item';
            item.dataset.mdId = mdId;
            item.innerHTML = `
                    <input type="text" class="sos-name-input" value="${escapeAttr(name)}" data-original="${escapeAttr(name)}">
                    <select class="sos-group-select" data-original="${group}" title="Compulsory or elective">${options}</select>
                    <div class="sos-actions">
                        <button type="button" class="sos-save" title="Save"><i class="fas fa-check"></i></button>
                        <button type="button" class="sos-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>`;
            wireItem(item);
            return item;
        }

        function wireItem(item) {
            const input = item.querySelector('.sos-name-input');
            const select = item.querySelector('.sos-group-select');

            function save() {
                const name = input.value.trim();
                const group = select.value;
                if (!name) {
                    Swal.fire('Missing name', 'Subject name cannot be empty.', 'warning');
                    return;
                }
                if (name === input.dataset.original && group === select.dataset.original) return;

                apiCall(`${baseUrl}/${item.dataset.mdId}`, 'PUT', { subject_name: name, subject_group: group })
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', res.message || 'Failed to save.', 'error');
                            input.value = input.dataset.original;
                            select.value = select.dataset.original;
                            return;
                        }
                        input.dataset.original = name;
                        if (group !== select.dataset.original) {
                            select.dataset.original = group;
                            lists[group].appendChild(item);   // move to the other card
                            refreshPlaceholders();
                        }
                        Swal.fire({ icon: 'success', title: 'Saved', timer: 1400, showConfirmButton: false });
                    })
                    .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'));
            }

            item.querySelector('.sos-save').addEventListener('click', function () {
                const group = select.value;
                if (group !== select.dataset.original) {
                    const toElective = group === 'Elective';
                    Swal.fire({
                        title: `Move "${input.dataset.original}" to ${GROUPS[group]}?`,
                        text: toElective
                            ? 'Students will no longer take it automatically — each student must be given it as an elective. Applies to every school.'
                            : 'Every student in a class that offers it will now take it. Applies to every school.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#2C29CA',
                        confirmButtonText: 'Yes, move it',
                    }).then(r => {
                        if (r.isConfirmed) save(); else select.value = select.dataset.original;
                    });
                    return;
                }
                save();
            });

            item.querySelector('.sos-delete').addEventListener('click', function () {
                Swal.fire({
                    title: `Delete "${input.dataset.original}"?`,
                    text: 'This removes it for every school. This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d64545',
                    confirmButtonText: 'Delete',
                }).then(result => {
                    if (!result.isConfirmed) return;
                    apiCall(`${baseUrl}/${item.dataset.mdId}`, 'DELETE')
                        .then(res => {
                            if (res.success) {
                                item.remove();
                                refreshPlaceholders();
                            } else {
                                Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
                });
            });
        }

        document.querySelectorAll('.sos-item').forEach(wireItem);

        document.querySelectorAll('.sos-card[data-group]').forEach(card => {
            const group = card.dataset.group;
            card.querySelector('.sos-add').addEventListener('click', function () {
                const input = card.querySelector('.sos-new-name');
                const name = input.value.trim();
                if (!name) {
                    Swal.fire('Missing name', 'Please type a subject name first.', 'warning');
                    return;
                }
                apiCall(baseUrl, 'POST', { subject_group: group, subject_name: name })
                    .then(res => {
                        if (res.success) {
                            lists[group].appendChild(makeItem(res.subject.md_id, res.subject.md_name, group));
                            refreshPlaceholders();
                            input.value = '';
                        } else {
                            Swal.fire('Error', res.message || 'Failed to add subject.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Failed to add subject — check your connection.', 'error'));
            });
        });
    </script>
@endsection