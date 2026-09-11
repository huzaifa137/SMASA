<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        /* ===== Hero Section (mirrors Discipline Ratings entry) ===== */
        .rmk-hero {
            background: linear-gradient(135deg, #1a1a7a 0%, #2C29CA 60%, #6b69e8 100%);
            border-radius: 0 0 2.5rem 2.5rem;
            padding: 2.5rem 2.5rem 3.5rem;
            margin-bottom: -1.5rem;
            position: relative;
            overflow: hidden;
        }

        .rmk-hero .row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-right: 0;
            margin-left: 0;
        }

        .rmk-hero .col-md-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }

        .rmk-hero .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        @media (max-width: 768px) {

            .rmk-hero .col-md-8,
            .rmk-hero .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
                text-align: center !important;
            }

            .rmk-hero .col-md-4 {
                margin-top: 1rem;
            }
        }

        .rmk-hero .hero-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: .4rem 1.2rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .rmk-hero .hero-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
        }

        .rmk-hero .hero-subtitle {
            color: rgba(255, 255, 255, .7);
            font-weight: 400;
            font-size: 1rem;
        }

        .btn-back {
            border-radius: 1rem;
            padding: 0.7rem 1.8rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: 600;
            transition: all .25s ease;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
            transform: translateY(-1px);
        }

        .rmk-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            overflow: hidden;
            background: #fff;
        }

        .rmk-card .card-header-custom {
            padding: 1.25rem 1.75rem;
            border-bottom: 2px solid #f0eeff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .rmk-card .card-header-custom .title {
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rmk-card .card-header-custom .title i {
            color: #5351e4;
        }

        .rmk-card .card-body-custom {
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
            transition: all .15s ease;
        }

        .class-pill:hover {
            background: #f4f3ff;
            color: #3a37b8;
        }

        .class-pill.active {
            background: #2C29CA;
            border-color: #2C29CA;
            color: #fff;
        }

        .signer-chip {
            display: flex;
            align-items: center;
            gap: .6rem;
            background: #f8f7ff;
            border: 1px solid #ece9ff;
            border-radius: 1rem;
            padding: .6rem 1rem;
            font-size: .82rem;
        }

        .signer-chip img {
            max-height: 26px;
            max-width: 90px;
            object-fit: contain;
        }

        .signer-chip .no-sig {
            color: #b5b2f0;
            font-style: italic;
        }

        .rmk-table {
            margin-bottom: 0;
            font-size: .85rem;
        }

        .rmk-table thead th {
            background: #4d4be0;
            color: #fff;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            padding: 0.9rem 0.75rem;
            border: none;
            white-space: nowrap;
        }

        .rmk-table thead th.student-th {
            text-align: left;
            min-width: 180px;
            padding-left: 1.5rem;
        }

        .rmk-table tbody td {
            vertical-align: top;
            padding: 0.9rem 0.75rem;
            border-bottom: 1px solid #f0eeff;
        }

        .rmk-table tbody td:first-child {
            padding-left: 1.5rem;
            font-weight: 600;
            color: #1e1b4b;
            white-space: nowrap;
        }

        .rmk-table tbody tr:hover {
            background: #f8f7ff;
        }

        .remark-input {
            width: 100%;
            min-width: 260px;
            border: 2px solid #ede9ff;
            border-radius: .6rem;
            padding: .5rem .7rem;
            font-size: .82rem;
            resize: vertical;
            min-height: 54px;
        }

        .remark-input:focus {
            outline: none;
            border-color: #5351e4;
            box-shadow: 0 0 0 4px rgba(83, 81, 228, .12);
        }

        .remark-input:disabled {
            background: #f7f7fb;
            color: #999;
        }

        .btn-save {
            background: #2C29CA;
            color: #fff;
            border: none;
            border-radius: .8rem;
            padding: .8rem 1.8rem;
            font-weight: 700;
            font-size: .88rem;
        }

        .btn-save:hover {
            background: #24219f;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #a3a0c9;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: .75rem;
            display: block;
        }

        /* ===== Compact Header Bar ===== */
        .rmk-topbar {
            background: linear-gradient(135deg, #1a1a7a 0%, #2C29CA 100%);
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 20px rgba(44, 41, 202, .15);
        }

        .rmk-topbar-icon {
            width: 42px;
            height: 42px;
            border-radius: .75rem;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .rmk-topbar-label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .65);
            margin-bottom: 2px;
        }

        .rmk-topbar-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
            letter-spacing: -.01em;
        }

        .btn-back {
            border-radius: .75rem;
            padding: .5rem 1.1rem;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .2);
            color: #fff;
            font-weight: 600;
            font-size: .82rem;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, .25);
            color: #fff;
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
    <div class="side-app">

        {{-- ===== COMPACT HEADER BAR ===== --}}
        <div class="rmk-topbar mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="rmk-topbar-icon">
                        <i class="fas fa-comment-alt"></i>
                    </div> &nbsp; &nbsp;
                    <div>
                        <div class="rmk-topbar-label">Remarks &amp; Signatures</div>
                        <h1 class="rmk-topbar-title">{{ $exam->exam_name }}</h1>
                    </div>
                </div>
                <a href="{{ route('examination.passslips.index', $exam->id) }}" class="btn-back">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Pass Slips
                </a>
            </div>
        </div>

        <div class="row px-3 px-md-4">
            <div class="col-12">

                {{-- ===== SIGNATORIES (read-only, managed from profiles) ===== --}}
                <div class="rmk-card mb-4">
                    <div class="card-header-custom">
                        <div class="title"><i class="fas fa-signature"></i> Signatories for this class</div>
                    </div>
                    <div class="card-body-custom">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="signer-chip">
                                <i class="fas fa-chalkboard-teacher" style="color:#5351e4;"></i>
                                <div>
                                    <div class="fw-bold">{{ $classTeacher['name'] ?? 'No class teacher assigned' }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">Class Teacher</div>
                                </div>
                                @if(!empty($classTeacher['signature']) && Helper::signatureUrl($classTeacher['signature']))
                                    <img src="{{ Helper::signatureUrl($classTeacher['signature']) }}" alt="signature">
                                @else
                                    <span class="no-sig">no signature uploaded</span>
                                @endif
                            </div> &nbsp; &nbsp;

                            <div class="signer-chip">
                                <i class="fas fa-user-tie" style="color:#5351e4;"></i>
                                <div>
                                    <div class="fw-bold">{{ $headTeacher['name'] ?? 'Head teacher not set' }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">Head Teacher</div>
                                </div>
                                @if(!empty($headTeacher['signature']) && Helper::signatureUrl($headTeacher['signature']))
                                    <img src="{{ Helper::signatureUrl($headTeacher['signature']) }}" alt="signature">
                                @else
                                    <span class="no-sig">no signature uploaded</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-muted mt-3 mb-0" style="font-size:.78rem;">
                            Class Teacher signatures are uploaded from that teacher's own profile page;
                            the Head Teacher signature is uploaded from School Profile settings. Both
                            appear automatically on every report card once set — nothing to do here.
                        </p>
                    </div>
                </div>

                {{-- ===== CLASS PICKER ===== --}}
                <div class="rmk-card mb-4">
                    <div class="card-header-custom">
                        <div class="title"><i class="fas fa-layer-group"></i> Class</div>
                    </div>
                    <div class="card-body-custom">
                        @forelse($classOptions as $opt)
                            <a class="class-pill {{ (string) $opt->class_id === (string) $classId && (string) $opt->stream_id === (string) $streamId ? 'active' : '' }}"
                                href="{{ route('examination.remarks.entry', $exam->id) }}?class_id={{ $opt->class_id }}&stream_id={{ $opt->stream_id }}">
                                {{ $opt->class_name }}{{ $opt->stream_name ? ' — ' . $opt->stream_name : '' }}
                            </a>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <p>No classes have marks recorded for this exam yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ===== REMARKS GRID ===== --}}
                @if($students->count())
                    <div class="rmk-card mb-4">
                        <div class="table-responsive">
                            <table class="table rmk-table">
                                <thead>
                                    <tr>
                                        <th class="student-th">Student</th>
                                        <th>Class Teacher's Remark</th>
                                        <th>Head Teacher's Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $stu)
                                        @php $existing = $remarks->get($stu->id); @endphp
                                        <tr data-student-id="{{ $stu->id }}">
                                            <td>{{ $stu->lastname }} {{ $stu->firstname }}</td>
                                            <td>
                                                <textarea class="remark-input" data-field="class_teacher_remark"
                                                    placeholder="e.g. Amara is attentive and works well with peers.">{{ $existing->class_teacher_remark ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea class="remark-input" data-field="head_teacher_remark"
                                                    @if(!$canEditHeadTeacherRemark) disabled @endif
                                                    placeholder="e.g. A promising term overall. Keep it up.">{{ $existing->head_teacher_remark ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-body-custom text-end">
                            <button id="saveRemarksBtn" class="btn-save">
                                <i class="fas fa-save me-2"></i> Save All Remarks
                            </button>
                        </div>
                    </div>
                @elseif($classOptions->count())
                    <div class="rmk-card mb-4">
                        <div class="card-body-custom">
                            <div class="empty-state">
                                <i class="fas fa-user-graduate"></i>
                                <p>No students found for this class / stream.</p>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const REMARKS_EXAM_ID = {{ $exam->id }};
        const REMARKS_CLASS_ID = @json($classId);
        const REMARKS_STREAM_ID = @json($streamId);

        document.getElementById('saveRemarksBtn')?.addEventListener('click', function () {
            const remarks = [];
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                const studentId = row.dataset.studentId;
                const ct = row.querySelector('[data-field="class_teacher_remark"]');
                const ht = row.querySelector('[data-field="head_teacher_remark"]');
                remarks.push({
                    student_id: studentId,
                    class_teacher_remark: ct ? ct.value : '',
                    head_teacher_remark: ht ? ht.value : '',
                });
            });

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

            fetch('{{ route('examination.remarks.save', $exam->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    class_id: REMARKS_CLASS_ID,
                    stream_id: REMARKS_STREAM_ID,
                    remarks,
                }),
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: res.message || 'All remarks have been saved successfully.',
                            confirmButtonColor: '#2C29CA',
                            timer: 2500,
                            timerProgressBar: true,
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Failed to save remarks.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'))
                .finally(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-2"></i> Save All Remarks';
                });
        });
    </script>
@endsection