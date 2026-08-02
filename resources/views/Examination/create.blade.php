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

        /* ── Hero V2: Premium Dark Gradient with Glow ──────────────── */
.exam-hero-v2 {
    background: linear-gradient(135deg, #0F0E1A 0%, #1B1D28 40%, #2C29CA 100%);
    border-radius: 1.25rem;
    padding: 0;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(44, 41, 202, .2);
}

/* Animated particles background */
.exam-hero-v2-particles {
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
    animation: particleMove 20s linear infinite;
}

@keyframes particleMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-20px, -20px); }
}

/* Decorative glow elements */
.exam-hero-v2::before {
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

.exam-hero-v2::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -5%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(44, 41, 202, .1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.exam-hero-v2-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    padding: 2.5rem 3rem;
    position: relative;
    z-index: 1;
}

.exam-hero-v2-content {
    flex: 1;
    min-width: 250px;
}

.exam-hero-v2-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .08);
    color: rgba(255, 255, 255, .7);
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    padding: .4rem 1rem;
    border-radius: 99px;
    margin-bottom: 1rem;
    backdrop-filter: blur(10px);
}

.exam-hero-v2-badge i {
    color: #818CF8;
    font-size: .6rem;
}

.exam-hero-v2-title {
    font-size: 2.2rem;
    font-weight: 900;
    color: #ffffff;
    margin: 0 0 .5rem 0;
    letter-spacing: -.03em;
    line-height: 1.15;
}

.exam-hero-v2-subtitle {
    font-size: .95rem;
    color: rgba(255, 255, 255, .6);
    margin: 0 0 1.5rem 0;
    line-height: 1.6;
    max-width: 50ch;
}

.exam-hero-v2-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.exam-hero-v2-stat {
    display: flex;
    flex-direction: column;
}

.exam-hero-v2-stat-number {
    font-size: 1.3rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.2;
}

.exam-hero-v2-stat-label {
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(255, 255, 255, .4);
}

.exam-hero-v2-stat-divider {
    width: 1px;
    height: 30px;
    background: rgba(255, 255, 255, .1);
}

.exam-hero-v2-actions {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-shrink: 0;
    flex-wrap: wrap;
}

.exam-hero-v2-action-divider {
    width: 1px;
    height: 35px;
    background: rgba(255, 255, 255, .1);
}

.btn-exam-v2-glass {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .7rem 1.3rem;
    background: rgba(255, 255, 255, .06);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, .08);
    color: rgba(255, 255, 255, .8);
    font-weight: 600;
    font-size: .85rem;
    border-radius: .6rem;
    text-decoration: none;
    transition: all .25s ease;
}

.btn-exam-v2-glass:hover {
    background: rgba(255, 255, 255, .12);
    color: #ffffff;
    text-decoration: none;
    transform: translateY(-2px);
    border-color: rgba(255, 255, 255, .15);
}

.btn-exam-v2-gradient {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .7rem 1.6rem;
    background: linear-gradient(135deg, #818CF8 0%, #6366F1 50%, #4F46E5 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: .85rem;
    border: none;
    border-radius: .6rem;
    text-decoration: none;
    transition: all .25s ease;
    box-shadow: 0 4px 20px rgba(99, 102, 241, .3);
}

.btn-exam-v2-gradient:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 28px rgba(99, 102, 241, .4);
    color: #ffffff;
    text-decoration: none;
}

.btn-exam-v2-gradient:active {
    transform: translateY(0) scale(1);
}

/* ── Responsive V2 ──────────────────────────────────────────── */
@media (max-width: 992px) {
    .exam-hero-v2-container {
        flex-direction: column;
        align-items: stretch;
        padding: 2rem 1.75rem;
    }

    .exam-hero-v2-title {
        font-size: 1.8rem;
    }

    .exam-hero-v2-actions {
        width: 100%;
        flex-direction: column;
    }

    .exam-hero-v2-action-divider {
        width: 100%;
        height: 1px;
        background: rgba(255, 255, 255, 0.93);
    }

    .btn-exam-v2-glass,
    .btn-exam-v2-gradient {
        width: 100%;
        justify-content: center;
    }

    .exam-hero-v2-subtitle br {
        display: none;
    }
}

@media (max-width: 480px) {
    .exam-hero-v2-container {
        padding: 1.5rem 1.25rem;
    }

    .exam-hero-v2-title {
        font-size: 1.5rem;
    }

    .exam-hero-v2-stats {
        gap: .75rem;
    }

    .exam-hero-v2-stat-number {
        font-size: 1.1rem;
    }
}
    </style>
@endsection

@section('content')
    <div class="side-app">

{{-- Hero Banner - Option 2: Premium Dark Gradient with Glow --}}
<div class="exam-hero-v2">
    <div class="exam-hero-v2-particles"></div>
    <div class="exam-hero-v2-container">
        <div class="exam-hero-v2-content">
            <div class="exam-hero-v2-badge">
                <i class="fas fa-sparkles"></i>
                <span>New Examination</span>
            </div>
            <h2 class="exam-hero-v2-title">
                Create Your Next<br>Examination
            </h2>
            <p class="exam-hero-v2-subtitle">
                Streamline your examination setup with intelligent defaults,<br>
                real-time validation, and seamless class assignment.
            </p>

            <div class="exam-hero-v2-stats">
                <div class="exam-hero-v2-stat">
                    <div class="exam-hero-v2-stat-number text-center">{{ $schoolExaminations }}</div>
                    <div class="exam-hero-v2-stat-label">Total Exams</div>
                </div>
                <div class="exam-hero-v2-stat-divider"></div>
                <div class="exam-hero-v2-stat">
                    <div class="exam-hero-v2-stat-number text-center">{{ count($gradingSchemes); }}</div>
                    <div class="exam-hero-v2-stat-label">Grading Schemes</div>
                </div>
                <div class="exam-hero-v2-stat-divider "></div>
                <div class="exam-hero-v2-stat">
                    <div class="exam-hero-v2-stat-number text-center">{{ $schoolClasses }}</div>
                    <div class="exam-hero-v2-stat-label">Active Classes</div>
                </div>
            </div>
        </div>
        <div class="exam-hero-v2-actions">
            <a href="{{ route('examination.index') }}" class="btn-exam-v2-glass">
                <i class="fas fa-arrow-left"></i>
                <span>All Examinations</span>
            </a>
            <div class="exam-hero-v2-action-divider"></div>
            <a href="{{ url('examinations/grading-schemes') }}" class="btn-exam-v2-gradient">
                <i class="fas fa-layer-group"></i>
                <span>Grading Schemes</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
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
        <!-- Keep existing section-header with flex for full screen -->
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center">
                <span class="step-badge">3</span> &nbsp;
                <i class="fas fa-percent ms-1"></i> &nbsp; Marks &amp; Grading
            </div>
            <a href="{{ route('examination.grading-schemes.index') }}" target="_blank"
                class="btn btn-sm btn-outline-primary mt-2 mt-sm-0" 
                style="border-radius:.5rem; font-size:.75rem;">
                <i class="fas fa-cog me-1"></i> Manage grading schemes
            </a>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Grading Scheme <span class="text-danger">*</span></label>
            <select name="grading_scheme_id" id="gradingSchemeSelect" class="form-control">
                <option value="">-- Select a grading scheme --</option>
              @forelse ($gradingSchemes as $scheme)
@php
$bandsData = $scheme->bands->map(fn($b) => [
    'grade' => $b->grade,
    'min' => $b->min_mark,
    'max' => $b->max_mark,
    'remark' => $b->remark
])->toArray();
$bandsJson = json_encode($bandsData);
@endphp
<option value="{{ $scheme->id }}"
    data-total-marks="{{ $scheme->total_marks }}"
    data-pass-mark="{{ $scheme->pass_mark }}"
    data-bands="{{ $bandsJson }}"
    {{ old('grading_scheme_id') == $scheme->id || (!old('grading_scheme_id') && $scheme->is_default) ? 'selected' : '' }}>
    {{ $scheme->name }}{{ $scheme->school_id ? '' : ' (Global default)' }}
    — out of {{ $scheme->total_marks }}, pass {{ $scheme->pass_mark }}
</option>
@empty
<option value="" disabled>No grading schemes available — create one first.</option>
@endforelse
            </select>
            @if ($gradingSchemes->isEmpty())
                <div class="text-danger mt-1" style="font-size:.78rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    No grading schemes yet. <a href="{{ route('examination.grading-schemes.index') }}">Create one</a> before scheduling an exam.
                </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Total Marks <span
                        class="text-danger">*</span></label>
                <input type="number" name="total_marks" id="totalMarksInput" class="form-control"
                    value="100" min="1" max="1000">
                <div class="form-text" style="font-size:.75rem;">Auto-filled from the scheme above; adjust if this exam differs.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Pass Mark <span
                        class="text-danger">*</span></label>
                <input type="number" name="pass_mark" id="passMarkInput" class="form-control"
                    value="50" min="1">
            </div>
        </div>

        <div class="mt-3 p-3 rounded"
            style="background:#f8f7ff; border:1px solid #ede9ff; font-size:.82rem;">
            <strong><i class="fas fa-table me-1 text-primary"></i> Grading Scale Preview</strong><br>
            <div class="d-flex flex-wrap gap-2 mt-2" id="gradingBandsPreview">
                <span class="text-muted" style="font-size:.78rem;">Select a grading scheme to preview its grade bands.</span>
            </div>
        </div>
    </div>
</div>

<style>
    /* Responsive section header - only affects small screens */
@media (max-width: 575.98px) {
    .section-header.d-flex {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.5rem !important;
    }
    
    .section-header.d-flex .d-flex.align-items-center {
        justify-content: center;
    }
    
    .section-header.d-flex .btn {
        width: 100% !important;
        text-align: center;
        justify-content: center;
    }
}
</style>
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
                    <div class="card form-card" style="border-left: 4px solid #2C29CA;">
                        <div class="card-body p-3">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="status-pill bg-light text-dark border border-2 border-primary px-3 py-1">
                                            <i class="fas fa-circle text-warning me-1" style="font-size: .5rem;"></i>
                                            DRAFT
                                        </span>
                                    </div>
                                    <div class="text-muted d-flex align-items-center gap-2" style="font-size:.85rem;">
                                       &nbsp; <i class="fas fa-shield-alt text-success"></i>
                                        <span>Auto-saved as draft. <strong>Activate</strong> when ready to publish.</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="submit"
                                        class="btn text-white submit-btn px-4 py-2 d-inline-flex align-items-center justify-content-center"
                                        style="border-radius: 50px;">
                                        <i class="fas fa-save me-2"></i> &nbsp;Create Examination
                                    </button>
                                </div>
                            </div>
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

        // ── Grading scheme: auto-fill total marks / pass mark + bands preview ────
        function gradeColor(grade) {
            if (/^[AD]1|^[AD]2|^A\b/i.test(grade)) return '#1a7a4a';
            if (/^F|FAIL/i.test(grade)) return '#c0392b';
            if (/^[CB]/i.test(grade)) return '#2C29CA';
            return '#f39c12';
        }

        function renderGradingPreview(bands) {
            const $preview = $('#gradingBandsPreview');
            if (!bands || !bands.length) {
                $preview.html('<span class="text-muted" style="font-size:.78rem;">This scheme has no grade bands defined yet.</span>');
                return;
            }
            const sorted = [...bands].sort((a, b) => b.min - a.min);
            const html = sorted.map(b => `
                <span class="badge text-white mb-1" style="background:${gradeColor(b.grade)}; font-size:.72rem; padding:.35rem .6rem; margin-right:5px;">
                    ${b.grade}: ${b.min}\u2013${b.max}${b.remark ? ' \u2014 ' + b.remark : ''}
                </span>`).join('');
            $preview.html(html);
        }

        function applySelectedScheme() {
            const $opt = $('#gradingSchemeSelect option:selected');
            const totalMarks = $opt.data('total-marks');
            const passMark = $opt.data('pass-mark');
            const bands = $opt.data('bands');

            if (totalMarks) $('#totalMarksInput').val(totalMarks);
            if (passMark) $('#passMarkInput').val(passMark);
            renderGradingPreview(bands);
        }

        $('#gradingSchemeSelect').on('change', applySelectedScheme);
        $(document).ready(function () {
            if ($('#gradingSchemeSelect').val()) applySelectedScheme();
        });

        // ── Form submit ────────────────────────────────────────────────────────
        $('#createExamForm').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');

            // Basic validation
            let errors = [];
            ['exam_name', 'exam_type', 'term', 'academic_year', 'start_date', 'end_date', 'marks_entry_deadline', 'grading_scheme_id', 'total_marks', 'pass_mark']
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