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
        --lib-amber-l: rgba(245, 158, 11, .12);
        --lib-rose: #f43f5e;
        --lib-rose-l: rgba(244, 63, 94, .12);
        --lib-violet: #7c3aed;
        --lib-violet-l: rgba(124, 58, 237, .12);
        --lib-green: #10b981;
        --lib-green-l: rgba(16, 185, 129, .12);
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

    .lib-hero-title {
        font-size: 1.6rem;
        font-weight: 800;
        margin: 0 0 .25rem;
    }

    .lib-hero-sub {
        font-size: .875rem;
        opacity: .7;
        margin: 0;
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

    .badge-active {
        background: var(--lib-green-l);
        color: var(--lib-green);
    }

    .badge-inactive {
        background: #f1f5f9;
        color: var(--text-3);
    }

    .color-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
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

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0
        }

        to {
            transform: translateY(0);
            opacity: 1
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .75rem;
    }

    .color-grid {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .3rem;
    }

    .color-swatch {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        cursor: pointer;
        border: 3px solid transparent;
        transition: transform .15s;
    }

    .color-swatch.selected,
    .color-swatch:hover {
        transform: scale(1.15);
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
</style>
@endsection

@section('content')
    <div style="padding:1.5rem;">

        {{-- Hero --}}
        <div class="lib-hero mb-4">
            <div class="lib-hero-title"><i class="fas fa-tags" style="color:#a5b4fc;margin-right:.5rem;"></i>Book Categories</div>
            <div class="lib-hero-sub">Organise your library collection by category</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

            {{-- Category List --}}
            <div class="lib-card">
                <div class="lib-card-header">
                    <h3><i class="fas fa-list" style="color:var(--lib-blue);"></i> All Categories</h3>
                    <span style="font-size:.8rem;color:var(--text-3);">{{ $categories->total() }} total</span>
                </div>
                <div style="overflow-x:auto;">
                    @if($categories->count())
                        <table class="lib-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Books</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $cat)
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:.6rem;">
                                                <span class="color-dot" style="background:{{ $cat->color ?? '#0ea5a0' }};"></span>
                                                <strong>{{ $cat->name }}</strong>
                                            </div>
                                        </td>
                                        <td style="color:var(--text-2);max-width:200px;">
                                            {{ Str::limit($cat->description, 60) ?? '—' }}</td>
                                        <td><span class="badge"
                                                style="background:var(--lib-blue-l);color:var(--lib-blue);">{{ $cat->books_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $cat->is_active ? 'badge-active' : 'badge-inactive' }}">
                                                {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}', '{{ $cat->color }}', {{ $cat->is_active ? 'true' : 'false' }})"
                                                class="btn-lib btn-outline-lib" style="padding:.35rem .75rem;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($cat->books_count == 0)
                                                <button type="button" 
                                                    onclick="confirmDelete({{ $cat->id }})" 
                                                    class="btn-lib btn-danger-lib"
                                                    style="padding:.35rem .75rem;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div style="padding:1rem 1.5rem;">{{ $categories->links() }}</div>
                    @else
                        <div class="empty-state"><i class="fas fa-tags"></i>No categories yet. Add your first one!</div>
                    @endif
                </div>
            </div>

            {{-- Add Category Panel --}}
            <div class="lib-card" style="position:sticky;top:1.5rem;">
                <div class="lib-card-header">
                    <h3><i class="fas fa-plus-circle" style="color:var(--lib-blue);"></i> Add Category</h3>
                </div>
                <div class="lib-card-body">
                    <form method="POST" action="{{ route('library.categories.store') }}" id="addCategoryForm">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Science Fiction" required id="categoryName">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Colour Label</label>
                            <input type="hidden" name="color" id="colorInput" value="#0ea5a0">
                            <div class="color-grid" id="colorGrid">
    @foreach([
        '#0ea5a0', // Teal
        '#7c3aed', // Violet
        '#f59e0b', // Amber
        '#f43f5e', // Rose
        '#10b981', // Emerald
        '#3b82f6', // Blue
        '#ec4899', // Pink
        '#6366f1', // Indigo
        '#14b8a6', // Cyan Teal
        '#f97316', // Orange
        '#84cc16', // Lime
        '#8b5cf6', // Purple
        '#ef4444', // Red
        '#22c55e', // Green
        '#06b6d4', // Cyan
        '#eab308', // Yellow
        '#a855f7', // Purple Bright
        '#d946ef', // Fuchsia
        '#64748b', // Slate
        '#334155', // Dark Slate
        '#1e293b', // Navy Slate
        '#0f172a', // Midnight
        '#be123c', // Dark Rose
        '#b45309', // Brown Amber
        '#15803d', // Forest Green
        '#1d4ed8', // Royal Blue
        '#4338ca', // Deep Indigo
        '#7e22ce', // Deep Purple
        '#c2410c', // Burnt Orange
        '#166534', // Dark Green
    ] as $c)
        <span class="color-swatch {{ $c === '#0ea5a0' ? 'selected' : '' }}"
            style="background:{{ $c }};"
            data-color="{{ $c }}"
            onclick="pickColor('{{ $c }}', this)">
        </span>
    @endforeach
</div>
                        </div>
                        <button type="submit" class="btn-lib btn-primary-lib" style="width:100%;justify-content:center;" id="submitAddBtn">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>


    {{-- Edit Modal --}}
    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
            <div class="modal-title"><i class="fas fa-edit" style="color:var(--lib-blue);"></i> Edit Category</div>
            <form method="POST" id="editForm">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Colour</label>
                    <input type="hidden" name="color" id="editColorInput">
                    <div class="color-grid" id="editColorGrid">
                        @foreach(['#0ea5a0', '#7c3aed', '#f59e0b', '#f43f5e', '#10b981', '#3b82f6', '#ec4899', '#6366f1', '#14b8a6', '#f97316', '#84cc16', '#8b5cf6'] as $c)
                            <span class="color-swatch" style="background:{{ $c }};" data-color="{{ $c }}"
                                onclick="pickEditColor('{{ $c }}', this)"></span>
                        @endforeach
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control" id="editStatus">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;">
                    <button type="button" onclick="closeEditModal()" class="btn-lib btn-outline-lib">Cancel</button>
                    <button type="submit" class="btn-lib btn-primary-lib" id="submitEditBtn"><i class="fas fa-save"></i> Save</button>
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

        // Color picker functions
        function pickColor(color, el) {
            document.getElementById('colorInput').value = color;
            document.querySelectorAll('#colorGrid .color-swatch').forEach(s => s.classList.remove('selected'));
            el.classList.add('selected');
        }
        
        function pickEditColor(color, el) {
            document.getElementById('editColorInput').value = color;
            document.querySelectorAll('#editColorGrid .color-swatch').forEach(s => s.classList.remove('selected'));
            el.classList.add('selected');
        }

        // Add Category with confirmation
        document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const categoryName = document.getElementById('categoryName').value.trim();
            
            if (!categoryName) {
                Toast.fire({
                    icon: 'error',
                    title: 'Please enter category name'
                });
                return;
            }
            
            Swal.fire({
                title: 'Add New Category?',
                text: `Are you sure you want to add "${categoryName}" as a category?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2c29ca',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'Yes, add it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Adding Category...',
                        text: 'Please wait while we add the category',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the form
                    const submitBtn = document.getElementById('submitAddBtn');
                    const originalHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                    
                    fetch(form.action, {
                        method: 'POST',
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
                                title: 'Added!',
                                text: 'Category has been added successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to add category');
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

        // Edit Category with confirmation
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const categoryName = document.getElementById('editName').value.trim();
            
            if (!categoryName) {
                Toast.fire({
                    icon: 'error',
                    title: 'Please enter category name'
                });
                return;
            }
            
            Swal.fire({
                title: 'Update Category?',
                text: `Are you sure you want to update "${categoryName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2c29ca',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Updating Category...',
                        text: 'Please wait while we update the category',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the form
                    const submitBtn = document.getElementById('submitEditBtn');
                    const originalHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    
                    fetch(form.action, {
                        method: 'POST',
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
                                title: 'Updated!',
                                text: 'Category has been updated successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to update category');
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

        // Delete Category with SweetAlert
        function confirmDelete(categoryId) {
            Swal.fire({
                title: 'Delete Category?',
                text: "Are you sure you want to delete this category? This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#2c29ca',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the category',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/library/categories/${categoryId}`;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    
                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    
                    fetch(form.action, {
                        method: 'POST',
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
                                title: 'Deleted!',
                                text: 'Category has been deleted successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to delete category');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: error.message || 'Something went wrong!'
                        });
                    })
                    .finally(() => {
                        document.body.removeChild(form);
                    });
                }
            });
        }

        // Modal functions
        function openEditModal(id, name, desc, color, active) {
            document.getElementById('editForm').action = `/library/categories/${id}`;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = desc;
            document.getElementById('editColorInput').value = color;
            document.getElementById('editStatus').value = active ? '1' : '0';
            document.querySelectorAll('#editColorGrid .color-swatch').forEach(s => {
                s.classList.toggle('selected', s.dataset.color === color);
            });
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeEditModal() { 
            document.getElementById('editModal').classList.remove('active'); 
        }
        
        document.getElementById('editModal').addEventListener('click', function (e) { 
            if (e.target === this) closeEditModal(); 
        });
    </script>
@endsection