@extends('layouts-side-bar.master')

@section('css')
    <style>
        .alc-hero {
            background: linear-gradient(135deg, #1a1a7a 0%, #2C29CA 60%, #6b69e8 100%);
            border-radius: 0 0 2.5rem 2.5rem;
            padding: 2.5rem 2.5rem 3.5rem;
            margin-bottom: -1.5rem;
        }

        .alc-hero .hero-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: .4rem 1.2rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .alc-hero .hero-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
        }

        .alc-hero .hero-subtitle {
            color: rgba(255, 255, 255, .7);
            font-size: 1rem;
        }

        .alc-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            overflow: hidden;
            background: #fff;
        }

        .alc-card .card-header-custom {
            padding: 1.25rem 1.75rem;
            border-bottom: 2px solid #f0eeff;
        }

        .alc-card .card-header-custom .title {
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
        }

        .alc-card .card-body-custom {
            padding: 1.5rem 1.75rem;
        }

        .class-pill {
            display: inline-block;
            padding: .5rem 1.1rem;
            margin: .2rem .4rem .2rem 0;
            border-radius: 999px;
            border: 1.5px solid #e4e2ff;
            color: #3a37b8;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
        }

        .class-pill.active {
            background: #2C29CA;
            border-color: #2C29CA;
            color: #fff;
        }

        .alc-table {
            margin-bottom: 0;
            font-size: .85rem;
        }

        .alc-table thead th {
            background: #2C29CA;
            color: #fff;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            padding: 0.9rem 0.75rem;
            border: none;
            white-space: nowrap;
        }

        .alc-table tbody td {
            vertical-align: top;
            padding: 0.9rem 0.75rem;
            border-bottom: 1px solid #f0eeff;
        }

        .alc-table tbody td:first-child {
            font-weight: 600;
            color: #1e1b4b;
            white-space: nowrap;
        }

        .gp-chip {
            display: inline-block;
            background: #eef0ff;
            color: #3a37b8;
            border-radius: .5rem;
            padding: .3rem .6rem;
            font-size: .75rem;
            font-weight: 700;
        }

        .principal-group-label {
            font-size: .68rem;
            text-transform: uppercase;
            color: #9a97c9;
            font-weight: 700;
            margin: .2rem 0;
        }

        .btn-save {
            background: #2C29CA;
            color: #fff;
            border: none;
            border-radius: .8rem;
            padding: .8rem 1.8rem;
            font-weight: 700;
        }

        .combo-preview {
            font-size: .78rem;
            font-weight: 600;
            color: #2C29CA;
            background: #f3f2ff;
            border: 1px dashed #cfccff;
            border-radius: .6rem;
            padding: .45rem .7rem;
            margin-top: .5rem;
        }

        .combo-preview.is-empty {
            color: #a3a0c9;
            border-style: dashed;
            font-weight: 500;
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #a3a0c9;
        }

        .alc-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem 1.75rem 1.75rem 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: -1rem;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid #2C29CA;
        }

        /* Subtle glow accent */
        .alc-hero::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -10%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(44, 41, 202, 0.45) 0%, transparent 70%);
            pointer-events: none;
        }

        .alc-hero::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -5%;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(107, 105, 232, 0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .alc-hero > * {
            position: relative;
            z-index: 1;
        }

        .alc-hero .hero-badge {
            background: rgba(44, 41, 202, 0.25);
            border: 1px solid rgba(107, 105, 232, 0.5);
            color: #c7c5ff;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            backdrop-filter: blur(6px);
        }

        .alc-hero .hero-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
            margin-bottom: .25rem;
            text-shadow: 0 2px 12px rgba(44, 41, 202, 0.4);
        }

        .alc-hero .hero-subtitle {
            color: rgba(255, 255, 255, .65);
            font-size: .82rem;
            line-height: 1.5;
            max-width: 720px;
        }
        .combo-preview.is-empty {
    color: #dc3545;
    border-style: dashed;
    font-weight: 500;
    font-style: italic;
}
    </style>
@endsection

@section('content')
    <div class="side-app">

 <div class="row px-3 px-md-4">
            <div class="col-12">

                {{-- ===== HERO ===== --}}
                <div class="alc-hero mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <span class="hero-badge">
                                <i class="fas fa-graduation-cap me-1"></i> A-Level Combinations
                            </span>
                            <h1 class="hero-title mt-1">Build Student Combinations</h1>
                            <p class="hero-subtitle mb-0">
                                Each student's own principal subjects + optional subsidiary. General
                                Paper is compulsory for every student — it's implied automatically, not chosen here.
                            </p>
                        </div>
                    </div>
                </div>

        <div class="row px-3 px-md-4">
            <div class="col-12">

                {{-- ===== CLASS/STREAM PICKER ===== --}}
                <div class="alc-card mb-4">
                    <div class="card-header-custom">
                        <div class="title"><i class="fas fa-layer-group"></i> Stream</div>
                    </div>
                    <div class="card-body-custom">
                        @forelse($classOptions as $opt)
                            <a class="class-pill {{ (string) $opt->class_id === (string) $selectedClassId && (string) $opt->stream_id === (string) $selectedStreamId ? 'active' : '' }}"
                                href="{{ route('alevel.combinations.entry') }}?class_id={{ $opt->class_id }}&stream_id={{ $opt->stream_id }}">
                                {{ $opt->class_name }}{{ $opt->stream_name ? ' — ' . $opt->stream_name : '' }}
                            </a>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-users-slash d-block mb-2" style="font-size:1.8rem;"></i>
                                No Senior 5 / Senior 6 streams found yet. Create one from Create Class first.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ===== ADD YOUR OWN SUBJECT ===== ---
                Adds to THIS school's own list only (school_alevel_subjects)
                — on top of the global Principal - Arts/Sciences/Subsidiary
                list every school starts with. See
                ALevelCombinationController::addSchoolSubject(). --}}
                @if($students->count())
                    <div class="alc-card mb-4" style="border-radius: 1.25rem; background: #fff; box-shadow: 0 8px 32px rgba(44, 41, 202, 0.12); border-top: 4px solid #2C29CA; overflow: hidden;">
    <div style="padding: 1.25rem 1.75rem; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: .75rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(44, 41, 202, .1); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-plus" style="color: #2C29CA; font-size: 1rem;"></i>
            </div>
            <div>
                <div style="font-weight: 800; font-size: 1rem; color: #1e1b4b; letter-spacing: -.01em;">Add Your Own Subject</div>
                <div style="font-size: .75rem; color: #9a97c9;">Visible to your school only</div>
            </div>
        </div>
    </div>
    <div style="padding: 0 1.75rem 1.75rem;">
        <div style="display: grid; grid-template-columns: 220px 1fr auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: .7rem; font-weight: 700; color: #6b6899; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .5rem;">Group</label>
                <select id="newSubjectGroup" style="width: 100%; height: 48px; padding: 0 1rem; border: 1.5px solid #e4e2ff; border-radius: .7rem; background: #fff; font-size: .88rem; font-weight: 600; color: #1e1b4b; outline: none; line-height: 48px;">
                    <option>Principal - Arts</option>
                    <option>Principal - Sciences</option>
                    <option>Subsidiary</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: .7rem; font-weight: 700; color: #6b6899; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .5rem;">Subject Name</label>
                <input type="text" id="newSubjectName" placeholder="e.g. Technical Drawing" style="width: 100%; height: 48px; padding: 0 1rem; border: 1.5px solid #e4e2ff; border-radius: .7rem; font-size: .88rem; color: #1e1b4b; outline: none;">
            </div>
            <div>
                <button type="button" id="addSubjectBtn" style="height: 48px; padding: 0 1.75rem; background: #2C29CA; color: #fff; border: none; border-radius: .7rem; font-weight: 700; font-size: .85rem; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: .5rem; box-shadow: 0 4px 14px rgba(44, 41, 202, .3);">
                    <i class="fas fa-check"></i> Add Subject
                </button>
            </div>
        </div>
        <div style="margin-top: 1.25rem; padding: .8rem 1rem; background: linear-gradient(135deg, #f7f6ff, #eef0ff); border-radius: .65rem; font-size: .78rem; color: #2C29CA; font-weight: 500;">
            <i class="fas fa-info-circle" style="margin-right: .4rem;"></i>
            This subject will only be visible to your school, and appears immediately below for every student in this stream.
        </div>
    </div>
</div>
                @endif

                {{-- ===== COMBINATIONS GRID ===== --}}
                @if($students->count())
                    <div class="alc-card mb-4">
                        <div class="table-responsive">
                            <table class="table alc-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>General Paper</th>
                                        <th>Principal Subjects</th>
                                        <th>Subsidiary (optional)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $stu)
                                        @php
                                            $existing = $combinations->get($stu->id);
                                            $existingPrincipals = $existing->principal_subject_ids ?? [];
                                            $existingSubsidiary = $existing->subsidiary_subject_id ?? null;
                                        @endphp
                                        <tr data-student-id="{{ $stu->id }}">
                                            <td>{{ $stu->lastname }} {{ $stu->firstname }}</td>
                                            <td><span class="gp-chip">GP — compulsory</span></td>
                                            <td>
                                                @foreach($principalSubjects as $group => $subjectsInGroup)
                                                    <div class="principal-group-label">{{ $group }}</div>
                                                    <div class="d-flex flex-wrap gap-2 mb-2" data-principal-group="{{ $group }}">
                                                        @foreach($subjectsInGroup as $subject)
                                                            <label class="form-check form-check-inline" style="margin-right:.75rem;">
                                                                <input type="checkbox" class="form-check-input principal-checkbox"
                                                                    value="{{ $subject->md_id }}"
                                                                    @if(in_array($subject->md_id, $existingPrincipals)) checked @endif>
                                                                <span class="form-check-label" style="font-size:.8rem;">{{ $subject->md_name }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    @if($group === 'Principal - Sciences')
                                                        {{-- Live preview moved here, under the Sciences group --}}
                                                        <span class="combo-preview" data-role="combo-preview">—</span>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td>
                                                <select class="form-select form-control-sm subsidiary-select">
                                                    <option value="">None</option>
                                                    @foreach($subsidiarySubjects as $sub)
                                                        <option value="{{ $sub->md_id }}" @if((string) $existingSubsidiary === (string) $sub->md_id) selected @endif>
                                                            {{ $sub->md_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body-custom text-end">
                            <button id="saveCombinationsBtn" class="btn-save">
                                <i class="fas fa-save me-2"></i> Save All Combinations
                            </button>
                        </div>
                    </div>
                @elseif($classOptions->count())
                    <div class="alc-card mb-4">
                        <div class="card-body-custom">
                            <div class="empty-state">
                                <i class="fas fa-user-graduate d-block mb-2" style="font-size:1.8rem;"></i>
                                No students found for this stream yet.
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
 </div>
        </div>
    </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // md_id -> subject name, for building the "Physics, Chemistry,
        // Biology / Subsidiary Mathematics" preview text client-side
        // without another round-trip — same subjects already rendered as
        // checkboxes/options above, just flattened into a lookup map.
        const subjectNameMap = {};
        @foreach($principalSubjects as $group => $subjectsInGroup)
            @foreach($subjectsInGroup as $subject)
                subjectNameMap['{{ $subject->md_id }}'] = @json($subject->md_name);
            @endforeach
        @endforeach
        @foreach($subsidiarySubjects as $sub)
            subjectNameMap['{{ $sub->md_id }}'] = @json($sub->md_name);
        @endforeach

        function updateComboPreview(row) {
            const preview = row.querySelector('[data-role="combo-preview"]');
            if (!preview) return;

            const principalNames = Array.from(row.querySelectorAll('.principal-checkbox:checked'))
                .map(cb => subjectNameMap[cb.value] || cb.value);
            const subsidiaryId = row.querySelector('.subsidiary-select')?.value || '';
            const subsidiaryName = subsidiaryId ? (subjectNameMap[subsidiaryId] || subsidiaryId) : '';

            if (principalNames.length === 0 && !subsidiaryName) {
                preview.textContent = 'No combination selected yet';
                preview.classList.add('is-empty');
                return;
            }

            let text = principalNames.join(', ');
            if (subsidiaryName) {
                text = text ? `${text} / ${subsidiaryName}` : subsidiaryName;
            }
            preview.textContent = text;
            preview.classList.remove('is-empty');
        }

        // Wire up every row: recompute on any change, and once immediately
        // on load so already-saved combinations show their preview text
        // right away rather than only after the next edit.
        document.querySelectorAll('tr[data-student-id]').forEach(row => {
            row.querySelectorAll('.principal-checkbox, .subsidiary-select').forEach(el => {
                el.addEventListener('change', () => updateComboPreview(row));
            });
            updateComboPreview(row);
        });

document.getElementById('addSubjectBtn')?.addEventListener('click', function () {
    const group = document.getElementById('newSubjectGroup').value;
    const name = document.getElementById('newSubjectName').value.trim();
    if (!name) {
        Swal.fire('Missing name', 'Please type a subject name first.', 'warning');
        return;
    }

    const $btn = this;
    const originalHTML = $btn.innerHTML;

    // Step 1: spinner state
    $btn.disabled = true;
    $btn.style.background = '#4d4be0';
    $btn.style.cursor = 'not-allowed';
    $btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

    fetch('{{ route('alevel.combinations.add-subject') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ subject_group: group, subject_name: name }),
    })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                // Restore on failure
                $btn.disabled = false;
                $btn.style.background = '#2C29CA';
                $btn.style.cursor = 'pointer';
                $btn.innerHTML = originalHTML;
                Swal.fire('Error', res.message || 'Failed to add subject.', 'error');
                return;
            }

            const subject = res.subject;
            subjectNameMap[subject.md_id] = subject.md_name;
            document.getElementById('newSubjectName').value = '';

            // Drop the new checkbox/option into every student row
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                if (subject.md_misc1 === 'Subsidiary') {
                    const select = row.querySelector('.subsidiary-select');
                    if (select) {
                        const opt = document.createElement('option');
                        opt.value = subject.md_id;
                        opt.textContent = subject.md_name;
                        select.appendChild(opt);
                    }
                    return;
                }

                let group = row.querySelector(`[data-principal-group="${subject.md_misc1}"]`);
                if (!group) return;

                const label = document.createElement('label');
                label.className = 'form-check form-check-inline';
                label.style.marginRight = '.75rem';
                label.innerHTML = `
                    <input type="checkbox" class="form-check-input principal-checkbox" value="${subject.md_id}">
                    <span class="form-check-label" style="font-size:.8rem;">${subject.md_name}</span>
                `;
                group.appendChild(label);
                label.querySelector('.principal-checkbox').addEventListener('change', () => updateComboPreview(row));
            });

            // Step 2: success state on the button itself
            $btn.style.background = '#1e9e5a';
            $btn.innerHTML = '<i class="fas fa-check"></i> Added!';

            // Small toast, no click needed
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'success',
                title: `"${subject.md_name}" added`,
            });

            // Step 3: hold "Added!" for 2 seconds, then reset
            setTimeout(() => {
                $btn.disabled = false;
                $btn.style.background = '#2C29CA';
                $btn.style.cursor = 'pointer';
                $btn.innerHTML = originalHTML;
            }, 2000);
        })
        .catch(() => {
            // Restore on network error
            $btn.disabled = false;
            $btn.style.background = '#2C29CA';
            $btn.style.cursor = 'pointer';
            $btn.innerHTML = originalHTML;
            Swal.fire('Error', 'Failed to add subject — check your connection.', 'error');
        });
});

        document.getElementById('saveCombinationsBtn')?.addEventListener('click', function () {
            const combinations = [];
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                const studentId = row.dataset.studentId;
                const principals = [];
                row.querySelectorAll('.principal-checkbox:checked').forEach(cb => principals.push(cb.value));
                const subsidiary = row.querySelector('.subsidiary-select')?.value || null;
                combinations.push({
                    student_id: studentId,
                    principal_subject_ids: principals,
                    subsidiary_subject_id: subsidiary,
                });
            });

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

            fetch('{{ route('alevel.combinations.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ combinations }),
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: res.message || 'All combinations have been saved.',
                            confirmButtonColor: '#2C29CA',
                            timer: 2500,
                            timerProgressBar: true,
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Failed to save combinations.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'))
                .finally(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-2"></i> Save All Combinations';
                });
        });
    </script>
@endsection