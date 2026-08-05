<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.reports.partials.styles')
@endsection

@section('content')
    <div class="side-app">

        <div class="rpt-hero-card">
            <div class="rpt-hero-main">
                <div class="rpt-hero-left">
                    <div class="rpt-hero-icon-wrapper">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="rpt-hero-info">
                        <h4>Grade Analysis</h4>
                        <p>{{ $exam->exam_name }} — grade distribution, subject strengths/weaknesses and top performers.</p>
                    </div>
                </div>
                <a href="{{ route('examination.reports.index') }}" class="rpt-hero-action no-print">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Reports</span>
                </a>
            </div>
            <div class="rpt-hero-meta">
                <div class="rpt-meta-items">
                    <div class="rpt-meta-item">
                        <i class="fas fa-code"></i>
                        <span>{{ $exam->exam_code }}</span>
                    </div>
                    <div class="rpt-meta-divider"></div>
                    <div class="rpt-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>{{ $exam->term }} • {{ $exam->academic_year }}</span>
                    </div>
                    <div class="rpt-meta-divider"></div>
                    <div class="rpt-meta-item">
                        <i class="fas fa-school"></i>
                        <span>{{ $selectedClassId ? collect($classOptions)->firstWhere('class_id', $selectedClassId)?->class_name : 'All Classes' }}</span>
                    </div>
                    @if($selectedClassId && $selectedStreamId)
                        <div class="rpt-meta-divider"></div>
                        <div class="rpt-meta-item">
                            <i class="fas fa-users"></i>
                            <span>{{ Helper::recordMdname($selectedStreamId) }}</span>
                        </div>
                    @endif
                    @if($selectedClassId && $selectedSubjectName)
                        <div class="rpt-meta-divider"></div>
                        <div class="rpt-meta-item rpt-meta-highlight">
                            <i class="fas fa-book-open"></i>
                            <span>{{ $selectedSubjectName }}</span>
                        </div>
                    @endif
                    @if($filters['gender'] ?? null)
                        <div class="rpt-meta-divider"></div>
                        <div class="rpt-meta-item">
                            <i class="fas fa-venus-mars"></i>
                            <span>{{ $filters['gender'] }}</span>
                        </div>
                    @endif
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
                margin-bottom: 1.25rem;
                padding-bottom: 1.25rem;
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

            .rpt-hero-meta {
                padding-top: 0.25rem;
            }

            .rpt-meta-items {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem 0.75rem;
            }

            .rpt-meta-item {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                color: rgba(255, 255, 255, 0.65);
                font-size: 0.78rem;
                padding: 0.25rem 0.75rem;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 99px;
                border: 1px solid rgba(255, 255, 255, 0.06);
            }

            .rpt-meta-item i {
                font-size: 0.7rem;
                color: rgba(255, 255, 255, 0.4);
            }

            .rpt-meta-highlight {
                background: rgba(102, 126, 234, 0.15);
                border-color: rgba(102, 126, 234, 0.2);
                color: rgba(255, 255, 255, 0.9);
            }

            .rpt-meta-highlight i {
                color: #818cf8;
            }

            .rpt-meta-divider {
                width: 1px;
                height: 20px;
                background: rgba(255, 255, 255, 0.08);
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

                .rpt-meta-items {
                    gap: 0.4rem;
                }

                .rpt-meta-divider {
                    display: none;
                }

                .rpt-meta-item {
                    font-size: 0.7rem;
                    padding: 0.2rem 0.6rem;
                }
            }
        </style>

        <div class="mb-3 no-print">
            @include('Examination.reports.partials.nav', ['active' => 'grade-analysis'])
        </div>

        {{-- ── Filter bar ─────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('examination.reports.grade-analysis', $exam->id) }}"
            class="rpt-filter-bar no-print">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="d-block">Class</label>
                    <select name="class_id" class="form-select w-100" onchange="this.form.submit()">
                        <option value="">All Classes</option>
                        @foreach ($classOptions as $opt)
                            <option value="{{ $opt->class_id }}" {{ (string) $selectedClassId === (string) $opt->class_id ? 'selected' : '' }}>
                                {{ $opt->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="d-block">Stream</label>
                    <select name="stream_id" class="form-select w-100" onchange="this.form.submit()" {{ $selectedClassId ? '' : 'disabled' }}>
                        <option value="">All Streams</option>
                        @foreach ($streamOptions as $s)
                            <option value="{{ $s->stream_id }}" {{ (string) $selectedStreamId === (string) $s->stream_id ? 'selected' : '' }}>
                                {{ $s->stream_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="d-block">Subject</label>
                    <select name="subject_select" class="form-select w-100" data-subject-router>
                        <option value="">All Subjects</option>
                        @foreach ($subjectOptions as $opt)
                            <option value="{{ $opt->report_key }}" data-subject-id="{{ $opt->subject_id }}"
                                data-custom-subject-id="{{ $opt->custom_subject_id }}" {{ (($filters['subject_id'] ?? null) == $opt->subject_id && !is_null($opt->subject_id)) || (($filters['custom_subject_id'] ?? null) == $opt->custom_subject_id && is_null($opt->subject_id) && ($filters['custom_subject_id'] ?? null)) ? 'selected' : '' }}>
                                {{ $opt->report_name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="subject_id" id="subject_id_field" value="{{ $filters['subject_id'] ?? '' }}">
                    <input type="hidden" name="custom_subject_id" id="custom_subject_id_field"
                        value="{{ $filters['custom_subject_id'] ?? '' }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="d-block">Gender</label>
                    <select name="gender" class="form-select w-100" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach (['Male', 'Female'] as $g)
                            <option value="{{ $g }}" {{ ($filters['gender'] ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <button type="submit" class="rpt-btn rpt-btn-primary w-100"><i class="fas fa-filter"></i> Apply</button>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const select = document.querySelector('[data-subject-router]');
                if (!select) return;
                select.addEventListener('change', function () {
                    const opt = select.options[select.selectedIndex];
                    document.getElementById('subject_id_field').value = opt.getAttribute('data-subject-id') || '';
                    document.getElementById('custom_subject_id_field').value = opt.getAttribute('data-custom-subject-id') || '';
                    select.form.submit(); // submit only after the hidden fields are correct
                });
            });
        </script>

        <div class="d-flex justify-content-end gap-2 mb-3 no-print">
            <button onclick="window.print()" class="rpt-btn rpt-btn-outline"><i class="fas fa-print"></i> Print</button>
        </div>

        {{-- ── Stat cards ─────────────────────────────────────────────────── --}}
        <div class="rpt-stat-grid">
            <div class="rpt-stat-card">
                <div class="rpt-stat-label">Students in Scope</div>
                <div class="rpt-stat-value">{{ $studentsInScope }}</div>
            </div>
            <div class="rpt-stat-card">
                <div class="rpt-stat-label">Marks Entered</div>
                <div class="rpt-stat-value">{{ $entriesInScope }}</div>
            </div>
            <div class="rpt-stat-card success">
                <div class="rpt-stat-label">Overall Average</div>
                <div class="rpt-stat-value">{{ $overallAverage ?? '—' }}{{ $overallAverage !== null ? '%' : '' }}</div>
            </div>
            <div class="rpt-stat-card {{ $passRate !== null && $passRate < 50 ? 'danger' : 'success' }}">
                <div class="rpt-stat-label">Pass Rate</div>
                <div class="rpt-stat-value">{{ $passRate ?? 'N/A' }}{{ $passRate !== null ? '%' : '' }}</div>
            </div>
        </div>

        <div class="row g-3">
            {{-- ── Grade distribution ───────────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="rpt-panel h-100">
                    <div class="rpt-panel-title"><i class="fas fa-chart-pie"></i> Grade Distribution</div>
                    @if ($gradeDistribution->where('count', '>', 0)->isEmpty())
                        <div class="rpt-empty-state py-4"><i class="fas fa-inbox"></i>
                            <p class="mb-0">No marks entered yet for this scope.</p>
                        </div>
                    @else
                        @foreach ($gradeDistribution as $g)
                            <div class="rpt-bar-row">
                                <div class="rpt-bar-label">{{ $g->grade }} <span class="text-muted">({{ $g->count }})</span></div>
                                <div class="rpt-bar-track">
                                    <div class="rpt-bar-fill" style="width: {{ $g->percentage }}%"></div>
                                </div>
                                <div class="rpt-bar-value">{{ $g->percentage }}%</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- ── Gender comparison ────────────────────────────────────── --}}
            <div class="col-lg-3">
                <div class="rpt-panel h-100">
                    <div class="rpt-panel-title"><i class="fas fa-venus-mars"></i> Gender Comparison</div>
                    @if ($genderComparison->isEmpty())
                        <div class="rpt-empty-state py-4"><i class="fas fa-inbox"></i>
                            <p class="mb-0">No data yet.</p>
                        </div>
                    @else
                        @foreach ($genderComparison as $g)
                            <div class="rpt-bar-row">
                                <div class="rpt-bar-label">{{ $g->gender }} <span class="text-muted">({{ $g->count }})</span></div>
                                <div class="rpt-bar-track">
                                    <div class="rpt-bar-fill" style="width: {{ $g->average ?? 0 }}%"></div>
                                </div>
                                <div class="rpt-bar-value">{{ $g->average ?? '—' }}%</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- ── Top performers ───────────────────────────────────────── --}}
            <div class="col-lg-4">
                <div class="rpt-panel h-100">
                    <div class="rpt-panel-title"><i class="fas fa-trophy"></i> Top 10 Performers</div>
                    @if ($topPerformers->isEmpty())
                        <div class="rpt-empty-state py-4"><i class="fas fa-inbox"></i>
                            <p class="mb-0">No data yet.</p>
                        </div>
                    @else
                        <ol class="ps-3 mb-0" style="font-size:.82rem;">
                            @foreach ($topPerformers as $i => $p)
                                <li class="mb-2 d-flex justify-content-between">
                                    <span>{{ $p->student->firstname }} {{ $p->student->lastname }}</span>
                                    <span>
                                        <strong>{{ $p->average }}%</strong>
                                        <span class="rpt-badge rpt-badge-good ms-1">{{ $p->grade }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Subject averages (weakest first) ─────────────────────────── --}}
        <div class="rpt-panel  mt-4">
            <div class="rpt-panel-title"><i class="fas fa-layer-group"></i> Subject Averages <span
                    class="text-muted fw-normal">(weakest first)</span></div>
            @if ($subjectAverages->isEmpty())
                <div class="rpt-empty-state py-4"><i class="fas fa-inbox"></i>
                    <p class="mb-0">No marks entered yet for this scope.</p>
                </div>
            @else
                @foreach ($subjectAverages as $s)

                    <div class="rpt-bar-row">
                        <div class="rpt-bar-label" title="{{ $s->subject_name }}">{{ $s->subject_name }}</div>
                        <div class="rpt-bar-track">
                            <div class="rpt-bar-fill" style="width: {{ $s->average }}%"></div>
                        </div>
                        <div class="rpt-bar-value">{{ $s->average }}%</div>
                        <div style="width:150px;flex-shrink:0;font-size:.72rem;color:#8b8fa3;text-align:right;">
                            {{ $s->entries }} entries · H {{ $s->highest }}% · L {{ $s->lowest }}%
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    </div>
    </div>
@endsection