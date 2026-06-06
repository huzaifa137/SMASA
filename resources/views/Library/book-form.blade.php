@extends('layouts-side-bar.master')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --lib-blue: #2c29ca;
            --lib-blue-l: rgba(44, 41, 202, .12);
            --lib-blue-d: #2420a8;
            --lib-amber: #f59e0b;
            --lib-rose: #f43f5e;
            --lib-rose-l: rgba(244, 63, 94, .12);
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
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .lib-hero::after {
            content: '';
            position: absolute;
            bottom: -40px;
            right: 120px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .lib-hero h1 {
            font-size: 1.6rem;
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

        .lib-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
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

        .btn-outline-lib {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-2);
        }

        .btn-outline-lib:hover {
            border-color: var(--lib-blue);
            color: var(--lib-blue);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-2);
            margin-bottom: .4rem;
        }

        .form-group label .required {
            color: var(--lib-rose);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: .55rem .9rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: .88rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-1);
            outline: none;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--lib-blue);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .cover-preview {
            width: 100px;
            height: 130px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
            margin-top: .5rem;
        }

        /* ── File Preview Styles ── */
        .file-drop-zone {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 1rem;
            transition: border-color .2s, background .2s;
            cursor: pointer;
            position: relative;
        }

        .file-drop-zone:hover {
            border-color: var(--lib-blue);
            background: var(--lib-blue-l);
        }

        .file-drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .file-drop-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .4rem;
            color: var(--text-3);
            font-size: .82rem;
            text-align: center;
            pointer-events: none;
        }

        .file-drop-placeholder i {
            font-size: 1.6rem;
            color: var(--lib-blue);
            opacity: .5;
        }

        /* Cover image preview */
        .cover-preview-wrap {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: .5rem;
            margin-top: .5rem;
        }

        .cover-preview-wrap img {
            width: 110px;
            height: 145px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--lib-blue);
            box-shadow: 0 4px 14px rgba(44, 41, 202, .2);
        }

        .cover-preview-name {
            font-size: .75rem;
            color: var(--text-2);
            font-weight: 600;
            max-width: 140px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview-remove-btn {
            font-size: .72rem;
            color: var(--lib-rose);
            background: var(--lib-rose-l);
            border: none;
            border-radius: 6px;
            padding: .2rem .6rem;
            cursor: pointer;
            font-weight: 600;
        }

        .preview-remove-btn:hover {
            opacity: .75;
        }

        /* Ebook preview */
        .ebook-preview-wrap {
            display: none;
            align-items: center;
            gap: .75rem;
            margin-top: .5rem;
            background: var(--lib-blue-l);
            border: 1.5px solid rgba(44, 41, 202, .2);
            border-radius: 10px;
            padding: .7rem 1rem;
        }

        .ebook-preview-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .ebook-preview-icon.pdf {
            background: rgba(244, 63, 94, .12);
            color: var(--lib-rose);
        }

        .ebook-preview-icon.epub {
            background: var(--lib-violet-l);
            color: var(--lib-violet);
        }

        .ebook-preview-details {
            flex: 1;
            min-width: 0;
        }

        .ebook-preview-details strong {
            display: block;
            font-size: .83rem;
            color: var(--text-1);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ebook-preview-details span {
            font-size: .75rem;
            color: var(--text-3);
        }
    </style>
@endsection

@section('content')
    <div style="padding:1.5rem; max-width: 1200px; margin: 0 auto;">

        <div class="lib-hero mb-4">
            <div class="hero-badge"><i class="fas fa-book"></i> {{ isset($book) ? 'Edit' : 'Add' }} Book</div>
            <h1><i class="fas {{ isset($book) ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                {{ isset($book) ? 'Edit Book' : 'Add New Book' }}</h1>
            <p>{{ isset($book) ? 'Update book details and information' : 'Add a new book to your library collection' }}</p>
        </div>

        <div class="lib-card">
            <div class="lib-card-header">
                <h3><i class="fas fa-info-circle" style="color:var(--lib-teal);"></i> Book Information</h3>
            </div>
            <div class="lib-card-body">
                <form method="POST" id="bookForm"
                    action="{{ isset($book) ? route('library.books.update', $book->id) : route('library.books.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    @if(isset($book)) @method('PUT') @endif

                    <div class="form-row">
                        <div class="form-group">
                            <label>Title <span class="required">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title', $book->title ?? '') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label>ISBN</label>
                            <input type="text" name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}"
                                placeholder="978-3-16-148410-0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Author</label>
                            <select name="author_id">
                                <option value="">— Select Author —</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}" {{ old('author_id', $book->author_id ?? '') == $author->id ? 'selected' : '' }}>
                                        {{ $author->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id">
                                <option value="">— Select Category —</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $book->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject_id">
                                <option value="">— Select Subject —</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $book->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Publisher</label>
                            <input type="text" name="publisher" value="{{ old('publisher', $book->publisher ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Publication Year</label>
                            <input type="number" name="publication_year"
                                value="{{ old('publication_year', $book->publication_year ?? '') }}" min="1800"
                                max="{{ date('Y') }}">
                        </div>
                        <div class="form-group">
                            <label>Edition</label>
                            <input type="text" name="edition" value="{{ old('edition', $book->edition ?? '') }}"
                                placeholder="1st Edition">
                        </div>
                        <div class="form-group">
                            <label>Language</label>
                            <input type="text" name="language" value="{{ old('language', $book->language ?? 'English') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Location / Shelf</label>
                            <input type="text" name="location" value="{{ old('location', $book->location ?? '') }}"
                                placeholder="A1, Shelf 3">
                        </div>
                        <div class="form-group">
                            <label>Price (UGX)</label>
                            <input type="text" id="price_display"
                                value="{{ old('price', isset($book->price) ? number_format($book->price) : '') }}"
                                placeholder="e.g. 1,000,000">
                            <input type="hidden" name="price" id="price">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Total Copies <span class="required">*</span></label>
                            <input type="number" name="total_copies" id="total_copies"
                                value="{{ old('total_copies', $book->total_copies ?? 1) }}" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Available Copies</label>
                            <input type="number" name="available_copies" id="available_copies"
                                value="{{ old('available_copies', $book->available_copies ?? 1) }}" min="0">
                            <small style="color:var(--text-3);">Leave blank to set equal to Total Copies</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4" placeholder="Book description, summary, or notes..."
                            style="width:100%;padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;">{{ old('description', $book->description ?? '') }}</textarea>
                    </div>

                    <div class="form-row">
                        <!-- ── Cover Image ── -->
                        <div class="form-group">
                            <label>Cover Image</label>
                            <small style="color:var(--text-3);display:block;margin-bottom:.4rem;">Max 2MB · JPG, PNG or
                                GIF</small>

                            <div class="file-drop-zone" id="coverDropZone">
                                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif"
                                    id="cover_image">
                                <div class="file-drop-placeholder" id="coverPlaceholder">
                                    <i class="fas fa-image"></i>
                                    <span>Click or drag & drop image</span>
                                </div>
                            </div>

                            <!-- New file preview -->
                            <div class="cover-preview-wrap" id="coverPreviewWrap">
                                <img id="coverPreviewImg" src="" alt="Cover preview">
                                <span class="cover-preview-name" id="coverPreviewName"></span>
                                <button type="button" class="preview-remove-btn" id="coverRemoveBtn">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>

                            @if(isset($book) && $book->cover_image)
                                <div style="margin-top:.5rem;" id="existingCoverWrap">
                                    <img src="{{ Storage::url($book->cover_image) }}" class="cover-preview" alt="Current cover">
                                    <br><small>Current cover</small>
                                </div>
                            @endif
                        </div>

                        <!-- ── E-Book File ── -->
                        <div class="form-group">
                            <label>E-Book File (PDF/EPUB)</label>
                            <small style="color:var(--text-3);display:block;margin-bottom:.4rem;">Max 50MB · PDF or
                                EPUB</small>

                            <div class="file-drop-zone" id="ebookDropZone">
                                <input type="file" name="ebook_file" accept=".pdf,.epub" id="ebook_file">
                                <div class="file-drop-placeholder" id="ebookPlaceholder">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Click or drag & drop file</span>
                                </div>
                            </div>

                            <!-- New file preview -->
                            <div class="ebook-preview-wrap" id="ebookPreviewWrap">
                                <div class="ebook-preview-icon" id="ebookPreviewIcon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="ebook-preview-details">
                                    <strong id="ebookPreviewName"></strong>
                                    <span id="ebookPreviewSize"></span>
                                </div>
                                <button type="button" class="preview-remove-btn" id="ebookRemoveBtn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            @if(isset($book) && $book->has_ebook)
                                <div style="margin-top:.5rem;" id="existingEbookWrap">
                                    <span class="badge-lib badge-teal"><i class="fas fa-tablet-alt"></i> E-Book Available</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div
                        style="display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                        <a href="{{ route('library.books') }}" class="btn-lib btn-outline-lib" id="cancelBtn">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn-lib btn-primary-lib" id="submitBtn">
                            <i class="fas fa-save"></i> {{ isset($book) ? 'Update Book' : 'Save Book' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const display = document.getElementById('price_display');
            const hidden = document.getElementById('price');
            const form = document.getElementById('bookForm');
            const submitBtn = document.getElementById('submitBtn');
            const isEditMode = {{ isset($book) ? 'true' : 'false' }};

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

            function formatNumber(value) {
                if (!value) return '';
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            function cleanNumber(value) {
                return value.replace(/,/g, '');
            }

            // Price formatting
            display.addEventListener('input', function (e) {
                let value = cleanNumber(e.target.value);
                if (isNaN(value)) {
                    value = value.replace(/[^0-9]/g, '');
                }
                e.target.value = formatNumber(value);
                hidden.value = value;
            });

            if (display.value) {
                let cleaned = cleanNumber(display.value);
                display.value = formatNumber(cleaned);
                hidden.value = cleaned;
            }

            // Validate available copies
            function validateCopies() {
                const total = parseInt(document.getElementById('total_copies').value);
                const available = parseInt(document.getElementById('available_copies').value);

                if (available > total) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Available copies cannot exceed total copies'
                    });
                    return false;
                }
                return true;
            }

            // Validate file sizes
            function validateFiles() {
                const coverFile = document.getElementById('cover_image');
                const ebookFile = document.getElementById('ebook_file');

                if (coverFile.files.length > 0) {
                    const fileSize = coverFile.files[0].size / 1024 / 1024; // in MB
                    if (fileSize > 2) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Cover image must be less than 2MB'
                        });
                        return false;
                    }
                }

                if (ebookFile.files.length > 0) {
                    const fileSize = ebookFile.files[0].size / 1024 / 1024; // in MB
                    if (fileSize > 50) {
                        Toast.fire({
                            icon: 'error',
                            title: 'E-Book file must be less than 50MB'
                        });
                        return false;
                    }
                }

                return true;
            }

            // Form submission with SweetAlert confirmation
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const title = document.getElementById('title').value.trim();
                const totalCopies = document.getElementById('total_copies').value;

                if (!title) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Please enter book title'
                    });
                    return;
                }

                if (!totalCopies || totalCopies < 1) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Total copies must be at least 1'
                    });
                    return;
                }

                if (!validateCopies() || !validateFiles()) {
                    return;
                }

                // Set available copies if empty
                const availableInput = document.getElementById('available_copies');
                if (!availableInput.value) {
                    availableInput.value = totalCopies;
                }

                // Ensure price is set
                hidden.value = cleanNumber(display.value);

                const action = isEditMode ? 'update' : 'add';
                const bookTitle = title;

                Swal.fire({
                    title: isEditMode ? 'Update Book?' : 'Add New Book?',
                    text: isEditMode ? `Are you sure you want to update "${bookTitle}"?` : `Are you sure you want to add "${bookTitle}" to the library?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2c29ca',
                    cancelButtonColor: '#f43f5e',
                    confirmButtonText: isEditMode ? 'Yes, update it!' : 'Yes, add it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: isEditMode ? 'Updating Book...' : 'Adding Book...',
                            text: 'Please wait while we process your request',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Disable submit button
                        const originalHtml = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                        // Submit the form via AJAX
                        fetch(form.action, {
                            method: form.method,
                            body: new FormData(form),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: isEditMode ? 'Updated!' : 'Added!',
                                        text: isEditMode ? 'Book has been updated successfully.' : 'Book has been added successfully.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        window.location.href = '{{ route("library.books") }}';
                                    });
                                } else {
                                    throw new Error(data.message || 'Failed to process book');
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: error.message || 'Something went wrong!'
                                });
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalHtml;
                            });
                    }
                });
            });

            // Cancel confirmation
            document.getElementById('cancelBtn').addEventListener('click', function (e) {
                e.preventDefault();
                const href = this.getAttribute('href');

                Swal.fire({
                    title: 'Leave Page?',
                    text: 'Are you sure you want to cancel? Any unsaved changes will be lost.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f43f5e',
                    cancelButtonColor: '#2c29ca',
                    confirmButtonText: 'Yes, leave',
                    cancelButtonText: 'Stay'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });


            // ── Cover Image Preview ──
            const coverInput = document.getElementById('cover_image');
            const coverWrap = document.getElementById('coverPreviewWrap');
            const coverImg = document.getElementById('coverPreviewImg');
            const coverName = document.getElementById('coverPreviewName');
            const coverRemove = document.getElementById('coverRemoveBtn');
            const coverHolder = document.getElementById('coverPlaceholder');
            const existingCover = document.getElementById('existingCoverWrap');

            coverInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = e => {
                    coverImg.src = e.target.result;
                    coverName.textContent = file.name;
                    coverWrap.style.display = 'flex';
                    coverHolder.style.display = 'none';
                    if (existingCover) existingCover.style.display = 'none';
                };
                reader.readAsDataURL(file);
            });

            coverRemove.addEventListener('click', function () {
                coverInput.value = '';
                coverImg.src = '';
                coverWrap.style.display = 'none';
                coverHolder.style.display = 'flex';
                if (existingCover) existingCover.style.display = 'block';
            });

            // ── E-Book File Preview ──
            const ebookInput = document.getElementById('ebook_file');
            const ebookWrap = document.getElementById('ebookPreviewWrap');
            const ebookIcon = document.getElementById('ebookPreviewIcon');
            const ebookName = document.getElementById('ebookPreviewName');
            const ebookSize = document.getElementById('ebookPreviewSize');
            const ebookRemove = document.getElementById('ebookRemoveBtn');
            const ebookHolder = document.getElementById('ebookPlaceholder');
            const existingEbook = document.getElementById('existingEbookWrap');

            function formatBytes(bytes) {
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            }

            ebookInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const isPDF = file.name.toLowerCase().endsWith('.pdf');
                const isEPUB = file.name.toLowerCase().endsWith('.epub');

                ebookIcon.className = 'ebook-preview-icon ' + (isPDF ? 'pdf' : 'epub');
                ebookIcon.innerHTML = isPDF
                    ? '<i class="fas fa-file-pdf"></i>'
                    : '<i class="fas fa-book-open"></i>';

                ebookName.textContent = file.name;
                ebookSize.textContent = formatBytes(file.size);

                ebookWrap.style.display = 'flex';
                ebookHolder.style.display = 'none';
                if (existingEbook) existingEbook.style.display = 'none';
            });

            ebookRemove.addEventListener('click', function () {
                ebookInput.value = '';
                ebookWrap.style.display = 'none';
                ebookHolder.style.display = 'flex';
                if (existingEbook) existingEbook.style.display = 'block';
            });
        });
    </script>
@endsection