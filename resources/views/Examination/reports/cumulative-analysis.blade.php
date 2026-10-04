<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.reports.partials.styles')
    <style>
        .rpt-hero-card {
            background: linear-gradient(135deg, #000000 0%, #070189 100%);
            border-radius: 20px;
            padding: 1.75rem 2.25rem;
            margin-bottom: 2rem;
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
        }

        .rpt-hero-action:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            text-decoration: none;
        }

        .cml-exam-picker {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.6rem;
        }

        .cml-exam-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--rpt-brand-ultra);
            border: 1px solid var(--rpt-brand-pale);
            border-radius: var(--rpt-radius-sm);
            padding: 0.5rem 0.7rem;
            font-size: 0.78rem;
            cursor: pointer;
        }

        .cml-exam-chip input {
            margin: 0;
        }

        .cml-exam-chip.unavailable {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .cml-term-group {
            margin-bottom: 0.75rem;
        }

        .cml-term-group-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #8b8fa3;
            margin-bottom: 0.4rem;
        }

        .cml-trend-up { color: #0d9668; }
        .cml-trend-down { color: #dc2626; }
        .cml-trend-flat { color: #8b8fa3; }

        .cml-tabs {
            display: inline-flex;
            background: #ede9ff;
            border-radius: 0.75rem;
            padding: 0.25rem;
            gap: 0.25rem;
        }

        .cml-tab-note {
            font-size: 0.72rem;
            color: #8b8fa3;
            margin-left: 0.25rem;
        }

        /* ─── Mobile: stack hero, filters, export buttons, tables ─────── */
        @media (max-width: 768px) {

            /* Hero card tighter */
            .rpt-hero-card {
                padding: 1.25rem 1.15rem;
                border-radius: 16px;
                margin-bottom: 1.25rem;
            }

            /* Hero main stacks */
            .rpt-hero-main {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
            }

            .rpt-hero-left {
                align-items: flex-start;
                gap: 0.85rem;
            }

            .rpt-hero-icon-wrapper {
                width: 46px;
                height: 46px;
                font-size: 1.2rem;
                border-radius: 12px;
            }

            .rpt-hero-info h4 {
                font-size: 1.15rem;
            }

            .rpt-hero-info p {
                font-size: 0.82rem;
                line-height: 1.45;
            }

            /* Back button full width */
            .rpt-hero-action {
                width: 100%;
                justify-content: center;
                padding: 0.7rem 1rem;
                font-size: 0.82rem;
            }

            /* Filter bar tighter padding */
            .rpt-filter-bar {
                padding: 1rem !important;
                border-radius: 12px;
            }

            .rpt-filter-bar label {
                font-size: 0.72rem;
            }

            /* Filter grid columns stack into 1 per row on mobile */
            .rpt-filter-bar .row > [class*="col-"] {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .rpt-filter-bar .form-select,
            .rpt-filter-bar .form-control {
                font-size: 0.82rem;
                padding: 0.55rem 0.7rem;
            }

            /* Term group labels tighter */
            .cml-term-group-label {
                font-size: 0.65rem;
            }

            /* Exam chips: 2 per row on mobile */
            .cml-exam-picker {
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
            }

            .cml-exam-chip {
                font-size: 0.72rem;
                padding: 0.45rem 0.55rem;
                gap: 0.4rem;
            }

            /* Refresh button full-width */
            .rpt-filter-bar .rpt-btn {
                width: 100%;
                justify-content: center;
                padding: 0.65rem 1rem;
                font-size: 0.82rem;
            }

            /* Export buttons: stack full-width */
            .no-print.d-flex.justify-content-end {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.5rem !important;
            }

            .no-print.d-flex.justify-content-end .rpt-btn {
                width: 100%;
                justify-content: center;
                padding: 0.65rem 1rem;
                font-size: 0.8rem;
                margin-left: 0 !important;
                margin-bottom: 0 !important;
            }

            /* Stat grid: 2 per row on mobile */
            .rpt-stat-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.6rem;
            }

            .rpt-stat-card {
                padding: 0.85rem 0.75rem;
            }

            .rpt-stat-label {
                font-size: 0.68rem;
            }

            .rpt-stat-value {
                font-size: 1.25rem;
            }

            .rpt-stat-sub {
                font-size: 0.65rem;
            }

            /* Panel tighter */
            .rpt-panel {
                padding: 1rem !important;
                border-radius: 12px;
            }

            .rpt-panel-title {
                font-size: 0.85rem;
                padding-bottom: 0.6rem;
                margin-bottom: 0.85rem;
            }

            /* Tables: horizontal scroll */
            .rpt-table-wrap {
                -webkit-overflow-scrolling: touch;
                border-radius: 10px;
            }

            .rpt-table-wrap .rpt-table {
                min-width: 640px;
            }

            .rpt-table th,
            .rpt-table td {
                padding: 0.55rem 0.65rem;
                font-size: 0.78rem;
            }

            .rpt-table th {
                font-size: 0.65rem;
            }

            /* Name column keeps its place */
            .rpt-table .rpt-name-col {
                min-width: 140px;
            }

            /* Subject deep-dive select full-width */
            .rpt-panel form select[name="subject_key"] {
                max-width: 100% !important;
                width: 100%;
            }
        }

        /* ─── Very small phones (≤ 420px): extra tightening ───────────── */
        @media (max-width: 420px) {
            .rpt-hero-card {
                padding: 1rem 0.95rem;
            }

            .rpt-hero-info h4 {
                font-size: 1.02rem;
            }

            .rpt-hero-info p {
                font-size: 0.76rem;
            }

            /* Exam chips: 1 per row on very small phones */
            .cml-exam-picker {
                grid-template-columns: 1fr;
            }

            /* Stat grid: single column on very small phones */
            .rpt-stat-grid {
                grid-template-columns: 1fr;
            }

            .rpt-panel {
                padding: 0.85rem !important;
            }

            .rpt-table th,
            .rpt-table td {
                padding: 0.5rem 0.55rem;
                font-size: 0.74rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="side-app">

        <div class="rpt-hero-card">
            <div class="rpt-hero-main">
                <div class="rpt-hero-left">
                    <div class="rpt-hero-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="rpt-hero-info">
                        <h4>Cumulative Performance Analysis</h4>
                        <p>Track a class's average performance across several examinations — BOT, Mid-Term and End-of-Term, Term I to Term III.</p>
                    </div>
                </div>
                <a href="{{ route('examination.reports.index') }}" class="rpt-hero-action no-print">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Reports</span>
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- ── Picker: academic year / class / stream / exams to include ──── --}}
        <form method="GET" action="{{ route('examination.reports.cumulative-analysis') }}" class="rpt-filter-bar no-print" id="cumulativeForm">
            <div class="row g-3 align-items-end mb-2">
                <div class="col-6 col-md-3">
                    <label class="d-block">Academic Year</label>
                    <select name="academic_year" class="form-select w-100" onchange="this.form.submit()">
                        @foreach ($academicYears as $year)
                            <option value="{{ $year }}" {{ (string) $year === (string) $academicYear ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="d-block">Class</label>
                    <select name="class_id" class="form-select w-100" onchange="this.form.submit()">
                        @foreach ($classOptions as $opt)
                            <option value="{{ $opt->class_id }}" {{ (string) $opt->class_id === (string) $selectedClassId ? 'selected' : '' }}>
                                {{ $opt->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="d-block">Stream</label>
                    <select name="stream_id" class="form-select w-100" onchange="this.form.submit()">
                        <option value="">All Streams</option>
                        @foreach ($streamOptions as $s)
                            <option value="{{ $s->stream_id }}" {{ (string) $selectedStreamId === (string) $s->stream_id ? 'selected' : '' }}>
                                {{ $s->stream_id }}
                            </option>
                        @endforeach
                    </select>
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
                <div class="col-12 col-md-2">
                    <label class="d-block">Search Student</label>
                    <input type="text" name="search" class="form-control w-100" placeholder="Name or Adm. No."
                        value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>

            <hr>

            <label class="d-block mb-2">
                Examinations to Include
                <span class="cml-tab-note">(tick any combination — defaults to every {{ $academicYear }} sitting this class has)</span>
            </label>

            @if ($availableExams->isEmpty())
                <div class="text-muted" style="font-size:.85rem;">
                    This class has no examinations configured for {{ $academicYear }} yet.
                </div>
            @else
                @foreach ($availableExams->groupBy('term') as $term => $examsInTerm)
                    <div class="cml-term-group">
                        <div class="cml-term-group-label">{{ \App\Support\Term::label($term) }}</div>
                        <div class="cml-exam-picker">
                            @foreach ($examsInTerm as $exam)
                                <label class="cml-exam-chip">
                                    <input type="checkbox" name="exam_ids[]" value="{{ $exam->id }}"
                                        {{ in_array($exam->id, $selectedExamIds) ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <span>
                                        {{ str_replace('-', ' ', $exam->exam_type) }}
                                        <div class="text-muted" style="font-size:.68rem;">{{ $exam->exam_code }}</div>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="rpt-btn rpt-btn-primary"><i class="fas fa-sync"></i> Refresh</button>
            </div>

            {{-- carry the currently-selected subject (deep-dive tab) through every re-submit of this form --}}
            <input type="hidden" name="subject_key" value="{{ $selectedSubjectKey }}">
        </form>

       <div class="d-flex justify-content-end mb-3 no-print">
    <button onclick="window.print()" class="rpt-btn rpt-btn-outline">
        <i class="fas fa-print"></i> Print
    </button>
    <a href="{{ route('examination.reports.cumulative-analysis.pdf', request()->query()) }}"
        class="rpt-btn rpt-btn-outline ml-2">
        <i class="fas fa-file-pdf"></i> Export PDF
    </a>
    <a href="{{ route('examination.reports.cumulative-analysis.excel', request()->query()) }}"
        class="rpt-btn rpt-btn-outline ml-2">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
</div>

        {{-- ── Stat cards ─────────────────────────────────────────────────── --}}
        <div class="rpt-stat-grid">
            <div class="rpt-stat-card">
                <div class="rpt-stat-label">Exams Included</div>
                <div class="rpt-stat-value">{{ $selectedExams->count() }}</div>
                <div class="rpt-stat-sub">of {{ $availableExams->count() }} available in {{ $academicYear }}</div>
            </div>
            <div class="rpt-stat-card">
                <div class="rpt-stat-label">Students</div>
                <div class="rpt-stat-value">{{ $report->count() }}</div>
            </div>
            <div class="rpt-stat-card">
                <div class="rpt-stat-label">Subjects</div>
                <div class="rpt-stat-value">{{ $subjects->count() }}</div>
            </div>
            <div class="rpt-stat-card success">
                <div class="rpt-stat-label">Class Cumulative Average</div>
                <div class="rpt-stat-value">{{ $classCumulativeAverage !== null ? $classCumulativeAverage . '%' : '—' }}</div>
                <div class="rpt-stat-sub">across {{ $classTotal }} ranked student(s)</div>
            </div>
        </div>

        {{-- ── Cumulative Overview matrix ─────────────────────────────────── --}}
        <div class="rpt-panel">
            <div class="rpt-panel-title"><i class="fas fa-table-cells"></i> Cumulative Overview — {{ $className }} ({{ $streamLabel }})</div>

            @if ($selectedExams->isEmpty())
                <div class="rpt-empty-state">
                    <i class="fas fa-calendar-xmark"></i>
                    <h6>Tick at least one examination above to generate the report</h6>
                </div>
            @elseif ($report->isEmpty())
                <div class="rpt-empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h6>No students match these filters</h6>
                </div>
            @elseif ($subjects->isEmpty())
                <div class="rpt-empty-state">
                    <i class="fas fa-book"></i>
                    <h6>No subjects are configured for this class/stream</h6>
                </div>
            @else
                <div class="mb-2" style="font-size:.78rem; color:#6c7080;">
                    Included: {{ $selectedExams->map(fn($e) => str_replace('-', ' ', $e->exam_type) . ' (' . $e->term . ')')->implode(' · ') }}
                </div>
                <div class="rpt-table-wrap">
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th class="rpt-name-col">Student</th>
                                <th>Gender</th>
                                @foreach ($subjects as $subj)
                                    <th title="{{ $subj->report_name }}">{{ Str::limit($subj->report_name, 10, '') }}</th>
                                @endforeach
                                <th>Cumulative Avg %</th>
                                <th>Grade</th>
                                <th>Rank</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report as $row)
                                <tr>
                                    <td class="rpt-name-col">
                                        {{ $row->student->firstname }} {{ $row->student->lastname }}
                                        <div class="text-muted" style="font-size:.65rem;">{{ $row->student->admission_number }}</div>
                                    </td>
                                    <td>{{ $row->student->gender ?? '—' }}</td>
                                    @foreach ($subjects as $subj)
                                        @php $cell = $row->subjectAverages[$subj->report_key] ?? null; @endphp
                                        <td>
                                            @if ($cell && $cell->average !== null)
                                                @php $cellColor = $cell->average >= 80 ? '#0d9668' : ($cell->average >= 50 ? '#b45309' : '#dc2626'); @endphp
                                                <span style="font-weight:600; color:{{ $cellColor }};">{{ $cell->average }}%</span>
                                                <div class="text-muted" style="font-size:.62rem;">{{ $cell->grade }} · {{ $cell->entries }} sat</div>
                                            @else
                                                <span class="rpt-cell-empty">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td><strong>{{ $row->cumulativeAverage !== null ? $row->cumulativeAverage . '%' : '—' }}</strong></td>
                                    <td>
                                        @php
                                            $badge = $row->cumulativeAverage === null ? 'rpt-badge-neutral' : ($row->cumulativeAverage >= 80 ? 'rpt-badge-good' : ($row->cumulativeAverage >= 50 ? 'rpt-badge-mid' : 'rpt-badge-bad'));
                                        @endphp
                                        <span class="rpt-badge {{ $badge }}">{{ $row->grade }}</span>
                                    </td>
                                    <td>{{ $row->rank ?? '—' }}</td>
                                    <td>
                                        @if ($row->trend === 'up')
                                            <i class="fas fa-arrow-trend-up cml-trend-up" title="Improving"></i>
                                        @elseif ($row->trend === 'down')
                                            <i class="fas fa-arrow-trend-down cml-trend-down" title="Declining"></i>
                                        @elseif ($row->trend === 'flat')
                                            <i class="fas fa-minus cml-trend-flat" title="Steady"></i>
                                        @else
                                            <span class="rpt-cell-empty">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--rpt-brand-ultra); font-weight:700;">
                                <td class="rpt-name-col">Cumulative Subject Average</td>
                                <td></td>
                                @foreach ($subjects as $subj)
                                    @php $avg = $subjectCumulativeAverages[$subj->report_key] ?? null; @endphp
                                    <td>{{ $avg !== null ? $avg . '%' : '—' }}</td>
                                @endforeach
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        {{-- ── Subject deep-dive: marks per exam, before the average ───────── --}}
        @if ($selectedExams->isNotEmpty() && $subjects->isNotEmpty())
            <div class="rpt-panel">
                <div class="rpt-panel-title"><i class="fas fa-book"></i> Subject Deep-Dive</div>

                <form method="GET" action="{{ route('examination.reports.cumulative-analysis') }}" class="mb-3 no-print">
                    @foreach (request()->except(['subject_key', 'exam_ids']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    @foreach ($selectedExamIds as $eid)
                        <input type="hidden" name="exam_ids[]" value="{{ $eid }}">
                    @endforeach
                    <label class="d-block mb-1" style="font-size:.7rem; font-weight:700; text-transform:uppercase; color:#6c7080;">Subject</label>
                    <select name="subject_key" class="form-control" style="max-width:320px;" onchange="this.form.submit()">
                        @foreach ($subjectOptions as $opt)
                            <option value="{{ $opt->report_key }}" {{ $selectedSubjectKey === $opt->report_key ? 'selected' : '' }}>
                                {{ $opt->report_name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                @if ($subjectDetail)
                    <div class="rpt-stat-grid mb-3">
                        <div class="rpt-stat-card">
                            <div class="rpt-stat-label">Entered</div>
                            <div class="rpt-stat-value">{{ $subjectDetail['entered_count'] }}/{{ $subjectDetail['total_count'] }}</div>
                        </div>
                        <div class="rpt-stat-card success">
                            <div class="rpt-stat-label">Subject Average</div>
                            <div class="rpt-stat-value">{{ $subjectDetail['average'] !== null ? $subjectDetail['average'] . '%' : '—' }}</div>
                        </div>
                        <div class="rpt-stat-card">
                            <div class="rpt-stat-label">Highest</div>
                            <div class="rpt-stat-value">{{ $subjectDetail['highest'] !== null ? $subjectDetail['highest'] . '%' : '—' }}</div>
                        </div>
                        <div class="rpt-stat-card warning">
                            <div class="rpt-stat-label">Lowest</div>
                            <div class="rpt-stat-value">{{ $subjectDetail['lowest'] !== null ? $subjectDetail['lowest'] . '%' : '—' }}</div>
                        </div>
                    </div>

                    <div class="rpt-table-wrap">
                        <table class="rpt-table">
                            <thead>
                                <tr>
                                    <th class="rpt-name-col">Student</th>
                                    @foreach ($selectedExams as $exam)
                                        <th title="{{ $exam->exam_name }}">{{ str_replace('-', ' ', $exam->exam_type) }}<br>{{ \App\Support\Term::label($exam->term) }}</th>
                                    @endforeach
                                    <th>Average %</th>
                                    <th>Grade</th>
                                    <th>Rank</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subjectDetail['rows'] as $row)
                                    <tr>
                                        <td class="rpt-name-col">
                                            {{ $row->student->firstname }} {{ $row->student->lastname }}
                                            <div class="text-muted" style="font-size:.65rem;">{{ $row->student->admission_number }}</div>
                                        </td>
                                        @foreach ($selectedExams as $exam)
                                            @php $entry = $row->exams[$exam->id] ?? null; @endphp
                                            <td>
                                                @if ($entry)
                                                    {{ $entry->marks }}/{{ $entry->total }}
                                                    <div class="text-muted" style="font-size:.62rem;">{{ $entry->percentage }}% · {{ $entry->grade }}</div>
                                                @else
                                                    <span class="rpt-cell-empty">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td><strong>{{ $row->average !== null ? $row->average . '%' : '—' }}</strong></td>
                                        <td>
                                            @php
                                                $badge = $row->average === null ? 'rpt-badge-neutral' : ($row->average >= 80 ? 'rpt-badge-good' : ($row->average >= 50 ? 'rpt-badge-mid' : 'rpt-badge-bad'));
                                            @endphp
                                            <span class="rpt-badge {{ $badge }}">{{ $row->grade }}</span>
                                        </td>
                                        <td>{{ $row->rank ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
     </div>
      </div>
@endsection