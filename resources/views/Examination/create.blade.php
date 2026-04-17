<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('assets/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />
    <style>
        .exam-hero {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 60%, #7c7aec 100%);
            border-radius: 0 0 2rem 2rem;
            padding: 2rem 2rem 3rem;
            margin-bottom: -1.5rem;
            position: relative;
        }

        .exam-hero::after {
            content: '';
            position: absolute;
            right: 2rem;
            bottom: 1rem;
            width: 120px;
            height: 120px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='rgba(255,255,255,0.08)'%3E%3Cpath d='M9 2H15a1 1 0 011 1v1h3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2h3V3a1 1 0 011-1zm0 8a1 1 0 100 2h6a1 1 0 100-2H9zm0 4a1 1 0 100 2h4a1 1 0 100-2H9z'/%3E%3C/svg%3E") no-repeat center;
            background-size: contain;
        }

        .form-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(44, 41, 202, .10);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-weight: 700;
            font-size: .85rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #2C29CA;
            margin-bottom: 1.2rem;
            padding-bottom: .5rem;
            border-bottom: 2px solid #ede9ff;
        }

        .section-header i {
            font-size: 1rem;
        }

        .class-stream-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: .7rem;
        }

        .cs-item {
            border: 2px solid #e0ddff;
            border-radius: .7rem;
            padding: .7rem 1rem;
            cursor: pointer;
            transition: all .18s;
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .85rem;
        }

        .cs-item:hover {
            border-color: #5351e4;
            background: #f5f4ff;
        }

        .cs-item.selected {
            border-color: #2C29CA;
            background: #ede9ff;
            color: #2C29CA;
            font-weight: 600;
        }

        .cs-item .cs-icon {
            width: 32px;
            height: 32px;
            border-radius: .4rem;
            background: #ede9ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            flex-shrink: 0;
            color: #5351e4;
            transition: background .18s;
        }

        .cs-item.selected .cs-icon {
            background: #2C29CA;
            color: #fff;
        }

        .step-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #2C29CA;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .submit-btn {
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            border: none;
            border-radius: .6rem;
            padding: .7rem 2.2rem;
            font-weight: 600;
            letter-spacing: .03em;
            transition: opacity .2s, transform .15s;
        }

        .submit-btn:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .status-pill {
            display: inline-block;
            padding: .2rem .8rem;
            border-radius: 99px;
            font-size: .72rem;
            font-weight: 600;
        }

        button {
            padding: 0.5rem 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="side-app">

        {{-- Hero Banner --}}
        <div class="exam-hero mb-4" style="position: relative; z-index: 1;">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div class="mb-2 mb-md-0">
                    <h3 class="text-white mb-1 fw-bold">
                        <i class="fas fa-file-alt me-2"></i> Create Examination
                    </h3>
                    <p class="text-white mb-0 opacity-75" style="font-size:.9rem;">
                        Define a new examination, assign classes, and set entry deadlines.
                    </p>
                </div>
                <a href="{{ route('examination.index') }}"
                    class="btn btn-white text-primary btn-sm fw-bold d-inline-flex align-items-center"
                    style="border-radius: .5rem; text-decoration: none; z-index: 10; position: relative;">
                    <i class="fas fa-list me-1" style="pointer-events: none;"></i> &nbsp; Go to All Examinations
                </a>
            </div>
        </div>

        <form id="createExamForm">
            @csrf
            <div class="row g-3">

                {{-- ── Left Column ─────────────────────────────────────────────── --}}
                <div class="col-lg-7">

                    {{-- Basic Info --}}
                    <div class="card form-card mb-3">
                        <div class="card-body p-4">
                            <div class="section-header">
                                <span class="step-badge">1</span>
                                <i class="fas fa-list-check me-2"></i>Examination Details
                            </div>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Examination Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="exam_name" class="form-control"
                                        placeholder="e.g. End of Term 1 Examinations 2025">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Exam Code</label>
                                    <input type="text" name="exam_code" id="exam_code" class="form-control bg-light"
                                        value="{{ $examCode }}" readonly>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Examination Type <span
                                            class="text-danger">*</span></label>
                                    <select name="exam_type" class="form-control form-select">
                                        <option value="">-- Select Type --</option>
                                        <option value="Beginning-of-Term">Beginning of Term</option>
                                        <option value="Mid-Term">Mid Term</option>
                                        <option value="End-of-Term">End of Term</option>
                                        <option value="Continuous Assessment">Continuous Assessment</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Term <span class="text-danger">*</span></label>
                                    <select name="term" class="form-control form-select">
                                        <option value="">-- Select Term --</option>
                                        <option value="Term 1">Term 1</option>
                                        <option value="Term 2">Term 2</option>
                                        <option value="Term 3">Term 3</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Academic Year <span
                                            class="text-danger">*</span></label>
                                    <!-- <input type="number" name="academic_year" class="form-control" value="{{ date('Y') }}"
                                        min="2000" max="2099" placeholder="{{ date('Y') }}"> -->
                                        <input type="number" name="academic_year" class="form-control" value="{{ Helper::active_year() }}"
                                        min="2000" max="2099" placeholder="{{ date('Y') }}" readonly>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-semibold">Description / Notes</label>
                                    <textarea name="description" class="form-control" rows="2"
                                        placeholder="Optional internal notes about this examination..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="card form-card mb-3">
                        <div class="card-body p-4">
                            <div class="section-header">
                                <span class="step-badge">2</span>
                                <i class="fas fa-calendar-alt"></i> Dates &amp; Timeline
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">End Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Marks Entry Deadline <span class="text-danger">*</span>
                                        <i class="fas fa-info-circle text-muted ms-1"
                                            title="Teachers cannot enter marks after this date."
                                            data-bs-toggle="tooltip"></i>
                                    </label>
                                    <input type="date" name="marks_entry_deadline" class="form-control">
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 py-2 mb-0" style="font-size:.82rem; border-radius:.6rem;">
                                <i class="fas fa-lightbulb me-1"></i>
                                Once the <strong>Marks Entry Deadline</strong> passes, the system will automatically close
                                the examination and prevent further mark submissions.
                            </div>
                        </div>
                    </div>

                    {{-- Marks & Grading --}}
                    <div class="card form-card">
                        <div class="card-body p-4">
                            <div class="section-header">
                                <span class="step-badge">3</span>
                                <i class="fas fa-percent"></i> Marks &amp; Grading
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Total Marks <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="total_marks" class="form-control" value="100" min="1"
                                        max="1000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Pass Mark <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="pass_mark" class="form-control" value="50" min="1">
                                </div>
                            </div>
                            <div class="mt-3 p-3 rounded"
                                style="background:#f8f7ff; border:1px solid #ede9ff; font-size:.82rem;">
                                <strong><i class="fas fa-table me-1 text-primary"></i> Grading Scale (Uganda
                                    O-Level)</strong><br>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach([['D1', '80–100', 'Distinction'], ['D2', '75–79', 'Distinction'], ['C3', '70–74', 'Credit'], ['C4', '65–69', 'Credit'], ['C5', '60–64', 'Credit'], ['C6', '55–59', 'Credit'], ['P7', '45–54', 'Pass'], ['P8', '40–44', 'Pass'], ['F9', '0–39', 'Fail']] as $g)
                                        <span class="badge text-white mb-1"
                                            style="background:{{ in_array($g[0], ['D1', 'D2']) ? '#1a7a4a' : (in_array($g[0], ['C3', 'C4', 'C5', 'C6']) ? '#2C29CA' : ($g[0] == 'F9' ? '#c0392b' : '#f39c12')) }}; font-size:.72rem; padding:.35rem .6rem;margin-right:5px;">
                                            {{ $g[0] }}: {{ $g[1] }} &mdash; {{ $g[2] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── Right Column — Classes ────────────────────────────────────── --}}
                <div class="col-lg-5">
                    <div class="card form-card h-100">
                        <div class="card-body p-4">
                            <div class="section-header">
                                <span class="step-badge">4</span>
                                <i class="fas fa-chalkboard-teacher"></i> Classes Involved
                            </div>
                            <p class="text-muted mb-3" style="font-size:.83rem;">
                                Select all class–stream combinations sitting this examination.
                            </p>

                            {{-- Select All toggle --}}
                            <div class="d-flex justify-content-between align-items-center mb-3 text-white">
                                <span id="selectedCount" class="badge bg-primary" style="font-size:.78rem;">0
                                    selected</span>
                                <button type="button" id="toggleAllClasses" class="btn btn-sm btn-outline-primary"
                                    style="border-radius:.5rem; font-size:.8rem;">
                                    <i class="fas fa-check-double me-1"></i> Select All
                                </button>
                            </div>

                            <div class="class-stream-grid" id="classStreamGrid">
                                @forelse ($classStreams as $cs)
                                    <div class="cs-item" data-value="{{ $cs->class_id }}_{{ $cs->stream_id }}"
                                        onclick="toggleClassStream(this)">
                                        <div class="cs-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="line-height:1.2;">
                                                {{ Helper::recordMdname($cs->class_id) }}
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">
                                                {{ $cs->stream_id ?? 'No Stream' }}
                                            </div>
                                        </div>
                                        <input type="checkbox" name="class_streams[]"
                                            value="{{ $cs->class_id }}_{{ $cs->stream_id }}" class="d-none cs-checkbox">
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No class-stream assignments found for this school.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-5 mt-md-3 mb-5">
                <div class="col-12">
                    <div class="card form-card">
                        <div
                            class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">

                            <div class="text-muted" style="font-size:.83rem;">
                                <i class="fas fa-shield-alt text-success me-1"></i>
                                Examination will be saved as <strong>Draft</strong>. Activate it when ready. 
                            </div>
                            &nbsp; &nbsp;
                            <button type="submit"
                                class="btn text-white submit-btn d-inline-flex align-items-center w-100 w-md-auto justify-content-center mt-2 mt-md-0">
                                <i class="fas fa-save me-2"></i> &nbsp;Create Examination
                            </button>

                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        // ── Class-stream toggle ─────────────────────────────────────────────────
        function toggleClassStream(el) {
            el.classList.toggle('selected');
            el.querySelector('.cs-checkbox').checked = el.classList.contains('selected');
            updateCount();
        }

        function updateCount() {
            const n = document.querySelectorAll('.cs-item.selected').length;
            document.getElementById('selectedCount').textContent = n + ' selected';
        }

        let allSelected = false;
        document.getElementById('toggleAllClasses').addEventListener('click', function () {
            allSelected = !allSelected;
            document.querySelectorAll('.cs-item').forEach(el => {
                el.classList.toggle('selected', allSelected);
                el.querySelector('.cs-checkbox').checked = allSelected;
            });
            this.innerHTML = allSelected
                ? '<i class="fas fa-times me-1"></i> Deselect All'
                : '<i class="fas fa-check-double me-1"></i> Select All';
            updateCount();
        });

        // ── Form submit ────────────────────────────────────────────────────────
        $('#createExamForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');

            // Basic validation
            let errors = [];
            ['exam_name', 'exam_type', 'term', 'academic_year', 'start_date', 'end_date', 'marks_entry_deadline', 'total_marks', 'pass_mark']
                .forEach(name => {
                    const $el = $form.find(`[name="${name}"]`);
                    if (!$el.val() || !$el.val().trim()) {
                        $el.addClass('is-invalid');
                        errors.push(name.replace(/_/g, ' '));
                    } else {
                        $el.removeClass('is-invalid');
                    }
                });

            if (document.querySelectorAll('.cs-checkbox:checked').length === 0) {
                errors.push('at least one class');
            }

            if (errors.length) {
                Swal.fire({ icon: 'error', title: 'Incomplete', text: 'Please fill: ' + errors.join(', ') + '.' });
                return;
            }

            // Date sanity
            const start = new Date($form.find('[name="start_date"]').val());
            const end = new Date($form.find('[name="end_date"]').val());
            const deadline = new Date($form.find('[name="marks_entry_deadline"]').val());

            if (end < start) {
                Swal.fire({ icon: 'error', title: 'Date Error', text: 'End date must be on or after start date.' });
                return;
            }
            if (deadline < end) {
                Swal.fire({ icon: 'error', title: 'Date Error', text: 'Marks entry deadline must be on or after the end date.' });
                return;
            }

            Swal.fire({
                title: 'Create Examination?',
                html: `<div style="text-align:left; font-size:.9rem;">
                                                <strong>${$form.find('[name="exam_name"]').val()}</strong><br>
                                                Type: ${$form.find('[name="exam_type"]').val()} &bull; ${$form.find('[name="term"]').val()} ${$form.find('[name="academic_year"]').val()}<br>
                                                Classes: ${document.querySelectorAll('.cs-checkbox:checked').length} selected
                                           </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, create it!',
                confirmButtonColor: '#2C29CA',
            }).then(result => {
                if (!result.isConfirmed) return;

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Creating...');

                $.ajax({
                    url: '{{ route("examination.store") }}',
                    method: 'POST',
                    data: $form.serialize(),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Examination Created!',
                                html: `Code: <strong>${res.exam_code}</strong><br>Status: <span class="badge bg-secondary text-white">Draft</span>`,
                                confirmButtonColor: '#2C29CA',
                            }).then(() => window.location.href = '{{ route("examination.index") }}');
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    // error: function (xhr) { $('body').html(xhr.responseText);

                    //  },
                    error: function (xhr) {
                        let message = 'Something went wrong';

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).map(err => err[0]).join('\n');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        alert(message);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Create Examination');
                    }
                });
            });
        });
    </script>
@endsection
@section('js')
    <script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
@endsection