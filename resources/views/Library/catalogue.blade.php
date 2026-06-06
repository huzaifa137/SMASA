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

    .badge {
        display: inline-flex;
        align-items: center;
        padding: .25rem .65rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
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

    .book-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.25rem;
    }

    .book-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid var(--border);
        overflow: hidden;
        transition: box-shadow .2s, transform .15s;
        cursor: pointer;
    }

    .book-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, .1);
        transform: translateY(-2px);
    }

    .book-cover {
        width: 100%;
        height: 160px;
        object-fit: cover;
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
    }

    .book-cover-placeholder {
        width: 100%;
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--text-3);
    }

    .book-info {
        padding: 1rem;
    }

    .book-title {
        font-weight: 700;
        font-size: .875rem;
        color: var(--text-1);
        margin-bottom: .25rem;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .book-author {
        font-size: .75rem;
        color: var(--text-3);
        margin-bottom: .5rem;
    }

    .filter-sidebar {
        position: sticky;
        top: 1.5rem;
    }

    .cat-chip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .5rem .75rem;
        border-radius: 10px;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        margin-bottom: .35rem;
        text-decoration: none;
        color: var(--text-2);
        transition: background .15s;
    }

    .cat-chip:hover,
    .cat-chip.active {
        background: var(--lib-blue-l);
        color: var(--lib-blue);
    }
</style>
@endsection

@section('content')
    <div style="padding:1.5rem;">
        <div class="lib-hero mb-4">
            <div style="font-size:1.6rem;font-weight:800;margin:0 0 .25rem;"><i class="fas fa-search"
                    style="color:var(--lib-teal);margin-right:.5rem;"></i>Library Catalogue</div>
            <div style="font-size:.875rem;opacity:.7;margin-bottom:1.25rem;">Browse and discover books in our collection
            </div>
            {{-- Search bar in hero --}}
            <form method="GET" action="{{ route('library.catalogue') }}" style="display:flex;gap:.75rem;max-width:560px;">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    style="border-radius:12px;background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.25);color:#fff;"
                    placeholder="Search by title, ISBN, author… and Click Search / Enter">
            </form>
        </div>

        @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
        @if(session('error'))
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif

        <div style="display:grid;grid-template-columns:220px 1fr;gap:1.5rem;align-items:start;">

            {{-- Sidebar Filters --}}
            <div class="filter-sidebar">
                <div class="lib-card" style="margin-bottom:1rem;">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-filter" style="color:var(--lib-teal);"></i> Filters</h3>
                    </div>
                    <div class="lib-card-body" style="padding:1rem;">
                        <form method="GET" action="{{ route('library.catalogue') }}">
                            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                            <div class="form-group">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-control">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $sub)
                                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                                            {{ $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Availability</label>
                                <select name="availability" class="form-control">
                                    <option value="">Any</option>
                                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>
                                        Available Now</option>
                                </select>
                            </div>
                            @if($settings->enable_ebooks)
                                <div class="form-group" style="display:flex;align-items:center;gap:.5rem;">
                                    <input type="checkbox" name="has_ebook" id="hasEbook" value="1" {{ request('has_ebook') ? 'checked' : '' }} style="width:auto;">
                                    <label for="hasEbook" class="form-label" style="margin:0;cursor:pointer;">E-Books
                                        only</label>
                                </div>
                            @endif
                            <button type="submit" class="btn-lib btn-primary-lib"
                                style="width:100%;justify-content:center;">Apply</button>
                            <a href="{{ route('library.catalogue') }}" class="btn-lib btn-outline-lib"
                                style="width:100%;justify-content:center;margin-top:.5rem;">Clear</a>
                        </form>
                    </div>
                </div>

                {{-- Categories --}}
                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-tags" style="color:var(--lib-teal);"></i> Categories</h3>
                    </div>
                    <div class="lib-card-body" style="padding:.75rem 1rem;">
                        <a href="{{ route('library.catalogue', array_merge(request()->except('category_id'), [])) }}"
                            class="cat-chip {{ !request('category_id') ? 'active' : '' }}">
                            <span>All Categories</span>
                        </a>
                        @foreach($categories as $cat)
                            <a href="{{ route('library.catalogue', array_merge(request()->all(), ['category_id' => $cat->id])) }}"
                                class="cat-chip {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="badge"
                                    style="background:var(--lib-teal-l);color:var(--lib-teal);">{{ $cat->books_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <div>
                {{-- Recommendations --}}
                @if($settings->enable_recommendations && $recommendations->count())
                    <div class="lib-card" style="margin-bottom:1.5rem;">
                        <div class="lib-card-header">
                            <h3><i class="fas fa-star" style="color:var(--lib-amber);"></i> Recommended for You</h3>
                        </div>
                        <div class="lib-card-body">
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;">
                                @foreach($recommendations as $book)
                                    <a href="{{ route('library.books.show', $book->id) }}" style="text-decoration:none;">
                                        <div class="book-card">
                                            @if($book->cover_image)
                                                <img src="{{ Storage::url($book->cover_image) }}" class="book-cover"
                                                    alt="{{ $book->title }}">
                                            @else
                                                <div class="book-cover-placeholder"
                                                    style="background:linear-gradient(135deg,{{ $book->category->color ?? '#e2e8f0' }}22,{{ $book->category->color ?? '#cbd5e1' }}44);">
                                                    <i class="fas fa-book"
                                                        style="color:{{ $book->category->color ?? 'var(--text-3)' }};"></i>
                                                </div>
                                            @endif
                                            <div class="book-info">
                                                <div class="book-title">{{ $book->title }}</div>
                                                <div class="book-author">{{ $book->author->name ?? '' }}</div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Book Grid --}}
                <div class="lib-card">
                    <div class="lib-card-header">
                        <h3><i class="fas fa-th" style="color:var(--lib-teal);"></i> {{ $books->total() }} Books</h3>
                        @if($member)
                            <a href="{{ route('library.my-borrowings') }}" class="btn-lib btn-outline-lib"><i
                                    class="fas fa-user-circle"></i> My Borrowings</a>
                        @endif
                    </div>
                    <div class="lib-card-body">
                        @if($books->count())
                            <div class="book-grid">
                                @foreach($books as $book)
                                    <a href="{{ route('library.books.show', $book->id) }}" style="text-decoration:none;">
                                        <div class="book-card">
                                            @if($book->cover_image)
                                                <img src="{{ Storage::url($book->cover_image) }}" class="book-cover"
                                                    alt="{{ $book->title }}">
                                            @else
                                                <div class="book-cover-placeholder"
                                                    style="background:linear-gradient(135deg,{{ $book->category->color ?? '#e2e8f0' }}22,{{ $book->category->color ?? '#cbd5e1' }}44);">
                                                    <i class="fas fa-book-open"
                                                        style="color:{{ $book->category->color ?? 'var(--text-3)' }};opacity:.5;"></i>
                                                </div>
                                            @endif
                                            <div class="book-info">
                                                <div class="book-title">{{ $book->title }}</div>
                                                <div class="book-author">{{ $book->author->name ?? 'Unknown Author' }}</div>
                                                <div
                                                    style="display:flex;align-items:center;justify-content:space-between;gap:.4rem;flex-wrap:wrap;">
                                                    @if($book->available_copies > 0)
                                                        <span class="badge"
                                                            style="background:var(--lib-green-l);color:var(--lib-green);">{{ $book->available_copies }}
                                                            avail.</span>
                                                    @else
                                                        <span class="badge"
                                                            style="background:var(--lib-rose-l);color:var(--lib-rose);">Checked
                                                            out</span>
                                                    @endif
                                                    @if($book->has_ebook)
                                                        <span class="badge"
                                                            style="background:var(--lib-violet-l);color:var(--lib-violet);"><i
                                                                class="fas fa-tablet-alt" style="font-size:.6rem;"></i> eBook</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div style="margin-top:1.5rem;">{{ $books->appends(request()->all())->links() }}</div>
                        @else
                            <div class="empty-state"><i class="fas fa-search"></i>No books found matching your search.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection