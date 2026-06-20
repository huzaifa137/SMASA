@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Your existing styles here (same as provided) */
        :root {
            --lib-blue: #2c29ca;
            --lib-blue-l: rgba(44, 41, 202, .12);
            --lib-blue-d: #2420a8;
            --lib-rose: #f43f5e;
            --lib-rose-l: rgba(244, 63, 94, .12);
            --lib-green: #10b981;
            --lib-green-l: rgba(16, 185, 129, .12);
            --lib-amber: #f59e0b;
            --lib-amber-l: rgba(245, 158, 11, .12);
            --lib-violet: #7c3aed;
            --lib-violet-l: rgba(124, 58, 237, .12);
            --surface: #fff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 20px rgba(0, 0, 0, .05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        .lib-hero {
            background: linear-gradient(135deg, #1a1869 0%, #2c29ca 60%, #0d0c5e 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .lib-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%, transparent 70%);
        }

        .lib-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 30%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .05) 0%, transparent 70%);
        }

        .stat-chip {
            background: rgba(255, 255, 255, .12);
            border-radius: 12px;
            padding: .5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .85rem;
            font-weight: 600;
        }

        .lib-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .lib-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .lib-card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-1);
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .lib-card-header h3 i {
            color: var(--lib-blue);
        }

        .lib-card-body {
            padding: 1.5rem;
        }

        .btn-lib {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1rem;
            border-radius: 10px;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .2s;
        }

        .btn-primary-lib {
            background: var(--lib-blue);
            color: #fff;
        }

        .btn-primary-lib:hover {
            background: var(--lib-blue-d);
            color: #fff;
        }

        .btn-warning-lib {
            background: var(--lib-amber-l);
            color: var(--lib-amber);
        }

        .btn-warning-lib:hover {
            background: var(--lib-amber);
            color: #fff;
        }

        .btn-outline-lib {
            background: transparent;
            color: var(--text-2);
            border: 1px solid var(--border);
        }

        .btn-outline-lib:hover {
            background: var(--bg);
            border-color: var(--lib-blue);
            color: var(--lib-blue);
        }

        .lib-table {
            width: 100%;
            border-collapse: collapse;
        }

        .lib-table th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            color: #fff;
            background: #2c29ca;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
        }

        .lib-table td {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: .875rem;
            color: var(--text-1);
            vertical-align: middle;
        }

        .lib-table tr:last-child td {
            border-bottom: none;
        }

        .lib-table tr:hover td {
            background: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: .25rem .65rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
            animation: slideUp .25s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-1);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-2);
            margin-bottom: .4rem;
        }

        .form-control {
            width: 100%;
            padding: .6rem .85rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            transition: border-color .2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--lib-blue);
        }

        .alert {
            padding: .85rem 1rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: var(--lib-green-l);
            color: var(--lib-green);
            border-left: 4px solid var(--lib-green);
        }

        .alert-error {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
            border-left: 4px solid var(--lib-rose);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-3);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .lib-back-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--text-2);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }

        .lib-back-link:hover {
            color: var(--lib-blue);
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
        }

        .filter-bar .form-control {
            width: auto;
            min-width: 140px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            border-radius: 14px;
            padding: 1.25rem;
            text-align: center;
        }

        /* Fines - Stack layout on mobile */

/* Hero section */
.lib-hero {
    padding: 2rem 2.5rem;
}

/* Summary grid */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary-card {
    border-radius: 14px;
    padding: 1.25rem;
    text-align: center;
}

/* Fines table responsive */
.lib-table {
    min-width: 700px;
}

.lib-table th,
.lib-table td {
    padding: .75rem 1rem;
}

/* Filter bar */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    align-items: center;
}

.filter-bar .form-control {
    width: auto;
    min-width: 140px;
}

/* Tablet */
@media (max-width: 992px) {
    .lib-table th,
    .lib-table td {
        padding: .65rem .85rem;
        font-size: .8rem;
    }
    
    .filter-bar .form-control {
        min-width: 120px;
        font-size: .8rem;
    }
    
    .summary-grid {
        gap: .75rem;
    }
    
    .summary-card {
        padding: 1rem;
    }
    
    .summary-card div:first-child {
        font-size: 1.25rem !important;
    }
}

/* Tablet - stack vertically */
@media (max-width: 768px) {
    .lib-hero {
        padding: 1.25rem 1.5rem;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1.3rem !important;
    }
    
    .lib-hero [style*="font-size:.875rem;"] {
        font-size: .8rem !important;
    }
    
    .summary-grid {
        grid-template-columns: 1fr 1fr;
        gap: .75rem;
    }
    
    .summary-card {
        padding: 1rem;
    }
    
    .summary-card div:first-child {
        font-size: 1.25rem !important;
    }
    
    .lib-table {
        min-width: 650px;
        font-size: .8rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .5rem .75rem;
    }
    
    .lib-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: .75rem;
        padding: 1rem 1.25rem;
    }
    
    .lib-card-header h3 {
        font-size: .9rem;
    }
    
    .lib-card-header form {
        width: 100%;
    }
    
    .lib-card-body {
        padding: 1rem;
    }
    
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }
    
    .filter-bar .form-control {
        width: 100%;
        min-width: auto;
    }
    
    .filter-bar .btn-lib {
        width: 100%;
        justify-content: center;
    }
}

/* Mobile landscape */
@media (max-width: 576px) {
    [style*="padding:1.5rem;"] {
        padding: 0.75rem !important;
    }
    
    .lib-hero {
        padding: 1rem 1.25rem;
        border-radius: 18px;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1.1rem !important;
    }
    
    .lib-hero [style*="font-size:.875rem;"] {
        font-size: .75rem !important;
    }
    
    .summary-grid {
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
    }
    
    .summary-card {
        padding: .75rem .5rem;
        border-radius: 10px;
    }
    
    .summary-card div:first-child {
        font-size: 1rem !important;
    }
    
    .summary-card div:last-child {
        font-size: .7rem !important;
    }
    
    .lib-card-header {
        padding: .75rem 1rem;
    }
    
    .lib-card-header h3 {
        font-size: .85rem;
    }
    
    .lib-card-body {
        padding: .75rem 1rem;
    }
    
    .lib-table {
        min-width: 580px;
        font-size: .75rem;
    }
    
    .lib-table th {
        font-size: .65rem;
        padding: .4rem .6rem;
    }
    
    .lib-table td {
        padding: .4rem .6rem;
        font-size: .75rem;
    }
    
    .lib-table td:first-child {
        min-width: 100px;
    }
    
    .lib-table td:nth-child(2) {
        min-width: 100px;
        max-width: 120px;
    }
    
    .lib-table td:nth-child(3) {
        min-width: 70px;
    }
    
    .lib-table td:nth-child(4) {
        min-width: 70px;
    }
    
    .lib-table td:nth-child(5) {
        min-width: 70px;
    }
    
    .lib-table td:nth-child(6) {
        min-width: 70px;
    }
    
    .badge {
        font-size: .6rem;
        padding: .15rem .5rem;
    }
    
    /* Action buttons in table */
    .lib-table td:last-child {
        min-width: 100px;
    }
    
    .lib-table td:last-child .btn-lib {
        padding: .2rem .5rem;
        font-size: .65rem;
        min-height: 28px;
        min-width: 28px;
        margin-bottom: .2rem;
    }
    
    .lib-table td:last-child .btn-lib i {
        font-size: .65rem;
    }
    
    .lib-table td:last-child .btn-lib[title="Waive"] {
        padding: .2rem .4rem;
    }
    
    .lib-table td:last-child div[style*="display:flex"] {
        flex-wrap: wrap;
        gap: .3rem !important;
    }
    
    /* Form elements */
    .form-control {
        font-size: 14px;
        padding: .5rem .75rem;
    }
    
    .form-label {
        font-size: .75rem;
    }
    
    /* Buttons */
    .btn-lib {
        font-size: .75rem;
        padding: .4rem .75rem;
    }
    
    /* Modal */
    .modal-box {
        margin: 1rem;
        padding: 1.5rem;
        max-width: 100%;
    }
    
    .modal-title {
        font-size: 1rem;
    }
    
    /* Pagination */
    nav[role="navigation"] {
        font-size: .75rem;
    }
    
    nav[role="navigation"] .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    nav[role="navigation"] .page-link {
        padding: .3rem .5rem;
        font-size: .7rem;
    }
}

/* Very small screens */
@media (max-width: 400px) {
    [style*="padding:1.5rem;"] {
        padding: 0.5rem !important;
    }
    
    .lib-hero {
        padding: .75rem 1rem;
        border-radius: 14px;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1rem !important;
    }
    
    .summary-grid {
        grid-template-columns: 1fr 1fr 1fr;
        gap: .4rem;
    }
    
    .summary-card {
        padding: .5rem .3rem;
        border-radius: 8px;
    }
    
    .summary-card div:first-child {
        font-size: .85rem !important;
    }
    
    .summary-card div:last-child {
        font-size: .6rem !important;
    }
    
    .lib-table {
        min-width: 480px;
        font-size: .7rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .3rem .5rem;
    }
    
    .lib-table td:first-child {
        min-width: 80px;
    }
    
    .lib-table td:nth-child(2) {
        min-width: 70px;
        max-width: 80px;
    }
    
    .lib-table td:nth-child(3) {
        min-width: 60px;
    }
    
    .lib-table td:nth-child(4) {
        min-width: 60px;
    }
    
    .lib-table td:nth-child(5) {
        min-width: 55px;
    }
    
    .lib-table td:nth-child(6) {
        min-width: 60px;
    }
    
    .lib-card-header {
        padding: .6rem .75rem;
    }
    
    .lib-card-body {
        padding: .6rem .75rem;
    }
    
    .lib-table td:last-child {
        min-width: 80px;
    }
    
    .lib-table td:last-child .btn-lib {
        padding: .15rem .35rem;
        font-size: .55rem;
        min-height: 22px;
        min-width: 22px;
    }
    
    .lib-table td:last-child .btn-lib i {
        font-size: .55rem;
    }
    
    .modal-box {
        padding: .75rem;
        margin: .5rem;
    }
    
    .modal-title {
        font-size: .85rem;
    }
}

/* Fix horizontal scroll on mobile */
@media (max-width: 768px) {
    [style*="overflow-x:auto;"] {
        -webkit-overflow-scrolling: touch;
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }
}

/* Improve touch targets on mobile */
@media (max-width: 576px) {
    .btn-lib,
    .lib-table td .btn-lib,
    button.btn-lib {
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }
    
    .lib-card-header .btn-lib {
        width: 100%;
        justify-content: center;
    }
    
    .lib-table td:last-child button {
        min-height: 32px;
        min-width: 32px;
    }
    
    .lib-table td:last-child div[style*="display:flex"] {
        justify-content: flex-start;
    }
    
    /* Make filter buttons full width */
    .filter-bar .btn-lib {
        width: 100%;
        justify-content: center;
    }
    
    /* Select dropdowns on mobile */
    select.form-control {
        font-size: 16px;
    }
    
    /* Summary cards on smallest screens */
    .summary-grid {
        gap: .4rem;
    }
}

/* Smooth transitions */
.lib-card,
.lib-hero,
.btn-lib,
.summary-card {
    transition: all 0.2s ease;
}

/* Paid date text on mobile */
@media (max-width: 576px) {
    .lib-table td:last-child span[style*="font-size:.75rem;"] {
        font-size: .65rem !important;
    }
}
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem;">
        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;">
                <i class="fas fa-coins" style="color:#a5b4fc;margin-right:.5rem;"></i>Fines
            </div>
            <div style="font-size:.875rem;opacity:.7;">Manage overdue fines and payments</div>
        </div>

        {{-- Summary Cards --}}
        <div class="summary-grid">
            <div class="summary-card" style="background:var(--lib-rose-l);">
                <div style="font-size:1.5rem;font-weight:800;color:var(--lib-rose);">{{ number_format($totalUnpaid, 2) }}
                </div>
                <div style="font-size:.8rem;color:var(--lib-rose);font-weight:600;margin-top:.25rem;">Unpaid</div>
            </div>
            <div class="summary-card" style="background:var(--lib-green-l);">
                <div style="font-size:1.5rem;font-weight:800;color:var(--lib-green);">{{ number_format($totalPaid, 2) }}
                </div>
                <div style="font-size:.8rem;color:var(--lib-green);font-weight:600;margin-top:.25rem;">Collected</div>
            </div>
            <div class="summary-card" style="background:var(--lib-violet-l);">
                <div style="font-size:1.5rem;font-weight:800;color:var(--lib-violet);">{{ number_format($totalWaived, 2) }}
                </div>
                <div style="font-size:.8rem;color:var(--lib-violet);font-weight:600;margin-top:.25rem;">Waived</div>
            </div>
        </div>

        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-list" style="color:var(--lib-blue);"></i> Fine Records</h3>
                <form method="GET" class="filter-bar" id="filterForm">
                    <select name="status" class="form-control" id="statusFilter">
                        <option value="">All</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="waived" {{ request('status') == 'waived' ? 'selected' : '' }}>Waived</option>
                    </select>
                    <button type="submit" class="btn-lib btn-primary-lib"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('library.fines') }}" class="btn-lib btn-outline-lib">Clear</a>
                </form>
            </div>
            <div style="overflow-x:auto;">
                @if($fines->count())
                    <table class="lib-table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Book</th>
                                <th>Amount</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fines as $fine)
                                <tr>
                                    <td>
                                        <div style="font-weight:600;">{{ $fine->member->name ?? '—' }}</div>
                                        <div style="font-size:.75rem;color:var(--text-3);">
                                            {{ ucfirst($fine->member->member_type ?? '') }}</div>
                                    </td>
                                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $fine->borrowing->book->title ?? '—' }}</td>
                                    <td style="font-weight:700;color:var(--lib-rose);">{{ number_format($fine->amount, 2) }}</td>
                                    <td style="color:var(--text-2);font-size:.8rem;">{{ $fine->reason ?? 'Overdue' }}</td>
                                    <td>
                                        @if($fine->status === 'unpaid')
                                            <span class="badge"
                                                style="background:var(--lib-rose-l);color:var(--lib-rose);">Unpaid</span>
                                        @elseif($fine->status === 'paid')
                                            <span class="badge"
                                                style="background:var(--lib-green-l);color:var(--lib-green);">Paid</span>
                                        @else
                                            <span class="badge"
                                                style="background:var(--lib-violet-l);color:var(--lib-violet);">Waived</span>
                                        @endif
                                    </td>
                                    <td style="color:var(--text-2);font-size:.8rem;">{{ $fine->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($fine->status === 'unpaid')
                                            <div style="display:flex;gap:.4rem;">
                                                <button onclick="confirmPay({{ $fine->id }}, {{ $fine->amount }})" 
                                                        class="btn-lib btn-primary-lib"
                                                        style="padding:.3rem .65rem;">
                                                    <i class="fas fa-check"></i> Pay
                                                </button>
                                                <button onclick="openWaive({{ $fine->id }}, {{ $fine->amount }})" 
                                                        class="btn-lib btn-warning-lib"
                                                        style="padding:.3rem .65rem;">
                                                    <i class="fas fa-hand-holding-usd"></i> Waive
                                                </button>
                                            </div>
                                        @else
                                            <span style="font-size:.75rem;color:var(--text-3);">
                                                @if($fine->paid_date) 
                                                    {{ \Carbon\Carbon::parse($fine->paid_date)->format('d M Y') }}
                                                @endif
                                                @if($fine->waive_reason)
                                                    <span title="{{ $fine->waive_reason }}"><i class="fas fa-info-circle"></i></span>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="padding:1rem 1.5rem;">{{ $fines->appends(request()->all())->links() }}</div>
                @else
                    <div class="empty-state"><i class="fas fa-coins"></i>No fine records found.</div>
                @endif
            </div>
        </div>
    </div>
     </div>
      </div>

    {{-- Waive Modal --}}
    <div class="modal-overlay" id="waiveModal">
        <div class="modal-box">
            <div class="modal-title"><i class="fas fa-hand-holding-usd" style="color:var(--lib-blue);"></i> Waive Fine</div>
            <form method="POST" id="waiveForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Fine Amount</label>
                    <input type="text" id="waiveAmount" class="form-control" readonly style="background:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label class="form-label">Reason for waiving *</label>
                    <textarea name="waive_reason" class="form-control" rows="3" placeholder="Enter reason…" id="waiveReason" required></textarea>
                </div>
                <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;">
                    <button type="button" onclick="closeWaiveModal()" class="btn-lib btn-outline-lib">Cancel</button>
                    <button type="submit" class="btn-lib btn-warning-lib" id="waiveBtn"><i class="fas fa-check"></i> Confirm Waive</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert Toast configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    let currentFineId = null;
    let currentFineAmount = null;

    // Pay Fine Function
    function confirmPay(fineId, amount) {
        Swal.fire({
            title: 'Pay Fine?',
            html: `Are you sure you want to mark this fine as <strong>paid</strong>?<br>Amount: <strong style="color:var(--lib-rose);">UGX ${amount.toLocaleString()}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, mark as paid!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process the payment',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`/library/fines/${fineId}/pay`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Paid!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to process payment');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message
                    });
                });
            }
        });
    }

    // Open Waive Modal
    function openWaive(fineId, amount) {
        currentFineId = fineId;
        currentFineAmount = amount;
        document.getElementById('waiveAmount').value = `UGX ${amount.toLocaleString()}`;
        document.getElementById('waiveReason').value = '';
        document.getElementById('waiveForm').action = `/library/fines/${fineId}/waive`;
        document.getElementById('waiveModal').classList.add('active');
    }

    function closeWaiveModal() {
        document.getElementById('waiveModal').classList.remove('active');
        currentFineId = null;
        currentFineAmount = null;
    }

    // Waive Fine Form Submission
    document.getElementById('waiveForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const waiveReason = document.getElementById('waiveReason').value.trim();
        
        if (!waiveReason) {
            Toast.fire({
                icon: 'error',
                title: 'Please enter a reason for waiving the fine'
            });
            return;
        }
        
        Swal.fire({
            title: 'Waive Fine?',
            html: `Are you sure you want to <strong>waive</strong> this fine?<br>Amount: <strong style="color:var(--lib-amber);">UGX ${currentFineAmount.toLocaleString()}</strong><br><br>Reason: ${waiveReason}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, waive it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we waive the fine',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Waived!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to waive fine');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message
                    });
                });
            }
        });
    });

    // Close modal on backdrop click
    document.getElementById('waiveModal').addEventListener('click', function (e) { 
        if (e.target === this) closeWaiveModal(); 
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeWaiveModal();
        }
    });
</script>
@endsection