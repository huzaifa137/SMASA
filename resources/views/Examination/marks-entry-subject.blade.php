<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
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

        /* ── Hero Section: Dark Gradient (matching Assessment Scales) ── */
.exam-hero {
    background: linear-gradient(135deg, #0F0E1A 0%, #1B1D28 40%, #2C29CA 100%);
    border-radius: 1.25rem;
    padding: 2rem 2.5rem 2.5rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(44, 41, 202, .2);
}

/* Animated particles background */
.exam-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,.1), transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,.08), transparent),
        radial-gradient(2px 2px at 50px 160px, rgba(255,255,255,.12), transparent),
        radial-gradient(2px 2px at 90px 40px, rgba(255,255,255,.06), transparent),
        radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,.1), transparent),
        radial-gradient(2px 2px at 160px 30px, rgba(255,255,255,.08), transparent);
    background-size: 200px 200px;
    opacity: 0.5;
    pointer-events: none;
    animation: examParticleMove 20s linear infinite;
}

@keyframes examParticleMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-20px, -20px); }
}

/* Decorative glow - top right */
.exam-hero::after {
    content: '';
    position: absolute;
    top: -30%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(99, 102, 241, .15) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

/* Ensure content stays above background */
.exam-hero .row {
    position: relative;
    z-index: 1;
}

/* Status pill - updated for dark background */
.status-pill {
    padding: .4rem 1.2rem;
    border-radius: 99px;
    font-size: .8rem;
    font-weight: 700;
    display: inline-block;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}

.status-marks_entry {
    background: rgba(255, 243, 205, 0.9);
    color: #856404;
}

.status-active {
    background: rgba(212, 245, 226, 0.9);
    color: #1a7a4a;
}

.status-closed {
    background: rgba(253, 232, 232, 0.9);
    color: #c0392b;
}

/* Exam meta pills - dark theme */
.exam-meta-pill {
    background: rgba(255, 255, 255, .08);
    border-radius: .75rem;
    padding: .6rem 1rem;
    font-size: .9rem;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    transition: all 0.2s ease;
    width: 100%;
}

.exam-meta-pill i {
    font-size: 1rem;
    opacity: 0.9;
    color: #818CF8;
}

.exam-meta-pill:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-1px);
    border-color: rgba(255, 255, 255, 0.15);
}

/* Progress bar - dark theme */
.progress-bar-wrap {
    height: 8px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 99px;
    overflow: hidden;
    flex: 1;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #818CF8, #ffffff);
    border-radius: 99px;
    transition: width .5s ease;
}

/* Back button - dark theme */
.btn-back-exam {
    border-radius: 1rem;
    padding: 0.7rem 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: white;
    transition: all 0.25s ease;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-back-exam:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    color: white;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

/* Title text - dark theme */
.exam-hero h3 {
    color: #ffffff !important;
    font-weight: 800;
    font-size: 1.75rem;
    line-height: 1.2;
    word-break: break-word;
}

.exam-hero h3 strong {
    color: #818CF8;
}

.exam-hero .text-white-50 {
    color: rgba(255, 255, 255, 0.6) !important;
}

/* Progress text */
.progress-text {
    font-size: .8rem;
    color: rgba(255, 255, 255, 0.8);
    white-space: nowrap;
}

.progress-text i {
    color: #34D399;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .exam-hero {
        padding: 1.5rem 1.5rem 2rem;
        border-radius: 1rem;
    }
    
    .exam-hero h3 {
        font-size: 1.25rem;
    }
    
    .exam-hero .d-flex {
        flex-direction: column;
        align-items: stretch !important;
        gap: 8px !important;
    }
    
    .exam-hero .d-flex .btn-back-exam {
        width: 100%;
        justify-content: center;
    }
    
    .exam-meta-pill {
        font-size: .8rem;
        padding: .5rem .8rem;
    }
}

@media (max-width: 480px) {
    .exam-hero {
        padding: 1rem 1rem 1.5rem;
        border-radius: 0.85rem;
    }
    
    .exam-hero h3 {
        font-size: 1.1rem;
    }
    
    .exam-hero h3 strong {
        display: block;
        margin-top: 4px;
    }
    
    .status-pill {
        font-size: .7rem;
        padding: .3rem .8rem;
    }
    
    .exam-meta-pill {
        font-size: .75rem;
        padding: .4rem .7rem;
    }
    
    .exam-meta-pill i {
        font-size: .8rem;
    }
    
    .progress-text {
        font-size: .7rem;
    }
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
                            <strong>{{ Helper::classSubjectName($classSubject) }}</strong>
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
                                    @if ($classSubject->stream_id)
                                        — {{ $classSubject->stream_id }}
                                    @endif
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
                            <i class="fas fa-check-circle me-1"></i> {{ $entered }}/{{ $total }} entered
                            ({{ $progress }}%)
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Marks Table --}}
        <div class="card marks-card mb-5">
            <div class="card-body p-0">
                @if ($isEarlyYears)
                    <div class="alert alert-info m-3 mb-0" style="border-radius:.75rem;font-size:.85rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        This subject uses the <strong>{{ $assessmentScale->name }}</strong> scale
                        ({{ rtrim(rtrim(number_format($assessmentScale->min_score, 2), '0'), '.') }}–{{ rtrim(rtrim(number_format($earlyYearsMaxMark, 2), '0'), '.') }})
                        with a comment instead of numeric marks. Pick a system comment to auto-fill the score and
                        remark, or write your own — the comment box always stays editable.
                        @if ($assessmentScale->allow_custom_score)
                            You may also type a score outside the usual range if needed.
                        @endif
                        @if ($assessmentScale->usesLinkedGrading())
                            A letter grade is also shown, based on the <strong>{{ $assessmentScale->gradingScheme->name ?? 'linked' }}</strong> grading scheme.
                        @endif
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table marks-table mb-0" id="marksTable">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Student</th>
                                @if ($isEarlyYears)
                                    <th style="width:70px;">Score <small class="fw-normal text-white">({{ rtrim(rtrim(number_format($assessmentScale->min_score, 2), '0'), '.') }}–{{ rtrim(rtrim(number_format($earlyYearsMaxMark, 2), '0'), '.') }}{{ $assessmentScale->allow_custom_score ? '+' : '' }})</small></th>
                                    <th style="min-width:230px;">System Comment</th>
                                    @if ($assessmentScale->usesLinkedGrading())
                                        <th>Grade</th>
                                    @endif
                                    <th>Remark</th>
                                @else
                                    <th>Marks <small class="fw-normal text-white">(/{{ $exam->total_marks }})</small></th>
                                    <th>Grade</th>
                                    <th>Remark</th>
                                @endif
                                <th>Comment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $key => $student)
                                @php
                                    $mark = $existingMarks[$student->id] ?? null;
                                    $initials = strtoupper(
                                        substr($student->lastname, 0, 1) . substr($student->firstname, 0, 1),
                                    );
                                    $presetMatch = ($isEarlyYears && $mark?->marks_obtained !== null)
                                        ? $assessmentScale->presetForScore($mark->marks_obtained)
                                        : null;
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

                                    @if ($isEarlyYears)
                                        <td>
                                            <input type="number" class="form-control marks-input"
                                                name="marks[{{ $student->id }}]" data-student="{{ $student->id }}"
                                                data-min="{{ $assessmentScale->min_score }}"
                                                data-max="{{ $earlyYearsMaxMark }}"
                                                data-allow-custom="{{ $assessmentScale->allow_custom_score ? 1 : 0 }}"
                                                data-early-years="1"
                                                value="{{ $mark?->marks_obtained ?? '' }}"
                                                min="{{ $assessmentScale->allow_custom_score ? 0 : $assessmentScale->min_score }}"
                                                max="{{ $assessmentScale->allow_custom_score ? '' : $earlyYearsMaxMark }}"
                                                step="0.01" placeholder="—"
                                                style="width:70px;">
                                        </td>
                                        <td>
                                            <select class="form-control preset-select" data-student="{{ $student->id }}">
                                                <option value="">— Choose a system comment —</option>
                                                @foreach ($earlyYearsPresets as $preset)
                                                    <option value="{{ $preset['marks'] }}"
                                                        data-label="{{ $preset['label'] }}"
                                                        data-remark="{{ $preset['remark'] }}"
                                                        @selected($presetMatch && $presetMatch->id === $preset['id'] && $mark?->teacher_comment === $preset['label'])>
                                                        {{ $preset['label'] }} ({{ $preset['range_label'] }} — {{ $preset['remark'] }})
                                                    </option>
                                                @endforeach
                                                <option value="custom"
                                                    @selected($mark && (!$presetMatch || $mark?->teacher_comment !== ($presetMatch->label ?? null)))>
                                                    Write my own comment
                                                </option>
                                            </select>
                                        </td>
                                        @if ($assessmentScale->usesLinkedGrading())
                                            <td>
                                                <span class="grade-badge grade-cell" id="grade_{{ $student->id }}">
                                                    {{ $mark?->grade ?? '—' }}
                                                </span>
                                            </td>
                                        @endif
                                        <td>
                                            <span class="text-muted remark-cell" style="font-size:.8rem;"
                                                id="remark_{{ $student->id }}">
                                                {{ $mark?->grade_remark ?? '—' }}
                                            </span>
                                        </td>
                                    @else
                                        <td>
                                            <input type="number" class="form-control marks-input"
                                                name="marks[{{ $student->id }}]" data-student="{{ $student->id }}"
                                                data-max="{{ $exam->total_marks }}" value="{{ $mark?->marks_obtained ?? '' }}"
                                                min="0" max="{{ $exam->total_marks }}" step="0.5" placeholder="—">
                                        </td>
                                        @php
                                            $g = $mark?->grade ?? '';
                                            $gradeClass = '';
                                            if (str_starts_with($g, 'D')) {
                                                $gradeClass = 'grade-D';
                                            } elseif (str_starts_with($g, 'C')) {
                                                $gradeClass = 'grade-C';
                                            } elseif (str_starts_with($g, 'P')) {
                                                $gradeClass = 'grade-P';
                                            } elseif (str_starts_with($g, 'F')) {
                                                $gradeClass = 'grade-F';
                                            }
                                        @endphp

                                        <td>
                                            <span class="grade-badge grade-cell {{ $gradeClass }}"
                                                id="grade_{{ $student->id }}">
                                                {{ $mark?->grade ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted remark-cell" style="font-size:.8rem;"
                                                id="remark_{{ $student->id }}">
                                                {{ $mark?->grade_remark ?? '—' }}
                                            </span>
                                        </td>
                                    @endif
                                    <td>
                                        <input type="text" class="form-control comment-input"
                                            name="comment[{{ $student->id }}]"
                                            value="{{ $mark?->teacher_comment ?? '' }}" placeholder="Optional comment">
                                    </td>
                                    <td>
                                        <span
                                            class="badge status-cell
                                                                            @if ($mark?->status === 'entered') badge-entered
                                                                            @elseif($mark?->status === 'verified') badge-verified
                                                                            @else badge-pending @endif"
                                            id="status_{{ $student->id }}">
                                            <i
                                                class="fas 
                                                                                @if ($mark?->status === 'entered') fa-check-circle
                                                                                @elseif($mark?->status === 'verified') fa-shield-alt
                                                                                @else fa-clock @endif me-1"></i>
                                            {{ $mark ? ucfirst($mark->status) : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
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
        @if (in_array($exam->status, ['active', 'marks_entry']) && PermissionHelper::canFeature('edit_exam'))
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
                            @foreach ($gradingScale as $gs)
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
    </div>
    </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Grading scale + assessment-scale presets from PHP → JS
        const gradingScale = @json($gradingScale);
        const earlyYearsPresets = @json($earlyYearsPresets); // [{id, marks, min, max, range_label, label, remark}, ...] — 'marks'/'min' are the band's lower bound
        const scaleGradingBands = @json($scaleGradingBands ?? []); // only populated when the scale links a grading scheme

        // Pass total_marks from PHP into JS
        const examTotalMarks = {{ $exam->total_marks }};
        const scaleMaxScore = {{ $isEarlyYears ? $earlyYearsMaxMark : 'null' }};

        // ✅ Convert to percentage FIRST, then match scale
        function getGrade(marks) {
            if (marks === '' || marks === null || isNaN(marks)) return null;
            marks = parseFloat(marks);

            // Convert raw mark to percentage
            const percentage = examTotalMarks > 0
                ? (marks / examTotalMarks) * 100
                : 0;

            for (const g of gradingScale) {
                if (percentage >= g.min_mark && percentage <= g.max_mark) {
                    return g;
                }
            }
            return null;
        }

        // Same idea, but against the assessment scale's own max_score and
        // its linked grading scheme's bands (only used when a scale is
        // attached AND it links a grading scheme).
        function getScaleGrade(score) {
            if (score === '' || score === null || isNaN(score) || !scaleMaxScore) return null;
            score = parseFloat(score);
            const percentage = scaleMaxScore > 0 ? (score / scaleMaxScore) * 100 : 0;

            for (const g of scaleGradingBands) {
                if (percentage >= g.min_mark && percentage <= g.max_mark) {
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

        // Look up an assessment-scale preset by score, matching against its
        // min-max band (works for both single-value presets and true
        // ranges like 1-39).
        function findEarlyYearsPreset(marks) {
            const num = parseFloat(marks);
            if (isNaN(num)) return null;
            return earlyYearsPresets.find(p => num >= parseFloat(p.min) && num <= parseFloat(p.max)) || null;
        }

        // ===== System Comment (preset) dropdown =====
        // Selecting a preset fills in the score, the remark, AND the Comment
        // field (teacher_comment) — this was previously missing entirely,
        // which is why nothing saved when a preset was chosen.
        $(document).on('change', '.preset-select', function () {
            const sid = $(this).data('student');
            const val = $(this).val();
            const $marksInput = $(`.marks-input[data-student="${sid}"]`);
            const $remark = $(`#remark_${sid}`);
            const $comment = $(`input[name="comment[${sid}]"]`);

            if (val === '') {
                // "— Choose a system comment —" re-selected: leave everything as-is
                return;
            }

            if (val === 'custom') {
                // Teacher explicitly asked to write their own comment. Only
                // clear the box if it's empty or still holds a previously
                // auto-filled preset label — never wipe text the teacher
                // actually typed themselves, and never force a blank box
                // just because the score no longer matches a preset (that
                // defeats the whole point of presets: saving typing time).
                const currentText = $comment.val();
                const wasAutoFilled = !currentText || earlyYearsPresets.some(p => p.label === currentText);
                if (wasAutoFilled) {
                    $comment.val('');
                }
                $comment.focus();
                $marksInput.removeClass('valid invalid');
                return;
            }

            const $opt = $(this).find('option:selected');
            const label = $opt.data('label');
            const remarkText = $opt.data('remark');

            $marksInput.val(val).addClass('valid').removeClass('invalid');
            $remark.text(remarkText);
            $comment.val(label);
        });

        // ===== Live grade/remark preview on marks input =====
        $(document).on('input', '.marks-input', function () {
            const val = $(this).val();
            const min = parseFloat($(this).data('min'));
            const max = parseFloat($(this).data('max'));
            const allowCustom = $(this).data('allow-custom') == 1;
            const sid = $(this).data('student');
            const isEarlyYears = $(this).data('early-years') == 1;
            const $remark = $(`#remark_${sid}`);

            // Empty field
            if (val === '') {
                $(this).removeClass('valid invalid');
                $remark.text('—');
                if (isEarlyYears) {
                    $(`.preset-select[data-student="${sid}"]`).val('');
                    $(`#grade_${sid}`).text('—');
                } else {
                    $(`#grade_${sid}`).text('—').attr('class', 'grade-badge grade-cell');
                }
                return;
            }

            const num = parseFloat(val);

            // Invalid / out of range. A scale that allows custom scores only
            // rejects negative numbers; otherwise it's clamped to min/max.
            const outOfRange = isEarlyYears
                ? (allowCustom ? num < 0 : (num < min || num > max))
                : (num < 0 || num > max);

            if (isNaN(num) || outOfRange) {
                $(this).removeClass('valid').addClass('invalid');
                $remark.text('Invalid');
                if (!isEarlyYears) {
                    $(`#grade_${sid}`).text('!').attr('class', 'grade-badge grade-cell grade-F');
                }
                return;
            }

            $(this).removeClass('invalid').addClass('valid');

            if (isEarlyYears) {
                // With ranges, a teacher normally types a raw score (e.g.
                // 23) rather than picking an exact preset value from the
                // dropdown, so the matching System Comment needs to
                // auto-fill here too — not just on dropdown selection.
                // Never overwrite text the teacher actually typed
                // themselves though: only fill in when the comment box is
                // empty or still holds a previous auto-filled label.
                const preset = findEarlyYearsPreset(num);
                $remark.text(preset ? preset.remark : '—');

                const $select = $(`.preset-select[data-student="${sid}"]`);
                const $comment = $(`input[name="comment[${sid}]"]`);
                const currentText = $comment.val();
                const wasAutoFilled = !currentText || earlyYearsPresets.some(p => p.label === currentText);

                if (preset && wasAutoFilled) {
                    $select.val(preset.marks);
                    $comment.val(preset.label);
                } else if (!preset && wasAutoFilled) {
                    $select.val('');
                    $comment.val('');
                }

                if (scaleGradingBands.length) {
                    const g = getScaleGrade(num);
                    $(`#grade_${sid}`).text(g ? g.grade : '—');
                }
            } else {
                const g = getGrade(num);
                const $grade = $(`#grade_${sid}`);
                if (g) {
                    $grade.text(g.grade).attr('class', 'grade-badge grade-cell ' + gradeCssClass(g.grade));
                    $remark.text(g.remark);
                }
            }
        });

        // Save marks
        $('#saveMarksBtn').on('click', function() {
            // Validate all inputs first
            let hasError = false;
            $('.marks-input').each(function() {
                const val = $(this).val();
                const min = parseFloat($(this).data('min'));
                const max = parseFloat($(this).data('max'));
                const allowCustom = $(this).data('allow-custom') == 1;
                const isEarlyYears = $(this).data('early-years') == 1;

                const outOfRange = isEarlyYears
                    ? (allowCustom ? parseFloat(val) < 0 : (parseFloat(val) < min || parseFloat(val) > max))
                    : (parseFloat(val) < 0 || parseFloat(val) > max);

                // Only validate if the field has a value
                if (val !== '' && (isNaN(parseFloat(val)) || outOfRange)) {
                    $(this).addClass('invalid');
                    hasError = true;
                } else {
                    $(this).removeClass('invalid');
                }
            });

            if (hasError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid marks',
                    text: 'Some marks are out of range. Please correct them before saving.'
                });
                return;
            }

            const enteredCount = $('.marks-input').filter(function() {
                return $(this).val() !== '';
            }).length;

            const totalCount = $('.marks-input').length;

            Swal.fire({
                title: 'Save Marks?',
                html: `You are saving marks for <strong>${enteredCount}</strong> of <strong>${totalCount}</strong> student(s).<br>
               Empty fields will remain pending.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2C29CA',
                confirmButtonText: 'Yes, save!',
            }).then(result => {
                if (!result.isConfirmed) return;

                const marksData = [];
                $('tr[data-student-id]').each(function() {
                    const sid = $(this).data('student-id');
                    const markVal = $(this).find('.marks-input').val();
                    const comment = $(this).find('.comment-input').val();

                    // Only include the marks value if it's not empty
                    marksData.push({
                        student_id: sid,
                        marks: markVal !== '' ? markVal : null,
                        comment: comment
                    });
                });

                const $btn = $('#saveMarksBtn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');

                $.ajax({
                    url: '{{ route('examination.marks.save', $exam->id) }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        _token: '{{ csrf_token() }}',
                        marks: marksData,
                        subject_id: @json($classSubject->subject_id),
                        custom_subject_id: @json($classSubject->custom_subject_id),
                        class_id: @json($classSubject->class_id),
                        stream_id: @json($classSubject->stream_id),
                    }),
                    success: function(res) {
                        if (res.success) {
                            // Update status badges live
                            $('tr[data-student-id]').each(function() {
                                const sid = $(this).data('student-id');
                                const val = $(this).find('.marks-input').val();
                                const $status = $(`#status_${sid}`);

                                if (val !== '') {
                                    $status.removeClass('badge-pending')
                                        .addClass('badge-entered')
                                        .html(
                                            '<i class="fas fa-check-circle me-1"></i> Entered'
                                            );
                                } else {
                                    $status.removeClass('badge-entered')
                                        .addClass('badge-pending')
                                        .html(
                                            '<i class="fas fa-clock me-1"></i> Pending'
                                            );
                                }
                            });

                            Swal.fire({
                                icon: 'success',
                                title: 'Marks Saved!',
                                text: res.message,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#2C29CA',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'An error occurred while saving marks'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-2"></i> Save All Marks');
                    }
                });
            });
        });
    </script>
@endsection