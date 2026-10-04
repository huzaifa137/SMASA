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
                <i class="fas fa-book"></i>
            </div>
            <div class="rpt-hero-info">
                <h4>Subject Performance Report</h4>
                <p>{{ $exam->exam_name }} — a deep dive into a single subject.</p>
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
                <span>{{ \App\Support\Term::label($exam->term) }} • {{ $exam->academic_year }}</span>
            </div>
            <div class="rpt-meta-divider"></div>
            <div class="rpt-meta-item">
                <i class="fas fa-school"></i>
                <span>{{ $className }} — {{ $streamLabel }}</span>
            </div>
            @if ($selectedSubject)
                <div class="rpt-meta-divider"></div>
                <div class="rpt-meta-item rpt-meta-highlight">
                    <i class="fas fa-book-open"></i>
                    <span>{{ $selectedSubject->report_name }}</span>
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

/* ─── Mobile: stack hero, meta chips, filter, exports ─────────── */
@media (max-width: 768px) {

    /* Hero card tighter */
    .rpt-hero-card {
        padding: 1.15rem 1.15rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
    }

    /* Hero main stacks fully */
    .rpt-hero-main {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
    }

    /* Icon + info row tighter */
    .rpt-hero-left {
        gap: 0.85rem;
        align-items: flex-start;
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
        transform: none !important;
    }

    /* Meta chips: stack each on its own row, full width */
    .rpt-meta-items {
        flex-direction: column;
        align-items: stretch;
        gap: 0.4rem;
        width: 100%;
    }

    .rpt-meta-item {
        width: 100%;
        justify-content: flex-start;
        padding: 0.5rem 0.75rem;
        font-size: 0.72rem;
        border-radius: 10px;
    }

    /* Filter bar: force columns to 1 per row */
    .rpt-filter-bar {
        padding: 1rem !important;
        border-radius: 12px;
    }

    .rpt-filter-bar .row > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .rpt-filter-bar label {
        font-size: 0.72rem;
    }

    .rpt-filter-bar .form-select,
    .rpt-filter-bar .form-control {
        font-size: 0.82rem;
        padding: 0.55rem 0.7rem;
    }

    /* Apply button full width */
    .rpt-filter-bar .rpt-btn {
        width: 100%;
        justify-content: center;
        padding: 0.65rem 1rem;
        font-size: 0.82rem;
    }

    /* Export buttons row: stack full-width with minimal spacing */
    .no-print.d-flex.justify-content-end {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.35rem !important;
    }

    .no-print.d-flex.justify-content-end .rpt-btn {
        width: 100%;
        justify-content: center;
        padding: 0.65rem 1rem;
        font-size: 0.8rem;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Stat grid: 2 per row on phones */
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

    /* Grade distribution bars tighter */
    .rpt-bar-row {
        gap: 0.5rem;
    }

    .rpt-bar-label {
        font-size: 0.75rem;
        min-width: 70px;
    }

    .rpt-bar-value {
        font-size: 0.72rem;
        min-width: 40px;
    }

    /* Student results table: horizontal scroll */
    .rpt-table-wrap {
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
    }

    .rpt-table-wrap .rpt-table {
        min-width: 620px;
    }

    .rpt-table th,
    .rpt-table td {
        padding: 0.55rem 0.65rem;
        font-size: 0.78rem;
    }

    .rpt-table th {
        font-size: 0.65rem;
    }

    .rpt-table .rpt-name-col {
        min-width: 140px;
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

    /* Stat grid single column */
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

    /* Grade distribution bars: tighter */
    .rpt-bar-label {
        font-size: 0.7rem;
        min-width: 60px;
    }

    /* Export buttons: even tighter gap on very small phones */
    .no-print.d-flex.justify-content-end {
        gap: 0.25rem !important;
    }
}

/* ─── Mobile: breathing room between stacked report panels ──────── */
@media (max-width: 768px) {

    /* Row wrapping Grade Distribution + Student Results */
    .row.g-3 {
        row-gap: 1rem !important;
    }

    /* Fallback for browsers that don't honor row-gap on flex rows */
    .row.g-3 > [class*="col-"] {
        margin-bottom: 0.5rem;
    }

    /* Last column doesn't need the extra bottom margin */
    .row.g-3 > [class*="col-"]:last-child {
        margin-bottom: 0;
    }
}

/* ─── Very small phones: keep the gap but slightly tighter ──────── */
@media (max-width: 420px) {
    .row.g-3 {
        row-gap: 0.85rem !important;
    }

    .row.g-3 > [class*="col-"] {
        margin-bottom: 0.4rem;
    }
}
</style>

        <div class="mb-3 no-print">
            @include('Examination.reports.partials.nav', ['active' => 'subject-report'])
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- ── Filter bar ─────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('examination.reports.subject-report', $exam->id) }}" class="rpt-filter-bar no-print">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
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
                    <label class="d-block">Subject</label>
                    <select name="subject_key" class="form-select w-100" onchange="this.form.submit()">
                        @forelse ($subjectOptions as $opt)
                            <option value="{{ $opt->report_key }}" {{ $selectedSubjectKey === $opt->report_key ? 'selected' : '' }}>
                                {{ $opt->report_name }}
                            </option>
                        @empty
                            <option value="">No subjects configured</option>
                        @endforelse
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
                <div class="col-6 col-md-2">
                    <label class="d-block">Grade</label>
                    <select name="grade" class="form-select w-100" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach ($exam->resolvedGradingBands() as $band)
                            <option value="{{ $band->grade }}" {{ ($filters['grade'] ?? '') === $band->grade ? 'selected' : '' }}>{{ $band->grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="d-block">Search Student</label>
                    <input type="text" name="search" class="form-control w-100" placeholder="Name or LIN No."
                        value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-12 col-md-12 d-flex gap-2 justify-content-end">
                    <button type="submit" class="rpt-btn rpt-btn-primary mt-2"><i class="fas fa-filter"></i> Apply</button>
                </div>
            </div>
        </form>

        @if ($selectedSubject)
            @php
                $pdfQuery = array_merge(request()->query(), [
                    'subject_id' => $selectedSubject->subject_id,
                    'custom_subject_id' => $selectedSubject->custom_subject_id,
                ]);
            @endphp
            <div class="d-flex justify-content-end gap-2 mb-3 no-print">
                <button onclick="window.print()" class="rpt-btn rpt-btn-outline"><i class="fas fa-print"></i> Print</button>
                <a href="{{ route('examination.reports.subject-report.pdf', array_merge(['examId' => $exam->id], $pdfQuery)) }}"
                    class="rpt-btn rpt-btn-outline"><i class="fas fa-file-pdf"></i> Export PDF</a>
                <a href="{{ route('examination.reports.subject-report.excel', array_merge(['examId' => $exam->id], $pdfQuery)) }}"
                    class="rpt-btn rpt-btn-outline"><i class="fas fa-file-excel"></i> Export Excel</a>
            </div>
        @endif

        @if (!$selectedSubject)
            <div class="rpt-panel rpt-empty-state">
                <i class="fas fa-book"></i>
                <h6>No subjects configured for this class/stream yet</h6>
            </div>
        @else
            {{-- ── Stat cards ─────────────────────────────────────────────── --}}
            <div class="rpt-stat-grid">
                <div class="rpt-stat-card">
                    <div class="rpt-stat-label">Students</div>
                    <div class="rpt-stat-value">{{ $stats['total_students'] }}</div>
                    <div class="rpt-stat-sub">{{ $stats['pending_count'] }} pending marks</div>
                </div>
                <div class="rpt-stat-card success">
                    <div class="rpt-stat-label">Average</div>
                    <div class="rpt-stat-value">@whole($stats['average'] ?? null){{ $stats['average'] !== null ? '%' : '' }}</div>
                </div>
                <div class="rpt-stat-card">
                    <div class="rpt-stat-label">Highest / Lowest</div>
                    <div class="rpt-stat-value" style="font-size:1.1rem;">
                        @whole($stats['highest'] ?? null)% / @whole($stats['lowest'] ?? null)%
                    </div>
                </div>
                <div class="rpt-stat-card {{ $stats['pass_rate'] !== null && $stats['pass_rate'] < 50 ? 'danger' : 'success' }}">
                    <div class="rpt-stat-label">Pass Rate</div>
                    <div class="rpt-stat-value">{{ $stats['pass_rate'] !== null ? \App\Helpers\NumberHelper::whole($stats['pass_rate']) . '%' : 'N/A' }}</div>
                    <div class="rpt-stat-sub">
                        @if ($stats['pass_rate'] !== null)
                            {{ $stats['pass_count'] }}/{{ $stats['entered_count'] }} at or above {{ $stats['pass_mark'] }}%
                        @else
                            no pass mark set on this exam
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-3">
                {{-- ── Grade distribution ───────────────────────────────── --}}
                <div class="col-lg-4">
                    <div class="rpt-panel h-100">
                        <div class="rpt-panel-title"><i class="fas fa-chart-pie"></i> Grade Distribution</div>
                        @if ($gradeDistribution->isEmpty())
                            <div class="rpt-empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No marks entered yet.</p></div>
                        @else
                            @foreach ($gradeDistribution as $g)
                                <div class="rpt-bar-row">
                                    <div class="rpt-bar-label">{{ $g->grade }} <span class="text-muted">({{ $g->count }})</span></div>
                                    <div class="rpt-bar-track"><div class="rpt-bar-fill" style="width: {{ $g->percentage }}%"></div></div>
                                    <div class="rpt-bar-value">{{ $g->percentage }}%</div>
                                </div>
                            @endforeach
                        @endif
                        @if ($stats['teacher_name'])
                            <div class="mt-3 pt-3" style="border-top:1px solid var(--rpt-brand-pale); font-size:.8rem; color:#6c7080;">
                                <i class="fas fa-chalkboard-teacher me-1"></i> Subject teacher: <strong>{{ $stats['teacher_name'] }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Student list ─────────────────────────────────────── --}}
                <div class="col-lg-8">
                    <div class="rpt-panel">
                        <div class="rpt-panel-title"><i class="fas fa-list-ol"></i> Student Results</div>
                        @if ($rows->isEmpty())
                            <div class="rpt-empty-state">
                                <i class="fas fa-user-slash"></i>
                                <h6>No students match these filters</h6>
                            </div>
                        @else
                            <div class="rpt-table-wrap">
                                <table class="rpt-table">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th class="rpt-name-col">Student</th>
                                            <th>Gender</th>
                                            <th>Marks{{ ($stats['subject_out_of'] ?? null) ? ' (/' . \App\Helpers\NumberHelper::whole($stats['subject_out_of']) . ')' : '' }}</th>
                                            <th>%</th>
                                            <th>Grade</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $row)
                                            <tr>
                                                <td>{{ $row->rank ?? '—' }}</td>
                                                <td class="rpt-name-col">
                                                    {{ $row->student->firstname }} {{ $row->student->lastname }}
                                                    <div class="text-muted" style="font-size:.65rem;">{{ $row->student->admission_number }}</div>
                                                </td>
                                                <td>{{ $row->student->gender ?? '—' }}</td>
                                                @if ($row->entered)
                                                    <td>@whole($row->marks)</td>
                                                    <td><strong>@whole($row->percentage)%</strong></td>
                                                    <td>
                                                        @php
                                                            $badge = $row->percentage >= 80 ? 'rpt-badge-good' : ($row->percentage >= 50 ? 'rpt-badge-mid' : 'rpt-badge-bad');
                                                        @endphp
                                                        <span class="rpt-badge {{ $badge }}">{{ $row->grade }}</span>
                                                    </td>
                                                    <td style="white-space:normal;max-width:220px;">{{ $row->remark }}</td>
                                                @else
                                                    <td colspan="4" class="rpt-cell-empty">Marks not entered</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    </div>
                </div>
            </div>
@endsection