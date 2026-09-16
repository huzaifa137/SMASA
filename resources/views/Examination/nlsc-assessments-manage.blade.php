<?php use App\Http\Controllers\Helper; ?>
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

        .nt-action-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            border: none; border-radius: .55rem; padding: .4rem .8rem;
            font-size: .76rem; font-weight: 700; cursor: pointer; text-decoration: none;
            margin: .15rem .3rem .15rem 0;
        }
        .btn-edit-sm { background: #2C29CA; color: #fff; }
        .btn-edit-sm:hover { background: #211ea3; color: #fff; }
        .btn-del-sm { background: #dc3545; color: #fff; }
        .btn-del-sm:hover { background: #b3212f; color: #fff; }

        .nt-type-pill {
            display: inline-flex; align-items: center; font-size: .72rem; font-weight: 700;
            padding: .3rem .7rem; border-radius: .6rem; background: #eef0ff; color: #3a37b8;
            white-space: nowrap;
        }

        .nt-tabs { display: flex; gap: .5rem; margin-bottom: 1.25rem; }
        .nt-tab {
            padding: .55rem 1.1rem; border-radius: .7rem; font-size: .82rem; font-weight: 700;
            text-decoration: none; color: #4a4870; background: #eef0ff;
        }
        .nt-tab.active { background: #2C29CA; color: #fff; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: #a3a0c9; }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="nt-hero">
            <span class="hero-badge"><i class="fas fa-clipboard-check me-1"></i> NLSC Assessment</span>
            <div class="hero-title">Manage Created Assessments ({{ $assessments->count() }})</div>
            <div class="hero-subtitle">
                Every assessment you've created that's still in an editable phase — open one to
                change what it's set against, or delete it to send that class-subject's marks
                entry back to needing Create Assessment again.
            </div>
        </div>

        <div class="nt-tabs">
            <a href="{{ route('nlsc-assessments.pending') }}" class="nt-tab"><i class="fas fa-list-check me-1"></i> Pending</a>
            <a href="{{ route('nlsc-assessments.manage') }}" class="nt-tab active"><i class="fas fa-clipboard-check me-1"></i> Manage Created</a>
        </div>

        <div class="nt-card">
            <div class="card-header-custom"><i class="fas fa-list me-2"></i> Your Created Assessments</div>
            <div class="table-responsive">
                <table class="table nt-table">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Subject Matter</th>
                            <th style="width:10%;">Term</th>
                            <th style="width:16%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assessmentsTbody">
                        @forelse ($assessments as $a)
                            <tr data-assessment-id="{{ $a->id }}" data-delete-url="{{ route('nlsc-assessments.destroy', ['examId' => $a->examination_id, 'classSubjectId' => $a->class_subject_id, 'id' => $a->id]) }}">
                                <td>{{ $a->exam->exam_name ?? '—' }}</td>
                                <td>{{ $a->class_name }}</td>
                                <td>{{ Helper::recordMdname($a->subject_id) }}</td>
                                <td>
                                    <span class="nt-type-pill">
                                        @if($a->assessment_type === 'projects')
                                            Projects
                                        @elseif($a->assessment_type === 'activities_of_integration')
                                            Activities of Integration
                                        @else
                                            Subject Achievement
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $a->subject_matter_name ?? '—' }}</td>
                                <td>{{ $a->term }}</td>
                                <td>
                                    @if($a->class_subject_id)
                                        <a href="{{ route('nlsc-assessments', ['examId' => $a->examination_id, 'classSubjectId' => $a->class_subject_id]) }}" class="nt-action-btn btn-edit-sm">
                                            <i class="fas fa-pen"></i> Open
                                        </a>
                                    @endif
                                    <button type="button" class="nt-action-btn btn-del-sm delete-assessment-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard d-block mb-2" style="font-size:1.8rem; color:#a3a0c9;"></i>
                                        You haven't created any assessments yet — check the Pending tab for
                                        class-subjects still waiting on one.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const CSRF = '{{ csrf_token() }}';

        document.querySelectorAll('.delete-assessment-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const url = row.dataset.deleteUrl;

                Swal.fire({
                    title: 'Delete this assessment?',
                    text: 'Marks entry for this class-subject will go back to needing one set up again.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Delete',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(url, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success) {
                                Swal.fire('Error', res.message || 'Failed to delete.', 'error');
                                return;
                            }
                            row.remove();
                        })
                        .catch(() => Swal.fire('Error', 'Failed to delete — check your connection.', 'error'));
                });
            });
        });
    </script>
@endsection
