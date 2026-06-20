@extends('layouts-side-bar.master')
<?php use App\Helpers\PermissionHelper; ?>

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    :root {
        --lib-blue: #2c29ca;
        --lib-blue-l: rgba(44, 41, 202, .12);
        --lib-blue-d: #2420a8;
        --lib-amber: #f59e0b;
        --lib-amber-l: rgba(245, 158, 11, .12);
        --lib-rose: #f43f5e;
        --lib-rose-l: rgba(244, 63, 94, .12);
        --lib-violet: #7c3aed;
        --lib-violet-l: rgba(124, 58, 237, .12);
        --lib-green: #10b981;
        --lib-green-l: rgba(16, 185, 129, .12);
        --surface: #ffffff;
        --bg: #f1f5f9;
        --border: #e2e8f0;
        --text-1: #0f172a;
        --text-2: #475569;
        --text-3: #94a3b8;
        --radius: 16px;
        --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 20px rgba(0, 0, 0, .05);
        --shadow-lg: 0 8px 40px rgba(0, 0, 0, .10);
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: var(--bg);
    }

    /* ── Hero ── */
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
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, .08) 0%);
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

    .lib-hero h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .lib-hero p {
        color: rgba(255, 255, 255, .7);
        margin: .3rem 0 0;
        font-size: .93rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(44, 41, 202, .2);
        border: 1px solid rgba(44, 41, 202, .4);
        color: #a5b4fc;
        padding: .25rem .8rem;
        border-radius: 20px;
        font-size: .75rem;
        font-weight: 600;
        margin-bottom: .7rem;
    }

    /* ── Stat Grid ── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-top: 1.5rem;
    }

    @media(max-width:900px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:540px) {
        .stat-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.4rem 1.5rem;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
        transition: transform .2s, box-shadow .2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card .icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .stat-card .val {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-1);
        line-height: 1;
    }

    .stat-card .label {
        font-size: .8rem;
        color: var(--text-3);
        margin-top: .3rem;
        font-weight: 500;
    }

    .stat-card .sub {
        font-size: .75rem;
        color: var(--text-3);
        margin-top: .4rem;
    }

    /* ── Cards ── */
    .lib-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .lib-card-header {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafbff;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .lib-card-header h3 {
        margin: 0;
        font-size: .95rem;
        font-weight: 700;
        color: var(--text-1);
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .lib-card-header h3 i {
        color: var(--lib-blue);
    }

    .lib-card-body {
        padding: 1.25rem 1.5rem;
    }

    /* ── Table ── */
    .lib-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .875rem;
    }

    .lib-table th {
        padding: .75rem 1rem;
        text-align: left;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-3);
        border-bottom: 1px solid var(--border);
        background: #3431ca;
    }

    .lib-table td {
        padding: .85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: var(--text-1);
    }

    .lib-table tr:last-child td {
        border-bottom: none;
    }

    .lib-table tr:hover td {
        background: #f8faff;
    }

    /* ── Badges ── */
    .badge-lib {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .22rem .65rem;
        border-radius: 20px;
        font-size: .73rem;
        font-weight: 600;
    }

    .badge-teal {
        background: var(--lib-blue-l);
        color: var(--lib-blue);
    }

    .badge-amber {
        background: var(--lib-amber-l);
        color: var(--lib-amber);
    }

    .badge-rose {
        background: var(--lib-rose-l);
        color: var(--lib-rose);
    }

    .badge-green {
        background: var(--lib-green-l);
        color: var(--lib-green);
    }

    .badge-violet {
        background: var(--lib-violet-l);
        color: var(--lib-violet);
    }

    .badge-gray {
        background: #f1f5f9;
        color: var(--text-2);
    }

    /* ── Btn ── */
    .btn-lib {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .55rem 1.1rem;
        border-radius: 10px;
        font-size: .85rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all .18s;
    }

    .btn-primary-lib {
        background: linear-gradient(135deg, #2c29ca, #2420a8);
        color: #fff;
    }

    .btn-primary-lib:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(44, 41, 202, .4);
        color: #fff;
    }

    .btn-sm-lib {
        padding: .35rem .75rem;
        font-size: .78rem;
    }

    .btn-outline-lib {
        background: transparent;
        border: 1.5px solid var(--border);
        color: var(--text-2);
    }

    .btn-outline-lib:hover {
        border-color: var(--lib-blue);
        color: var(--lib-blue);
    }

    /* ── Popular books ── */
    .book-rank {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .book-rank:last-child {
        border-bottom: none;
    }

    .rank-num {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--lib-blue-l);
        color: var(--lib-blue);
        font-weight: 800;
        font-size: .85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .rank-info {
        flex: 1;
        min-width: 0;
    }

    .rank-info .title {
        font-weight: 600;
        font-size: .88rem;
        color: var(--text-1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rank-info .meta {
        font-size: .75rem;
        color: var(--text-3);
    }

    .rank-count {
        font-size: .85rem;
        font-weight: 700;
        color: var(--lib-blue);
    }

    /* ── Chart ── */
    .chart-wrap {
        position: relative;
        height: 220px;
    }

    /* ── Category pill ── */
    .cat-pill {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem .85rem;
        border-radius: 10px;
        margin-bottom: .5rem;
        font-size: .83rem;
    }

    .cat-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>
@endsection

@section('page-header')
    <div style="margin-bottom:1.5rem;margin-top:1rem;">
        <div class="lib-hero">
            <div class="hero-badge"><i class="fas fa-book-open"></i> Library Management</div>
            <h1><i class="fas fa-university"></i> Library Dashboard</h1>
            <p>Manage books, borrowings, members, and more — all in one place.</p>
        </div>
    </div>
@endsection

@section('content')
    <div style="padding:0 .25rem;">

        {{-- Stat Cards --}}
        <div class="stat-grid mb-5" style="margin-top:2rem;">
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-teal-l);color:var(--lib-teal);"><i class="fas fa-book text-primary"></i>
                </div>
                <div class="val">{{ number_format($totalBooks) }}</div>
                <div class="label">Total Titles</div>
                <div class="sub">{{ number_format($availableCopies) }} / {{ number_format($totalCopies) }} copies available
                </div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-green);color:var(--lib-violet);"><i
                        class="fas fa-users text-white"></i></div>
                <div class="val">{{ number_format($totalMembers) }}</div>
                <div class="label">Active Members</div>
                <div class="sub">{{ number_format($activeBorrowings) }} books currently out</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-rose-l);color:var(--lib-rose);"><i class="fas fa-clock text-danger"></i>
                </div>
                <div class="val" style="color:var(--lib-rose);">{{ number_format($overdueCount) }}</div>
                <div class="label">Overdue Books</div>
                <div class="sub">{{ $pendingReservations }} pending reservations</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:var(--lib-amber-l);color:var(--lib-amber);"><i class="fas fa-coins text-warning"></i>
                </div>
                <div class="val" style="color:var(--lib-amber);">{{ number_format($unpaidFines, 0) }}</div>
                <div class="label">Unpaid Fines (UGX)</div>
                <div class="sub">{{ $pendingRequests }} book requests pending</div>
            </div>
        </div>

        
        {{-- Quick Links --}}
        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-bolt" style="color:var(--lib-amber);"></i> Quick Actions</h3>
            </div>
            <div class="lib-card-body" style="display:flex;flex-wrap:wrap;gap:.75rem;">
                @if(PermissionHelper::canFeature('add_book'))
                    <a href="{{ route('library.books.create') }}" class="btn-lib btn-primary-lib"><i class="fas fa-plus"></i>
                        Add Book</a>
                @endif
                @if(PermissionHelper::canFeature('manage_borrowing'))
                    <a href="{{ route('library.borrowings') }}" class="btn-lib"
                        style="background:var(--lib-violet-l);color:var(--lib-violet);"><i
                            class="fas fa-hand-holding-heart"></i> Issue Book</a>
                @endif
                @if(PermissionHelper::canFeature('manage_members'))
                    <a href="{{ route('library.members') }}" class="btn-lib"
                        style="background:var(--lib-amber-l);color:var(--lib-amber);"><i class="fas fa-user-plus"></i> Add
                        Member</a>
                @endif
                @if(PermissionHelper::canFeature('manage_borrowing'))
                    <a href="{{ route('library.fines') }}" class="btn-lib"
                        style="background:var(--lib-rose-l);color:var(--lib-rose);"><i class="fas fa-coins"></i> Manage
                        Fines</a>
                @endif
                @if(PermissionHelper::canFeature('library_reports'))
                    <a href="{{ route('library.reports') }}" class="btn-lib"
                        style="background:var(--lib-green-l);color:var(--lib-green);"><i class="fas fa-chart-bar"></i>
                        Reports</a>
                @endif
                @if(PermissionHelper::canFeature('view_books'))
                    <a href="{{ route('library.catalogue') }}" class="btn-lib btn-outline-lib"><i class="fas fa-search"></i>
                        Book Catalogue</a>
                @endif
                @if(PermissionHelper::canFeature('manage_settings'))
                    <a href="{{ route('library.settings') }}" class="btn-lib btn-outline-lib"><i class="fas fa-cog"></i>
                        Settings</a>
                @endif
            </div>
        </div>

        
        <div class="row mt-4">
            {{-- Borrowing Chart --}}
            <div class="col-lg-8">
                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-chart-line" style="color:var(--lib-teal);"></i> Monthly Borrowing Activity</h3>
                        <span style="font-size:.78rem;color:var(--text-3);">Last 6 months</span>
                    </div>
                    <div class="lib-card-body">
                        <div class="chart-wrap"><canvas id="borrowingChart"></canvas></div>
                    </div>
                </div>
            </div>

            {{-- Category Distribution --}}
            <div class="col-lg-4">
                <div class="lib-card" style="height:calc(100% - 1.5rem);">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-tags" style="color:var(--lib-violet);"></i> Book Categories</h3>
                    </div>
                    <div class="lib-card-body">
                        @foreach($categoryStats as $cat)
                            <div class="cat-pill">
                                <div class="cat-dot" style="background:{{ $cat->color }};"></div>
                                <span style="flex:1;font-size:.85rem;font-weight:500;">{{ $cat->name }}</span>
                                <span style="font-weight:700;color:var(--text-1);">{{ $cat->count }}</span>
                            </div>
                        @endforeach
                        @if($categoryStats->isEmpty())
                            <p style="color:var(--text-3);font-size:.85rem;text-align:center;margin:2rem 0;">No category data
                                yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Popular Books --}}
            <div class="col-lg-5">
                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-fire" style="color:var(--lib-amber);"></i> Most Borrowed Books</h3>
                        @if(PermissionHelper::canFeature('library_reports'))
                            <a href="{{ route('library.reports') }}" class="btn-lib btn-sm-lib btn-outline-lib">View All</a>
                        @endif
                    </div>
                    <div class="lib-card-body">
                        @forelse($popularBooks as $i => $book)
                            <div class="book-rank">
                                <div class="rank-num">{{ $i + 1 }}</div>
                                <div class="rank-info">
                                    <div class="title">{{ $book->title }}</div>
                                    <div class="meta">{{ $book->author?->name ?? 'Unknown Author' }} &bull;
                                        {{ $book->category?->name ?? '—' }}</div>
                                </div>
                                <div class="rank-count">{{ $book->borrowings_count }}×</div>
                            </div>
                        @empty
                            <p style="color:var(--text-3);font-size:.85rem;text-align:center;margin:2rem 0;">No borrowing data
                                yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recent Borrowings --}}
            <div class="col-lg-7">
                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-history" style="color:var(--lib-teal);"></i> Recent Borrowings</h3>
                        @if(PermissionHelper::canFeature('manage_borrowing'))
                            <a href="{{ route('library.borrowings') }}" class="btn-lib btn-sm-lib btn-outline-lib">View All</a>
                        @endif
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="lib-table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Member</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBorrowings as $b)
                                    <tr>
                                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $b->book?->title }}</td>
                                        <td>{{ $b->member?->name }}</td>
                                        <td style="font-family:'JetBrains Mono',monospace;font-size:.8rem;">
                                            {{ $b->due_date?->format('d M Y') }}</td>
                                        <td>
                                            @if($b->status === 'returned')
                                                <span class="badge-lib badge-green"><i class="fas fa-check"></i> Returned</span>
                                            @elseif($b->status === 'overdue')
                                                <span class="badge-lib badge-rose"><i class="fas fa-exclamation"></i> Overdue</span>
                                            @elseif($b->status === 'lost')
                                                <span class="badge-lib badge-amber"><i class="fas fa-times"></i> Lost</span>
                                            @else
                                                <span class="badge-lib badge-teal"><i class="fas fa-book"></i> Borrowed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align:center;color:var(--text-3);padding:2rem;">No
                                            borrowings yet.</td>
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
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('borrowingChart').getContext('2d');
        const labels = @json($monthlyTrend->pluck('month'));
        const data = @json($monthlyTrend->pluck('total'));
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Borrowings',
                    data,
                    borderColor: '#2c29ca',
                    backgroundColor: 'rgba(44, 41, 202, .1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2c29ca',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } } },
                    y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } } }
                }
            }
        });
    </script>
@endsection