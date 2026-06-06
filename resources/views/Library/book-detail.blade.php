@extends('layouts-side-bar.master')
@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    :root {
        --lib-blue: #2c29ca;
        --lib-blue-l: rgba(44, 41, 202, .12);
        --lib-blue-d: #2420a8;
        --lib-rose: #f43f5e;
        --lib-rose-l: rgba(244, 63, 94, .12);
        --lib-amber: #f59e0b;
        --lib-green: #10b981;
        --lib-green-l: rgba(16, 185, 129, .12);
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
        padding: 1.5rem;
    }

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

    .badge-rose {
        background: var(--lib-rose-l);
        color: var(--lib-rose);
    }

    .badge-green {
        background: var(--lib-green-l);
        color: var(--lib-green);
    }

    .badge-amber {
        background: rgba(245, 158, 11, .12);
        color: var(--lib-amber);
    }

    .badge-violet {
        background: var(--lib-violet-l);
        color: var(--lib-violet);
    }

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
        background: linear-gradient(135deg, var(--lib-blue), var(--lib-blue-d));
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

    .meta-row {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: .3rem;
    }

    .meta-item {
        font-size: .83rem;
        color: var(--text-2);
    }

    .meta-item strong {
        color: var(--text-1);
    }

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
        color: #fff;
        background: #2c29ca;
        border-bottom: none;
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
        background: #f8fafc;
    }

    .rec-card {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .75rem;
        border: 1px solid var(--border);
        border-radius: 12px;
        text-decoration: none;
        transition: all .18s;
        background: #fff;
    }

    .rec-card:hover {
        border-color: var(--lib-blue);
        box-shadow: 0 4px 16px rgba(44, 41, 202, .15);
        transform: translateY(-2px);
    }

    .rec-cover {
        width: 44px;
        height: 58px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--lib-violet-l), var(--lib-blue-l));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--lib-blue);
        flex-shrink: 0;
        overflow: hidden;
    }

    .avail-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.4rem;
        border: 4px solid var(--lib-blue);
        color: var(--lib-blue);
    }
</style>
@endsection

@section('content')
    @if(session('success'))
        <div
            style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:.85rem 1.2rem;border-radius:12px;margin-bottom:1rem;font-size:.875rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
    @if(session('error'))
        <div
            style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.85rem 1.2rem;border-radius:12px;margin-bottom:1rem;font-size:.875rem;">
    <i class="fas fa-times-circle"></i> {{ session('error') }}</div>@endif

    <div class="row" style="margin-top:1rem;">
        <div class="col-lg-4">
            {{-- Book Card --}}
            <div class="lib-card">
                <div class="lib-card-body" style="text-align:center;">
                    @if($book->cover_image)
                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}"
                            style="max-width:180px;max-height:240px;border-radius:12px;object-fit:cover;box-shadow:0 8px 30px rgba(0,0,0,.15);">
                    @else
                        <div
                            style="width:180px;height:240px;border-radius:12px;background:linear-gradient(135deg,var(--lib-violet-l) 0%,var(--lib-teal-l) 100%);display:flex;align-items:center;justify-content:center;font-size:5rem;color:var(--lib-teal);margin:0 auto;">
                            <i class="fas fa-book"></i></div>
                    @endif

                    <h2 style="font-size:1.2rem;font-weight:800;margin:1.2rem 0 .3rem;color:var(--text-1);">
                        {{ $book->title }}</h2>
                    @if($book->author)
                    <p style="color:var(--text-3);font-size:.88rem;margin:0;">by {{ $book->author->name }}</p>@endif

                    @if($book->category)
                        <span class="badge-lib"
                            style="background:{{ $book->category->color }}22;color:{{ $book->category->color }};margin-top:.75rem;">{{ $book->category->name }}</span>
                    @endif

                    <div style="margin-top:1.5rem;display:flex;justify-content:center;">
                        <div class="avail-circle"
                            style="border-color:{{ $book->available_copies > 0 ? 'var(--lib-teal)' : 'var(--lib-rose)' }};color:{{ $book->available_copies > 0 ? 'var(--lib-teal)' : 'var(--lib-rose)' }};">
                            {{ $book->available_copies }}
                            <span style="font-size:.65rem;font-weight:500;margin-top:2px;">of
                                {{ $book->total_copies }}</span>
                        </div>
                    </div>
                    <p style="font-size:.8rem;color:var(--text-3);margin:.5rem 0 0;">copies available</p>

                    <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:.6rem;">
                        <a href="{{ route('library.books.edit', $book->id) }}" class="btn-lib btn-primary-lib"
                            style="justify-content:center;"><i class="fas fa-edit"></i> Edit Book</a>
                        @if($book->has_ebook)<a href="{{ route('library.books.ebook', $book->id) }}"
                            class="btn-lib btn-outline-lib" style="justify-content:center;"><i
                        class="fas fa-tablet-alt"></i> Download E-Book</a>@endif
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="lib-card">
                <div class="lib-card-header">
                    <h3><i class="fas fa-info-circle" style="color:var(--lib-teal);"></i> Details</h3>
                </div>
                <div class="lib-card-body" style="padding:1.25rem 1.5rem;">
                    @php $items = [
                        ['ISBN', 'isbn', null],
                        ['Publisher', 'publisher', null],
                        ['Year', 'publication_year', null],
                        ['Edition', 'edition', null],
                        ['Language', 'language', null],
                        ['Location', 'location', null],
                        ['Subject', null, $book->subject?->name],
                        ['Price', 'price', $book->price ? 'UGX ' . number_format($book->price, 0) : null],
                    ]; @endphp
                    @foreach($items as [$label, $key, $custom])
                            @php $val = $custom ?? ($key ? $book->$key : null); @endphp
                        @if($val)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px solid #f1f5f9;font-size:.85rem;">
                                    <span style="color:var(--text-3);">{{ $label }}</span>
                                    <span style="font-weight:600;color:var(--text-1);">{{ $val }}</span>
                                </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-8">


                        {{-- Description --}}


                        @if($book->description)
                            <div class="lib-card">
                                <div class="lib-card-header"><h3><i class="fas fa-align-left" style="color:var(--lib-teal);"></i> Description</h3></div>
                                <div class="lib-card-body"><p style="color:var(--text-2);line-height:1.7;margin:0;">{{ $book->description }}</p></div>
                            </div>
                        @endif

            {{-- Active Reservations --}}

                               @if($reservations->isNotEmpty())
                                <div class="lib-card">
                                    <div class="lib-card-header">
                                        <h3><i clas
                                                s="f
                                                    as fa-clock" st
                                                    yle="color:var(--
                                                    lib-amber);"></
                                                    i> Active Reser
                                                vatio
                                            ns ({{ $reservations->count() }})</h3>
                                    </div>
                                    <div style="overflow-x:auto;">
                                        <table class="lib-table">
                                            <thead><tr><th>Member</th><th>Reserved</th><th>Expiry</th><th>Status</th></tr></thead>
                                            <tbody>
                                                @foreach($reservations as $r)
                                                    <tr>
                                                        <td>{{ $r->member?->name }}</td>
                                                        <td>{{ $r->reservation_date?->format('d M Y') }}</td>
                                                        <td>{{ $r->expiry_date?->format('d M Y') }}</td>
                                                        <td><span class="badge-lib badge-amber">{{ ucfirst($r->status) }}</span></td>
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
                    <a href="{{ route('library.borrowings', ['book_id' => $book->id]) }}" class="b
                                tn-lib btn-sm-l
                            ib bt
                        n-outline-lib">View All</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="lib-table">
                        <thead><tr><th>Member</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($borrowings as $b)
                                <tr>
                                    <td>{{ $b->member?->name }}</td>
                                    <td>{{ $b->borrow_date?->format('d M Y') }}</td>
                                    <td>{{ $b->due_date?->format('d M Y') }}</td>
                                    <td>{{ $b->return_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if($b->status === 'returned')<span class="badge-lib badge-green">Returned</span>
                                        @elseif($b->status === 'overdue')<span class="badge-lib badge-rose">Overdue</span>
                                        @elseif($b->status === 'lost')<span class="badge-lib badge-amber">Lost</span>

                                        @else<span class="badge-lib badge-teal">Borrowed</span>@endif


                                                            </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:2rem;">No borrowing history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

                        {{-- Recommendations --}}
            @if($recommendations->isNotEmpty())
                <div class="lib-card">
                    <div class="lib-card-header"><h3><i class="fas fa-lightbulb" style="color:var(--lib-amber);"></i> You Might Also Like</h3></div>
                    <div class="lib-card-body">
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap
                                       :1rem;">
                            @foreach($recommendations as $rec)
                                <a href="{{ route('library.books.show', $rec->id) }}" class="rec-card">
                                    <div class="rec-cover">
                                        @if($rec->cover_image)<img src="{{ asset('storage/' . $rec->cover_image) }}" style="w
                                            idth:100%;height:100%;object-fit:cover;">
                                        @else<i class="fas fa-book"></i>@endif
                                    </div>


                                                                   <div>
                                        <div style="font-weight:600;font-size:.85rem;color:var(--text-1);line-height:1.3;">{{ Str::limit($rec->title, 35) }}</div>
                                        <div style="font-size:.75rem;color:var(--text-3);">{{ $rec->author?->name ?? '—' }}</div>
                                        @if($rec->available_copies > 0)<span class="badge-lib badge-teal" style="margin-top:.3rem;font-size:.7rem;">Available</span>@else<span class="badge-lib badge-rose" style="margin-top:.3rem;font-size:.7rem;">Out</span>@endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>
    </div>
@endsection