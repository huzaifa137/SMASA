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

    /* Additional decorative patch for more depth */
    .lib-hero .hero-patch {
        position: absolute;
        top: 20%;
        left: -60px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(165, 180, 252, .06) 0%, transparent 70%);
        pointer-events: none;
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
        flex-wrap: wrap;
        gap: .75rem;
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

    .btn-violet-lib {
        background: var(--lib-violet-l);
        color: var(--lib-violet);
    }

    .btn-violet-lib:hover {
        background: var(--lib-violet);
        color: #fff;
    }

    .btn-amber-lib {
        background: var(--lib-amber-l);
        color: var(--lib-amber);
    }

    .btn-amber-lib:hover {
        background: var(--lib-amber);
        color: #fff;
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

    .form-control {
        padding: .6rem .85rem;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-size: .875rem;
        font-family: inherit;
        transition: border-color .2s;
        outline: none;
        background: #fff;
    }

    .form-control:focus {
        border-color: var(--lib-blue);
    }

    .book-cover-thumb {
        width: 36px;
        height: 48px;
        border-radius: 6px;
        object-fit: cover;
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        flex-shrink: 0;
    }

    .book-cover-placeholder {
        width: 36px;
        height: 48px;
        border-radius: 6px;
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: .85rem;
        color: var(--text-3);
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
        max-width: 480px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        animation: slideUp .25s ease;
    }

    .modal-box-lg {
        max-width: 600px;
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

    .form-control-full {
        width: 100%;
        padding: .6rem .85rem;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-size: .875rem;
        font-family: inherit;
        transition: border-color .2s;
        outline: none;
    }

    .form-control-full:focus {
        border-color: var(--lib-blue);
    }

    .avail-bar-wrap {
        width: 80px;
        height: 6px;
        background: var(--border);
        border-radius: 999px;
        overflow: hidden;
    }

    .avail-bar {
        height: 100%;
        border-radius: 999px;
        background: var(--lib-blue);
    }
</style>
@endsection

@section('content')
    <div style="padding:1.5rem;">
       
<div class="lib-hero mb-4">
    <div class="hero-patch"></div>
    <div style="font-size:1.6rem;font-weight:800;margin:0 0 .5rem;">
        <i class="fas fa-books" style="color:#a5b4fc;margin-right:.5rem;"></i>Books
    </div>
    <div style="font-size:.875rem;opacity:.7;margin-bottom:1rem;">Manage your library book collection</div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <span class="stat-chip"><i class="fas fa-book"></i> {{ $books->total() }} books</span>
    </div>
</div>

        @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
        @if(session('error'))
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif

        {{-- Filters --}}
        <div class="lib-card" style="margin-bottom:1.25rem;">
            <div class="lib-card-body" style="padding:1rem 1.5rem;">
                <form method="GET" action="{{ route('library.books') }}" class="filter-bar">
                    <input type="text" name="search" class="form-control" placeholder="Search title, ISBN, author…"
                        value="{{ request('search') }}" style="min-width:220px;">
                    <select name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="author_id" class="form-control">
                        <option value="">All Authors</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}" {{ request('author_id') == $author->id ? 'selected' : '' }}>
                                {{ $author->name }}</option>
                        @endforeach
                    </select>
                    <select name="subject_id" class="form-control">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <select name="availability" class="form-control">
                        <option value="">Any Availability</option>
                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available Now
                        </option>
                        <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Fully Checked
                            Out</option>
                    </select>
                    <label
                        style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;font-weight:600;color:var(--text-2);cursor:pointer;white-space:nowrap;">
                        <input type="checkbox" name="has_ebook" value="1" {{ request('has_ebook') ? 'checked' : '' }}> E-Books
                        Only
                    </label>
                    <button type="submit" class="btn-lib btn-primary-lib"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('library.books') }}" class="btn-lib btn-outline-lib">Clear</a>
                </form>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-list" style="color:var(--lib-blue);"></i> All Books</h3>
                <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
                    {{-- Import --}}
                    <button onclick="document.getElementById('importModal').classList.add('active')"
                        class="btn-lib btn-violet-lib">
                        <i class="fas fa-file-import"></i> Import
                    </button>
                    {{-- Export --}}
                    <a href="{{ route('library.books.export') }}" class="btn-lib btn-amber-lib">
                        <i class="fas fa-file-export"></i> Export
                    </a>
                    {{-- Add Book --}}
                    <a href="{{ route('library.books.create') }}" class="btn-lib btn-primary-lib">
                        <i class="fas fa-plus"></i> Add Book
                    </a>
                </div>
            </div>

            <div style="overflow-x:auto;">
                @if($books->count())
                    <table class="lib-table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Subject</th>
                                <th>Copies</th>
                                <th>Availability</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($books as $book)
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.75rem;">
                                            @if($book->cover_image)
                                                <img src="{{ Storage::url($book->cover_image) }}" class="book-cover-thumb"
                                                    alt="{{ $book->title }}">
                                            @else
                                                <div class="book-cover-placeholder"><i class="fas fa-book"></i></div>
                                            @endif
                                            <div>
                                                <div
                                                    style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                    {{ $book->title }}</div>
                                                <div style="display:flex;gap:.4rem;margin-top:.25rem;flex-wrap:wrap;">
                                                    @if($book->has_ebook)
                                                        <span class="badge"
                                                            style="background:var(--lib-violet-l);color:var(--lib-violet);"><i
                                                                class="fas fa-tablet-alt" style="font-size:.6rem;"></i> eBook</span>
                                                    @endif
                                                    @if(!$book->is_active)
                                                        <span class="badge"
                                                            style="background:#f1f5f9;color:var(--text-3);">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color:var(--text-2);">{{ $book->author->name ?? '—' }}</td>
                                    <td>
                                        @if($book->category)
                                            <span class="badge"
                                                style="background:{{ $book->category->color ?? '#e2e8f0' }}22;color:{{ $book->category->color ?? 'var(--text-2)' }};border:1px solid {{ $book->category->color ?? 'var(--border)' }}44;">
                                                {{ $book->category->name }}
                                            </span>
                                        @else
                                            <span style="color:var(--text-3);">—</span>
                                        @endif
                                    </td>
                                    <td style="color:var(--text-2);">{{ $book->subject->name ?? '—' }}</td>
                                    </td>
                                    <td style="color:var(--text-2);text-align:center;">{{ $book->total_copies }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:.5rem;">
                                            <div class="avail-bar-wrap">
                                                @php $pct = $book->total_copies > 0 ? ($book->available_copies / $book->total_copies) * 100 : 0; @endphp
                                                <div class="avail-bar"
                                                    style="width:{{ $pct }}%;background:{{ $pct > 30 ? 'var(--lib-green)' : ($pct > 0 ? 'var(--lib-amber)' : 'var(--lib-rose)') }};">
                                                </div>
                                            </div>
                                            <span
                                                style="font-size:.8rem;font-weight:600;color:{{ $book->available_copies > 0 ? 'var(--lib-green)' : 'var(--lib-rose)' }};">
                                                {{ $book->available_copies }}/{{ $book->total_copies }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                            <a href="{{ route('library.books.show', $book->id) }}" class="btn-lib btn-outline-lib"
                                                style="padding:.3rem .65rem;" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('library.books.edit', $book->id) }}" class="btn-lib btn-outline-lib"
                                                style="padding:.3rem .65rem;" title="Edit"><i class="fas fa-edit"></i></a>
                                            @if($book->has_ebook)
                                                <a href="{{ route('library.books.ebook', $book->id) }}" class="btn-lib btn-violet-lib"
                                                    style="padding:.3rem .65rem;" title="Download eBook"><i
                                                        class="fas fa-download"></i></a>
                                            @endif
                                            <form method="POST" action="{{ route('library.books.destroy', $book->id) }}"
                                                style="display:inline;" onsubmit="return confirm('Delete " {{ addslashes($book->title) }}"? This cannot be undone.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-lib btn-danger-lib" style="padding:.3rem .65rem;"
                                                    title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="padding:1rem 1.5rem;">{{ $books->appends(request()->all())->links() }}</div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-books"></i>
                        @if(request()->hasAny(['search', 'category_id', 'author_id', 'subject_id', 'availability', 'has_ebook']))
                            No books match your current filters.
                            <div style="margin-top:.75rem;"><a href="{{ route('library.books') }}"
                                    class="btn-lib btn-outline-lib">Clear Filters</a></div>
                        @else
                            No books yet. <a href="{{ route('library.books.create') }}"
                                style="color:var(--lib-blue);font-weight:600;">Add your first book</a> or import a CSV.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div class="modal-overlay" id="importModal">
        <div class="modal-box">
            <div class="modal-title"><i class="fas fa-file-import" style="color:var(--lib-violet);"></i> Import Books</div>
            <form method="POST" action="{{ route('library.books.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Upload File (XLSX or CSV) *</label>
                    <input type="file" name="file" class="form-control-full" accept=".xlsx,.csv" required>
                </div>
                <div
                    style="background:var(--bg);border-radius:10px;padding:.85rem 1rem;font-size:.8rem;color:var(--text-2);margin-bottom:1rem;">
                    <strong style="display:block;margin-bottom:.35rem;"><i class="fas fa-info-circle"
    style="color:var(--lib-blue);"></i> Expected column order:</strong>
                    Title · ISBN · Author · Category · Publisher · Year · Copies · Language
                </div>
                <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                    <button type="button" onclick="document.getElementById('importModal').classList.remove('active')"
                        class="btn-lib btn-outline-lib">Cancel</button>
                    <button type="submit" class="btn-lib btn-primary-lib"><i class="fas fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script>
        document.getElementById('importModal').addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });
    </script>
@endsection