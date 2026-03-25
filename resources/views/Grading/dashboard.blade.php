<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('content')
    <div class="side-app">

        <!-- Header Row -->
        <div class="row">
            <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                <div class="card bg-primary">
                    <div class="card-header text-white">
                        @include('layouts.grading-buttons')
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="row mt-4">
            <div class="col-lg-12">
                {{-- FILTER CARD --}}
                <div class="card mb-4">
                    <div class="card-body">

                        <form method="GET" action="{{ route('grading.dashboard') }}">
                            <div class="row">

                                <div class="col-md-3">
                                    <label>{{ trans('common.exam_type') }}</label>
                                    <select name="exam_type" class="form-control">
                                        <option value="">{{ trans('common.all') }}</option>
                                        <option value="thanawi">{{ trans('common.thanawi') }}</option>
                                        <option value="idaad">{{ trans('common.idaad') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label>{{ trans('common.academic_year') }}</label>
                                    <select name="academic_year" class="form-control">
                                        <option value="">{{ trans('common.all') }}</option>
                                        @foreach($years as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label>School</label>
                                    <select name="school_id" class="form-control">
                                        <option value="">{{ trans('common.national') }} (All Schools)</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}">
                                                {{ $school->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100">
                                        {{ trans('common.filter_results') }}
                                    </button>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>

                {{-- RESULTS TABLE --}}
                <div class="card">
                    <div class="card-header bg-dark text-white">
                       {{ trans('common.national_ranking_results') }}
                    </div>

                    <div class="card-body">

                        @if($results->isEmpty())
                            <div class="alert alert-warning">
                               {{ trans('common.no_results_found') }}.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">

                                    <thead class="table-dark">
                                        <tr>
                                            <th>{{ trans('common.national_rank') }}</th>
                                            <th>{{ trans('common.student_name') }}</th>
                                            <th>{{ trans('common.school') }}</th>
                                            <th>{{ trans('common.total') }}</th>
                                            <th>{{ trans('common.average') }}</th>
                                            <th>{{ trans('common.grade') }}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($results as $student)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-success">
                                                        {{ $student->national_rank }}
                                                    </span>
                                                </td>
                                                <td>{{ $student->student_name }}</td>
                                                <td>{{ $student->school_name }}</td>
                                                <td>{{ number_format($student->total_marks, 2) }}</td>
                                                <td>{{ number_format($student->average_marks, 2) }}%</td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        {{ $student->grade }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- SUBJECT ANALYTICS --}}
                @if($subjectStats)
                    <div class="row mt-4">

                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>{{ trans('common.best_subject_nationally') }}</h5>
                                    <h4>{{ $subjectStats['best_subject']['subject_name'] }}</h4>
                                    <p>{{ trans('common.average') }}: {{ number_format($subjectStats['best_subject']['average_marks'], 2) }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5>{{ trans('common.hardest_subject') }}</h5>
                                    <h4>{{ $subjectStats['hardest_subject']['subject_name'] }}</h4>
                                    <p>{{ trans('common.average') }}: {{ number_format($subjectStats['hardest_subject']['average_marks'], 2) }}%</p>
                                </div>
                            </div>
                        </div>

                    </div>
                @endif

            </div>
        </div>
    </div>
    </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection