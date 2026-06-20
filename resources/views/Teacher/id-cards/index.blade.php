{{-- resources/views/teacher/id-cards/index.blade.php --}}
<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --b: #2f2ccb;
            /* blue – main brand color */
            --bl: rgba(47, 44, 203, .10);
            --b2: #2420a8;
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --sh-lg: 0 10px 40px rgba(0, 0, 0, .12);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]):not([class*="bi"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        /* Hero Section - Blue Gradient */
        .fin-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2f2ccb 60%, #0d0c5e 100%);
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
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .fin-hero::after {
            content: '';
            position: absolute;
            bottom: -40px;
            right: 120px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .hero-title {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 .3rem;
        }

        .hero-sub {
            color: rgba(255, 255, 255, .7);
            font-size: .93rem;
            margin: 0;
        }

        .hero-actions {
            display: flex;
            gap: .75rem;
            margin-top: 1.25rem;
            flex-wrap: wrap;
        }

        /* Stat Cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: var(--surf);
            border-radius: var(--rad);
            padding: 1.2rem 1.4rem;
            box-shadow: var(--sh);
            border-left: 4px solid transparent;
        }

        .stat-card.blue {
            border-color: var(--b);
        }

        .stat-card.green {
            border-color: var(--g);
        }

        .stat-card.red {
            border-color: var(--r);
        }

        .stat-card.amber {
            border-color: var(--a);
        }

        .stat-label {
            font-size: .73rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--t3);
            margin-bottom: .3rem;
        }

        .stat-val {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--t1);
            line-height: 1;
        }

        .stat-icon {
            float: right;
            font-size: 1.4rem;
            margin-top: -2px;
        }

        /* Panel / Card */
        .panel {
            background: var(--surf);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        .panel-head {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .panel-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--t1);
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: var(--bg);
            border: 1.5px solid var(--brd);
            border-radius: 10px;
            padding: .45rem .9rem;
            min-width: 220px;
        }

        .search-bar input {
            border: none;
            background: transparent;
            outline: none;
            font-size: .9rem;
            color: var(--t1);
            width: 100%;
        }

        .search-bar i {
            color: var(--t3);
        }

        /* Table */
        .tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl th {
            background: #2c29ca;
            color: #ffffff;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .75rem 1.25rem;
            border-bottom: none;
            text-align: left;
        }

        .tbl td {
            padding: .85rem 1.25rem;
            border-bottom: 1px solid var(--brd);
            font-size: .9rem;
            color: var(--t2);
            vertical-align: middle;
        }

        .tbl tr:last-child td {
            border-bottom: none;
        }

        .tbl tr:hover td {
            background: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .25rem .65rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
        }

        .badge-active {
            background: var(--gl);
            color: var(--g);
        }

        .badge-revoked {
            background: var(--rl);
            color: var(--r);
        }

        .badge-expired {
            background: var(--al);
            color: var(--a);
        }

        .badge-blue {
            background: var(--bl);
            color: var(--b);
        }

        /* Avatar */
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            overflow: hidden;
            background: var(--bl);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--b);
            font-size: .85rem;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1.1rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: .15s;
        }

        .btn-primary,
        .btn-teal {
            background: var(--b);
            color: #fff;
        }

        .btn-primary:hover,
        .btn-teal:hover {
            background: var(--b2);
            color: #fff;
        }

        .btn-outline,
        .btn-ghost {
            background: transparent;
            color: var(--b);
            border: 1.5px solid var(--b);
        }

        .btn-outline:hover,
        .btn-ghost:hover {
            background: var(--bl);
        }

        .btn-success {
            background: var(--g);
            color: #fff;
        }

        .btn-success:hover {
            background: #047857;
        }

        .btn-danger {
            background: var(--r);
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-sm {
            padding: .35rem .75rem;
            font-size: .78rem;
            border-radius: 8px;
        }

        /* Icon Buttons */
        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: .15s;
        }

        .btn-view {
            background: var(--bl);
            color: var(--b);
        }

        .btn-view:hover {
            background: var(--b);
            color: #fff;
        }

        .btn-print {
            background: var(--gl);
            color: var(--g);
        }

        .btn-print:hover {
            background: var(--g);
            color: #fff;
        }

        .btn-revoke {
            background: var(--rl);
            color: var(--r);
        }

        .btn-revoke:hover {
            background: var(--r);
            color: #fff;
        }

        .action-btns {
            display: flex;
            gap: .4rem;
            align-items: center;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3.5rem 1rem;
            color: var(--t3);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: .4;
        }

        .empty-state p {
            font-size: .95rem;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            width: 96%;
            max-width: 420px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, .25);
        }

        .modal-box iframe {
            width: 100%;
            border: none;
            display: block;
        }

        /* Card Number */
        .card-no {
            font-family: 'DM Mono', monospace;
            font-size: .75rem;
            color: var(--b);
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: .25rem;
            align-items: center;
            flex-wrap: wrap;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--brd);
        }

        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 .5rem;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--t2);
            background: var(--surf);
            border: 1px solid var(--brd);
            text-decoration: none;
            transition: all .15s;
        }

        .pagination .page-link:hover {
            background: var(--bl);
            color: var(--b);
            border-color: rgba(47, 44, 203, .3);
        }

        .pagination .page-item.active .page-link {
            background: var(--b);
            color: #fff;
            border-color: var(--b);
        }

        .pagination .page-item.disabled .page-link {
            opacity: .4;
            pointer-events: none;
        }

        /* Responsive */
        @media(max-width: 640px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 1.25rem;
            }

            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }

            .tbl thead {
                display: none;
            }

            .tbl td {
                display: block;
                padding: .5rem 1rem;
            }

            .tbl td::before {
                content: attr(data-label) ": ";
                font-weight: 700;
                color: var(--t3);
                font-size: .72rem;
                text-transform: uppercase;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5" style="max-width:1280px;">

        {{-- Hero --}}
        <div class="fin-hero">
            <div style="position:relative;z-index:2;">
                <p class="hero-title"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Identity Cards</p>
                <p class="hero-sub">Manage, generate and print professional ID cards for all staff members</p>
                <div class="hero-actions">
                    @if(PermissionHelper::canFeature('generate_teacher_cards'))
                        <a href="{{ route('teacher-id-cards.create') }}" class="btn btn-teal">
                            <i class="fas fa-plus"></i> Generate Cards
                        </a>
                    @endif
                    @if(PermissionHelper::canFeature('print_teacher_cards'))
                        <a href="{{ route('teacher-id-cards.print.bulk') }}" class="btn btn-ghost"
                            style="background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.25);"
                            target="_blank">
                            <i class="fas fa-print"></i> Print All Active
                        </a>
                    @endif
                    @if(PermissionHelper::canFeature('verify_teacher_cards'))
                        <a href="{{ route('teacher-id-cards.scanner') }}" class="btn btn-ghost"
                            style="background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.25);">
                            <i class="fas fa-qrcode"></i> QR Scanner
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-grid" id="statsGrid">
            <div class="stat-card teal">
                <span class="stat-icon" style="color:var(--b);"><i class="fas fa-id-card"></i></span>
                <div class="stat-label">Total Cards</div>
                <div class="stat-val" id="stat-total">—</div>
            </div>
            <div class="stat-card green">
                <span class="stat-icon" style="color:var(--g);"><i class="fas fa-check-circle"></i></span>
                <div class="stat-label">Active</div>
                <div class="stat-val" id="stat-active">—</div>
            </div>
            <div class="stat-card red">
                <span class="stat-icon" style="color:var(--r);"><i class="fas fa-ban"></i></span>
                <div class="stat-label">Revoked</div>
                <div class="stat-val" id="stat-revoked">—</div>
            </div>
            <div class="stat-card amber">
                <span class="stat-icon" style="color:var(--a);"><i class="fas fa-chalkboard-teacher"></i></span>
                <div class="stat-label">Total Teachers</div>
                <div class="stat-val" id="stat-teachers">—</div>
            </div>
        </div>

        {{-- Table Panel --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title"><i class="fas fa-list mr-2" style="color:var(--b);"></i>All Teacher ID
                    Cards</span>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tableSearch" placeholder="Search name, card no…">
                </div>
            </div>

            @if($cards->count())
                <div style="overflow-x:auto;">
                    <table class="tbl" id="cardsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Teacher</th>
                                <th>Card Number</th>
                                <th>Year</th>
                                <th>Issue Date</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $i => $card)
                                @php $t = $card->teacher; @endphp
                                <tr>
                                    <td data-label="#">{{ $cards->firstItem() + $i }}</td>
                                    <td data-label="Teacher">
                                        <div style="display:flex;align-items:center;gap:.65rem;">
                                            <div class="avatar">
                                                @if($t && $t->teacher_profile && file_exists(public_path($t->teacher_profile)))
                                                    <img src="{{ asset($t->teacher_profile) }}" alt="">
                                                @else
                                                    {{ $t ? strtoupper(substr($t->firstname, 0, 1) . substr($t->surname, 0, 1)) : '?' }}
                                                @endif
                                            </div>
                                            <div>
                                                <div style="font-weight:700;color:var(--t1);">
                                                    {{ $t ? $t->firstname . ' ' . $t->surname : '—' }}
                                                </div>
                                                <div style="font-size:.75rem;color:var(--t3);">
                                                    {{ $t->employee_number ?? $t->phonenumber ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Card No"><span class="card-no">{{ $card->card_number }}</span></td>
                                    <td data-label="Year">{{ $card->academic_year }}</td>
                                    <td data-label="Issued">{{ $card->issue_date?->format('d M Y') ?? '—' }}</td>
                                    <td data-label="Expires"
                                        style="{{ $card->expiry_date && $card->expiry_date->isPast() ? 'color:#dc2626;font-weight:700;' : '' }}">
                                        {{ $card->expiry_date?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td data-label="Status">
                                        @if($card->status === 'active')
                                            <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.4rem;"></i>
                                                Active</span>
                                        @elseif($card->status === 'revoked')
                                            <span class="badge badge-revoked"><i class="fas fa-ban" style="font-size:.6rem;"></i>
                                                Revoked</span>
                                        @else
                                            <span class="badge badge-expired"><i class="fas fa-clock" style="font-size:.6rem;"></i>
                                                Expired</span>
                                        @endif
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-btns">
                                            @if(PermissionHelper::canFeature('print_teacher_cards'))
                                                <button class="btn btn-ghost btn-sm" onclick="openPreview({{ $card->id }})"
                                                    title="Preview">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="{{ route('teacher-id-cards.print', $card->id) }}" class="btn btn-teal btn-sm"
                                                    title="Download PDF" target="_blank">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                            @if($card->status === 'active' && PermissionHelper::canFeature('revoke_teacher_cards'))
                                                <button class="btn btn-danger btn-sm" onclick="revokeCard({{ $card->id }})"
                                                    title="Revoke">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @elseif($card->status === 'revoked' && PermissionHelper::canFeature('reactivate_teacher_cards'))
                                                <button class="btn btn-ghost btn-sm" style="color:var(--g);border-color:var(--gl);"
                                                    onclick="reactivateCard({{ $card->id }})" title="Reactivate">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:1rem 1.5rem;">
                    {{ $cards->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-id-card-alt"></i>
                    <p>No teacher ID cards generated yet.</p>
                    @if(PermissionHelper::canFeature('generate_teacher_cards'))
                        <a href="{{ route('teacher-id-cards.create') }}" class="btn btn-teal" style="margin-top:1rem;">
                            <i class="fas fa-plus"></i> Generate Now
                        </a>
                    @endif
                </div>
            @endif
        </div>

    </div>

    {{-- Preview Modal --}}
    <div class="modal-overlay" id="previewModal">
        <div class="modal-box">
            <iframe id="previewFrame" src="" height="560"></iframe>
        </div>
    </div>
    </div>
    </div>
    </div>

@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Load stats
        fetch('{{ route("teacher-id-cards.stats") }}')
            .then(r => r.json())
            .then(d => {
                document.getElementById('stat-total').textContent = d.total;
                document.getElementById('stat-active').textContent = d.active;
                document.getElementById('stat-revoked').textContent = d.revoked;
                document.getElementById('stat-teachers').textContent = d.totalTeachers;
            });

        // Table search
        document.getElementById('tableSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            const rows = document.querySelectorAll('#cardsTable tbody tr');
            rows.forEach(r => {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        // Preview modal
        function openPreview(id) {
            document.getElementById('previewFrame').src = `/teacher-id-cards/preview/${id}`;
            document.getElementById('previewModal').classList.add('show');
        }
        function closePreviewModal() {
            document.getElementById('previewModal').classList.remove('show');
            document.getElementById('previewFrame').src = '';
        }
        document.getElementById('previewModal').addEventListener('click', function (e) {
            if (e.target === this) closePreviewModal();
        });

        // Revoke
        function revokeCard(id) {
            Swal.fire({
                title: 'Revoke this ID card?',
                text: 'The teacher will no longer have a valid card.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, revoke it!'
            }).then(r => {
                if (!r.isConfirmed) return;
                fetch(`/teacher-id-cards/revoke/${id}`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                })
                    .then(res => res.json())
                    .then(d => {
                        Swal.fire('Revoked!', d.message, 'success').then(() => location.reload());
                    });
            });
        }

        // Reactivate
        function reactivateCard(id) {
            Swal.fire({
                title: 'Reactivate this card?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f766e',
                confirmButtonText: 'Yes, reactivate!'
            }).then(r => {
                if (!r.isConfirmed) return;
                fetch(`/teacher-id-cards/reactivate/${id}`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                })
                    .then(res => res.json())
                    .then(d => {
                        Swal.fire(d.status === 'success' ? 'Done!' : 'Error', d.message, d.status === 'success' ? 'success' : 'error')
                            .then(() => { if (d.status === 'success') location.reload(); });
                    });
            });
        }

        window.closePreviewModal = closePreviewModal;
    </script>
@endsection