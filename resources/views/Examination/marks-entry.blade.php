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

        .subject-card {
            border: 2px solid #ede9ff;
            border-radius: 1rem;
            transition: all .18s;
            cursor: pointer;
            text-decoration: none !important;
            color: inherit !important;
            display: block;
        }

        .subject-card:hover {
            border-color: #2C29CA;
            box-shadow: 0 4px 20px rgba(44, 41, 202, .14);
            transform: translateY(-2px);
        }

        .subject-icon {
            width: 52px;
            height: 52px;
            border-radius: .75rem;
            background: #ede9ff;
            color: #2C29CA;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .progress-track {
            height: 6px;
            border-radius: 99px;
            background: #ede9ff;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #2C29CA, #5351e4);
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
            /* keep for desktop grid */
        }

        /* ADD THIS */
        @media (max-width: 768px) {
            .exam-meta-pill {
                margin-bottom: 0.75rem;
            }
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

        .subject-header {
            gap: 14px;
            /* THIS is the real spacing you were missing */
        }

        .subject-icon {
            width: 52px;
            height: 52px;
            border-radius: .75rem;
            background: #ede9ff;
            color: #2C29CA;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .subject-text {
            flex: 1;
            padding-left: 2px;
            /* subtle breathing space */
            min-width: 0;
        }

        .subject-title,
        .subject-meta {
            text-align: left;
            word-break: break-word;
        }
    </style>
@endsection

@section('content')
    <div class="side-app">

        {{-- Hero --}}
        <div class="exam-hero mb-4">
            <div class="row">

                {{-- TOP ROW (responsive stack with proper spacing) --}}
                <div class="col-12">

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center"
                        style="gap: 12px;">

                        {{-- LEFT: STATUS --}}
                        <span class="status-pill status-{{ $exam->status }}">
                            {{ $exam->statusLabel() }}
                        </span>

                        {{-- RIGHT: BUTTON --}}
                        <a href="{{ route('examination.index') }}" class="btn fw-semibold mt-2 mt-md-0" style="border-radius: 1rem; padding: 0.7rem 1.5rem;
                      background: rgba(255,255,255,0.2);
                      backdrop-filter: blur(10px);
                      border: 1px solid rgba(255,255,255,0.3);
                      color: white;
                      transition: all 0.2s ease;"
                            onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-2px)'"
                            onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(0)'">

                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>

                    </div>
                </div>

                {{-- TITLE --}}
                <div class="col-12">
                    <h3 class="text-white fw-bold mb-4"
                        style="font-size: 1.75rem; line-height: 1.2; word-break: break-word;">
                        {{ $exam->exam_name }}
                    </h3>
                </div>

                {{-- META PILLS --}}
                <div class="col-12">
                    <div class="row g-3 gy-3">

                        <div class="col-12 col-sm-6 col-lg-3">
                            <span class="exam-meta-pill">
                                <i class="fas fa-tag"></i>
                                <span>{{ $exam->exam_type }}</span>
                            </span>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <span class="exam-meta-pill">
                                <i class="fas fa-calendar"></i>
                                <span>{{ $exam->term }} {{ $exam->academic_year }}</span>
                            </span>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <span class="exam-meta-pill">
                                <i class="fas fa-code"></i>
                                <span>{{ $exam->exam_code }}</span>
                            </span>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <span class="exam-meta-pill">
                                <i class="fas fa-hourglass-end"></i>
                                <span>Deadline: {{ $exam->marks_entry_deadline->format('d M Y') }}</span>
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        @if(!in_array($exam->status, ['active', 'marks_entry']))
            <div class="alert alert-danger rounded-3 mb-4" role="alert">
                <i class="fas fa-lock me-2"></i>
                Marks entry is currently <strong>closed</strong> for this examination.
                @if($exam->status === 'results_released')
                    Results have already been released.
                @endif
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-12">
                <h5 class="fw-bold mb-0" style="color:#2C29CA; font-size: 1.25rem;">
                    <i class="fas fa-book me-2"></i> Teacher Assigned Subjects
                </h5>
                <p class="text-muted mt-2" style="font-size:.9rem;">
                    Click a subject to enter student marks.
                </p>
            </div>
        </div>

        <div class="row g-4">
            @forelse ($assignedSubjects as $subject)
                @php
                    $subjectName = Helper::recordMdname($subject->subject_id);
                    $className = Helper::recordMdname($subject->class_id);
                @endphp

                <div class="col-md-4 col-lg-3 d-flex">
                    <a href="{{ route('examination.marks.subject', [$exam->id, $subject->id]) }}" class="subject-card card p-3">

                        <div class="d-flex align-items-start mb-3 subject-header">

                            <div class="subject-icon">
                                <i class="fas fa-book-open"></i>
                            </div>

                            <div class="subject-text">
                                <div class="fw-bold subject-title">
                                    {{ $subjectName }}
                                </div>

                                <div class="text-muted subject-meta">
                                    {{ $className }} &bull; {{ $subject->stream_id ?? '—' }}
                                </div>
                            </div>

                        </div>

                        @php
                            $key = $subject->subject_id . '_' . $subject->class_id . '_' . $subject->stream_id;
                            $studentKey = $subject->class_id . '_' . $subject->stream_id;
                            $enteredCount = $markCounts[$key]->entered_count ?? 0;
                            $totalStudents = $studentCounts[$studentKey]->total ?? 0;
                            $pct = $totalStudents > 0 ? round(($enteredCount / $totalStudents) * 100) : 0;
                        @endphp

                        <div class="d-flex justify-content-between mb-1" style="font-size:.8rem; color:#666;">
                            <span>Marks entered</span>
                            <span class="fw-semibold text-primary">{{ $enteredCount }}/{{ $totalStudents }}</span>
                        </div>

                        <div class="progress-track">
                            <div class="progress-fill" style="width:{{ $pct }}%"></div>
                        </div>

                        <div class="mt-3 d-flex justify-content-between align-items-center">

                            <span class="badge"
                                style="background:#ede9ff; color:#2C29CA; font-size:.75rem; border-radius:.4rem; padding:.3rem .8rem;">
                                {{ ucwords($subject->subject_type ?? 'Core') }}
                            </span>

                            <span style="font-size:.85rem; color:#5351e4; font-weight:600;">
                                Enter Marks <i class="fas fa-chevron-right ms-1"></i>
                            </span>

                        </div>
                    </a>
                </div>

            @empty
                <div class="col-12">
                    <div class="card border-0 text-center py-5" style="border-radius:1rem; background:#f8f7ff;">

                        <i class="fas fa-book fa-3x mb-3 d-block" style="color:#c0bdff;"></i>

                        <h5 class="fw-semibold text-muted">No subjects assigned</h5>

                        <p class="text-muted" style="font-size:.85rem;">
                            You have not been assigned as a subject teacher for any class in this examination.
                            <br>Please contact the school administrator.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
    </div>
    </div>
@endsection