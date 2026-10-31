<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    @php
        $pillFor = ['complete' => 'green', 'marks_pending' => 'amber', 'in_progress' => 'amber', 'not_started' => 'grey', 'overdue' => 'amber'];
        $query = array_filter(['academic_year' => $yearFilter, 'term' => $termFilter, 'class_id' => $classFilter, 'teacher_id' => $teacherFilter, 'status' => $statusFilter]);
    @endphp

    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-bullseye me-1"></i> Administrator view</span>
        <div class="hero-title">Assessment Targets — Teacher Progress</div>
        <div class="hero-subtitle">
            What each teacher was expected to submit this term against what they have actually created and marked,
            so you can see at a glance who is on track and how many assessments are still outstanding.
        </div>
        <div class="hero-links mt-2">
            @if($canManage)
                <a href="{{ route('open-assessments.expectations', ['academic_year' => $yearFilter, 'term' => $termFilter !== 'all' ? $termFilter : null]) }}"
                    class="btn btn-primary">
                    <i class="fas fa-sliders me-1"></i>
                    Set the Targets
                </a>
            @endif

            <a href="{{ route('open-assessments.overview') }}" class="btn btn-primary">
                <i class="fas fa-user-check me-1"></i>
                Assessments by Teacher
            </a>

            @if($groups->isNotEmpty())
                <a href="{{ route('open-assessments.compliance.export', $query) }}" class="btn btn-success">
                    <i class="fas fa-file-csv me-1"></i>
                    Download CSV
                </a>
            @endif
        </div>

        <style>
            .hero-links {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
            }

            .hero-links a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 10px 18px;
                min-height: 42px;
                border-radius: 7px;
                font-size: 13px;
                font-weight: 600;
                text-decoration: none !important;
                transition: all 0.2s ease;
            }

            .hero-links .btn-primary {
                background: #2C29CA;
                border: 1px solid #2C29CA;
                color: #fff;
            }

            .hero-links .btn-outline-primary {
                background: #fff;
                border: 1px solid #2C29CA;
                color: #2C29CA;
            }

            .hero-links .btn-success {
                background: #198754;
                border: 1px solid #198754;
                color: #fff;
            }

            .hero-links a:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
                background: #2C29CA;
            }

            .hero-links .btn-outline-primary:hover {
                background: #2C29CA;
            }

            @media (max-width: 576px) {
                .hero-links a {
                    flex: 1 1 100%;
                }
            }
        </style>
    </div>

    <div class="oa-card">
        <div class="oa-card-body">
            <form method="GET" action="{{ route('open-assessments.compliance') }}" class="oa-filters">
                <div>
                    <label class="oa-label">Year</label>
                    <select name="academic_year" class="oa-input" onchange="this.form.submit()">
                        @foreach($yearOptions as $y)
                            <option value="{{ $y }}" {{ (string) $yearFilter === (string) $y ? 'selected' : '' }}>{{ $y }}
                        </option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Term</label>
                    <select name="term" class="oa-input" onchange="this.form.submit()">
                        <option value="all" {{ $termFilter === 'all' ? 'selected' : '' }}>All terms</option>
                        @foreach($termOptions as $t)
                            <option value="{{ $t }}" {{ $termFilter === $t ? 'selected' : '' }}>{{ \App\Support\Term::label($t) }}
                        </option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Class</label>
                    <select name="class_id" class="oa-input" onchange="this.form.submit()">
                        <option value="">All classes</option>
                        @foreach($classOptions as $id => $name)
                            <option value="{{ $id }}" {{ (string) $classFilter === (string) $id ? 'selected' : '' }}>{{ $name }}
                        </option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Teacher</label>
                    <select name="teacher_id" class="oa-input" onchange="this.form.submit()">
                        <option value="">All teachers</option>
                        @foreach($teacherOptions as $id => $name)
                            <option value="{{ $id }}" {{ (string) $teacherFilter === (string) $id ? 'selected' : '' }}>{{ $name }}
                        </option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Show</label>
                    <select name="status" class="oa-input" onchange="this.form.submit()">
                        <option value="">Everything</option>
                        @foreach($statusOptions as $k => $label)
                        <option value="{{ $k }}" {{ $statusFilter === $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                    </select>
                </div>
                @if($classFilter || $teacherFilter || $statusFilter)
                    <div style="min-width:auto;"><a
                            href="{{ route('open-assessments.compliance', ['academic_year' => $yearFilter, 'term' => $termFilter]) }}"
                            class="oa-btn light">Clear filters</a></div>
                @endif
            </form>
        </div>
    </div>

    <div class="oa-stats">
        <div class="oa-stat">
            <div class="v">{{ $summary['percent'] }}%</div>
            <div class="l">Overall created</div>
        </div>
        <div class="oa-stat">
            <div class="v" style="color:#157347;">{{ $summary['met'] }}<span style="font-size:1rem;color:#7c79b3;"> /
                    {{ $summary['targets'] }}</span></div>
            <div class="l">Targets fully met</div>
        </div>
        <div class="oa-stat">
            <div class="v" style="color:#a06a00;">{{ $summary['remaining'] }}</div>
            <div class="l">Assessments still to create</div>
        </div>
        <div class="oa-stat">
            <div class="v" style="color:#dc3545;">{{ $summary['overdue'] }}</div>
            <div class="l">Overdue targets</div>
        </div>
        <div class="oa-stat">
            <div class="v">{{ $summary['marks_pending'] }}</div>
            <div class="l">Created, marks pending</div>
        </div>
    </div>

    @forelse($groups as $g)
        @php $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z ]/', '', $g->name) ?: '?', 0, 1) . (explode(' ', trim($g->name))[1][0] ?? '')); @endphp
        <details class="oa-teacher" {{ $teacherFilter || $statusFilter || $loop->count <= 3 ? 'open' : '' }}>
            <summary>
                <span class="t-avatar">{{ $g->teacher_id > 0 ? $initials : '?' }}</span>
                <div>
                    <div class="t-name">{{ $g->name }}</div>
                    <div class="oa-sub">{{ $g->tally['met'] }} of {{ $g->tally['targets'] }} target(s) met ·
                        {{ $g->tally['remaining'] }} assessment(s) still to create</div>
                </div>
                @if($g->tally['overdue'] > 0)<span class="oa-pill amber ms-2">{{ $g->tally['overdue'] }} overdue</span>@endif
                <div style="width:130px;" class="ms-auto d-none d-md-block">
                    <div class="oa-bar"><span style="width: {{ $g->tally['percent'] }}%;"></span></div>
                </div>
                <span class="oa-sub">{{ $g->tally['percent'] }}%</span>
                <i class="fas fa-chevron-right chev" style="margin-left:.5rem;"></i>
            </summary>
            <div class="oa-subject">
                <div class="table-responsive">
                    <table class="table oa-table">
                        <thead>
                            <tr>
                                <th>Class · Subject</th>
                                <th>Term</th>
                                <th>Assessment type</th>
                                <th style="width:170px;">Created</th>
                                <th>Fully marked</th>
                                <th>Still to create</th>
                                <th>Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($g->subjects as $s)
                                @foreach($s->lines as $l)
                                    <tr>
                                        @if($loop->first)
                                            <td class="fw-bold" rowspan="{{ $s->lines->count() }}">{{ $s->subject }}
                                                <div class="oa-sub">{{ $s->class_name }}@if($s->stream) · {{ $s->stream }}@endif</div>
                                            </td>
                                        @endif
                                        <td>{{ \App\Support\Term::label($l->term) }}</td>
                                        <td>{{ $l->label }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="oa-bar flex-grow-1"><span style="width: {{ $l->percent }}%;"></span></div>
                                                <span class="oa-sub">{{ $l->created }}/{{ $l->required }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $l->marked }}/{{ $l->created }}</td>
                                        <td>{{ $l->remaining }}</td>
                                        <td>{{ $l->due_text ?? '—' }}</td>
                                        <td><span class="oa-pill {{ $pillFor[$l->status] ?? 'grey' }}">{{ $l->status_label }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @empty
        <div class="oa-card">
            <div class="oa-empty"><i class="fas fa-bullseye"></i>
                @if(!$hasTargets)
                    No targets have been set for this period yet.
                    @if($canManage)
                        <div class="mt-2"><a class="oa-btn"
                                href="{{ route('open-assessments.expectations', ['academic_year' => $yearFilter, 'term' => $termFilter !== 'all' ? $termFilter : null]) }}">Set
                    the targets</a></div>@endif
                @else
                    Nothing matches these filters.
                @endif
            </div>
        </div>
    @endforelse

    </div>
    </div>
    </div>
    </div>
@endsection