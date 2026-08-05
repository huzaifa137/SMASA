<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.reports.partials.styles')
    <style>
        .rpt-exam-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
        }

        .rpt-exam-card {
            background: #fff;
            border-radius: var(--rpt-radius-lg);
            box-shadow: var(--rpt-shadow);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid var(--rpt-brand-pale);
        }

        .rpt-exam-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(44, 41, 202, 0.16);
        }

        .rpt-exam-card-header {
            padding: 1.1rem 1.25rem;
            background: linear-gradient(135deg, #fafbff, #f6f7ff);
            border-bottom: 2px solid var(--rpt-brand-pale);
        }

        .rpt-exam-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1a1a2e;
            margin-bottom: 0.35rem;
        }

        .rpt-exam-code {
            font-size: 0.7rem;
            color: var(--rpt-brand-mid);
            font-family: monospace;
            background: var(--rpt-brand-pale);
            padding: 0.2rem 0.5rem;
            border-radius: 0.5rem;
            display: inline-block;
        }

        .rpt-exam-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.1rem;
            padding: 1rem 1.25rem;
        }

        .rpt-exam-stat {
            font-size: 0.75rem;
            color: #6c7080;
        }

        .rpt-exam-stat strong {
            display: block;
            font-size: 1.05rem;
            color: #1a1a2e;
        }

        .rpt-exam-actions {
            display: flex;
            gap: 0.5rem;
            padding: 0 1.25rem 1.1rem;
            flex-wrap: wrap;
        }

        .rpt-exam-actions a {
            flex: 1;
            min-width: 120px;
            text-align: center;
            justify-content: center;
        }

        .status-pill {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.25rem 0.7rem;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-draft {
            background: #e5e7eb;
            color: #4b5563;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.15);
            color: #0d9668;
        }

        .status-marks_entry {
            background: rgba(245, 158, 11, 0.15);
            color: #b45309;
        }

        .status-closed {
            background: rgba(239, 68, 68, 0.15);
            color: #dc2626;
        }

        .status-results_released {
            background: rgba(44, 41, 202, 0.12);
            color: #2C29CA;
        }

        /* Add these styles to your existing CSS section */
.rpt-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 2px solid transparent;
}

.rpt-btn-primary {
    background: linear-gradient(135deg, #2C29CA, #4f46e5);
    color: #fff;
    border-color: #2C29CA;
}

.rpt-btn-primary:hover {
    background: linear-gradient(135deg, #1f1d9e, #4338ca);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 41, 202, 0.3);
}

.rpt-btn-outline {
    background: transparent;
    color: #2C29CA;
    border-color: #2C29CA;
}

.rpt-btn-outline:hover {
    background: #2C29CA;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 41, 202, 0.2);
}

/* Alternative - if you want a lighter outline variant */
.rpt-btn-outline-light {
    background: transparent;
    color: #6b7280;
    border-color: #d1d5db;
}

.rpt-btn-outline-light:hover {
    background: #6b7280;
    color: #fff;
    border-color: #6b7280;
}
    </style>
@endsection

@section('content')
    <div class="side-app">

        <div class="rpt-hero-card">
            <div class="rpt-hero-main">
                <div class="rpt-hero-left">
                    <div class="rpt-hero-icon-wrapper">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="rpt-hero-info">
                        <h4>Examination Reports</h4>
                        <p>Generate detailed performance analysis with just one click</p>
                    </div>
                </div>
                <a href="{{ route('examination.index') }}" class="rpt-hero-action">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Exams</span>
                </a>
            </div>
            <div class="rpt-hero-features">
                <div class="rpt-feature">
                    <div class="rpt-feature-icon" style="background: rgba(238, 242, 255, 0.15);">
                        <i class="fas fa-table-cells" style="color: #818cf8;"></i>
                    </div>
                    <div>
                        <div class="rpt-feature-title">Class Summary</div>
                        <div class="rpt-feature-desc">View performance by class</div>
                    </div>
                </div>
                <div class="rpt-feature">
                    <div class="rpt-feature-icon" style="background: rgba(254, 243, 199, 0.15);">
                        <i class="fas fa-book" style="color: #fbbf24;"></i>
                    </div>
                    <div>
                        <div class="rpt-feature-title">Subject Report</div>
                        <div class="rpt-feature-desc">Deep-dive into subject stats</div>
                    </div>
                </div>
                <div class="rpt-feature">
                    <div class="rpt-feature-icon" style="background: rgba(209, 250, 229, 0.15);">
                        <i class="fas fa-chart-pie" style="color: #34d399;"></i>
                    </div>
                    <div>
                        <div class="rpt-feature-title">Grade Analysis</div>
                        <div class="rpt-feature-desc">Distribution &amp; grade bands</div>
                    </div>
                </div>
                <div class="rpt-feature">
                    <div class="rpt-feature-icon" style="background: rgba(252, 228, 236, 0.15);">
                        <i class="fas fa-download" style="color: #f87171;"></i>
                    </div>
                    <div>
                        <div class="rpt-feature-title">Export Data</div>
                        <div class="rpt-feature-desc">PDF &amp; Excel formats</div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .rpt-hero-card {
                background: linear-gradient(135deg, #000000 0%, #070189 100%);
                border-radius: 20px;
                padding: 1.75rem 2.25rem;
                margin-bottom: 2.5rem;
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 4px 24px rgba(7, 1, 137, 0.3);
            }

            .rpt-hero-main {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1.5rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .rpt-hero-left {
                display: flex;
                align-items: center;
                gap: 1.25rem;
            }

            .rpt-hero-icon-wrapper {
                width: 56px;
                height: 56px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 1.5rem;
                flex-shrink: 0;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            }

            .rpt-hero-info h4 {
                font-size: 1.35rem;
                font-weight: 700;
                color: #ffffff;
                margin-bottom: 0.15rem;
            }

            .rpt-hero-info p {
                color: rgba(255, 255, 255, 0.7);
                font-size: 0.9rem;
                margin: 0;
            }

            .rpt-hero-action {
                display: inline-flex;
                align-items: center;
                gap: 0.6rem;
                padding: 0.6rem 1.25rem;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 99px;
                color: rgba(255, 255, 255, 0.9);
                font-size: 0.85rem;
                font-weight: 500;
                transition: all 0.2s ease;
                text-decoration: none;
                white-space: nowrap;
                backdrop-filter: blur(10px);
            }

            .rpt-hero-action:hover {
                background: rgba(255, 255, 255, 0.2);
                color: #ffffff;
                text-decoration: none;
                border-color: rgba(255, 255, 255, 0.3);
                transform: translateX(-4px);
            }

            .rpt-hero-features {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1.25rem;
            }

            .rpt-feature {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.5rem 0.75rem;
                border-radius: 10px;
                transition: all 0.2s ease;
                cursor: default;
            }

            .rpt-feature:hover {
                background: rgba(255, 255, 255, 0.05);
                transform: translateY(-2px);
            }

            .rpt-feature-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                flex-shrink: 0;
                border: 1px solid rgba(255, 255, 255, 0.06);
            }

            .rpt-feature-title {
                font-size: 0.8rem;
                font-weight: 600;
                color: #ffffff;
                line-height: 1.2;
            }

            .rpt-feature-desc {
                font-size: 0.7rem;
                color: rgba(255, 255, 255, 0.5);
            }

            @media (max-width: 992px) {
                .rpt-hero-features {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (max-width: 768px) {
                .rpt-hero-card {
                    padding: 1.25rem;
                }

                .rpt-hero-main {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 1rem;
                }

                .rpt-hero-action {
                    justify-content: center;
                }

                .rpt-hero-features {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($examinations->isEmpty())
            <div class="rpt-panel rpt-empty-state">
                <i class="fas fa-folder-open"></i>
                <h6>No examinations yet</h6>
                <p class="mb-0">Create an examination first to start generating reports.</p>
            </div>
        @else
            <div class="rpt-exam-grid">
                @foreach ($examinations as $exam)
                    <div class="rpt-exam-card">
                        <div class="rpt-exam-card-header">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="rpt-exam-title">{{ Str::limit($exam->exam_name, 36) }}</div>
                                    <span class="rpt-exam-code">{{ $exam->exam_code }}</span>
                                </div>
                                <span class="status-pill status-{{ $exam->status }}">{{ $exam->statusLabel() }}</span>
                            </div>
                        </div>
                        <div class="rpt-exam-stats">
                            <div class="rpt-exam-stat">
                                <strong>{{ $exam->report_classes_count }}</strong> Class(es)
                            </div>
                            <div class="rpt-exam-stat">
                                <strong>{{ $exam->report_streams_count }}</strong> Stream(s)
                            </div>
                            <div class="rpt-exam-stat">
                                <strong>{{ $exam->report_student_count }}</strong> Students
                            </div>
                            <div class="rpt-exam-stat">
                                <strong>{{ $exam->report_marks_count }}</strong> Marks entered
                            </div>
                        </div>
                        <div class="rpt-exam-actions">
                            <a href="{{ route('examination.reports.class-summary', $exam->id) }}" class="rpt-btn rpt-btn-primary">
                                <i class="fas fa-table-cells"></i> Class Summary
                            </a>
                            <a href="{{ route('examination.reports.subject-report', $exam->id) }}" class="rpt-btn rpt-btn-outline">
                                <i class="fas fa-book"></i> Subject
                            </a>
                            <a href="{{ route('examination.reports.grade-analysis', $exam->id) }}" class="rpt-btn rpt-btn-outline">
                                <i class="fas fa-chart-pie"></i> Grades
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    </div>
                    </div>
@endsection