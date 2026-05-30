@extends('layouts-side-bar.master')
<?php use App\Http\Controllers\Helper;  ?>
@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --g: #2f2ccb;
            --gl: rgba(47, 44, 203, .10);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --grn: #059669;
            --grnl: rgba(5, 150, 105, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        /* Hero */
        .fin-hero {
            background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(165, 180, 252, .2) 0%, transparent 70%);
        }

        .fin-hero h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .fin-hero p {
            color: #c7d2fe;
            margin: .2rem 0 0;
            font-size: .88rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(47, 44, 203, .25);
            border: 1px solid rgba(165, 180, 252, .4);
            color: #a5b4fc;
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: .6rem;
        }

        /* Stat pills row */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .stat-pills {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .stat-pill {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: var(--surf);
            border: 1px solid var(--brd);
            border-radius: 10px;
            padding: .5rem 1rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--t2);
            box-shadow: var(--sh);
        }

        .stat-pill i {
            font-size: .85rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.25rem;
            border-radius: var(--rads);
            font-size: .875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }

        .btn-sm {
            padding: .4rem .85rem;
            font-size: .8rem;
        }

        .btn-primary {
            background: #2f2ccb;
            color: #fff;
        }

        .btn-primary:hover {
            background: #2420a8;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(47, 44, 203, .35);
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--brd);
            color: var(--t2);
        }

        .btn-outline:hover {
            border-color: var(--b);
            color: var(--b);
        }

        .btn-danger {
            background: #dc2626 !important;
            /* Solid red */
            color: #fff !important;
            border: none !important;
            width: 32px;
            height: 32px;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-danger:hover {
            background: #b91c1c !important;
            transform: scale(1.05);
        }

        .btn-danger i {
            font-size: 0.85rem;
            margin: 0;
        }

        /* Update table header to match the item-cols-header styling */
        .fs-table thead tr {
            background: #2c29ca !important;
        }

        .fs-table th {
            padding: .75rem 1.25rem;
            font-size: .72rem;
            font-weight: 700;
            color: #fff !important;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
            white-space: nowrap;
        }

        /* Add border radius to first and last header cells */
        .fs-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .fs-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        /* Card */
        .card {
            background: var(--surf);
            border-radius: var(--rad);
            border: 1px solid var(--brd);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        .card-hd {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
        }

        .card-hd-left {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .card-hd h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
        }

        /* Table */
        .fs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .fs-table thead tr {
            background: #f5f6ff;
        }

        .fs-table th {
            padding: .75rem 1.25rem;
            font-size: .72rem;
            font-weight: 700;
            color: var(--b);
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 2px solid var(--brd);
            white-space: nowrap;
        }

        .fs-table td {
            padding: .9rem 1.25rem;
            border-bottom: 1px solid #f8fafc;
            font-size: .875rem;
            color: var(--t1);
            vertical-align: middle;
        }

        .fs-table tbody tr:last-child td {
            border-bottom: none;
        }

        .fs-table tbody tr {
            transition: background .12s;
        }

        .fs-table tbody tr:hover td {
            background: #f5f6ff;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .22rem .7rem;
            border-radius: 20px;
            font-size: .74rem;
            font-weight: 600;
        }

        .badge-blue {
            background: var(--bl);
            color: var(--b);
        }

        .badge-gray {
            background: #f1f5f9;
            color: var(--t2);
        }

        .badge-green {
            background: var(--grnl);
            color: var(--grn);
        }

        .badge-amber {
            background: var(--al);
            color: var(--a);
        }

        .badge-red {
            background: var(--rl);
            color: var(--r);
        }

        /* Amount */
        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 700;
            color: var(--b);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3.5rem 2rem;
            color: var(--t3);
        }

        .empty-state .empty-icon {
            width: 72px;
            height: 72px;
            background: var(--bl);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .empty-state .empty-icon i {
            font-size: 1.75rem;
            color: var(--b);
        }

        .empty-state h4 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--t2);
            margin: 0 0 .4rem;
        }

        .empty-state p {
            font-size: .875rem;
            margin: 0 0 1.25rem;
        }

        /* Row name cell */
        .struct-name {
            font-weight: 600;
            color: var(--t1);
        }

        .struct-note {
            font-size: .75rem;
            color: var(--t3);
            margin-top: 2px;
        }

        /* Actions cell */
        .actions-cell {
            display: flex;
            gap: .5rem;
            align-items: center;
        }

        /* Responsive */
        @media(max-width:768px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.3rem;
            }

            .fs-table th,
            .fs-table td {
                padding: .65rem .85rem;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .stat-pills {
                justify-content: space-between;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-layer-group"></i> Finance — Fee Structures</div>
            <h1>Fee Structures</h1>
            <p>Manage tuition fee templates by term, class level and student type</p>
        </div>
    </div>
@endsection

@section('content')

    <div class="toolbar">
        <div class="stat-pills">
            <div class="stat-pill">
                <i class="fas fa-layer-group" style="color:var(--b);"></i>
                <span>{{ $structures->count() }} Total</span>
            </div>
            <div class="stat-pill">
                <i class="fas fa-check-circle" style="color:var(--grn);"></i>
                <span>{{ $structures->where('is_active', true)->count() }} Active</span>
            </div>
            <div class="stat-pill">
                <i class="fas fa-coins" style="color:var(--a);"></i>
                <span>UGX {{ number_format($structures->sum('total_amount'), 0) }} Total</span>
            </div>
        </div>
        <a href="{{ route('finance.fee-structures.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Fee Structure
        </a>
    </div>

    <div class="card">
        <div class="card-hd">
            <div class="card-hd-left">
                <i class="fas fa-layer-group" style="color:var(--b);"></i>
                <h3>All Fee Structures</h3>
            </div>
            @if($structures->isNotEmpty())
                <span style="font-size:.78rem;color:#000;font-weight:bold;">{{ $structures->count() }}
                    record{{ $structures->count() !== 1 ? 's' : '' }}</span>
            @endif
        </div>

        @if($structures->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-layer-group"></i></div>
                <h4>No Fee Structures Yet</h4>
                <p>Create your first fee structure to start allocating fees to students.</p>
                <a href="{{ route('finance.fee-structures.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Fee Structure
                </a>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="fs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Year</th>
                            <th>Term</th>
                            <th>Class Level</th>
                            <th>Student Type</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($structures as $i => $s)
                            <tr>
                                <td style="color:var(--t3);font-size:.8rem;font-weight:600;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="struct-name">{{ $s->name }}</div>
                                    @if($s->notes)
                                        <div class="struct-note">{{ Str::limit($s->notes, 55) }}</div>
                                    @endif
                                </td>
                                <td><span class="badge badge-blue">{{ $s->academic_year }}</span></td>
                                <td><span class="badge badge-gray">{{ $s->termLabel() }}</span></td>
                                <td>
                                    @if($s->class_level)
                                        <span class="badge badge-blue"><i class="fas fa-chalkboard"></i> {{ Helper::recordMdname($s->class_level) }}</span>
                                    @else
                                        <span style="color:var(--t3);font-size:.82rem;">— All Classes</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->student_type === 'boarding')
                                        <span class="badge badge-amber"><i class="fas fa-building"></i> Boarding</span>
                                    @elseif($s->student_type === 'day')
                                        <span class="badge badge-green"><i class="fas fa-sun"></i> Day</span>
                                    @else
                                        <span class="badge badge-gray"><i class="fas fa-users"></i> All</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="amount-mono">UGX {{ number_format($s->total_amount, 0) }}</span>
                                </td>
                                <td>
                                    @if($s->is_active)
                                        <span class="badge badge-green"><i class="fas fa-circle"
                                                style="font-size:.45rem;vertical-align:middle;"></i> Active</span>
                                    @else
                                        <span class="badge badge-gray"><i class="fas fa-circle"
                                                style="font-size:.45rem;vertical-align:middle;"></i> Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="{{ route('finance.fee-structures.edit', $s->id) }}" class="btn btn-sm btn-outline"
                                            title="Edit Structure">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('finance.fee-structures.destroy', $s->id) }}"
                                            style="margin:0;" id="del-{{ $s->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                                onclick="confirmDelete({{ $s->id }}, '{{ addslashes($s->name) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    </div>
    </div>
    </div>

    @if(session('success'))
        <div style="position:fixed;bottom:1.5rem;right:1.5rem;background:#2f2ccb;color:#fff;padding:.85rem 1.4rem;border-radius:12px;font-size:.875rem;font-weight:600;box-shadow:0 8px 24px rgba(47,44,203,.35);z-index:9999;display:flex;align-items:center;gap:.5rem;"
            id="toast-msg">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        <script>setTimeout(() => { let t = document.getElementById('toast-msg'); if (t) { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; setTimeout(() => t.remove(), 400); } }, 3500);</script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Delete Fee Structure?',
                html: `<span style="color:#475569;">Are you sure you want to delete <strong>${name}</strong>? This cannot be undone.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) document.getElementById('del-' + id).submit();
            });
        }
    </script>
@endsection