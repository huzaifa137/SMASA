@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Your existing styles here (same as provided) */
        :root {
            --lib-le: #2c29ca;
            --lib-le-l: rgba(44, 41, 202, .12);
            --lib-le-d: #2420a8;
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
            color: var(--lib-le);
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
            background: var(--lib-le);
            color: #fff;
        }

        .btn-primary-lib:hover {
            background: var(--lib-le-d);
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

        .btn-success-lib {
            background: var(--lib-green-l);
            color: var(--lib-green);
        }

        .btn-success-lib:hover {
            background: var(--lib-green);
            color: #fff;
        }

        .btn-outline-lib {
            background: transparent;
            color: var(--text-2);
            border: 1px solid var(--border);
        }

        .btn-outline-lib:hover {
            background: var(--bg);
            border-color: var(--lib-le);
            color: var(--lib-le);
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
            max-width: 460px;
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
            border-color: var(--lib-le);
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

        /* Book Requests - Stack layout on mobile */

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

/* Requests table responsive */
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
    
    /* Request sidebar moves below */
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
        min-width: 120px;
        max-width: 140px;
    }
    
    .lib-table td:nth-child(3) {
        min-width: 80px;
    }
    
    .lib-table td:nth-child(4) {
        min-width: 80px;
    }
    
    .lib-table td:nth-child(5) {
        min-width: 80px;
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
    
    .lib-table td:last-child div[style*="display:flex"] {
        flex-wrap: wrap;
        gap: .3rem !important;
    }
    
    .lib-table td:last-child .btn-lib[title="Fulfill"] {
        font-size: .6rem;
        padding: .2rem .4rem;
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
    
    /* Reason text on mobile */
    .lib-table td:last-child div[title] {
        max-width: 100px;
        font-size: .65rem;
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
        min-width: 90px;
        max-width: 100px;
    }
    
    .lib-table td:nth-child(3) {
        min-width: 60px;
    }
    
    .lib-table td:nth-child(4) {
        min-width: 60px;
    }
    
    .lib-table td:nth-child(5) {
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
    
    .lib-table td:last-child .btn-lib[title="Fulfill"] {
        font-size: .55rem;
        padding: .15rem .3rem;
    }
    
    .lib-table td:last-child div[title] {
        max-width: 70px;
        font-size: .6rem;
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
}

/* Smooth transitions */
.lib-card,
.lib-hero,
.btn-lib {
    transition: all 0.2s ease;
}

/* Non-member message card */
.lib-card .lib-card-body[style*="text-align:center"] {
    padding: 1.5rem !important;
}

.lib-card .lib-card-body[style*="text-align:center"] i {
    color: var(--text-3);
}

@media (max-width: 576px) {
    .lib-card .lib-card-body[style*="text-align:center"] {
        padding: 1rem !important;
    }
    
    .lib-card .lib-card-body[style*="text-align:center"] i {
        font-size: 2rem !important;
    }
    
    .lib-card .lib-card-body[style*="text-align:center"] div {
        font-size: .8rem !important;
    }
}
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem;">
        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;">
                <i class="fas fa-inbox" style="color:#a5b4fc;margin-right:.5rem;"></i>Book Requests
            </div>
            <div style="font-size:.875rem;opacity:.7;">Review and manage book purchase requests from members</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">
            <div>
                {{-- Filters --}}
                <div class="lib-card" style="margin-bottom:1.25rem;">
                    <div class="lib-card-body" style="padding:1rem 1.5rem;">
                        <form method="GET" class="filter-bar" id="filterForm">
                            <select name="status" class="form-control" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                            </select>
                            <button type="submit" class="btn-lib btn-primary-lib"><i class="fas fa-filter"></i> Filter</button>
                            <a href="{{ route('library.book-requests') }}" class="btn-lib btn-outline-lib">Clear</a>
                        </form>
                    </div>
                </div>

                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-list" style="color:var(--lib-blue);"></i> All Requests</h3>
                        <span style="font-size:.8rem;color:var(--text-3);">{{ $requests->total() }} total</span>
                    </div>
                    <div style="overflow-x:auto;">
                        @if($requests->count())
                            <table class="lib-table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $req)
                                        <tr>
                                            <td>
                                                <div style="font-weight:600;">{{ $req->member->name ?? '—' }}</div>
                                                <div style="font-size:.75rem;color:var(--text-3);">
                                                    {{ ucfirst($req->member->member_type ?? '') }}</div>
                                            </td>
                                            <td>
                                                <div style="font-weight:500;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                    {{ $req->book_title }}</div>
                                                @if($req->isbn)
                                                    <div style="font-size:.75rem;color:var(--text-3);">ISBN: {{ $req->isbn }}</div>
                                                @endif
                                            </td>
                                            <td style="color:var(--text-2);">{{ $req->author ?? '—' }}</td>
                                            <td>
                                                @php
                                                    $map = [
                                                        'pending' => ['var(--lib-amber-l)', 'var(--lib-amber)'],
                                                        'approved' => ['var(--lib-blue-l)', 'var(--lib-blue)'],
                                                        'rejected' => ['var(--lib-rose-l)', 'var(--lib-rose)'],
                                                        'fulfilled' => ['var(--lib-green-l)', 'var(--lib-green)']
                                                    ];
                                                    [$bg, $c] = ($map[$req->status] ?? $map['pending']);
                                                @endphp
                                                <span class="badge" style="background:{{ $bg }};color:{{ $c }};">{{ ucfirst($req->status) }}</span>
                                            </td>
                                            <td style="color:var(--text-2);font-size:.8rem;">{{ $req->created_at->format('d M Y') }}</td>
                                            <td>
                                                @if($req->status === 'pending')
                                                    <div style="display:flex;gap:.4rem;">
                                                        <button onclick="updateStatus({{ $req->id }}, 'approved', '{{ addslashes($req->book_title) }}')" 
                                                                class="btn-lib btn-success-lib"
                                                                style="padding:.3rem .65rem;" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button onclick="updateStatus({{ $req->id }}, 'rejected', '{{ addslashes($req->book_title) }}')" 
                                                                class="btn-lib btn-danger-lib"
                                                                style="padding:.3rem .65rem;" title="Reject">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                @elseif($req->status === 'approved')
                                                    <button onclick="updateStatus({{ $req->id }}, 'fulfilled', '{{ addslashes($req->book_title) }}')" 
                                                            class="btn-lib btn-primary-lib"
                                                            style="padding:.3rem .65rem;" title="Fulfill">
                                                        <i class="fas fa-check-double"></i> Fulfill
                                                    </button>
                                                @endif
                                                @if($req->reason)
                                                    <div style="font-size:.72rem;color:var(--text-3);margin-top:.25rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                                        title="{{ $req->reason }}">
                                                        <i class="fas fa-quote-left" style="font-size:.6rem;"></i> {{ $req->reason }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div style="padding:1rem 1.5rem;">{{ $requests->appends(request()->all())->links() }}</div>
                        @else
                            <div class="empty-state"><i class="fas fa-inbox"></i>No book requests found.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Submit Request (shown if member) --}}
            @if($member)
                <div class="lib-card" style="position:sticky;top:1.5rem;">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-plus-circle" style="color:var(--lib-blue);"></i> Request a Book</h3>
                    </div>
                    <div class="lib-card-body">
                        <form id="bookRequestForm">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Book Title *</label>
                                <input type="text" name="book_title" id="book_title" class="form-control" placeholder="e.g. Atomic Habits" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Author</label>
                                <input type="text" name="author" id="author" class="form-control" placeholder="e.g. James Clear">
                            </div>
                            <div class="form-group">
                                <label class="form-label">ISBN</label>
                                <input type="text" name="isbn" id="isbn" class="form-control" placeholder="Optional">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Reason</label>
                                <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Why do you need this book?"></textarea>
                            </div>
                            <button type="submit" class="btn-lib btn-primary-lib" style="width:100%;justify-content:center;" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="lib-card" style="position:sticky;top:1.5rem;">
                    <div class="lib-card-body" style="text-align:center;color:var(--text-3);padding:2rem;">
                        <i class="fas fa-user-slash" style="font-size:2.5rem;display:block;margin-bottom:1rem;"></i>
                        <div style="font-size:.875rem;">Only library members can submit book requests.</div>
                    </div>
                </div>
            @endif
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

    // Submit Book Request
    @if($member)
    document.getElementById('bookRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const bookTitle = document.getElementById('book_title').value.trim();
        
        if (!bookTitle) {
            Toast.fire({
                icon: 'error',
                title: 'Please enter the book title'
            });
            return;
        }
        
        Swal.fire({
            title: 'Submit Book Request?',
            html: `Are you sure you want to request <strong>"${bookTitle}"</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2c29ca',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Yes, submit request!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Submitting Request...',
                    text: 'Please wait while we submit your request',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                const submitBtn = document.getElementById('submitBtn');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                
                fetch('{{ route("library.book-requests.store") }}', {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Request Submitted!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to submit request');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                });
            }
        });
    });
    @endif

    // Update Request Status
    function updateStatus(requestId, newStatus, bookTitle) {
        let title = '';
        let text = '';
        let confirmText = '';
        let confirmColor = '';
        let icon = 'question';
        
        switch(newStatus) {
            case 'approved':
                title = 'Approve Request?';
                text = `Are you sure you want to approve the request for "${bookTitle}"?`;
                confirmText = 'Yes, approve!';
                confirmColor = '#10b981';
                break;
            case 'rejected':
                title = 'Reject Request?';
                text = `Are you sure you want to reject the request for "${bookTitle}"?`;
                confirmText = 'Yes, reject!';
                confirmColor = '#f43f5e';
                icon = 'warning';
                break;
            case 'fulfilled':
                title = 'Mark as Fulfilled?';
                text = `Are you sure you want to mark "${bookTitle}" as fulfilled? This means the book has been added to the library.`;
                confirmText = 'Yes, mark as fulfilled!';
                confirmColor = '#2c29ca';
                break;
        }
        
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the status',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(`/library/book-requests/${requestId}/review`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let successMessage = '';
                        switch(newStatus) {
                            case 'approved': successMessage = 'Request approved successfully!'; break;
                            case 'rejected': successMessage = 'Request rejected.'; break;
                            case 'fulfilled': successMessage = 'Request marked as fulfilled!'; break;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: successMessage,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { location.reload(); });
                    } else {
                        throw new Error(data.message || 'Failed to update status');
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
</script>
@endsection