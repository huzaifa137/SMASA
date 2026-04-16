<?php
use App\Http\Controllers\Helper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <link href="{{ URL::asset('assets/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <style>
        .exam-hero {
            background: linear-gradient(135deg, #2C29CA 0%, #5351e4 60%, #7c7aec 100%);
            border-radius: 0 0 2rem 2rem;
            padding: 2rem 2rem 3rem;
            margin-bottom: -1.5rem;
        }
        .stat-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(44,41,202,.08);
            transition: transform .15s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: .75rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .exam-table-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(44,41,202,.07);
            overflow: hidden;
        }
        .table thead th {
            background: #f5f4ff;
            color: #2C29CA;
            font-size: .78rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            border-bottom: 2px solid #ede9ff;
            font-weight: 700;
        }
        .status-pill {
            padding: .28rem .85rem;
            border-radius: 99px;
            font-size: .73rem;
            font-weight: 700;
            letter-spacing: .03em;
            display: inline-block;
        }
        .status-draft            { background:#f0f0f0; color:#666; }
        .status-active           { background:#d4f5e2; color:#1a7a4a; }
        .status-marks_entry      { background:#fff3cd; color:#856404; }
        .status-closed           { background:#fde8e8; color:#c0392b; }
        .status-results_released { background:#cfe2ff; color:#0a4191; }

        .action-btn {
            border-radius: .45rem;
            font-size: .78rem;
            padding: .3rem .75rem;
        }
        .progress-mini {
            height: 6px; border-radius: 99px;
        }
    </style>
@endsection

@section('content')
<div class="side-app">

    {{-- Hero --}}
    <div class="exam-hero mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-white fw-bold mb-1"><i class="fas fa-clipboard-list me-2"></i>Examinations</h3>
                <p class="text-white mb-0 opacity-75" style="font-size:.9rem;">
                    Manage all school examinations, marks entry and result releases.
                </p>
            </div>
            <a href="{{ route('examination.create') }}" class="btn btn-light fw-semibold" style="border-radius:.6rem;">
                <i class="fas fa-plus me-1"></i> New Examination
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    @php
        $total    = $examinations->count();
        $active   = $examinations->where('status','active')->count();
        $entry    = $examinations->where('status','marks_entry')->count();
        $released = $examinations->where('status','results_released')->count();
    @endphp
    <div class="row g-3 mb-4">
        @foreach([
            ['Total', $total,    '#2C29CA', '#ede9ff', 'fa-clipboard-list'],
            ['Active', $active,  '#1a7a4a', '#d4f5e2', 'fa-play-circle'],
            ['Marks Entry', $entry, '#856404', '#fff3cd', 'fa-pen-alt'],
            ['Released', $released,'#0a4191','#cfe2ff', 'fa-award'],
        ] as [$label, $count, $color, $bg, $icon])
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:{{ $bg }}; color:{{ $color }};">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:800; color:#222; line-height:1;">{{ $count }}</div>
                        <div class="text-muted" style="font-size:.78rem;">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card exam-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="examTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Exam Code</th>
                            <th>Examination</th>
                            <th>Type / Term</th>
                            <th>Period</th>
                            <th>Entry Deadline</th>
                            <th>Status</th>
                            <th style="width:180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($examinations as $key => $exam)
                        <tr>
                            <td class="text-muted" style="font-size:.8rem;">{{ $key + 1 }}</td>
                            <td>
                                <span style="font-family:monospace; font-size:.8rem; color:#5351e4; font-weight:600;">
                                    {{ $exam->exam_code }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:.88rem;">{{ $exam->exam_name }}</div>
                                <div class="text-muted" style="font-size:.76rem;">AY {{ $exam->academic_year }}</div>
                            </td>
                            <td>
                                <div style="font-size:.83rem;">{{ $exam->exam_type }}</div>
                                <div class="text-muted" style="font-size:.76rem;">{{ $exam->term }}</div>
                            </td>
                            <td style="font-size:.82rem;">
                                {{ $exam->start_date->format('d M Y') }}<br>
                                <span class="text-muted">to {{ $exam->end_date->format('d M Y') }}</span>
                            </td>
                            <td style="font-size:.82rem;">
                                @php $daysLeft = now()->diffInDays($exam->marks_entry_deadline, false); @endphp
                                {{ $exam->marks_entry_deadline->format('d M Y') }}<br>
                                @if($daysLeft > 0)
                                    <span class="text-success" style="font-size:.74rem;"><i class="fas fa-clock me-1"></i>{{ $daysLeft }}d left</span>
                                @elseif($daysLeft == 0)
                                    <span class="text-warning" style="font-size:.74rem;"><i class="fas fa-exclamation-circle me-1"></i>Due today</span>
                                @else
                                    <span class="text-danger" style="font-size:.74rem;"><i class="fas fa-lock me-1"></i>Passed</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-pill status-{{ $exam->status }}">
                                    {{ $exam->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    {{-- Status control --}}
                                    @if($exam->status === 'draft')
                                        <button class="btn btn-success action-btn btn-status"
                                            data-id="{{ $exam->id }}" data-status="active"
                                            title="Activate Examination">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    @elseif($exam->status === 'active')
                                        <button class="btn btn-warning action-btn btn-status"
                                            data-id="{{ $exam->id }}" data-status="marks_entry"
                                            title="Open Marks Entry">
                                            <i class="fas fa-pen-alt"></i>
                                        </button>
                                    @elseif($exam->status === 'marks_entry')
                                        <button class="btn btn-danger action-btn btn-status"
                                            data-id="{{ $exam->id }}" data-status="closed"
                                            title="Close Exam">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @elseif($exam->status === 'closed')
                                        <button class="btn action-btn btn-status text-white"
                                            style="background:#0a4191;"
                                            data-id="{{ $exam->id }}" data-status="results_released"
                                            title="Release Results">
                                            <i class="fas fa-award"></i>
                                        </button>
                                    @endif

                                    {{-- Marks entry (teacher) --}}
                                    @if(in_array($exam->status, ['active','marks_entry']))
                                        <a href="{{ route('examination.marks.entry', $exam->id) }}"
                                           class="btn btn-primary action-btn" title="Enter Marks">
                                            <i class="fas fa-table"></i>
                                        </a>
                                    @endif

                                    {{-- Delete (draft only) --}}
                                    @if($exam->status === 'draft')
                                        <button class="btn btn-outline-danger action-btn btn-delete"
                                            data-id="{{ $exam->id }}" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard fa-3x mb-3 d-block opacity-25"></i>
                                No examinations found. <a href="{{ route('examination.create') }}">Create one now.</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {

    $('#examTable').DataTable({ pageLength: 25, order: [[0, 'desc']] });

    // ── Update Status ──────────────────────────────────────────────────────
    const statusLabels = {
        active:           'Activate',
        marks_entry:      'Open Marks Entry',
        closed:           'Close Examination',
        results_released: 'Release Results',
    };
    const statusWarnings = {
        active:           'This will make the examination live for students.',
        marks_entry:      'Teachers will now be able to enter marks.',
        closed:           'Marks entry will be closed. This cannot easily be undone.',
        results_released: 'Results will be visible to students and staff.',
    };

    $(document).on('click', '.btn-status', function () {
        const id     = $(this).data('id');
        const status = $(this).data('status');

        Swal.fire({
            title: statusLabels[status] + '?',
            text:  statusWarnings[status],
            icon:  'warning',
            showCancelButton: true,
            confirmButtonColor: '#2C29CA',
            confirmButtonText: 'Yes, proceed!',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url:  `/examinations/${id}/status`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', status: status },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ icon:'success', title:'Updated!', text: res.message, timer:1500, showConfirmButton:false });
                        setTimeout(() => location.reload(), 1600);
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function (xhr) { $('body').html(xhr.responseText); }
            });
        });
    });

    // ── Delete ─────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Examination?',
            text:  'This action is irreversible. Only draft examinations can be deleted.',
            icon:  'error',
            showCancelButton: true,
            confirmButtonColor: '#c0392b',
            confirmButtonText: 'Yes, delete!',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url:  `/examinations/${id}`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ icon:'success', title:'Deleted!', timer:1200, showConfirmButton:false });
                        setTimeout(() => location.reload(), 1300);
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function (xhr) { $('body').html(xhr.responseText); }
            });
        });
    });
});
</script>
@endsection
@section('js')
    <script src="{{ URL::asset('assets/plugins/datatable/js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('assets/plugins/datatable/js/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ URL::asset('assets/js/datatables.js') }}"></script>
@endsection
