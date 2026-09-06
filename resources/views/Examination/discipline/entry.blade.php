<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        /* ===== Hero Section ===== */
        .disc-hero {
            background: linear-gradient(135deg, #1a1a7a 0%, #2C29CA 60%, #6b69e8 100%);
            border-radius: 0 0 2.5rem 2.5rem;
            padding: 2.5rem 2.5rem 3.5rem;
            margin-bottom: -1.5rem;
            position: relative;
            overflow: hidden;
        }

        .disc-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 40%;
            height: 200%;
            background: rgba(255, 255, 255, 0.04);
            transform: rotate(15deg);
            pointer-events: none;
        }

        .disc-hero .hero-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: .4rem 1.2rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .disc-hero .hero-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
        }

        .disc-hero .hero-subtitle {
            color: rgba(255, 255, 255, .7);
            font-weight: 400;
            font-size: 1rem;
        }

        .btn-back {
            border-radius: 1rem;
            padding: 0.7rem 1.8rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: 600;
            transition: all .25s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ===== Cards ===== */
        .disc-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            overflow: hidden;
            background: #fff;
            transition: box-shadow .2s ease;
        }

        .disc-card:hover {
            box-shadow: 0 8px 40px rgba(44, 41, 202, .12);
        }

        .disc-card .card-header-custom {
            padding: 1.25rem 1.75rem;
            border-bottom: 2px solid #f0eeff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .disc-card .card-header-custom .title {
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .disc-card .card-header-custom .title i {
            color: #5351e4;
        }

        .disc-card .card-body-custom {
            padding: 1.5rem 1.75rem;
        }

        /* ===== Table ===== */
        .disc-table {
            margin-bottom: 0;
            font-size: .85rem;
        }

        .disc-table thead th {
            background: #4d4be0;
            color: #fff;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            padding: 0.9rem 0.75rem;
            text-align: center;
            white-space: nowrap;
            border: none;
        }

        .disc-table thead th.student-th {
            text-align: left;
            min-width: 200px;
            padding-left: 1.5rem;
        }

        .disc-table tbody tr {
            transition: background .15s ease;
        }

        .disc-table tbody tr:hover {
            background: #f8f7ff;
        }

        .disc-table tbody td {
            vertical-align: middle;
            padding: 0.75rem;
            border-bottom: 1px solid #f0eeff;
        }

        .disc-table tbody td:first-child {
            padding-left: 1.5rem;
            font-weight: 600;
            color: #1e1b4b;
        }

        /* ===== Rate Select ===== */
        .rate-select {
            width: 72px;
            text-align: center;
            border: 2px solid #ede9ff;
            border-radius: .6rem;
            padding: .35rem .2rem;
            font-weight: 700;
            font-size: .8rem;
            background: #fff;
            cursor: pointer;
            transition: all .2s ease;
            appearance: auto;
        }

        .rate-select:hover {
            border-color: #b5b2f0;
        }

        .rate-select:focus {
            outline: none;
            border-color: #5351e4;
            box-shadow: 0 0 0 4px rgba(83, 81, 228, .12);
        }

        /* ===== Criteria Chips ===== */
        .criteria-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #f4f3ff;
            border: 1px solid #e4e2ff;
            color: #3a37b8;
            border-radius: 999px;
            padding: .35rem .7rem .35rem 1.1rem;
            font-size: .78rem;
            font-weight: 600;
            margin: .2rem .4rem .2rem 0;
            transition: all .15s ease;
        }

        .criteria-chip:hover {
            background: #edebff;
            border-color: #cdcaff;
        }

        .criteria-chip .remove-btn {
            border: none;
            background: transparent;
            color: #a3a0e8;
            cursor: pointer;
            font-size: .85rem;
            line-height: 1;
            padding: 0;
            transition: color .15s ease;
        }

        .criteria-chip .remove-btn:hover {
            color: #c0392b;
        }

        /* ===== Class Pills ===== */
        .class-pill {
            display: inline-block;
            padding: .55rem 1.4rem;
            border-radius: .7rem;
            border: 2px solid #ede9ff;
            font-weight: 600;
            font-size: .82rem;
            color: #2C29CA;
            text-decoration: none;
            margin: 0 .4rem .4rem 0;
            transition: all .2s ease;
            background: #fff;
        }

        .class-pill:hover {
            background: #f4f3ff;
            border-color: #c5c2f2;
            color: #2C29CA;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .class-pill.active {
            background: #2C29CA;
            border-color: #2C29CA;
            color: #fff;
            box-shadow: 0 4px 14px rgba(44, 41, 202, .25);
        }

        .class-pill.active:hover {
            background: #2522b0;
            border-color: #2522b0;
            color: #fff;
        }

        /* ===== Form Elements ===== */
        .form-control-custom {
            border-radius: .7rem;
            border: 2px solid #ede9ff;
            font-size: .82rem;
            padding: .55rem 1rem;
            transition: all .2s ease;
        }

        .form-control-custom:focus {
            border-color: #5351e4;
            box-shadow: 0 0 0 4px rgba(83, 81, 228, .08);
            outline: none;
        }

        .btn-primary-custom {
            background: #2C29CA;
            color: #fff;
            border-radius: .7rem;
            font-weight: 600;
            padding: .55rem 1.5rem;
            border: none;
            transition: all .2s ease;
        }

        .btn-primary-custom:hover {
            background: #2522b0;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(44, 41, 202, .25);
        }

        .btn-primary-custom:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-save {
            background: #2C29CA;
            color: #fff;
            border-radius: .7rem;
            padding: .7rem 2rem;
            font-weight: 700;
            border: none;
            transition: all .2s ease;
        }

        .btn-save:hover {
            background: #2522b0;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 18px rgba(44, 41, 202, .3);
        }

        .btn-save:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        /* ===== Empty State ===== */
        .empty-state {
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: #8a87b8;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #cdcaff;
            margin-bottom: .75rem;
            display: block;
        }

        .empty-state p {
            margin-bottom: 0;
            font-size: .9rem;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .disc-hero {
                padding: 1.5rem 1.25rem 2rem;
                border-radius: 0 0 1.5rem 1.5rem;
            }

            .disc-hero .hero-title {
                font-size: 1.4rem;
            }

            .disc-card .card-header-custom {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                padding: 1rem 1.25rem;
            }

            .disc-card .card-body-custom {
                padding: 1rem 1.25rem;
            }

            .disc-table thead th.student-th {
                min-width: 120px;
            }

            .disc-table tbody td:first-child {
                padding-left: 0.75rem;
            }

            .disc-table thead th {
                font-size: .6rem;
                padding: 0.6rem 0.4rem;
            }

            .disc-table tbody td {
                padding: 0.5rem 0.4rem;
                font-size: .78rem;
            }

            .rate-select {
                width: 60px;
                font-size: .7rem;
                padding: .25rem .1rem;
            }
        }

        /* ===== Hero Section Fix ===== */
.disc-hero .row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-right: 0;
    margin-left: 0;
}

.disc-hero [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}

.disc-hero .col-md-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
}

.disc-hero .col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
}

.text-md-end {
    text-align: right !important;
}

.disc-hero .btn-back {
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
}

/* Mobile Fix */
@media (max-width: 768px) {
    .disc-hero .col-md-8,
    .disc-hero .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
        text-align: center !important;
    }
    
    .disc-hero .col-md-4 {
        margin-top: 1rem;
    }
}
    </style>
@endsection

@section('content')
    <div class="side-app">

        {{-- ===== HERO SECTION ===== --}}
        <div class="disc-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                        <span class="hero-badge">
                            <i class="fas fa-user-shield me-1"></i> Discipline Ratings
                        </span>
                    </div>
                    <h1 class="hero-title">{{ $exam->exam_name }}</h1>
                    <p class="hero-subtitle">Rate conduct &amp; discipline per student</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('examination.passslips.index', $exam->id) }}" class="btn-back">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Pass Slips
                    </a>
                </div>
            </div>
        </div>

        <div class="row px-3 px-md-4">
            <div class="col-12">

                {{-- ===== CRITERIA MANAGER ===== --}}
                <div class="disc-card mb-4">
                    <div class="card-header-custom">
                        <div class="title">
                            <i class="fas fa-list-check"></i> Criteria
                        </div>
                        <span class="text-muted" style="font-size:.72rem; font-weight:500;">
                            Shown on report card in this order
                        </span>
                    </div>
                    <div class="card-body-custom">
                        <div id="criteriaChips" class="mb-3">
                            @foreach($criteria as $c)
                                <span class="criteria-chip" data-id="{{ $c->id }}">
                                    {{ $c->name }}
                                    <button type="button" class="remove-btn" onclick="removeCriteria({{ $c->id }})"
                                        title="Remove criterion">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </span>
                            @endforeach
                        </div>

                        <form class="d-flex flex-wrap gap-2" onsubmit="addCriteria(event)">
                            <input type="text" id="newCriteriaName" class="form-control form-control-custom"
                                placeholder="e.g. Sports &amp; Co-curricular"
                                style="flex:1; min-width:200px; max-width:340px;"> &nbsp; &nbsp;
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Criterion
                            </button>
                        </form>
                    </div>
                </div>

                {{-- ===== CLASS PICKER ===== --}}
                <div class="disc-card mb-4">
                    <div class="card-header-custom">
                        <div class="title">
                            <i class="fas fa-layer-group"></i> Class
                        </div>
                    </div>
                    <div class="card-body-custom">
                        @forelse($classOptions as $opt)
                            <a class="class-pill {{ (string) $opt->class_id === (string) $classId && (string) $opt->stream_id === (string) $streamId ? 'active' : '' }}"
                                href="{{ route('examination.discipline.entry', $exam->id) }}?class_id={{ $opt->class_id }}&stream_id={{ $opt->stream_id }}">
                                {{ $opt->class_name }}{{ $opt->stream_name ? ' — ' . $opt->stream_name : '' }}
                            </a>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <p>No classes have marks recorded for this exam yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ===== RATING GRID ===== --}}
                @if($students->count() && $criteria->count())
                    <div class="disc-card mb-4">
                        <div class="table-responsive">
                            <table class="table disc-table">
                                <thead>
                                    <tr>
                                        <th class="student-th">Student</th>
                                        @foreach($criteria as $c)
                                            <th>{{ $c->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $stu)
                                        <tr data-student-id="{{ $stu->id }}">
                                            <td>{{ $stu->lastname }} {{ $stu->firstname }}</td>
                                            @foreach($criteria as $c)
                                                @php
                                                    $existing = optional($ratings->get($stu->id))
                                                        ->firstWhere('discipline_criteria_id', $c->id);
                                                @endphp
                                                <td class="text-center">
                                                    <select class="rate-select" data-criteria-id="{{ $c->id }}">
                                                        <option value="">—</option>
                                                        <option value="A" @selected(optional($existing)->rating === 'A')>A</option>
                                                        <option value="B" @selected(optional($existing)->rating === 'B')>B</option>
                                                        <option value="C" @selected(optional($existing)->rating === 'C')>C</option>
                                                    </select>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-body-custom text-end">
                            <button id="saveDisciplineBtn" class="btn-save">
                                <i class="fas fa-save me-2"></i> Save All Ratings
                            </button>
                        </div>
                    </div>
                @elseif($classOptions->count())
                    <div class="disc-card mb-4">
                        <div class="card-body-custom">
                            <div class="empty-state">
                                @if(!$criteria->count())
                                    <i class="fas fa-plus-circle"></i>
                                    <p>Add at least one criterion above to start rating students.</p>
                                @else
                                    <i class="fas fa-user-graduate"></i>
                                    <p>No students found for this class / stream.</p>
                                @endif
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

    {{-- ===== JAVASCRIPT ===== --}}
    <script>
        const EXAM_ID = {{ $exam->id }};
        const CLASS_ID = @json($classId);
        const STREAM_ID = @json($streamId);

        function addCriteria(e) {
            e.preventDefault();
            const input = document.getElementById('newCriteriaName');
            const name = input.value.trim();
            if (!name) {
                Swal.fire('Warning', 'Please enter a criterion name.', 'warning');
                return;
            }

            fetch('{{ route('examination.discipline.criteria.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name }),
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error', res.message || 'Could not add criterion.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Could not add criterion. Please check your connection.', 'error'));
        }

        function removeCriteria(id) {
            Swal.fire({
                title: 'Remove this criterion?',
                text: 'Any ratings already saved for it will also be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c0392b',
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch('{{ url('/examinations/discipline/criteria') }}/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            location.reload();
                        } else {
                            Swal.fire('Error', res.message || 'Could not remove criterion.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Could not remove criterion. Please check your connection.', 'error'));
            });
        }

        document.getElementById('saveDisciplineBtn')?.addEventListener('click', function () {
            const ratings = [];
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                const studentId = row.dataset.studentId;
                row.querySelectorAll('.rate-select').forEach(sel => {
                    ratings.push({
                        student_id: studentId,
                        discipline_criteria_id: sel.dataset.criteriaId,
                        rating: sel.value,
                    });
                });
            });

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

            fetch('{{ route('examination.discipline.save', $exam->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    class_id: CLASS_ID,
                    stream_id: STREAM_ID,
                    ratings,
                }),
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: res.message || 'All ratings have been saved successfully.',
                            confirmButtonColor: '#2C29CA',
                            timer: 2500,
                            timerProgressBar: true,
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Failed to save ratings.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'))
                .finally(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-2"></i> Save All Ratings';
                });
        });
    </script>
@endsection