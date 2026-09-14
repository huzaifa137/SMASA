@extends('layouts-side-bar.master')

@section('css')
    <style>
        .ole-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: -1rem;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid #2C29CA;
        }

        .ole-hero::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -10%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(44, 41, 202, 0.45) 0%, transparent 70%);
            pointer-events: none;
        }

        .ole-hero::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -5%;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(107, 105, 232, 0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .ole-hero>* {
            position: relative;
            z-index: 1;
        }

        .ole-hero .hero-badge {
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

        .ole-hero .hero-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
            margin-bottom: .25rem;
            text-shadow: 0 2px 12px rgba(44, 41, 202, 0.4);
        }

        .ole-hero .hero-subtitle {
            color: rgba(255, 255, 255, .65);
            font-size: .82rem;
            line-height: 1.5;
            max-width: 720px;
        }

        .ole-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            overflow: hidden;
            background: #fff;
        }

        .ole-card .card-header-custom {
            padding: 1.25rem 1.75rem;
            border-bottom: 2px solid #f0eeff;
        }

        .ole-card .card-header-custom .title {
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
        }

        .ole-card .card-body-custom {
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

        .ole-table {
            margin-bottom: 0;
            font-size: .85rem;
        }

        .ole-table thead th {
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

        .ole-table tbody td {
            vertical-align: top;
            padding: 0.9rem 0.75rem;
            border-bottom: 1px solid #f0eeff;
        }

        .ole-table tbody td:first-child {
            font-weight: 600;
            color: #1e1b4b;
            white-space: nowrap;
        }

        .ole-table tbody tr.is-filtered-out {
            display: none;
        }

        .btn-save {
            background: #2C29CA;
            color: #fff;
            border: none;
            border-radius: .8rem;
            padding: .8rem 1.8rem;
            font-weight: 700;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #a3a0c9;
        }

        /* ===== Elective count badge (replaces A-Level's combo preview —
           there's no short-form/PCM-style text to build for a flat "pick
           up to 2" list, just how many of the 2 slots are filled) ===== */
        .elective-count {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .76rem;
            font-weight: 700;
            border-radius: .5rem;
            padding: .35rem .7rem;
            margin-top: .5rem;
            background: #f3f2ff;
            color: #2C29CA;
            border: 1px dashed #cfccff;
        }

        .elective-count.is-empty {
            color: #a3a0c9;
            font-weight: 500;
            font-style: italic;
        }

        .elective-count.is-full {
            background: #eef9f0;
            color: #1e9e5a;
            border-color: #b9e8c4;
            border-style: solid;
        }

        .pending-chip {
            display: none;
            align-items: center;
            gap: .3rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: #9a6b00;
            background: #fff6e0;
            border: 1px solid #ffe19c;
            border-radius: 999px;
            padding: .3rem .65rem;
            margin-left: .5rem;
        }

        .pending-chip.is-visible {
            display: inline-flex;
        }

        .elective-checkbox-label {
            display: inline-flex;
            align-items: center;
            margin: 0 .75rem .4rem 0;
        }

        .elective-checkbox-label.is-filtered-out {
            display: none;
        }

        /* ===== Per-row subject search ===== */
        .subject-search-wrap {
            position: relative;
            margin-bottom: .75rem;
            max-width: 260px;
        }

        .subject-search-icon {
            position: absolute;
            top: 50%;
            left: .75rem;
            transform: translateY(-50%);
            color: #9a97c9;
            font-size: .75rem;
            pointer-events: none;
        }

        .subject-search-input {
            width: 100%;
            height: 34px;
            padding: 0 .75rem 0 1.85rem;
            border: 1.5px solid #e4e2ff;
            border-radius: .6rem;
            background: #fafaff;
            font-size: .8rem;
        }

        /* ===== Student name search ===== */
        .student-search-bar {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1.1rem 1.75rem;
            border-bottom: 2px solid #f0eeff;
        }

        .student-search-wrap {
            position: relative;
            flex: 1;
            max-width: 340px;
        }

        .student-search-icon {
            position: absolute;
            top: 50%;
            left: .85rem;
            transform: translateY(-50%);
            color: #9a97c9;
            font-size: .8rem;
            pointer-events: none;
        }

        .student-search-input {
            width: 100%;
            height: 40px;
            padding: 0 2.2rem 0 2.1rem;
            border: 1.5px solid #e4e2ff;
            border-radius: .7rem;
            background: #fafaff;
            font-size: .85rem;
        }

        .student-search-clear {
            display: none;
            position: absolute;
            top: 50%;
            right: .6rem;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #9a97c9;
            cursor: pointer;
        }

        .student-search-clear.is-visible {
            display: block;
        }

        .student-search-count {
            font-size: .78rem;
            color: #6b6899;
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="row px-3 px-md-4">
            <div class="col-12">
                <div class="row px-3 px-md-4">
                    <div class="col-12">

                        {{-- ===== HERO ===== --}}
                        <div class="ole-hero mb-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div>
                                    <span class="hero-badge">
                                        <i class="fas fa-graduation-cap me-1"></i> O-Level Electives
                                    </span>
                                    <h1 class="hero-title mt-1">Pick Student Electives</h1>
                                    <p class="hero-subtitle mb-0">
                                        Each student's own electives — up to {{ \App\Http\Controllers\OLevelElectiveController::ELECTIVE_LIMIT }}
                                        — on top of the class's compulsory subjects, which the school sets separately at
                                        class-creation time (Create Class).
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- ===== CLASS/STREAM PICKER ===== --}}
                        <div class="ole-card mb-4">
                            <div class="card-header-custom">
                                <div class="title"><i class="fas fa-layer-group"></i> Stream</div>
                            </div>
                            <div class="card-body-custom">
                                @forelse($classOptions as $opt)
                                    <a class="class-pill {{ (string) $opt->class_id === (string) $selectedClassId && (string) $opt->stream_id === (string) $selectedStreamId ? 'active' : '' }}"
                                        href="{{ route('olevel.electives.entry') }}?class_id={{ $opt->class_id }}&stream_id={{ $opt->stream_id }}">
                                        {{ $opt->class_name }}{{ $opt->stream_name ? ' — ' . $opt->stream_name : '' }}
                                    </a>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-users-slash d-block mb-2" style="font-size:1.8rem;"></i>
                                        No Senior 1 - Senior 4 streams found yet. Create one from Create Class first.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- ===== ADD YOUR OWN ELECTIVE ===== ---
                        Adds to THIS school's own list only (school_olevel_electives)
                        — on top of the global elective list every school starts
                        with. See OLevelElectiveController::addSchoolSubject(). --}}
                        @if($students->count())
                            <div class="ole-card mb-4"
                                style="border-radius: 1.25rem; background: #fff; box-shadow: 0 8px 32px rgba(44, 41, 202, 0.12); border-top: 4px solid #2C29CA; overflow: hidden;">
                                <div
                                    style="padding: 1.25rem 1.75rem; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: .75rem;">
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 50%; background: rgba(44, 41, 202, .1); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-plus" style="color: #2C29CA; font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div
                                                style="font-weight: 800; font-size: 1rem; color: #1e1b4b; letter-spacing: -.01em;">
                                                Add Your Own Elective</div>
                                            <div style="font-size: .75rem; color: #9a97c9;">Visible to your school only</div>
                                        </div>
                                    </div>
                                </div>
                                <div style="padding: 0 1.75rem 1.75rem;">
                                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: end;">
                                        <div>
                                            <label
                                                style="display: block; font-size: .7rem; font-weight: 700; color: #6b6899; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .5rem;">Elective
                                                Name</label>
                                            <input type="text" id="newSubjectName" placeholder="e.g. Swahili"
                                                style="width: 100%; height: 48px; padding: 0 1rem; border: 1.5px solid #e4e2ff; border-radius: .7rem; font-size: .88rem; color: #1e1b4b; outline: none;">
                                        </div>
                                        <div>
                                            <button type="button" id="addSubjectBtn"
                                                style="height: 48px; padding: 0 1.75rem; background: #2C29CA; color: #fff; border: none; border-radius: .7rem; font-weight: 700; font-size: .85rem; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: .5rem; box-shadow: 0 4px 14px rgba(44, 41, 202, .3);">
                                                <i class="fas fa-check"></i> Add Elective
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        style="margin-top: 1.25rem; padding: .8rem 1rem; background: linear-gradient(135deg, #f7f6ff, #eef0ff); border-radius: .65rem; font-size: .78rem; color: #2C29CA; font-weight: 500;">
                                        <i class="fas fa-info-circle" style="margin-right: .4rem;"></i>
                                        This elective will only be visible to your school, and appears immediately below for
                                        every student in this stream.
                                    </div>
                                </div>
                            </div>

                            {{-- ===== MANAGE YOUR OWN ELECTIVES ===== ---
                            Rename/delete a subject added above — doesn't touch
                            the global elective list every school starts with.
                            See OLevelElectiveController::updateSchoolSubject()/
                            deleteSchoolSubject(). --}}
                            @if($mySchoolSubjects->count())
                                <div class="ole-card mb-4"
                                    style="border-radius: 1.25rem; background: #fff; box-shadow: 0 8px 32px rgba(44, 41, 202, 0.12); border-top: 4px solid #2C29CA; overflow: hidden;">
                                    <div style="padding: 1.25rem 1.75rem; display: flex; align-items: center; gap: .75rem;">
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 50%; background: rgba(44, 41, 202, .1); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-pen-to-square" style="color: #2C29CA; font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 800; font-size: 1rem; color: #1e1b4b; letter-spacing: -.01em;">
                                                Manage Your Own Electives</div>
                                            <div style="font-size: .75rem; color: #9a97c9;">Rename or remove an elective you
                                                added above</div>
                                        </div>
                                    </div>
                                    <div style="padding: 0 1.75rem 1.75rem;">
                                        <div id="mySubjectsList" style="display: flex; flex-direction: column; gap: .5rem;">
                                            @foreach($mySchoolSubjects as $mySubject)
                                                <div class="my-subject-row" data-id="{{ $mySubject->id }}"
                                                    data-md-id="{{ $mySubject->syntheticId() }}"
                                                    style="display: flex; align-items: center; gap: .75rem; padding: .6rem .9rem; border: 1.5px solid #e4e2ff; border-radius: .7rem;">
                                                    <span class="my-subject-name"
                                                        style="flex: 1; font-weight: 600; color: #1e1b4b; font-size: .88rem;">{{ $mySubject->subject_name }}</span>
                                                    <button type="button" class="btn btn-sm my-subject-edit" title="Rename"
                                                        style="background:transparent; border:1px solid #e4e2ff; border-radius:.5rem; color:#2C29CA; width:32px; height:32px;">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm my-subject-delete" title="Delete"
                                                        style="background:transparent; border:1px solid #ffd9d9; border-radius:.5rem; color:#dc3545; width:32px; height:32px;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- ===== ELECTIVES GRID ===== --}}
                            <div class="ole-card mb-4">
                                {{-- Student-name search: filters the table rows live as you type. --}}
                                <div class="student-search-bar">
                                    <div class="student-search-wrap">
                                        <i class="fas fa-user-search student-search-icon"></i>
                                        <input type="text" id="studentSearchInput" class="student-search-input"
                                            placeholder="Search students by name…" autocomplete="off">
                                        <button type="button" id="studentSearchClear" class="student-search-clear"
                                            title="Clear">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <span id="studentSearchCount" class="student-search-count"></span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table ole-table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Electives (up to {{ \App\Http\Controllers\OLevelElectiveController::ELECTIVE_LIMIT }})</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $stu)
                                                @php
                                                    $existing = $electives->get($stu->id);
                                                    $existingElectiveIds = $existing->elective_subject_ids ?? [];
                                                @endphp
                                                <tr data-student-id="{{ $stu->id }}"
                                                    data-initial-electives='@json(array_values($existingElectiveIds))'>
                                                    <td>{{ $stu->lastname }} {{ $stu->firstname }}</td>
                                                    <td>
                                                        <div class="subject-search-wrap">
                                                            <i class="fas fa-search subject-search-icon"></i>
                                                            <input type="text" class="subject-search-input"
                                                                placeholder="Search electives…" autocomplete="off">
                                                        </div>

                                                        <div>
                                                            @foreach($electiveSubjects as $subject)
                                                                <label class="elective-checkbox-label"
                                                                    data-subject-name="{{ strtolower($subject->md_name) }}">
                                                                    <input type="checkbox" class="form-check-input elective-checkbox"
                                                                        value="{{ $subject->md_id }}"
                                                                        @if(in_array($subject->md_id, $existingElectiveIds)) checked @endif>
                                                                    <span class="form-check-label ms-1"
                                                                        style="font-size:.8rem;">{{ $subject->md_name }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>

                                                        <div>
                                                            <span class="elective-count" data-role="elective-count">—</span>
                                                            <span class="pending-chip" data-role="pending-chip">
                                                                <i class="fas fa-circle"></i> Unsaved
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-body-custom d-flex align-items-center justify-content-end gap-3">
                                    <span id="pendingSummary"
                                        style="display:none; font-size:.82rem; font-weight:700; color:#9a6b00;">
                                        <i class="fas fa-triangle-exclamation"></i> <span id="pendingCount">0</span> student(s)
                                        have unsaved changes
                                    </span> &nbsp; &nbsp;
                                    <button id="saveElectivesBtn" class="btn-save">
                                        <i class="fas fa-save me-2"></i> <span id="saveBtnLabel">Save All Electives</span>
                                    </button>
                                </div>
                            </div>
                        @elseif($classOptions->count())
                            <div class="ole-card mb-4">
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
    <script>
        const ELECTIVE_LIMIT = {{ \App\Http\Controllers\OLevelElectiveController::ELECTIVE_LIMIT }};

        // Shared toast style — small, top-right, auto-dismissing.
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        // md_id -> subject name, for the "2/2 electives chosen" style text
        // and for reflecting a rename everywhere without a page reload.
        const subjectNameMap = {};
        @foreach($electiveSubjects as $subject)
            subjectNameMap['{{ $subject->md_id }}'] = @json($subject->md_name);
        @endforeach

        // Each row's electives, in the order the user actually checked them
        // (or, on first load, the order they were originally saved in).
        const rowElectiveOrder = new WeakMap();

        // Rows with a change made this session that hasn't been sent to
        // Save yet — drives the per-row "Unsaved" chip and the Save
        // button's pending count.
        const dirtyRows = new Set();

        function electiveOrderFor(row) {
            if (!rowElectiveOrder.has(row)) {
                rowElectiveOrder.set(row, []);
            }
            return rowElectiveOrder.get(row);
        }

        function updateElectiveCount(row) {
            const badge = row.querySelector('[data-role="elective-count"]');
            if (!badge) return;

            const count = electiveOrderFor(row).length;
            badge.classList.remove('is-empty', 'is-full');

            if (count === 0) {
                badge.textContent = 'No electives selected yet';
                badge.classList.add('is-empty');
            } else {
                const names = electiveOrderFor(row).map(id => subjectNameMap[id] || id).join(', ');
                badge.textContent = `${count}/${ELECTIVE_LIMIT} — ${names}`;
                if (count >= ELECTIVE_LIMIT) badge.classList.add('is-full');
            }
        }

        function markRowDirty(row) {
            dirtyRows.add(row);
            const chip = row.querySelector('[data-role="pending-chip"]');
            if (chip) chip.classList.add('is-visible');
            updatePendingSummary();
        }

        function markRowClean(row) {
            dirtyRows.delete(row);
            const chip = row.querySelector('[data-role="pending-chip"]');
            if (chip) chip.classList.remove('is-visible');
            updatePendingSummary();
        }

        function updatePendingSummary() {
            const summary = document.getElementById('pendingSummary');
            const count = document.getElementById('pendingCount');
            const saveLabel = document.getElementById('saveBtnLabel');
            if (!summary || !count || !saveLabel) return;

            if (dirtyRows.size > 0) {
                summary.style.display = 'inline-block';
                count.textContent = dirtyRows.size;
                saveLabel.textContent = `Save All Electives (${dirtyRows.size} pending)`;
            } else {
                summary.style.display = 'none';
                saveLabel.textContent = 'Save All Electives';
            }
        }

        // A forgotten, unsaved change is easy to lose by navigating away
        // entirely — warn on the way out too, not just on-page.
        window.addEventListener('beforeunload', (e) => {
            if (dirtyRows.size > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Once ELECTIVE_LIMIT are checked in a row, lock the rest until the
        // user frees a slot by unchecking one.
        function applyElectiveLimit(row) {
            const order = electiveOrderFor(row);
            const atLimit = order.length >= ELECTIVE_LIMIT;
            row.querySelectorAll('.elective-checkbox').forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = atLimit;
                }
            });
        }

        function wireElectiveCheckbox(cb, row) {
            cb.addEventListener('change', () => {
                const order = electiveOrderFor(row);
                const i = order.indexOf(cb.value);
                if (cb.checked && i === -1) {
                    order.push(cb.value);
                } else if (!cb.checked && i !== -1) {
                    order.splice(i, 1);
                }
                applyElectiveLimit(row);
                updateElectiveCount(row);
                markRowDirty(row);
            });
        }

        // Wire up every row: seed each row's check-order from whatever was
        // actually saved (data-initial-electives), then render once
        // immediately — nothing here counts as a "change" yet, so rows
        // start clean, not pending.
        document.querySelectorAll('tr[data-student-id]').forEach(row => {
            let initial = [];
            try {
                initial = JSON.parse(row.dataset.initialElectives || '[]').map(String);
            } catch (e) {
                initial = [];
            }
            const checkedNow = new Set(
                Array.from(row.querySelectorAll('.elective-checkbox:checked')).map(cb => cb.value)
            );
            const order = initial.filter(id => checkedNow.has(id));
            checkedNow.forEach(id => { if (!order.includes(id)) order.push(id); });
            rowElectiveOrder.set(row, order);

            row.querySelectorAll('.elective-checkbox').forEach(cb => wireElectiveCheckbox(cb, row));

            applyElectiveLimit(row);
            updateElectiveCount(row);
        });

        document.getElementById('addSubjectBtn')?.addEventListener('click', function () {
            const name = document.getElementById('newSubjectName').value.trim();
            if (!name) {
                Swal.fire('Missing name', 'Please type an elective name first.', 'warning');
                return;
            }

            const $btn = this;
            const originalHTML = $btn.innerHTML;

            $btn.disabled = true;
            $btn.style.background = '#4d4be0';
            $btn.style.cursor = 'not-allowed';
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

            fetch('{{ route('olevel.electives.add-subject') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ subject_name: name }),
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        $btn.disabled = false;
                        $btn.style.background = '#2C29CA';
                        $btn.style.cursor = 'pointer';
                        $btn.innerHTML = originalHTML;
                        Swal.fire('Error', res.message || 'Failed to add elective.', 'error');
                        return;
                    }

                    const subject = res.subject;
                    subjectNameMap[subject.md_id] = subject.md_name;
                    document.getElementById('newSubjectName').value = '';

                    // Drop the new checkbox into every student row.
                    document.querySelectorAll('tr[data-student-id]').forEach(row => {
                        const list = row.querySelector('td:nth-child(2) > div:nth-of-type(1)');
                        if (!list) return;

                        const label = document.createElement('label');
                        label.className = 'elective-checkbox-label';
                        label.dataset.subjectName = subject.md_name.toLowerCase();
                        label.innerHTML = `
                            <input type="checkbox" class="form-check-input elective-checkbox" value="${subject.md_id}">
                            <span class="form-check-label ms-1" style="font-size:.8rem;">${subject.md_name}</span>
                        `;
                        list.appendChild(label);
                        wireElectiveCheckbox(label.querySelector('.elective-checkbox'), row);
                        applyElectiveLimit(row);
                    });

                    if (subject.id) {
                        addRowToManageList(subject);
                    }

                    $btn.style.background = '#1e9e5a';
                    $btn.innerHTML = '<i class="fas fa-check"></i> Added!';

                    Toast.fire({
                        icon: 'success',
                        title: `"${subject.md_name}" added`,
                    });

                    setTimeout(() => {
                        $btn.disabled = false;
                        $btn.style.background = '#2C29CA';
                        $btn.style.cursor = 'pointer';
                        $btn.innerHTML = originalHTML;
                    }, 2000);
                })
                .catch(() => {
                    $btn.disabled = false;
                    $btn.style.background = '#2C29CA';
                    $btn.style.cursor = 'pointer';
                    $btn.innerHTML = originalHTML;
                    Swal.fire('Error', 'Failed to add elective — check your connection.', 'error');
                });
        });

        // ===== MANAGE YOUR OWN ELECTIVES (edit/delete) =====
        function addRowToManageList(subject) {
            const list = document.getElementById('mySubjectsList');
            if (!list) return;

            const row = document.createElement('div');
            row.className = 'my-subject-row';
            row.dataset.id = subject.id;
            row.dataset.mdId = subject.md_id;
            row.style.cssText = 'display:flex; align-items:center; gap:.75rem; padding:.6rem .9rem; border:1.5px solid #e4e2ff; border-radius:.7rem;';
            row.innerHTML = `
                <span class="my-subject-name" style="flex:1; font-weight:600; color:#1e1b4b; font-size:.88rem;">${subject.md_name}</span>
                <button type="button" class="btn btn-sm my-subject-edit" title="Rename" style="background:transparent; border:1px solid #e4e2ff; border-radius:.5rem; color:#2C29CA; width:32px; height:32px;"><i class="fas fa-pen"></i></button>
                <button type="button" class="btn btn-sm my-subject-delete" title="Delete" style="background:transparent; border:1px solid #ffd9d9; border-radius:.5rem; color:#dc3545; width:32px; height:32px;"><i class="fas fa-trash"></i></button>
            `;
            list.appendChild(row);
            wireManageRow(row);
        }

        function wireManageRow(row) {
            const mdId = row.dataset.mdId;

            row.querySelector('.my-subject-edit')?.addEventListener('click', () => {
                const nameEl = row.querySelector('.my-subject-name');
                const currentName = nameEl.textContent.trim();

                Swal.fire({
                    title: 'Rename elective',
                    input: 'text',
                    inputValue: currentName,
                    inputPlaceholder: 'Elective name',
                    confirmButtonText: 'Save',
                    confirmButtonColor: '#2C29CA',
                    showCancelButton: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: (name) => {
                        name = (name || '').trim();
                        if (!name) {
                            Swal.showValidationMessage('Please enter an elective name.');
                            return false;
                        }

                        return fetch(`{{ url('o-level-electives/subjects') }}/${row.dataset.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ subject_name: name }),
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.showValidationMessage(res.message || 'Failed to rename elective.');
                                    return false;
                                }
                                return res.subject;
                            })
                            .catch(() => {
                                Swal.showValidationMessage('Failed to rename — check your connection.');
                                return false;
                            });
                    },
                    showLoaderOnConfirm: true,
                }).then(result => {
                    if (!result.isConfirmed || !result.value) return;

                    const subject = result.value;
                    subjectNameMap[subject.md_id] = subject.md_name;
                    nameEl.textContent = subject.md_name;

                    document.querySelectorAll('tr[data-student-id]').forEach(studentRow => {
                        const cb = studentRow.querySelector(`.elective-checkbox[value="${mdId}"]`);
                        if (cb) {
                            const label = cb.closest('.elective-checkbox-label');
                            label.dataset.subjectName = subject.md_name.toLowerCase();
                            label.querySelector('.form-check-label').textContent = subject.md_name;
                        }
                        updateElectiveCount(studentRow);
                    });

                    Toast.fire({ icon: 'success', title: `"${subject.md_name}" renamed` });
                });
            });

            row.querySelector('.my-subject-delete')?.addEventListener('click', () => {
                Swal.fire({
                    title: 'Delete this elective?',
                    text: 'This cannot be undone. It can only be deleted while no student currently has it selected.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#dc3545',
                    allowOutsideClick: () => !Swal.isLoading(),
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(`{{ url('o-level-electives/subjects') }}/${row.dataset.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.showValidationMessage(res.message || 'Failed to delete elective.');
                                    return false;
                                }
                                return true;
                            })
                            .catch(() => {
                                Swal.showValidationMessage('Failed to delete — check your connection.');
                                return false;
                            });
                    },
                }).then(result => {
                    if (!result.isConfirmed || !result.value) return;

                    const removedName = row.querySelector('.my-subject-name')?.textContent.trim();
                    delete subjectNameMap[mdId];

                    document.querySelectorAll('tr[data-student-id]').forEach(studentRow => {
                        studentRow.querySelector(`.elective-checkbox[value="${mdId}"]`)?.closest('.elective-checkbox-label')?.remove();

                        const order = electiveOrderFor(studentRow);
                        const i = order.indexOf(String(mdId));
                        if (i !== -1) order.splice(i, 1);

                        applyElectiveLimit(studentRow);
                        updateElectiveCount(studentRow);
                    });

                    row.remove();
                    Toast.fire({ icon: 'success', title: `"${removedName}" deleted` });
                });
            });
        }

        document.querySelectorAll('.my-subject-row').forEach(wireManageRow);

        document.getElementById('saveElectivesBtn')?.addEventListener('click', function () {
            const electives = [];
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                electives.push({
                    student_id: row.dataset.studentId,
                    elective_subject_ids: electiveOrderFor(row).slice(),
                });
            });

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

            fetch('{{ route('olevel.electives.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ electives }),
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.querySelectorAll('tr[data-student-id]').forEach(row => markRowClean(row));
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: res.message || 'All electives have been saved.',
                            confirmButtonColor: '#2C29CA',
                            timer: 2500,
                            timerProgressBar: true,
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Failed to save electives.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'))
                .finally(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-2"></i> <span id="saveBtnLabel">Save All Electives</span>';
                    updatePendingSummary();
                });
        });

        // ===== Per-row elective search =====
        document.querySelectorAll('tr[data-student-id]').forEach(row => {
            const input = row.querySelector('.subject-search-input');
            if (!input) return;

            input.addEventListener('input', () => {
                const q = input.value.trim().toLowerCase();

                row.querySelectorAll('.elective-checkbox-label[data-subject-name]').forEach(label => {
                    const name = label.dataset.subjectName || '';
                    const match = q === '' || name.includes(q);
                    label.classList.toggle('is-filtered-out', !match);
                });
            });
        });

        // ===== Student name search =====
        (function () {
            const input = document.getElementById('studentSearchInput');
            const clearBtn = document.getElementById('studentSearchClear');
            const countEl = document.getElementById('studentSearchCount');
            if (!input) return;

            const rows = Array.from(document.querySelectorAll('tr[data-student-id]'));
            const total = rows.length;

            const rowNameCache = new WeakMap();
            rows.forEach(row => {
                const cell = row.querySelector('td');
                rowNameCache.set(row, (cell ? cell.textContent : '').trim().toLowerCase());
            });

            function applyFilter() {
                const q = input.value.trim().toLowerCase();
                let visible = 0;

                rows.forEach(row => {
                    const name = rowNameCache.get(row) || '';
                    const match = q === '' || name.includes(q);
                    row.classList.toggle('is-filtered-out', !match);
                    if (match) visible++;
                });

                clearBtn.classList.toggle('is-visible', q !== '');

                if (q === '') {
                    countEl.textContent = '';
                } else {
                    countEl.innerHTML = 'Showing <b>' + visible + '</b> of ' + total + ' student' +
                        (total === 1 ? '' : 's');
                }
            }

            input.addEventListener('input', applyFilter);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    input.value = '';
                    applyFilter();
                }
            });

            clearBtn.addEventListener('click', () => {
                input.value = '';
                input.focus();
                applyFilter();
            });

            applyFilter();
        })();
    </script>
@endsection