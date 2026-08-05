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
                <i class="fas fa-table-cells"></i>
            </div>
            <div class="rpt-hero-info">
                <h4>Class Performance Summary</h4>
                <p>{{ $exam->exam_name }} — every subject, every student, side by side.</p>
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
            <div class="rpt-meta-item rpt-meta-highlight">
                <i class="fas fa-school"></i>
                <span>{{ $className }} — {{ $streamLabel }}</span>
            </div>
            @if($filters['gender'] ?? null)
                <div class="rpt-meta-divider"></div>
                <div class="rpt-meta-item">
                    <i class="fas fa-venus-mars"></i>
                    <span>{{ $filters['gender'] }}</span>
                </div>
            @endif
            @if($filters['grade'] ?? null)
                <div class="rpt-meta-divider"></div>
                <div class="rpt-meta-item">
                    <i class="fas fa-star"></i>
                    <span>Grade: {{ $filters['grade'] }}</span>
                </div>
            @endif
            @if($filters['search'] ?? null)
                <div class="rpt-meta-divider"></div>
                <div class="rpt-meta-item">
                    <i class="fas fa-search"></i>
                    <span>"{{ $filters['search'] }}"</span>
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
            @include('Examination.reports.partials.nav', ['active' => 'class-summary'])
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- ── Filter bar ─────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('examination.reports.class-summary', $exam->id) }}" class="rpt-filter-bar no-print">
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
                        @foreach ($gradingScale as $band)
                            <option value="{{ $band->grade }}" {{ ($filters['grade'] ?? '') === $band->grade ? 'selected' : '' }}>{{ $band->grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-8 col-md-2">
                    <label class="d-block">Search Student</label>
                    <input type="text" name="search" class="form-control w-100" placeholder="Name or Adm No."
                        value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-4 col-md-2 d-flex gap-2">
                    <button type="submit" class="rpt-btn rpt-btn-primary flex-fill"><i class="fas fa-filter"></i> Apply</button>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-end gap-2 mb-3 no-print">
            <button onclick="window.print()" class="rpt-btn rpt-btn-outline"><i class="fas fa-print"></i> Print</button> &nbsp;
            <a href="{{ route('examination.reports.class-summary.pdf', array_merge(['examId' => $exam->id], request()->query())) }}"
                class="rpt-btn rpt-btn-outline"><i class="fas fa-file-pdf"></i> Export PDF</a>
        </div>

        {{-- ── Stat cards ─────────────────────────────────────────────────── --}}
        <div class="rpt-stat-grid">
            <div class="rpt-stat-card">
                <div class="rpt-stat-label">Students</div>
                <div class="rpt-stat-value">{{ $report->count() }}</div>
            </div>
            <div class="rpt-stat-card">
                <div class="rpt-stat-label">Subjects</div>
                <div class="rpt-stat-value">{{ $subjects->count() }}</div>
            </div>
            <div class="rpt-stat-card success">
                <div class="rpt-stat-label">Class Average</div>
                <div class="rpt-stat-value">{{ $classAverage }}%</div>
                <div class="rpt-stat-sub">across {{ $classTotal }} ranked student(s)</div>
            </div>
            <div class="rpt-stat-card {{ ($report->count() - $classTotal) > 0 ? 'warning' : 'success' }}">
                <div class="rpt-stat-label">Pending</div>
                <div class="rpt-stat-value">{{ $report->count() - $classTotal }}</div>
                <div class="rpt-stat-sub">student(s) with no marks yet</div>
            </div>
        </div>

        {{-- ── Matrix table ───────────────────────────────────────────────── --}}
        <div class="rpt-panel">
            <div class="rpt-panel-title"><i class="fas fa-table-cells"></i> Subject x Student Matrix</div>

            @if ($report->isEmpty())
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
                <div class="rpt-table-wrap">
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th class="rpt-name-col">Student</th>
                                <th>Gender</th>
                                @foreach ($subjects as $subj)
                                    <th title="{{ $subj->report_name }}">{{ Str::limit($subj->report_name, 10, '') }}</th>
                                @endforeach
                                <th>Total</th>
                                <th>Avg %</th>
                                <th>Grade</th>
                                <th>Rank</th>
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
                                        @php $cell = $row->cells[$subj->report_key] ?? null; @endphp
                                        <td>
                                            @if ($cell)
                                                {{ $cell->marks }}/{{ $cell->total }}
                                                <div style="font-size:.65rem;color:#8b8fa3;">{{ $cell->percentage }}% · {{ $cell->grade }}</div>
                                            @else
                                                <span class="rpt-cell-empty">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>{{ $row->total_obtained }}/{{ $row->total_max }}</td>
                                    <td><strong>{{ $row->average }}%</strong></td>
                                    <td>
                                        @php
                                            $badge = $row->average >= 80 ? 'rpt-badge-good' : ($row->average >= 50 ? 'rpt-badge-mid' : ($row->subjects_done > 0 ? 'rpt-badge-bad' : 'rpt-badge-neutral'));
                                        @endphp
                                        <span class="rpt-badge {{ $badge }}">{{ $row->grade }}</span>
                                    </td>
                                    <td>{{ $row->rank ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--rpt-brand-ultra); font-weight:700;">
                                <td class="rpt-name-col">Subject Average</td>
                                <td></td>
                                @foreach ($subjects as $subj)
                                    @php $avg = $subjectAverages[$subj->report_key] ?? null; @endphp
                                    <td>{{ $avg && $avg['average'] !== null ? $avg['average'] . '%' : '—' }}</td>
                                @endforeach
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
     </div>
    </div>
@endsection