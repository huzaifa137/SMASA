<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-user-check me-1"></i> Administrator view</span>
        <div class="hero-title">Assessments by Teacher</div>
        <div class="hero-subtitle">
            Every open assessment each teacher has created in their subjects, with how far marks entry has got.
            These are created by teachers on their own, with no examination. To decide which ones count on a report card, tick them on the O-Level Report Card form.
        </div>
        @if(\App\Helpers\PermissionHelper::canFeature('create_olevel_report_card'))
            <div class="hero-links mt-2">
                <a href="{{ route('olevel-report-cards.create') }}"><i class="fas fa-file-alt me-1"></i> Create an O-Level report card</a>
                <a href="{{ route('olevel-report-cards.index') }}"><i class="fas fa-list me-1"></i> Existing report cards</a>
            </div>
        @endif
    </div>

    <div class="oa-card">
        <div class="oa-card-body">
            <form method="GET" action="{{ route('open-assessments.overview') }}" class="oa-filters">
                <div>
                    <label class="oa-label">Year</label>
                    <select name="academic_year" class="oa-input" onchange="this.form.submit()">
                        <option value="">All years</option>
                        @foreach($yearOptions as $y)
                            <option value="{{ $y }}" {{ (string) $yearFilter === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Term</label>
                    <select name="term" class="oa-input" onchange="this.form.submit()">
                        <option value="">All terms</option>
                        @foreach($termOptions as $t)
                            <option value="{{ $t }}" {{ $termFilter === $t ? 'selected' : '' }}>{{ \App\Support\Term::label($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Class</label>
                    <select name="class_id" class="oa-input" onchange="this.form.submit()">
                        <option value="">All classes</option>
                        @foreach($classOptions as $id => $name)
                            <option value="{{ $id }}" {{ (string) $classFilter === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="oa-label">Teacher</label>
                    <select name="teacher_id" class="oa-input" onchange="this.form.submit()">
                        <option value="">All teachers</option>
                        @foreach($teacherOptions as $id => $name)
                            <option value="{{ $id }}" {{ (string) $teacherFilter === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($yearFilter || $termFilter || $classFilter || $teacherFilter)
                    <div style="min-width:auto;"><a href="{{ route('open-assessments.overview') }}" class="oa-btn light">Clear filters</a></div>
                @endif
            </form>
        </div>
    </div>

        <div class="oa-stats">
            <div class="oa-stat"><div class="v">{{ $summary['assessments'] }}</div><div class="l">Assessments created</div></div>
            <div class="oa-stat"><div class="v">{{ $summary['teachers'] }}</div><div class="l">Teachers who created some</div></div>
            <div class="oa-stat"><div class="v" style="color:#a06a00;">{{ $summary['subjects_without'] }}</div><div class="l">Subjects with none yet</div></div>
            <div class="oa-stat"><div class="v" style="color:#157347;">{{ $summary['on_cards'] }}</div><div class="l">Picked for a report card</div></div>
        </div>

        @forelse($groups as $g)
            @php
                $count = collect($g->subjects)->sum(fn($s) => $s->assessments->count());
                $initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z ]/', '', $g->name) ?: '?', 0, 1) . (explode(' ', trim($g->name))[1][0] ?? ''));
            @endphp
            <details class="oa-teacher" {{ $teacherFilter || $loop->count <= 3 ? 'open' : '' }}>
                <summary>
                    <span class="t-avatar">{{ $g->teacher_id > 0 ? $initials : '?' }}</span>
                    <div>
                        <div class="t-name">{{ $g->name }}</div>
                        <div class="oa-sub">{{ count($g->subjects) }} subject(s) · {{ $count }} assessment(s)</div>
                    </div>
                    <i class="fas fa-chevron-right chev"></i>
                </summary>

                @foreach($g->subjects as $s)
                    <div class="oa-subject">
                        <h6>
                            {{ $s->subject }}
                            <span class="oa-pill">{{ $s->class_name }}@if($s->stream) · {{ $s->stream }}@endif</span>
                            @if($s->assessments->isEmpty())<span class="oa-pill amber">No assessments yet</span>@endif
                        </h6>

                        @if($s->assessments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table oa-table">
                                    <thead>
                                        <tr>
                                            <th>Assessment</th><th>Term</th><th>Out of</th><th style="width:170px;">Marks entered</th>
                                            <th>Avg</th><th>High / Low</th><th>On report card</th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($s->assessments as $a)
                                            @php $pct = ($a->expected ?? 0) > 0 ? min(100, round(($a->entered / $a->expected) * 100)) : 0; @endphp
                                            <tr>
                                                <td class="fw-bold">
                                                    {{ $a->title }} @if($a->category)<span class="oa-pill">{{ $a->category }}</span>@endif
                                                    <div class="oa-sub">by {{ $a->created_by }} · {{ $a->created_at }}</div>
                                                </td>
                                                <td>{{ $a->term }}</td>
                                                <td>{{ rtrim(rtrim(number_format($a->max_marks, 2), '0'), '.') }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="oa-bar flex-grow-1"><span style="width: {{ $pct }}%;"></span></div>
                                                        <span class="oa-sub">{{ $a->entered }}{{ $a->expected !== null ? '/' . $a->expected : '' }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $a->average_percent !== null ? $a->average_percent . '%' : '—' }}</td>
                                                <td class="oa-sub">{{ $a->highest !== null ? rtrim(rtrim(number_format($a->highest, 2), '0'), '.') . ' / ' . rtrim(rtrim(number_format($a->lowest, 2), '0'), '.') : '—' }}</td>
                                                <td>
                                                    @forelse($a->cards as $card)
                                                        <span class="oa-pill green" title="Picked on this report card">{{ $card }}</span>
                                                    @empty
                                                        <span class="oa-pill grey">Not used</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('open-assessments.results', $a->id) }}" class="oa-btn sm light"><i class="fas fa-eye"></i> Marks</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            </details>
        @empty
            <div class="oa-card"><div class="oa-empty"><i class="fas fa-filter"></i>Nothing matches these filters.</div></div>
        @endforelse

        </div></div></div></div>
@endsection
