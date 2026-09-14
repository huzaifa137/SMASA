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

        /* =========================================================
   SUBJECT ITEMS
   ========================================================= */

.sas-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;

    padding: .65rem .85rem;
    margin-bottom: .6rem;

    border: 1px solid #eef0ff;
    border-radius: .75rem;

    background: linear-gradient(135deg, #fafaff 0%, #f6f7ff 100%);

    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease,
        background .2s ease;
}

/* Beautiful hover effect */
.sas-item:hover {
    transform: translateX(4px);
    border-color: rgba(44, 41, 202, .22);

    background: linear-gradient(
        135deg,
        #ffffff 0%,
        #f5f5ff 100%
    );

    box-shadow:
        0 6px 18px rgba(44, 41, 202, .10),
        0 2px 5px rgba(0, 0, 0, .03);
}

/* =========================================================
   SUBJECT NAME INPUT
   ========================================================= */

.sas-item input.sas-name-input {
    flex: 1;

    min-width: 0;
    width: 100%;

    border: 1px solid transparent;
    border-radius: .55rem;

    background: transparent;

    padding: .5rem .65rem;

    font-size: .9rem;
    font-weight: 600;

    color: #1e1b4b;

    transition:
        background .2s ease,
        border-color .2s ease,
        box-shadow .2s ease,
        color .2s ease;
}

/* Input hover */
.sas-item input.sas-name-input:hover {
    background: #fff;
    border-color: #e4e3ff;
}

/* Input focus */
.sas-item input.sas-name-input:focus {
    outline: none;

    background: #fff;

    border-color: rgba(44, 41, 202, .45);

    box-shadow:
        0 0 0 3px rgba(44, 41, 202, .08),
        0 4px 12px rgba(44, 41, 202, .06);

    color: #151344;
}

/* =========================================================
   ADD SUBJECT AREA
   ========================================================= */

.sas-card .d-flex.gap-2.mt-3 {
    gap: .75rem !important;
    align-items: stretch;
}

/* New subject input */
.sas-new-name {
    height: 42px;

    border: 1px solid #e2e3f5 !important;
    border-radius: .7rem !important;

    background: #fafaff !important;

    padding: .55rem .85rem !important;

    font-size: .88rem;
    font-weight: 500;

    color: #1e1b4b;

    box-shadow: inset 0 1px 2px rgba(44, 41, 202, .03);

    transition:
        border-color .2s ease,
        background .2s ease,
        box-shadow .2s ease,
        transform .2s ease;
}

/* Placeholder */
.sas-new-name::placeholder {
    color: #a09fba;
    font-weight: 500;
}

/* Hover */
.sas-new-name:hover {
    background: #fff !important;
    border-color: #cfcdf5 !important;
}

/* Focus */
.sas-new-name:focus {
    outline: none !important;

    background: #fff !important;

    border-color: #2C29CA !important;

    box-shadow:
        0 0 0 3px rgba(44, 41, 202, .10),
        0 5px 15px rgba(44, 41, 202, .07) !important;

    transform: translateY(-1px);
}

/* =========================================================
   ADD BUTTON
   ========================================================= */

.sas-add {
    height: 42px;

    display: inline-flex !important;
    align-items: center;
    justify-content: center;

    gap: .45rem;

    padding: .55rem 1rem !important;

    border: none !important;
    border-radius: .7rem !important;

    background: linear-gradient(
        135deg,
        #2C29CA 0%,
        #4542df 100%
    ) !important;

    color: #fff !important;

    font-size: .82rem;
    font-weight: 700;

    box-shadow:
        0 5px 14px rgba(44, 41, 202, .22);

    transition:
        transform .2s ease,
        box-shadow .2s ease,
        filter .2s ease;
}

/* Add button hover */
.sas-add:hover {
    color: #fff !important;

    transform: translateY(-2px);

    filter: brightness(1.05);

    box-shadow:
        0 8px 20px rgba(44, 41, 202, .30);
}

/* Add button click */
.sas-add:active {
    transform: translateY(0);
    box-shadow:
        0 3px 8px rgba(44, 41, 202, .20);
}

/* Plus icon */
.sas-add i {
    font-size: .75rem;

    transition:
        transform .2s ease;
}

.sas-add:hover i {
    transform: rotate(90deg);
}

/* =========================================================
   SAVE / DELETE ACTIONS
   ========================================================= */

.sas-actions {
    display: flex;
    align-items: center;

    gap: .35rem;

    flex-shrink: 0;
}

/* Base action button */
.sas-actions button {
    width: 34px;
    height: 34px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: none;
    border-radius: .55rem;

    background: transparent;

    padding: 0;

    transition:
        background .2s ease,
        transform .2s ease,
        box-shadow .2s ease;
}

/* =========================================================
   CHECK / SAVE
   ========================================================= */

.sas-actions button.sas-save {
    color: #2C29CA !important;
}

.sas-actions button.sas-save i {
    color: #2C29CA !important;
    font-size: 1rem;
}

/* Save hover */
.sas-actions button.sas-save:hover {
    background: rgba(44, 41, 202, .09);

    transform: translateY(-2px);

    box-shadow:
        0 4px 10px rgba(44, 41, 202, .10);
}

/* =========================================================
   DELETE / BIN
   ========================================================= */

.sas-actions button.sas-delete {
    color: #dc3545 !important;
}

.sas-actions button.sas-delete i {
    color: #dc3545 !important;
    font-size: 1rem;
}

/* Delete hover */
.sas-actions button.sas-delete:hover {
    background: rgba(220, 53, 69, .09);

    transform: translateY(-2px);

    box-shadow:
        0 4px 10px rgba(220, 53, 69, .10);
}

/* =========================================================
   SUBJECT LIST ANIMATION
   ========================================================= */

.sas-item {
    animation: sasItemIn .3s ease both;
}

@keyframes sasItemIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* When hovering over a subject, slightly soften others */
.sas-item-list:hover .sas-item:not(:hover) {
    opacity: .72;
}

/* Keep the hovered item prominent */
.sas-item-list:hover .sas-item:hover {
    opacity: 1;
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
                               <button type="button" class="btn btn-sm sas-add">
    <i class="fas fa-plus"></i> Add
</button>
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
