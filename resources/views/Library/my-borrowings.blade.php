@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root{--lib-teal:#0ea5a0;--lib-teal-l:rgba(14,165,160,.12);--lib-rose:#f43f5e;--lib-rose-l:rgba(244,63,94,.12);--lib-green:#10b981;--lib-green-l:rgba(16,185,129,.12);--lib-amber:#f59e0b;--lib-amber-l:rgba(245,158,11,.12);--lib-blue:#3b82f6;--lib-blue-l:rgba(59,130,246,.12);--surface:#fff;--bg:#f1f5f9;--border:#e2e8f0;--text-1:#0f172a;--text-2:#475569;--text-3:#94a3b8;--radius:16px;--shadow:0 1px 4px rgba(0,0,0,.06),0 4px 20px rgba(0,0,0,.05);}
        *{font-family:'Plus Jakarta Sans',sans-serif;box-sizing:border-box;}
        body{background:var(--bg);}
        .lib-hero{background:linear-gradient(135deg,#0f1a2e 0%,#0f3460 60%,#16213e 100%);border-radius:20px;padding:2rem 2.5rem 3rem;margin-bottom:-1.5rem;position:relative;overflow:hidden;color:#fff;}
        .lib-hero::before{content:'';position:absolute;top:-80px;right:-80px;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(14,165,160,.2) 0%,transparent 70%);}
        .lib-card{background:var(--surface);border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);}
        .lib-card-header{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);}
        .lib-card-header h3{font-size:1rem;font-weight:700;color:var(--text-1);margin:0;display:flex;align-items:center;gap:.5rem;}
        .lib-card-body{padding:1.5rem;}
        .btn-lib{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:10px;font-size:.8rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .2s;}
        .btn-primary-lib{background:var(--lib-teal);color:#fff;}
        .btn-primary-lib:hover{background:#0b8f8a;color:#fff;}
        .btn-outline-lib{background:transparent;color:var(--text-2);border:1px solid var(--border);}
        .btn-outline-lib:hover{background:var(--bg);}
        .lib-table{width:100%;border-collapse:collapse;}
        .lib-table th{padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid var(--border);}
        .lib-table td{padding:.85rem 1rem;border-bottom:1px solid var(--border);font-size:.875rem;color:var(--text-1);vertical-align:middle;}
        .lib-table tr:last-child td{border-bottom:none;}
        .lib-table tr:hover td{background:#f8fafc;}
        .badge{display:inline-flex;align-items:center;padding:.25rem .65rem;border-radius:999px;font-size:.7rem;font-weight:700;}
        .alert{padding:.85rem 1rem;border-radius:10px;font-size:.85rem;font-weight:500;margin-bottom:1rem;}
        .alert-success{background:var(--lib-green-l);color:var(--lib-green);border-left:4px solid var(--lib-green);}
        .alert-error{background:var(--lib-rose-l);color:var(--lib-rose);border-left:4px solid var(--lib-rose);}
        .alert-warning{background:var(--lib-amber-l);color:var(--lib-amber);border-left:4px solid var(--lib-amber);}
        .empty-state{text-align:center;padding:3rem 1rem;color:var(--text-3);}
        .empty-state i{font-size:3rem;margin-bottom:1rem;display:block;}
        .lib-back-link{display:inline-flex;align-items:center;gap:.4rem;color:var(--text-2);text-decoration:none;font-size:.85rem;font-weight:500;margin-bottom:1.25rem;}
        .lib-back-link:hover{color:var(--lib-teal);}
        .member-card{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:14px;padding:1.25rem;display:flex;align-items:center;gap:1rem;margin-top:1rem;}
        .member-avatar{width:52px;height:52px;border-radius:14px;background:var(--lib-teal);display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#fff;flex-shrink:0;}
    </style>
@endsection

@section('content')
<div style="padding:1.5rem;">
    <a href="{{ route('library.catalogue') }}" class="lib-back-link"><i class="fas fa-arrow-left"></i> Back to Catalogue</a>

    <div class="lib-hero mb-4">
        <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;"><i class="fas fa-user-circle" style="color:var(--lib-teal);margin-right:.5rem;"></i>My Library</div>
        <div style="font-size:.875rem;opacity:.7;">Your borrowings, fines & reservations</div>
        <div class="member-card">
            <div class="member-avatar">{{ strtoupper(substr($member->name, 0, 2)) }}</div>
            <div>
                <div style="font-weight:700;font-size:1rem;">{{ $member->name }}</div>
                <div style="font-size:.8rem;opacity:.7;">{{ ucfirst($member->member_type) }} · Member #{{ $member->member_number }}</div>
                <div style="font-size:.8rem;opacity:.7;margin-top:.15rem;">
                    Expires: {{ $member->membership_expiry ? \Carbon\Carbon::parse($member->membership_expiry)->format('d M Y') : 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif

    @php $unpaidFines = $myFines->where('status','unpaid'); @endphp
    @if($unpaidFines->count())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        You have <strong>{{ $unpaidFines->count() }}</strong> unpaid fine(s) totalling <strong>{{ number_format($unpaidFines->sum('amount'), 2) }}</strong>. Please clear them at the library desk.
    </div>
    @endif

    <div style="display:grid;gap:1.5rem;">

        {{-- Active Borrowings --}}
        @php $active = $borrowings->getCollection()->whereIn('status', ['borrowed','overdue']); @endphp
        @if($active->count())
        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-clock" style="color:var(--lib-teal);"></i> Currently Borrowed</h3>
                <span class="badge" style="background:var(--lib-teal-l);color:var(--lib-teal);">{{ $active->count() }}</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="lib-table">
                    <thead><tr><th>Book</th><th>Borrowed</th><th>Due Date</th><th>Renewals</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($active as $b)
                        @php $isOverdue = $b->due_date < now()->toDateString(); @endphp
                        <tr>
                            <td style="font-weight:600;">{{ $b->book->title ?? '—' }}</td>
                            <td style="color:var(--text-2);">{{ \Carbon\Carbon::parse($b->borrow_date)->format('d M Y') }}</td>
                            <td>
                                <span style="color:{{ $isOverdue ? 'var(--lib-rose)' : 'var(--text-1)' }};font-weight:{{ $isOverdue ? '700' : '400' }};">
                                    {{ \Carbon\Carbon::parse($b->due_date)->format('d M Y') }}
                                </span>
                                @if($isOverdue)<div style="font-size:.7rem;color:var(--lib-rose);">{{ \Carbon\Carbon::parse($b->due_date)->diffForHumans() }}</div>@endif
                            </td>
                            <td style="color:var(--text-2);text-align:center;">{{ $b->renewals }}</td>
                            <td>
                                @if($isOverdue)
                                    <span class="badge" style="background:var(--lib-rose-l);color:var(--lib-rose);">Overdue</span>
                                @else
                                    <span class="badge" style="background:var(--lib-blue-l);color:var(--lib-blue);">Borrowed</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Reservations --}}
        @if($myReservations->count())
        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-calendar-check" style="color:var(--lib-amber);"></i> My Reservations</h3>
            </div>
            <div style="overflow-x:auto;">
                <table class="lib-table">
                    <thead><tr><th>Book</th><th>Reserved</th><th>Expires</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($myReservations as $r)
                        <tr>
                            <td style="font-weight:600;">{{ $r->book->title ?? '—' }}</td>
                            <td style="color:var(--text-2);">{{ \Carbon\Carbon::parse($r->reservation_date)->format('d M Y') }}</td>
                            <td style="color:var(--text-2);">{{ \Carbon\Carbon::parse($r->expiry_date)->format('d M Y') }}</td>
                            <td>
                                @if($r->status === 'ready')
                                    <span class="badge" style="background:var(--lib-green-l);color:var(--lib-green);">Ready for pickup!</span>
                                @else
                                    <span class="badge" style="background:var(--lib-amber-l);color:var(--lib-amber);">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Fines --}}
        @if($myFines->count())
        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-coins" style="color:var(--lib-amber);"></i> My Fines</h3>
            </div>
            <div style="overflow-x:auto;">
                <table class="lib-table">
                    <thead><tr><th>Book</th><th>Amount</th><th>Reason</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($myFines as $fine)
                        <tr>
                            <td>{{ $fine->borrowing->book->title ?? '—' }}</td>
                            <td style="font-weight:700;color:var(--lib-rose);">{{ number_format($fine->amount, 2) }}</td>
                            <td style="color:var(--text-2);">{{ $fine->reason ?? 'Overdue' }}</td>
                            <td>
                                @if($fine->status === 'unpaid')
                                    <span class="badge" style="background:var(--lib-rose-l);color:var(--lib-rose);">Unpaid</span>
                                @elseif($fine->status === 'paid')
                                    <span class="badge" style="background:var(--lib-green-l);color:var(--lib-green);">Paid</span>
                                @else
                                    <span class="badge" style="background:var(--lib-violet-l);color:var(--lib-violet);">Waived</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Borrowing History --}}
        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-history" style="color:var(--lib-teal);"></i> Borrowing History</h3>
                <span style="font-size:.8rem;color:var(--text-3);">{{ $borrowings->total() }} total</span>
            </div>
            <div style="overflow-x:auto;">
                @if($borrowings->count())
                <table class="lib-table">
                    <thead><tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($borrowings as $b)
                        <tr>
                            <td style="font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $b->book->title ?? '—' }}</td>
                            <td style="color:var(--text-2);font-size:.8rem;">{{ \Carbon\Carbon::parse($b->borrow_date)->format('d M Y') }}</td>
                            <td style="color:var(--text-2);font-size:.8rem;">{{ \Carbon\Carbon::parse($b->due_date)->format('d M Y') }}</td>
                            <td style="color:var(--text-2);font-size:.8rem;">{{ $b->return_date ? \Carbon\Carbon::parse($b->return_date)->format('d M Y') : '—' }}</td>
                            <td>
                                @if($b->status === 'returned')
                                    <span class="badge" style="background:var(--lib-green-l);color:var(--lib-green);">Returned</span>
                                @elseif($b->status === 'lost')
                                    <span class="badge" style="background:var(--lib-amber-l);color:var(--lib-amber);">Lost</span>
                                @elseif($b->status === 'borrowed')
                                    <span class="badge" style="background:var(--lib-blue-l);color:var(--lib-blue);">Active</span>
                                @else
                                    <span class="badge" style="background:var(--lib-rose-l);color:var(--lib-rose);">{{ ucfirst($b->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="padding:1rem 1.5rem;">{{ $borrowings->links() }}</div>
                @else
                <div class="empty-state"><i class="fas fa-history"></i>No borrowing history yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
