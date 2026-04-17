<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .exam-hero {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 60%, #7c7aec 100%);
            border-radius: 0 0 2rem 2rem;
            padding: 2rem 2rem 3rem;
            margin-bottom: -1.5rem;
        }

        .marks-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 18px rgba(44, 41, 202, .09);
            overflow: hidden;
        }

        .marks-table thead th {
            background: #4d4be0;
            color: #FFF;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            border-bottom: 2px solid #ede9ff;
        }

        .marks-table tbody tr:hover {
            background: #faf9ff;
        }

        .marks-input {
            width: 90px;
            text-align: center;
            border: 2px solid #ede9ff;
            border-radius: .5rem;
            font-weight: 700;
            font-size: .9rem;
            transition: border-color .15s;
        }

        .marks-input:focus {
            border-color: #5351e4;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, .12);
        }

        .marks-input.valid {
            border-color: #1a7a4a;
            background: #f0fdf4;
        }

        .marks-input.invalid {
            border-color: #c0392b;
            background: #fef2f2;
        }

        .grade-badge {
            display: inline-block;
            min-width: 38px;
            text-align: center;
            padding: .25rem .55rem;
            border-radius: .4rem;
            font-weight: 800;
            font-size: .82rem;
            letter-spacing: .03em;
        }

        .grade-D {
            background: #d4f5e2;
            color: #1a7a4a;
        }

        .grade-C {
            background: #cfe2ff;
            color: #0a4191;
        }

        .grade-P {
            background: #fff3cd;
            color: #856404;
        }

        .grade-F {
            background: #fde8e8;
            color: #c0392b;
        }

        .student-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2C29CA, #7c7aec);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .save-fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #2C29CA, #5351e4);
            color: #fff;
            border: none;
            border-radius: 2rem;
            padding: .85rem 2rem;
            font-weight: 700;
            font-size: .9rem;
            box-shadow: 0 6px 24px rgba(44, 41, 202, .35);
            z-index: 999;
            transition: transform .15s, box-shadow .15s;
        }

        .save-fab:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(44, 41, 202, .45);
        }

        .save-fab:active {
            transform: translateY(0);
        }

        .progress-bar-wrap {
            height: 8px;
            background: rgba(255, 255, 255, .2);
            border-radius: 99px;
            overflow: hidden;
            margin-top: .5rem;
        }

        .progress-bar-fill {
            height: 100%;
            background: #fff;
            border-radius: 99px;
            transition: width .3s;
        }

        .comment-input {
            border: 2px solid #ede9ff;
            border-radius: .5rem;
            font-size: .8rem;
            width: 160px;
            transition: border-color .15s;
        }

        .comment-input:focus {
            border-color: #5351e4;
        }

        .exam-meta-pill {
            background: rgba(255, 255, 255, .15);
            border-radius: .75rem;
            padding: .6rem 1rem;
            font-size: .9rem;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            width: 100%;
        }

        .exam-meta-pill i {
            font-size: 1rem;
            opacity: 0.9;
        }

        .exam-meta-pill:hover {
            background: rgba(255, 255, 255, .25);
            transform: translateY(-1px);
        }

        .status-pill {
            padding: .4rem 1rem;
            border-radius: 99px;
            font-size: .8rem;
            font-weight: 700;
            display: inline-block;
        }

        .status-marks_entry {
            background: #fff3cd;
            color: #856404;
        }

        .status-active {
            background: #d4f5e2;
            color: #1a7a4a;
        }

        .status-closed {
            background: #fde8e8;
            color: #c0392b;
        }

        .progress-bar-wrap {
            height: 8px;
            background: rgba(255, 255, 255, .2);
            border-radius: 99px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: #fff;
            border-radius: 99px;
            transition: width .3s;
        }
        .badge-entered {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: .4rem .9rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: .72rem;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.badge-verified {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    padding: .4rem .9rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: .72rem;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
}

.badge-pending {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    padding: .4rem .9rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: .72rem;
    box-shadow: 0 2px 4px rgba(107, 114, 128, 0.2);
}
    </style>
@endsection

@section('content')
    <div class="side-app">

        {{-- Hero --}}
        <div class="exam-hero mb-4">
            <div class="row">
                <div class="col-12">

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center"
                        style="gap: 12px;">

                        <span class="status-pill status-{{ $exam->status }}">
                            {{ $exam->statusLabel() }}
                        </span>

                        <a href="{{ route('examination.marks.entry', $exam->id) }}" class="btn fw-semibold mt-2 mt-md-0"
                            style="border-radius: 1rem; padding: 0.7rem 1.5rem;
                                              background: rgba(255,255,255,0.2);
                                              backdrop-filter: blur(10px);
                                              border: 1px solid rgba(255,255,255,0.3);
                                              color: white;
                                              transition: all 0.2s ease;"
                            onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-2px)'"
                            onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">
                            <i class="fas fa-arrow-left me-2"></i> Back to Exams
                        </a>

                    </div>
                </div>

                {{-- TITLE AND BREADCRUMB --}}
                <div class="col-12">
                    <div class="mb-1" style="font-size:.82rem; color:rgba(255,255,255,.7); margin-top: 1rem;">
                        <h3 class="text-white fw-bold mb-3"
                            style="font-size: 1.75rem; line-height: 1.2; word-break: break-word;">
                            {{ $exam->exam_name }} &rsaquo;
                            <strong>{{ Helper::recordMdname($classSubject->subject_id) }}</strong>
                        </h3>
                        <h5 style="color: color:rgba(255,255,255,.7);">Enter Marks</h5>
                    </div>
                </div>

                {{-- META PILLS --}}
                <div class="col-12">
                    <div class="row g-3 gy-3">
                        <div class="col-12 col-sm-6 col-lg-4">
                            <span class="exam-meta-pill">
                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                <span>{{ Helper::recordMdname($classSubject->class_id) }}
                                    @if($classSubject->stream_id) — {{ $classSubject->stream_id }} @endif
                                </span>
                            </span>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-4">
                            <span class="exam-meta-pill">
                                <i class="fas fa-star me-1"></i>
                                <span>Out of {{ $exam->total_marks }}</span>
                            </span>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-4">
                            <span class="exam-meta-pill">
                                <i class="fas fa-hourglass-end me-1"></i>
                                <span>Deadline: {{ $exam->marks_entry_deadline->format('d M Y') }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- PROGRESS BAR --}}
                @php
                    $entered = $existingMarks->where('status', 'entered')->count();
                    $total = $students->count();
                    $progress = $total > 0 ? round(($entered / $total) * 100) : 0;
                @endphp
                <div class="col-12 mt-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="progress-bar-wrap flex-grow-1"
                            style="max-width: 100%; background: rgba(255,255,255,0.2);">
                            <div class="progress-bar-fill" style="width:{{ $progress }}%; background: #fff;"></div>
                        </div> &nbsp; &nbsp;
                        <span style="font-size:.8rem; color:#fff; white-space: nowrap;">
                            <i class="fas fa-check-circle me-1"></i> {{ $entered }}/{{ $total }} entered ({{ $progress }}%)
                        </span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Marks Table --}}
        <div class="card marks-card mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table marks-table mb-0" id="marksTable">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Student</th>
                                <th>Adm No.</th>
                                <th>Marks <small class="fw-normal text-muted">(/{{ $exam->total_marks }})</small></th>
                                <th>Grade</th>
                                <th>Remark</th>
                                <th>Comment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $key => $student)
                                @php
                                    $mark = $existingMarks[$student->id] ?? null;
                                    $initials = strtoupper(substr($student->lastname, 0, 1) . substr($student->firstname, 0, 1));
                                @endphp
                                <tr data-student-id="{{ $student->id }}">
                                    <td class="text-muted" style="font-size:.8rem;">{{ $key + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="student-avatar">{{ $initials }}</div>
                                            <div>
                                                <div class="fw-semibold" style="font-size:.86rem;padding-left:5px;">
                                                    {{ $student->lastname }} {{ $student->firstname }}
                                                </div>
                                                <div class="text-muted" style="font-size:.74rem;">
                                                    {{ $student->other_names ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-family:monospace; font-size:.82rem; color:#5351e4;">
                                        {{ $student->admission_number ?? '—' }}
                                    </td>
                                    <td>
                                        <input type="number" class="form-control marks-input" name="marks[{{ $student->id }}]"
                                            data-student="{{ $student->id }}" data-max="{{ $exam->total_marks }}"
                                            value="{{ $mark?->marks_obtained ?? '' }}" min="0" max="{{ $exam->total_marks }}"
                                            step="0.5" placeholder="—">
                                    </td>
                                    <td>
                                        <span class="grade-badge grade-cell
                                                         @php
                                                            $g = $mark?->grade ?? '';
                                                            if (str_starts_with($g, 'D'))
                                                                echo 'grade-D';
                                                            elseif (str_starts_with($g, 'C'))
                                                                echo 'grade-C';
                                                            elseif (str_starts_with($g, 'P'))
                                                                echo 'grade-P';
                                                            elseif (str_starts_with($g, 'F'))
                                                                echo 'grade-F';
                                                        @endphp" id="grade_{{ $student->id }}">
                                            {{ $mark?->grade ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted remark-cell" style="font-size:.8rem;"
                                            id="remark_{{ $student->id }}">
                                            {{ $mark?->grade_remark ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control comment-input" name="comment[{{ $student->id }}]"
                                            value="{{ $mark?->teacher_comment ?? '' }}" placeholder="Optional comment">
                                    </td>
                                   <td>
    <span class="badge status-cell
        @if($mark?->status === 'entered') badge-entered
        @elseif($mark?->status === 'verified') badge-verified
        @else badge-pending @endif"
        id="status_{{ $student->id }}">
        <i class="fas 
            @if($mark?->status === 'entered') fa-check-circle
            @elseif($mark?->status === 'verified') fa-shield-alt
            @else fa-clock @endif me-1"></i>
        {{ $mark ? ucfirst($mark->status) : 'Pending' }}
    </span>
</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py           uted">
                                        <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                                        No students found in this class-stream.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Floating Save Button --}}
        @if(in_array($exam->status, ['active', 'marks_entry']))
            <button type="button" id="saveMarksBtn" class="save-fab">
                <i class="fas fa-save me-2"></i> Save All Marks
            </button>
        @endif

    </div>

    {{-- Grading scale reference modal --}}
    <div class="modal fade" id="gradeScaleModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-0" style="border-radius:1rem;">
                <div class="modal-header"
                    style="background:linear-gradient(135deg,#2C29CA,#5351e4); border-radius:1rem 1rem 0 0;">
                    <h6 class="modal-title text-white fw-bold mb-0"><i class="fas fa-table me-2"></i>Grading Scale</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-sm mb-0">
                        <thead style="background:#f5f4ff;">
                            <tr>
                                <th style="font-size:.75rem;">Grade</th>
                                <th style="font-size:.75rem;">Range</th>
                                <th style="font-size:.75rem;">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gradingScale as $gs)
                                <tr>
                                    <td><strong>{{ $gs->grade }}</strong></td>
                                    <td style="font-size:.8rem;">{{ $gs->min_mark }}–{{ $gs->max_mark }}</td>
                                    <td style="font-size:.8rem;">{{ $gs->remark }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Grading scale from PHP → JS
        const gradingScale = @json($gradingScale);

        function getGrade(marks) {
            if (marks === '' || marks === null || isNaN(marks)) return null;
            marks = parseFloat(marks);
            for (const g of gradingScale) {
                if (marks >= g.min_mark && marks <= g.max_mark) {
                    return g;
                }
            }
            return null;
        }

        function gradeCssClass(grade) {
            if (!grade) return '';
            if (grade.startsWith('D')) return 'grade-D';
            if (grade.startsWith('C')) return 'grade-C';
            if (grade.startsWith('P')) return 'grade-P';
            if (grade.startsWith('F')) return 'grade-F';
            return '';
        }

        // Live grade preview on marks input
        $(document).on('input', '.marks-input', function () {
            const val = $(this).val();
            const max = parseFloat($(this).data('max'));
            const sid = $(this).data('student');
            const $grade = $(`#grade_${sid}`);
            const $remark = $(`#remark_${sid}`);

            // Visual validity
            if (val === '') {
                $(this).removeClass('valid invalid');
                $grade.text('—').attr('class', 'grade-badge grade-cell');
                $remark.text('—');
                return;
            }
            const num = parseFloat(val);
            if (isNaN(num) || num < 0 || num > max) {
                $(this).removeClass('valid').addClass('invalid');
                $grade.text('!').attr('class', 'grade-badge grade-cell grade-F');
                $remark.text('Invalid');
                return;
            }

            $(this).removeClass('invalid').addClass('valid');

            const g = getGrade(num);
            if (g) {
                $grade.text(g.grade).attr('class', 'grade-badge grade-cell ' + gradeCssClass(g.grade));
                $remark.text(g.remark);
            }
        });

        // Save marks
        $('#saveMarksBtn').on('click', function () {

            // Validate all inputs first
            let hasError = false;
            $('.marks-input').each(function () {
                const val = $(this).val();
                const max = parseFloat($(this).data('max'));
                if (val !== '' && (isNaN(parseFloat(val)) || parseFloat(val) < 0 || parseFloat(val) > max)) {
                    $(this).addClass('invalid');
                    hasError = true;
                }
            });

            if (hasError) {
                Swal.fire({ icon: 'error', title: 'Invalid marks', text: 'Some marks are out of range. Please correct them before saving.' });
                return;
            }

            const enteredCount = $('.marks-input').filter(function () { return $(this).val() !== ''; }).length;

            Swal.fire({
                title: 'Save Marks?',
                html: `You are saving marks for <strong>${enteredCount}</strong> student(s).`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2C29CA',
                confirmButtonText: 'Yes, save!',
            }).then(result => {
                if (!result.isConfirmed) return;

                const marksData = [];
                $('tr[data-student-id]').each(function () {
                    const sid = $(this).data('student-id');
                    const markVal = $(this).find('.marks-input').val();
                    const comment = $(this).find('.comment-input').val();
                    marksData.push({ student_id: sid, marks: markVal, comment: comment });
                });

                const $btn = $('#saveMarksBtn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');

                $.ajax({
                    url: '{{ route("examination.marks.save", $exam->id) }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        _token: '{{ csrf_token() }}',
                        marks: marksData,
                        subject_id: {{ $classSubject->subject_id }},
                        class_id:   {{ $classSubject->class_id }},
                        stream_id:  {{ $classSubject->stream_id ?? 'null' }},
                    }),
                    success: function (res) {
                        if (res.success) {
                            // Update status badges live
                            $('tr[data-student-id]').each(function () {
                                const sid = $(this).data('student-id');
                                const val = $(this).find('.marks-input').val();
                                const $s = $(`#status_${sid}`);
                                if (val !== '') {
                                    $s.text('Entered').removeClass('bg-secondary').addClass('bg-success');
                                }
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Marks Saved!',
                                text: res.message,
                                timer: 1800,
                                showConfirmButton: false,
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function (xhr) { $('body').html(xhr.responseText); },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Save All Marks');
                    }
                });
            });
        });
    </script>
@endsection