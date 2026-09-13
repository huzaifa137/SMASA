@extends('layouts-side-bar.master')

@section('css')
    <style>
        .sas-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: 1.5rem;
        }

        .sas-hero .hero-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
        }

        .sas-hero .hero-subtitle {
            color: rgba(255, 255, 255, .7);
            font-size: .85rem;
            max-width: 720px;
        }

        .sas-card {
            border: none;
            border-radius: 1.1rem;
            box-shadow: 0 4px 24px rgba(44, 41, 202, .08);
            background: #fff;
            margin-bottom: 1.5rem;
        }

        .sas-card .card-header-custom {
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #f0eeff;
            font-weight: 700;
            color: #1e1b4b;
        }

        .sas-card .card-body-custom {
            padding: 1.25rem 1.5rem;
        }

        .sas-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .6rem .8rem;
            border-radius: .6rem;
            background: #f8f8ff;
            margin-bottom: .5rem;
        }

        .sas-item input.sas-name-input {
            border: none;
            background: transparent;
            font-weight: 600;
            color: #1e1b4b;
            width: 100%;
        }

        .sas-item input.sas-name-input:focus {
            outline: 1px solid #cfccff;
            background: #fff;
            border-radius: .4rem;
            padding: .1rem .4rem;
        }

        .sas-actions button {
            border: none;
            background: transparent;
            color: #9a97c9;
            padding: .2rem .4rem;
        }

        .sas-actions button:hover {
            color: #2C29CA;
        }

        .sas-actions button.sas-delete:hover {
            color: #d64545;
        }

        .gp-chip {
            display: inline-block;
            background: #eef0ff;
            color: #3a37b8;
            border-radius: .5rem;
            padding: .4rem .7rem;
            font-size: .8rem;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="row px-3 px-md-4">
            <div class="col-12">

                <div class="sas-hero">
                    <span class="hero-badge" style="background:rgba(44,41,202,.25);border:1px solid rgba(107,105,232,.5);color:#c7c5ff;padding:.3rem .9rem;border-radius:999px;font-size:.65rem;font-weight:700;text-transform:uppercase;">
                        <i class="fas fa-graduation-cap me-1"></i> Global Master Data
                    </span>
                    <h1 class="hero-title mt-2 mb-1">Secondary A-Level Subjects</h1>
                    <p class="hero-subtitle mb-0">
                        The Principal - Arts / Principal - Sciences / Subsidiary list every school starts
                        with on their A-Level Combinations page. Changes here apply to <strong>every school</strong> —
                        a school can also add its own subjects that only it can see, from its own
                        A-Level Combinations page.
                    </p>
                </div>

                <div class="sas-card">
                    <div class="card-header-custom"><i class="fas fa-book"></i> General Paper</div>
                    <div class="card-body-custom">
                        @if($generalPaper)
                            <span class="gp-chip">{{ $generalPaper->md_name }} — compulsory, cannot be edited or removed here</span>
                        @else
                            <span class="text-muted">Not set up yet.</span>
                        @endif
                    </div>
                </div>

                @foreach($groups as $group)
                    <div class="sas-card" data-group="{{ $group }}">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-layer-group"></i> {{ $group }}</span>
                        </div>
                        <div class="card-body-custom">
                            <div class="sas-item-list" data-group-list="{{ $group }}">
                                @forelse($groupedSubjects[$group] ?? [] as $subject)
                                    <div class="sas-item" data-md-id="{{ $subject->md_id }}">
                                        <input type="text" class="sas-name-input" value="{{ $subject->md_name }}" data-original="{{ $subject->md_name }}">
                                        <div class="sas-actions">
                                            <button type="button" class="sas-save" title="Save"><i class="fas fa-check"></i></button>
                                            <button type="button" class="sas-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted" data-empty-placeholder>No subjects in this group yet.</div>
                                @endforelse
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <input type="text" class="form-control form-control-sm sas-new-name" placeholder="e.g. {{ $group === 'Subsidiary' ? 'Subsidiary Geography' : 'New subject name' }}">
                                <button type="button" class="btn btn-sm sas-add" style="background:#2C29CA;color:#fff;white-space:nowrap;">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';

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

        document.querySelectorAll('.sas-card[data-group]').forEach(card => {
            const group = card.dataset.group;
            const list = card.querySelector('[data-group-list]');

            function clearEmptyPlaceholder() {
                list.querySelector('[data-empty-placeholder]')?.remove();
            }

            function makeItem(mdId, name) {
                const item = document.createElement('div');
                item.className = 'sas-item';
                item.dataset.mdId = mdId;
                item.innerHTML = `
                    <input type="text" class="sas-name-input" value="${name}" data-original="${name}">
                    <div class="sas-actions">
                        <button type="button" class="sas-save" title="Save"><i class="fas fa-check"></i></button>
                        <button type="button" class="sas-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                wireItem(item);
                return item;
            }

            function wireItem(item) {
                const input = item.querySelector('.sas-name-input');

                item.querySelector('.sas-save').addEventListener('click', function () {
                    const name = input.value.trim();
                    if (!name) {
                        Swal.fire('Missing name', 'Subject name cannot be empty.', 'warning');
                        return;
                    }
                    if (name === input.dataset.original) {
                        return; // nothing changed
                    }

                    apiCall(`{{ url('admin/secondary-alevel-subjects') }}/${item.dataset.mdId}`, 'PUT', { subject_name: name })
                        .then(res => {
                            if (res.success) {
                                input.dataset.original = name;
                                Swal.fire({ icon: 'success', title: 'Saved', timer: 1400, showConfirmButton: false });
                            } else {
                                Swal.fire('Error', res.message || 'Failed to save.', 'error');
                                input.value = input.dataset.original;
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'));
                });

                item.querySelector('.sas-delete').addEventListener('click', function () {
                    Swal.fire({
                        title: `Delete "${input.dataset.original}"?`,
                        text: 'This removes it for every school. This cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d64545',
                        confirmButtonText: 'Delete',
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        apiCall(`{{ url('admin/secondary-alevel-subjects') }}/${item.dataset.mdId}`, 'DELETE')
                            .then(res => {
                                if (res.success) {
                                    item.remove();
                                    if (!list.querySelector('.sas-item')) {
                                        const placeholder = document.createElement('div');
                                        placeholder.className = 'text-muted';
                                        placeholder.setAttribute('data-empty-placeholder', '');
                                        placeholder.textContent = 'No subjects in this group yet.';
                                        list.appendChild(placeholder);
                                    }
                                } else {
                                    Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                                }
                            })
                            .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
                    });
                });
            }

            list.querySelectorAll('.sas-item').forEach(wireItem);

            card.querySelector('.sas-add').addEventListener('click', function () {
                const input = card.querySelector('.sas-new-name');
                const name = input.value.trim();
                if (!name) {
                    Swal.fire('Missing name', 'Please type a subject name first.', 'warning');
                    return;
                }

                apiCall('{{ route('admin.secondary-alevel-subjects.store') }}', 'POST', { subject_group: group, subject_name: name })
                    .then(res => {
                        if (res.success) {
                            clearEmptyPlaceholder();
                            list.appendChild(makeItem(res.subject.md_id, res.subject.md_name));
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
