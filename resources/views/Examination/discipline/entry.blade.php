<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .disc-hero {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 60%, #7c7aec 100%);
            border-radius: 0 0 2rem 2rem;
            padding: 2rem 2rem 3rem;
            margin-bottom: -1.5rem;
        }

        .disc-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 18px rgba(44, 41, 202, .09);
            overflow: hidden;
        }

        .disc-table thead th {
            background: #4d4be0;
            color: #fff;
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            border-bottom: 2px solid #ede9ff;
            text-align: center;
            white-space: nowrap;
        }

        .disc-table thead th.student-th {
            text-align: left;
            min-width: 180px;
        }

        .disc-table tbody tr:hover {
            background: #faf9ff;
        }

        .disc-table td {
            vertical-align: middle;
        }

        .rate-select {
            width: 72px;
            text-align: center;
            border: 2px solid #ede9ff;
            border-radius: .5rem;
            padding: .35rem .2rem;
            font-weight: 700;
            font-size: .8rem;
            background: #fff;
        }

        .rate-select:focus {
            outline: none;
            border-color: #5351e4;
            box-shadow: 0 0 0 3px rgba(83, 81, 228, .12);
        }

        .criteria-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #f4f3ff;
            border: 1px solid #e4e2ff;
            color: #3a37b8;
            border-radius: 999px;
            padding: .3rem .7rem .3rem .9rem;
            font-size: .78rem;
            font-weight: 600;
            margin: .2rem .3rem .2rem 0;
        }

        .criteria-chip button {
            border: none;
            background: transparent;
            color: #a3a0e8;
            cursor: pointer;
            font-size: .8rem;
            line-height: 1;
        }

        .criteria-chip button:hover {
            color: #c0392b;
        }

        .criteria-chip.inactive {
            opacity: .5;
        }

        .class-pill {
            display: inline-block;
            padding: .5rem 1rem;
            border-radius: .6rem;
            border: 2px solid #ede9ff;
            font-weight: 600;
            font-size: .82rem;
            color: #2C29CA;
            text-decoration: none;
            margin: 0 .4rem .4rem 0;
        }

        .class-pill.active {
            background: #2C29CA;
            border-color: #2C29CA;
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="side-app">

        {{-- Hero --}}
        <div class="disc-hero mb-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center"
                        style="gap: 12px;">
                        <span class="status-pill" style="background:rgba(255,255,255,.18);color:#fff;padding:.35rem .9rem;border-radius:999px;font-size:.75rem;font-weight:700;">
                            <i class="fas fa-user-shield me-1"></i> Discipline Ratings
                        </span>

                        <a href="{{ route('examination.passslips.index', $exam->id) }}" class="btn fw-semibold mt-2 mt-md-0"
                            style="border-radius: 1rem; padding: 0.7rem 1.5rem;
                                        background: rgba(255,255,255,0.2);
                                        backdrop-filter: blur(10px);
                                        border: 1px solid rgba(255,255,255,0.3);
                                        color: white;">
                            <i class="fas fa-arrow-left me-2"></i> Back to Pass Slips
                        </a>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-1" style="font-size:.82rem; color:rgba(255,255,255,.7); margin-top: 1rem;">
                        <h3 class="text-white fw-bold mb-1" style="font-size: 1.75rem;">
                            {{ $exam->exam_name }}
                        </h3>
                        <h5 style="color:rgba(255,255,255,.7);">Rate conduct &amp; discipline per student</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row px-3 px-md-4">
            <div class="col-12">

                {{-- ── Criteria manager ──────────────────────────────────── --}}
                <div class="disc-card mb-4">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-bold" style="font-size:.95rem;color:#1e1b4b;">
                                <i class="fas fa-list-check me-1" style="color:#5351e4;"></i> Criteria
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">
                                Shown on the report card in this order
                            </div>
                        </div>

                        <div id="criteriaChips" class="mb-3">
                            @foreach($criteria as $c)
                                <span class="criteria-chip" data-id="{{ $c->id }}">
                                    {{ $c->name }}
                                    <button type="button" onclick="removeCriteria({{ $c->id }})" title="Remove">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </span>
                            @endforeach
                        </div>

                        <form class="d-flex gap-2" onsubmit="addCriteria(event)">
                            <input type="text" id="newCriteriaName" class="form-control" placeholder="e.g. Sports & Co-curricular"
                                style="max-width:320px;border-radius:.6rem;border:2px solid #ede9ff;font-size:.82rem;">
                            <button type="submit" class="btn" style="background:#2C29CA;color:#fff;border-radius:.6rem;font-size:.82rem;font-weight:600;">
                                <i class="fas fa-plus me-1"></i> Add Criterion
                            </button>
                        </form>
                    </div>
                </div>

                {{-- ── Class picker ──────────────────────────────────────── --}}
                <div class="disc-card mb-4">
                    <div class="p-4">
                        <div class="fw-bold mb-2" style="font-size:.95rem;color:#1e1b4b;">
                            <i class="fas fa-layer-group me-1" style="color:#5351e4;"></i> Class
                        </div>
                        @forelse($classOptions as $opt)
                            <a class="class-pill {{ (string)$opt->class_id === (string)$classId && (string)$opt->stream_id === (string)$streamId ? 'active' : '' }}"
                               href="{{ route('examination.discipline.entry', $exam->id) }}?class_id={{ $opt->class_id }}&stream_id={{ $opt->stream_id }}">
                                {{ $opt->class_name }}{{ $opt->stream_name ? ' — ' . $opt->stream_name : '' }}
                            </a>
                        @empty
                            <div class="text-muted" style="font-size:.82rem;">
                                No classes have marks recorded for this exam yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ── Rating grid ───────────────────────────────────────── --}}
                @if($students->count() && $criteria->count())
                    <div class="disc-card mb-4">
                        <div class="table-responsive">
                            <table class="table disc-table mb-0">
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
                                            <td style="font-weight:600;">
                                                {{ $stu->lastname }} {{ $stu->firstname }}
                                            </td>
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

                        <div class="p-4 text-end">
                            <button id="saveDisciplineBtn" class="btn fw-semibold"
                                style="background:#2C29CA;color:#fff;border-radius:.7rem;padding:.7rem 1.6rem;">
                                <i class="fas fa-save me-2"></i> Save All Ratings
                            </button>
                        </div>
                    </div>
                @elseif($classOptions->count())
                    <div class="disc-card mb-4">
                        <div class="p-4 text-muted" style="font-size:.85rem;">
                            @if(!$criteria->count())
                                Add at least one criterion above to start rating students.
                            @else
                                No students found for this class/stream.
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        const EXAM_ID = {{ $exam->id }};
        const CLASS_ID = @json($classId);
        const STREAM_ID = @json($streamId);

        function addCriteria(e) {
            e.preventDefault();
            const input = document.getElementById('newCriteriaName');
            const name = input.value.trim();
            if (!name) return;

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
                .catch(() => Swal.fire('Error', 'Could not add criterion.', 'error'));
        }

        function removeCriteria(id) {
            Swal.fire({
                title: 'Remove this criterion?',
                text: 'Any ratings already saved for it will also be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c0392b',
                confirmButtonText: 'Yes, remove',
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
                    .catch(() => Swal.fire('Error', 'Could not remove criterion.', 'error'));
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
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

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
                            text: res.message,
                            confirmButtonColor: '#2C29CA',
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Failed to save.', 'error');
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
