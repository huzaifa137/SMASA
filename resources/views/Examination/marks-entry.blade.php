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
            box-shadow: 0 4px 20px rgba(44,41,202,.14);
            transform: translateY(-2px);
        }
        .subject-icon {
            width: 52px; height: 52px;
            border-radius: .75rem;
            background: #ede9ff;
            color: #2C29CA;
            display: flex; align-items: center; justify-content: center;
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
            background: rgba(255,255,255,.15);
            border-radius: .5rem;
            padding: .3rem .8rem;
            font-size: .8rem;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }
        .status-pill {
            padding: .28rem .85rem;
            border-radius: 99px;
            font-size: .73rem;
            font-weight: 700;
        }
        .status-marks_entry { background:#fff3cd; color:#856404; }
        .status-active       { background:#d4f5e2; color:#1a7a4a; }
        .status-closed       { background:#fde8e8; color:#c0392b; }
    </style>
@endsection

@section('content')
<div class="side-app">

    {{-- Hero --}}
    <div class="exam-hero mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="mb-2">
                    <span class="status-pill status-{{ $exam->status }}">
                        {{ $exam->statusLabel() }}
                    </span>
                </div>
                <h3 class="text-white fw-bold mb-2">{{ $exam->exam_name }}</h3>
                <div class="d-flex flex-wrap gap-2">
                    <span class="exam-meta-pill"><i class="fas fa-tag"></i> {{ $exam->exam_type }}</span>
                    <span class="exam-meta-pill"><i class="fas fa-calendar"></i> {{ $exam->term }} {{ $exam->academic_year }}</span>
                    <span class="exam-meta-pill"><i class="fas fa-code"></i> {{ $exam->exam_code }}</span>
                    <span class="exam-meta-pill"><i class="fas fa-hourglass-end"></i> Deadline: {{ $exam->marks_entry_deadline->format('d M Y') }}</span>
                </div>
            </div>
            <a href="{{ route('examination.index') }}" class="btn btn-light btn-sm fw-semibold" style="border-radius:.5rem;">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(!in_array($exam->status, ['active','marks_entry']))
        <div class="alert alert-danger rounded-3 mb-4" role="alert">
            <i class="fas fa-lock me-2"></i>
            Marks entry is currently <strong>closed</strong> for this examination.
            @if($exam->status === 'results_released')
                Results have already been released.
            @endif
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-12">
            <h5 class="fw-bold mb-0" style="color:#2C29CA;">
                <i class="fas fa-book me-2"></i>Your Assigned Subjects
            </h5>
            <p class="text-muted mt-1" style="font-size:.85rem;">
                Click a subject to enter student marks.
            </p>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($assignedSubjects as $subject)
            @php
                $subjectName = Helper::recordMdname($subject->subject_id);
                $className   = Helper::recordMdname($subject->class_id);
            @endphp
            <div class="col-md-4 col-lg-3">
                <a href="{{ route('examination.marks.subject', [$exam->id, $subject->id]) }}"
                   class="subject-card card p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="subject-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:.9rem; line-height:1.2;">{{ $subjectName }}</div>
                            <div class="text-muted" style="font-size:.76rem;">{{ $className }} &bull; {{ $subject->stream_id ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem; color:#666;">
                        <span>Marks entered</span>
                        <span class="fw-semibold text-primary">—</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:0%"></div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <span class="badge" style="background:#ede9ff; color:#2C29CA; font-size:.72rem; border-radius:.4rem;">
                            {{ $subject->subject_type ?? 'Core' }}
                        </span>
                        <span style="font-size:.78rem; color:#5351e4; font-weight:600;">
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
@endsection
