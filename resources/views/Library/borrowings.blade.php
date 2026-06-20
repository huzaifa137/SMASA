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

        .btn-danger-lib {
            background: var(--lib-rose-l);
            color: var(--lib-rose);
        }

        .btn-danger-lib:hover {
            background: var(--lib-rose);
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
            max-width: 520px;
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

        .overdue-row td {
            background: #fff5f5 !important;
        }

        /* Borrowings - Stack layout on mobile */

/* Main grid layout - side by side on large screens */
[style*="display:grid;grid-template-columns:1fr 320px;"] {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}

/* Hero section */
.lib-hero {
    padding: 2rem 2.5rem;
}

/* Borrowings table responsive */
.lib-table {
    min-width: 800px;
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

/* Stat chips */
.stat-chip {
    padding: .5rem 1rem;
    font-size: .85rem;
}

/* Tablet - reduce right column */
@media (max-width: 992px) {
    [style*="display:grid;grid-template-columns:1fr 320px;"] {
        grid-template-columns: 1fr 260px;
        gap: 1.25rem;
    }
    
    .lib-table th,
    .lib-table td {
        padding: .65rem .85rem;
        font-size: .8rem;
    }
    
    .filter-bar .form-control {
        min-width: 120px;
        font-size: .8rem;
    }
}

/* Tablet - stack vertically */
@media (max-width: 768px) {
    [style*="display:grid;grid-template-columns:1fr 320px;"] {
        grid-template-columns: 1fr !important;
        gap: 1.25rem;
    }
    
    /* Quick issue sidebar moves below */
    .lib-card[style*="position:sticky;"] {
        position: relative !important;
        top: 0 !important;
        width: 100% !important;
    }
    
    .lib-hero {
        padding: 1.25rem 1.5rem;
    }
    
    .lib-hero [style*="font-size:1.6rem;"] {
        font-size: 1.3rem !important;
    }
    
    .lib-hero [style*="font-size:.875rem;"] {
        font-size: .8rem !important;
    }
    
    .lib-hero [style*="display:flex;gap:.75rem;flex-wrap:wrap;"] {
        gap: .5rem;
    }
    
    .stat-chip {
        font-size: .75rem;
        padding: .4rem .75rem;
    }
    
    .lib-table {
        min-width: 700px;
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
    
    .lib-card-header .btn-lib {
        width: 100%;
        justify-content: center;
    }
    
    .lib-card-body {
        padding: 1rem;
    }
    
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
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
        min-width: 600px;
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
        min-width: 120px;
    }
    
    .lib-table td:nth-child(3),
    .lib-table td:nth-child(4) {
        min-width: 85px;
    }
    
    .badge {
        font-size: .6rem;
        padding: .15rem .5rem;
    }
    
    /* Action buttons in table */
    .lib-table td:last-child {
        min-width: 120px;
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
    
    .lib-table {
        min-width: 500px;
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
        min-width: 90px;
    }
    
    .lib-table td:nth-child(3),
    .lib-table td:nth-child(4) {
        min-width: 70px;
    }
    
    .lib-card-header {
        padding: .6rem .75rem;
    }
    
    .lib-card-body {
        padding: .6rem .75rem;
    }
    
    .lib-table td:last-child {
        min-width: 100px;
    }
    
    .lib-table td:last-child .btn-lib {
        padding: .15rem .4rem;
        font-size: .6rem;
        min-height: 24px;
        min-width: 24px;
    }
    
    .lib-table td:last-child .btn-lib i {
        font-size: .6rem;
    }
    
    .stat-chip {
        font-size: .65rem;
        padding: .3rem .6rem;
    }
    
    .stat-chip i {
        font-size: .65rem;
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
    button.btn-lib,
    .stat-chip {
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
    
    /* Make filter buttons full width */
    .filter-bar .btn-lib {
        width: 100%;
        justify-content: center;
    }
}

/* Smooth transitions */
.lib-card,
.lib-hero,
.btn-lib,
.stat-chip {
    transition: all 0.2s ease;
}

/* Overdue row highlight on mobile - keep visible */
@media (max-width: 576px) {
    .overdue-row td {
        background: #fff5f5 !important;
    }
}
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem;">

        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .5rem;"><i class="fas fa-book-reader"
                    style="color:var(--lib-blue);margin-right:.5rem;"></i>Borrowings</div>
            <div style="font-size:.875rem;opacity:.7;margin-bottom:1rem;">Track and manage all book loans</div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <span class="stat-chip"><i class="fas fa-clock"></i> {{ $borrowings->total() }} total records</span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">

            {{-- Borrowings Table --}}
            <div>
                {{-- Filters --}}
                <div class="lib-card" style="margin-bottom:1.25rem;">
                    <div class="lib-card-body" style="padding:1rem 1.5rem;">
                        <form method="GET" class="filter-bar" id="filterForm">
                            <input type="text" name="search" class="form-control" placeholder="Search member or book…"
                                value="{{ request('search') }}">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                                <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                            </select>
                            <button type="submit" class="btn-lib btn-primary-lib"><i class="fas fa-filter"></i>
                                Filter</button>
                            <a href="{{ route('library.borrowings') }}" class="btn-lib btn-outline-lib">Clear</a>
                        </form>
                    </div>
                </div>

                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-list" style="color:var(--lib-blue);"></i> Borrowing Records</h3>
                        <button onclick="openBorrowModal()" class="btn-lib btn-primary-lib"><i class="fas fa-plus"></i> Issue Book</button>
                    </div>
                    <div style="overflow-x:auto;">
                        @if($borrowings->count())
                            <table class="lib-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Book</th>
                                        <th>Borrow Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Renewals</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrowings as $b)
                                        @php
                                            $isOverdue = $b->status === 'borrowed' && $b->due_date < now()->toDateString();
                                        @endphp
                                        <tr class="{{ $isOverdue ? 'overdue-row' : '' }}">
                                            <td>
                                                <div style="font-weight:600;">{{ $b->member->name ?? '—' }}</div>
                                                <div style="font-size:.75rem;color:var(--text-3);">
                                                    {{ ucfirst($b->member->member_type ?? '') }}</div>
                                            </td>
                                            <td>
                                                <div
                                                    style="font-weight:500;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                    {{ $b->book->title ?? '—' }}</div>
                                                <div style="font-size:.75rem;color:var(--text-3);">{{ $b->book->isbn ?? '' }}</div>
                                            </td>
                                            <td style="color:var(--text-2);">
                                                {{ \Carbon\Carbon::parse($b->borrow_date)->format('d M Y') }}</td>
                                            <td>
                                                <span
                                                    style="color:{{ $isOverdue ? 'var(--lib-rose)' : 'var(--text-2)' }};font-weight:{{ $isOverdue ? '700' : '400' }};">
                                                    {{ \Carbon\Carbon::parse($b->due_date)->format('d M Y') }}
                                                </span>
                                                @if($isOverdue)
                                                    <div style="font-size:.7rem;color:var(--lib-rose);">
                                                        {{ \Carbon\Carbon::parse($b->due_date)->diffForHumans() }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($b->status === 'borrowed')
                                                    <span class="badge"
                                                        style="background:var(--lib-blue-l);color:var(--lib-blue);">Borrowed</span>
                                                @elseif($b->status === 'returned')
                                                    <span class="badge"
                                                        style="background:var(--lib-green-l);color:var(--lib-green);">Returned</span>
                                                @elseif($b->status === 'overdue')
                                                    <span class="badge"
                                                        style="background:var(--lib-rose-l);color:var(--lib-rose);">Overdue</span>
                                                @elseif($b->status === 'lost')
                                                    <span class="badge"
                                                        style="background:var(--lib-amber-l);color:var(--lib-amber);">Lost</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center;color:var(--text-2);">{{ $b->renewals }}</td>
                                            <td>
                                                <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                                                    @if($b->status === 'borrowed' || $b->status === 'overdue')
                                                        <button onclick="confirmReturn({{ $b->id }})" class="btn-lib btn-primary-lib"
                                                                style="padding:.3rem .7rem;" title="Return">
                                                            <i class="fas fa-undo"></i> Return
                                                        </button>
                                                        <button onclick="confirmRenew({{ $b->id }})" class="btn-lib btn-warning-lib"
                                                                style="padding:.3rem .7rem;" title="Renew">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                        <button onclick="confirmMarkLost({{ $b->id }})" class="btn-lib btn-danger-lib"
                                                                style="padding:.3rem .7rem;" title="Mark Lost">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                        </button>
                                                    @else
                                                        <span style="font-size:.75rem;color:var(--text-3);">
                                                            @if($b->return_date) Returned
                                                            {{ \Carbon\Carbon::parse($b->return_date)->format('d M Y') }} @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div style="padding:1rem 1.5rem;">{{ $borrowings->appends(request()->all())->links() }}</div>
                        @else
                            <div class="empty-state"><i class="fas fa-book-reader"></i>No borrowing records found.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Issue Book Sidebar --}}
            <div class="lib-card" style="position:sticky;top:1.5rem;">
                <div class="lib-card-header">
                    <h3><i class="fas fa-hand-holding-heart" style="color:var(--lib-blue);"></i> Quick Issue</h3>
                </div>
                <div class="lib-card-body">
                    <form id="issueBookForm">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Member *</label>
                            <select name="member_id" class="form-control" required id="member_id">
                                <option value="">— Select Member —</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ ucfirst($m->member_type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Book *</label>
                            <select name="book_id" class="form-control" required id="book_id">
                                <option value="">— Select Book —</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}" data-available="{{ $book->available_copies }}">
                                        {{ $book->title }} ({{ $book->available_copies }} avail.)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" required id="due_date"
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes…" id="notes"></textarea>
                        </div>
                        <button type="submit" class="btn-lib btn-primary-lib" style="width:100%;justify-content:center;" id="issueBtn">
                            <i class="fas fa-book"></i> Issue Book
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Issue Book Modal --}}
    <div class="modal-overlay" id="borrowModal">
        <div class="modal-box">
            <div class="modal-title">
                <i class="fas fa-hand-holding-heart" style="color:var(--lib-blue);"></i> Issue Book
            </div>
            <form id="modalIssueForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Member <span style="color:var(--lib-rose)">*</span></label>
                    <select name="member_id" class="form-control" required id="modal_member_id">
                        <option value="">— Select Member —</option>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ ucfirst($m->member_type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Book <span style="color:var(--lib-rose)">*</span></label>
                    <select name="book_id" class="form-control" required id="modal_book_id">
                        <option value="">— Select Book —</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}" data-available="{{ $book->available_copies }}">
                                {{ $book->title }} ({{ $book->available_copies }} avail.)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date <span style="color:var(--lib-rose)">*</span></label>
                    <input type="date" name="due_date" class="form-control" required id="modal_due_date"
                        min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes…" id="modal_notes"></textarea>
                </div>
                <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;">
                    <button type="button" onclick="closeBorrowModal()" class="btn-lib btn-outline-lib">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-lib btn-primary-lib" id="modalIssueBtn">
                        <i class="fas fa-book"></i> Issue Book
                    </button>
                </div>
            </form>
        </div>
    </div>
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

    function openBorrowModal() {
        document.getElementById('borrowModal').classList.add('active');
        // Reset form
        document.getElementById('modalIssueForm').reset();
    }

    function closeBorrowModal() {
        document.getElementById('borrowModal').classList.remove('active');
    }

    // Issue Book Function (for both sidebar and modal)
    async function issueBook(formData, buttonElement) {
        const originalHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Issuing...';

        try {
            const response = await fetch('{{ route("library.borrowings.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Issued!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Failed to issue book');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: error.message
            });
        } finally {
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalHtml;
        }
    }

    // Sidebar Issue Form
    document.getElementById('issueBookForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const memberId = document.getElementById('member_id').value;
        const bookId = document.getElementById('book_id').value;
        const dueDate = document.getElementById('due_date').value;
        
        if (!memberId || !bookId || !dueDate) {
            Toast.fire({ icon: 'error', title: 'Please fill all required fields' });
            return;
        }
        
        Swal.fire({
            title: 'Issue Book?',
            text: 'Are you sure you want to issue this book to the selected member?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2c29ca',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, issue it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                issueBook(formData, document.getElementById('issueBtn'));
            }
        });
    });

    // Modal Issue Form
    document.getElementById('modalIssueForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const memberId = document.getElementById('modal_member_id').value;
        const bookId = document.getElementById('modal_book_id').value;
        const dueDate = document.getElementById('modal_due_date').value;
        
        if (!memberId || !bookId || !dueDate) {
            Toast.fire({ icon: 'error', title: 'Please fill all required fields' });
            return;
        }
        
        Swal.fire({
            title: 'Issue Book?',
            text: 'Are you sure you want to issue this book to the selected member?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2c29ca',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, issue it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                issueBook(formData, document.getElementById('modalIssueBtn'));
            }
        });
    });

    // Return Book Function
    function confirmReturn(borrowingId) {
        Swal.fire({
            title: 'Return Book?',
            text: 'Are you sure you want to return this book? Any overdue fines will be generated.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, return it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process the return',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`/library/borrowings/${borrowingId}/return`, {
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
                            title: 'Returned!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to return book');
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: error.message });
                });
            }
        });
    }

    // Renew Book Function
    function confirmRenew(borrowingId) {
        Swal.fire({
            title: 'Renew Book?',
            text: 'Are you sure you want to renew this book? The due date will be extended.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, renew it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we renew the book',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`/library/borrowings/${borrowingId}/renew`, {
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
                            title: 'Renewed!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to renew book');
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: error.message });
                });
            }
        });
    }

    // Mark Lost Function
    function confirmMarkLost(borrowingId) {
        Swal.fire({
            title: 'Mark as Lost?',
            text: 'Are you sure you want to mark this book as lost? This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            cancelButtonColor: '#2c29ca',
            confirmButtonText: 'Yes, mark as lost!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the status',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`/library/borrowings/${borrowingId}/lost`, {
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
                            title: 'Updated!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to mark book as lost');
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: error.message });
                });
            }
        });
    }

    // Close modal on backdrop click
    document.getElementById('borrowModal').addEventListener('click', function (e) {
        if (e.target === this) closeBorrowModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeBorrowModal();
        }
    });
</script>
@endsection