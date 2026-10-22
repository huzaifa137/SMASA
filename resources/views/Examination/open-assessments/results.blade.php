<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    @php $max = (float) $assessment->max_marks; @endphp

    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-eye me-1"></i> Read-only</span>
        <div class="hero-title">{{ $assessment->title }}</div>
        <div class="hero-subtitle">
            {{ $subjectName }} — {{ $className }}@if($streamLabel) · {{ $streamLabel }}@endif · {{ $termLabel }}
            · by {{ $creator }} · marked out of <strong style="color:#fff;">{{ rtrim(rtrim(number_format($max, 2), '0'), '.') }}</strong>
        </div>
        <div class="hero-links mt-2">
            <a href="{{ route('open-assessments.overview') }}"><i class="fas fa-arrow-left me-1"></i> Back to assessments by teacher</a>
        </div>
    </div>

    <div class="oa-stats">
        <div class="oa-stat"><div class="v">{{ $stats['entered'] }}/{{ $stats['total'] }}</div><div class="l">Marks entered</div></div>
        <div class="oa-stat"><div class="v">{{ $stats['average'] !== null ? $stats['average'] . '%' : '—' }}</div><div class="l">Class average</div></div>
        <div class="oa-stat"><div class="v">{{ $stats['highest'] !== null ? rtrim(rtrim(number_format($stats['highest'], 2), '0'), '.') : '—' }}</div><div class="l">Highest</div></div>
        <div class="oa-stat"><div class="v">{{ $stats['lowest'] !== null ? rtrim(rtrim(number_format($stats['lowest'], 2), '0'), '.') : '—' }}</div><div class="l">Lowest</div></div>
    </div>

    <div class="oa-card">
        <div class="oa-card-head">
            <span><i class="fas fa-id-card me-2"></i> Report cards using this assessment</span>
            <span>
                @forelse($cards as $c)<span class="oa-pill green">{{ $c }}</span>@empty<span class="oa-pill grey">Not used on any report card yet</span>@endforelse
            </span>
        </div>
    </div>

    <div class="oa-card">
        <div class="oa-card-head"><span><i class="fas fa-users me-2"></i> Marks</span></div>
        <div class="table-responsive">
            <table class="table oa-table">
                <thead><tr><th style="width:44px;">#</th><th>Student</th><th style="width:130px;">Marks</th><th style="width:110px;">Percent</th></tr></thead>
                <tbody>
                    @forelse($rows as $i => $r)
                        <tr>
                            <td class="oa-sub">{{ $i + 1 }}</td>
                            <td>{{ $r->name }}</td>
                            <td class="fw-bold">{{ $r->marks !== null ? rtrim(rtrim(number_format($r->marks, 2), '0'), '.') : '—' }}</td>
                            <td>{{ $r->percent !== null ? $r->percent . '%' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="oa-empty"><i class="fas fa-users"></i>No students in this class-stream.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
