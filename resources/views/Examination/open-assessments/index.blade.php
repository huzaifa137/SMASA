<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-list-check me-1"></i> Open Assessments</span>
        <div class="hero-title">My Open Assessments</div>
        <div class="hero-subtitle">
            Create as many assessments as you like for each of your subjects — quizzes, tests, assignments — whenever you
            want, and enter marks for each one. No examination is needed. The administrator later chooses which of them
            count on the report card.
        </div>
        @if(\App\Helpers\PermissionHelper::canFeature('view_open_assessments_overview'))
            <div class="hero-links mt-2">
                <a href="{{ route('open-assessments.overview') }}"><i class="fas fa-user-check me-1"></i> See every teacher's assessments</a>
            </div>
        @endif
    </div>

    <div class="oa-card">
        <div class="oa-card-head">
            <span><i class="fas fa-book-open me-2"></i> My subjects ({{ $rows->count() }})</span>
            @if($currentLabel !== '')<span class="oa-sub">Current term: {{ $currentLabel }}</span>@endif
        </div>
        <div class="table-responsive">
            <table class="table oa-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Subject</th>
                        <th style="width:90px;">Students</th>
                        <th style="width:130px;">This term</th>
                        <th style="width:110px;">All time</th>
                        <th style="width:130px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->class_name }}@if($row->stream) <span class="oa-sub">· {{ $row->stream }}</span>@endif</td>
                            <td class="fw-bold">{{ $row->subject }}</td>
                            <td>{{ $row->students }}</td>
                            <td><span class="oa-pill {{ $row->this_term ? 'green' : 'amber' }}">{{ $row->this_term }}</span></td>
                            <td><span class="oa-pill grey">{{ $row->total }}</span></td>
                            <td class="text-end">
                                <a class="oa-btn sm" href="{{ route('open-assessments.hub', $row->class_subject_id) }}"><i class="fas fa-folder-open"></i> Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="oa-empty"><i class="fas fa-clipboard-list"></i>
                            You have no Senior 1–4 subjects assigned yet, so there is nothing to assess.
                        </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
    </div>
    
@endsection
