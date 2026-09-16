@extends('layouts-side-bar.master')

@section('css')
    <style>
        .nt-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: 1.5rem;
        }

        .nt-hero .hero-badge {
            background: rgba(44, 41, 202, 0.25);
            border: 1px solid rgba(107, 105, 232, 0.5);
            color: #c7c5ff;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .nt-hero .hero-title { font-size: 1.4rem; font-weight: 800; color: #fff; margin: .25rem 0; }
        .nt-hero .hero-subtitle { color: rgba(255, 255, 255, .68); font-size: .85rem; max-width: 760px; }

        .nt-card {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .nt-card .card-header-custom {
            padding: 1.1rem 1.6rem;
            border-bottom: 2px solid #f0eeff;
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
        }

        .nt-table { margin-bottom: 0; font-size: .87rem; }
        .nt-table thead th {
            background: #2C29CA; color: #fff; font-size: .68rem; text-transform: uppercase;
            letter-spacing: .06em; font-weight: 700; padding: .85rem .9rem; border: none; white-space: nowrap;
        }
        .nt-table tbody td { vertical-align: middle; padding: .85rem .9rem; border-bottom: 1px solid #f0eeff; }

        .btn-nt-primary {
            background: #2C29CA; color: #fff; border: none; border-radius: .65rem;
            padding: .5rem 1.1rem; font-weight: 700; font-size: .82rem; text-decoration: none; display: inline-block;
        }
        .btn-nt-primary:hover { background: #211ea3; color: #fff; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: #a3a0c9; }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-clipboard-list me-1"></i> NLSC Assessment</span>
            <div class="hero-title">Create Assessment — Pending ({{ $pending->count() }})</div>
            <div class="hero-subtitle">
                Every Secondary O-Level (Senior 1-4) class-subject you teach that still needs an
                assessment created before marks entry opens for it. Secondary A-Level
                (Senior 5/6) never appears here — it goes straight to marks entry.
            </div>
        </div>

        <div class="nt-card">
            <div class="card-header-custom"><i class="fas fa-list me-2"></i> Pending Assessments</div>
            <div class="table-responsive">
                <table class="table nt-table">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th style="width:14%;">Term</th>
                            <th style="width:18%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pending as $item)
                            <tr>
                                <td>{{ $item->exam->exam_name }}</td>
                                <td>{{ $item->class_name }} {{ $item->stream_id }}</td>
                                <td>{{ $item->subject_name }}</td>
                                <td>{{ $item->exam->term }}</td>
                                <td>
                                    <a href="{{ route('nlsc-assessments', ['examId' => $item->exam->id, 'classSubjectId' => $item->class_subject_id]) }}" class="btn-nt-primary">
                                        <i class="fas fa-plus me-1"></i> Create Assessment
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-circle-check d-block mb-2" style="font-size:1.8rem; color:#16a34a;"></i>
                                        Nothing pending — every Secondary O-Level class-subject you teach already has
                                        an assessment for its current exam.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection