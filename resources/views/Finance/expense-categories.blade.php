{{-- resources/views/Finance/expense-categories.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    :root {
        --fin-green: #059669;
        --fin-green-l: rgba(5, 150, 105, .10);
        --fin-red: #ce2838;
        --fin-red-l: rgba(220, 38, 38, .10);
        --fin-blue: #2f2ccb;
        --fin-blue-l: rgba(47, 44, 203, .10);
        --fin-amber: #d97706;
        --fin-amber-l: rgba(217, 119, 6, .10);
        --fin-purple: #7c3aed;
        --fin-purple-l: rgba(124, 58, 237, .10);
        --surface: #ffffff;
        --bg: #f0f4f8;
        --border: #e2e8f0;
        --text-1: #0f172a;
        --text-2: #475569;
        --text-3: #94a3b8;
        --radius: 16px;
        --radius-sm: 12px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        --shadow-lg: 0 8px 32px rgba(0, 0, 0, .10);
    }

    * {
        font-family: 'DM Sans', sans-serif;
        box-sizing: border-box;
    }

    body {
        background: var(--bg);
    }

    /* Hero Section */
    .fin-hero {
        background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
        border-radius: 24px;
        padding: 2rem 2.5rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .fin-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(165, 180, 252, .2) 0%, transparent 70%);
    }

    .fin-hero h1 {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    .fin-hero p {
        color: #c7d2fe;
        margin: .2rem 0 0;
        font-size: .88rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(47, 44, 203, .25);
        border: 1px solid rgba(165, 180, 252, .4);
        color: #a5b4fc;
        padding: .25rem .75rem;
        border-radius: 20px;
        font-size: .75rem;
        font-weight: 600;
        margin-bottom: .6rem;
    }

    /* Cards */
    .fin-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .fin-card-header {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafbff;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .fin-card-header h3 {
        margin: 0;
        font-size: .95rem;
        font-weight: 700;
        color: var(--text-1);
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    /* Stat Cards */
    .stat-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 1.2rem;
        text-align: center;
        transition: all .2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .stat-card .value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-1);
        font-family: 'DM Mono', monospace;
    }

    .stat-card .label {
        font-size: .75rem;
        color: var(--text-3);
        margin-top: .3rem;
        font-weight: 500;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Buttons */
    .btn-fin {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .6rem 1.25rem;
        border-radius: 10px;
        font-size: .875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all .18s;
    }

    .btn-sm {
        padding: .4rem .85rem;
        font-size: .8rem;
    }

    .btn-primary-fin {
        background: #2f2ccb;
        color: #fff;
    }

    .btn-primary-fin:hover {
        background: #2420a8;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(47, 44, 203, .35);
    }

    .btn-outline-fin {
        background: transparent;
        border: 1.5px solid var(--border);
        color: var(--text-2);
    }

    .btn-outline-fin:hover {
        border-color: #2f2ccb;
        color: #2f2ccb;
    }

    .btn-danger-fin {
        background: var(--fin-red-l);
        color: var(--fin-red);
        border: 1px solid rgba(220, 38, 38, .2);
    }

    .btn-danger-fin:hover {
        background: var(--fin-red);
        color: #fff;
    }

    /* Badges */
    .badge-fin {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .25rem .7rem;
        border-radius: 20px;
        font-size: .74rem;
        font-weight: 600;
    }

    .badge-green {
        background: var(--fin-green-l);
        color: var(--fin-green);
    }

    .badge-red {
        background: var(--fin-red-l);
        color: var(--fin-red);
    }

    .badge-amber {
        background: var(--fin-amber-l);
        color: var(--fin-amber);
    }

    .badge-blue {
        background: rgba(47, 44, 203, .1);
        color: #2f2ccb;
    }

    .badge-purple {
        background: rgba(124, 58, 237, .1);
        color: #7c3aed;
    }

    .amount-mono {
        font-family: 'DM Mono', monospace;
        font-weight: 600;
    }

    /* Categories Grid */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1rem;
        padding: 1.5rem;
    }

    .category-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 1rem;
        transition: all .2s;
        position: relative;
    }

    .category-card:hover {
        box-shadow: var(--shadow);
        transform: translateY(-2px);
    }

    .category-header {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .category-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .category-info {
        flex: 1;
    }

    .category-name {
        font-weight: 700;
        color: var(--text-1);
        font-size: 1rem;
        margin-bottom: .2rem;
    }

    .category-stats {
        display: flex;
        gap: 1rem;
        margin-top: .5rem;
        font-size: .75rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: .3rem;
        color: var(--text-2);
    }

    .stat-value {
        font-weight: 700;
        color: var(--text-1);
        font-family: 'DM Mono', monospace;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: .5rem;
        margin-top: .5rem;
        justify-content: flex-end;
        padding-top: .5rem;
        border-top: 1px solid var(--border);
    }

    .action-buttons button {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: .4rem .8rem;
        font-size: .75rem;
        border-radius: 8px;
        transition: all .2s;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .edit-cat {
        color: #2f2ccb;
    }

    .edit-cat:hover {
        background: rgba(47, 44, 203, .1);
    }

    .delete-cat {
        color: var(--fin-red);
    }

    .delete-cat:hover {
        background: var(--fin-red-l);
    }

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.65);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--surface);
    border-radius: 24px;
    max-width: 550px;
    width: 90%;
    padding: 0;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    animation: modalFadeIn 0.25s ease-out;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, #2f2ccb 0%, #2420a8 100%) !important;
    border-radius: 24px 24px 0 0;
    flex-shrink: 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-modal {
    cursor: pointer;
    font-size: 1.5rem;
    color: rgba(255, 255, 255, 0.7);
    transition: all 0.2s;
    line-height: 1;
}

.close-modal:hover {
    color: #fff;
    transform: scale(1.1);
}

/* Scrollable body area - limited height */
.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    background: var(--surface);
    max-height: calc(85vh - 130px);
}

.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #2f2ccb;
}

    /* Form inside modal */
    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        margin-bottom: .5rem;
        font-weight: 600;
        color: var(--text-2);
        font-size: .85rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: .7rem .9rem;
        border-radius: 12px;
        border: 1.5px solid var(--border);
        font-size: .9rem;
        transition: all .2s;
        background: var(--surface);
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2f2ccb;
        box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
    }

    .color-preview-row {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .color-hex {
        font-family: 'DM Mono', monospace;
        font-size: .8rem;
        color: var(--text-2);
    }

    /* Icon Selector */
    .icon-selector {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: .5rem;
        margin-top: .5rem;
        max-height: 120px;
        overflow-y: auto;
        padding: .5rem;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        background: #fafbff;
    }

    .icon-option {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: .5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all .15s;
        border: 1.5px solid transparent;
    }

    .icon-option i {
        font-size: 1rem;
        color: var(--text-2);
    }

    .icon-option:hover {
        background: rgba(47, 44, 203, .1);
        border-color: #2f2ccb;
    }

    .icon-option.selected {
        background: #2f2ccb;
        border-color: #2f2ccb;
    }

    .icon-option.selected i {
        color: #fff;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: .5rem;
        cursor: pointer;
    }

    .checkbox-label input {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #2f2ccb;
    }

    .checkbox-label span {
        font-weight: 500;
    }

/* Fixed footer - always visible at bottom */
.modal-actions {
    flex-shrink: 0;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    padding: 1rem 1.5rem 1.5rem;
    border-top: 1px solid var(--border);
    background: #fafbff;
    border-radius: 0 0 24px 24px;
    position: sticky;
    bottom: 0;
}

    small {
        font-size: .7rem;
        color: var(--text-3);
        margin-top: .2rem;
        display: block;
    }

    /* Responsive */
    @media(max-width:768px) {
        .fin-hero {
            padding: 1.5rem;
        }
        .fin-hero h1 {
            font-size: 1.3rem;
        }
        .stat-grid {
            grid-template-columns: 1fr;
        }
        .categories-grid {
            grid-template-columns: 1fr;
            padding: 1rem;
        }
        .modal-content {
            width: 95%;
        }
        .icon-selector {
            grid-template-columns: repeat(6, 1fr);
        }
        .action-buttons button {
            padding: .3rem .6rem;
            font-size: .7rem;
        }
    }
</style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-tags"></i> Finance — Expense Categories</div>
            <h1>Expense Categories</h1>
            <p>Organize expenses by category, assign colors and icons for better tracking</p>
        </div>
    </div>
@endsection

@section('content')

    {{-- Stats Summary --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="value">{{ $categories->count() }}</div>
            <div class="label">Total Categories</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $categories->where('is_active', true)->count() }}</div>
            <div class="label">Active Categories</div>
        </div>
        <div class="stat-card">
            <div class="value">UGX {{ number_format($categories->sum('expenses_sum_amount'), 0) }}</div>
            <div class="label">Total Spent (All Time)</div>
        </div>
    </div>

    {{-- Add Category Button --}}
    <div style="margin-bottom:1.5rem;text-align:right;">
        <button class="btn-fin btn-primary-fin" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add New Category
        </button>
    </div>

    {{-- Categories Grid --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-th-large"></i> Expense Categories</h3>
            <span style="font-size:.75rem;color:var(--text-3);">{{ $categories->count() }} categories</span>
        </div>
        @if($categories->isEmpty())
            <div style="text-align:center;padding:3rem;">
                <i class="fas fa-tags" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
                <p style="margin-bottom:1rem;">No expense categories yet. Create your first category to organize expenses.</p>
                <button class="btn-fin btn-primary-fin" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Create Category
                </button>
            </div>
        @else
            <div class="categories-grid">
                @foreach($categories as $cat)
                    <div class="category-card">
                        <div class="category-header">
                            <div class="category-icon" style="background:{{ $cat->color }}15; color:{{ $cat->color }};">
                                <i class="fas {{ $cat->icon }}"></i>
                            </div>
                            <div class="category-info">
                                <div class="category-name">{{ $cat->name }}</div>
                                <div class="category-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-receipt"></i>
                                        <span class="stat-value">{{ $cat->expenses_count ?? 0 }}</span>
                                        <span>expenses</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span class="stat-value">UGX {{ number_format($cat->expenses_sum_amount ?? 0, 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($cat->description)
                            <div
                                style="font-size:.75rem;color:var(--text-3);margin-bottom:.5rem;padding:.3rem 0;border-top:1px dashed var(--border);">
                                <i class="fas fa-align-left" style="margin-right:.3rem;"></i>{{ Str::limit($cat->description, 60) }}
                            </div>
                        @endif
                        <div class="action-buttons">
                            <button class="edit-cat"
                                onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->color }}', '{{ $cat->icon }}', '{{ addslashes($cat->description) }}', {{ $cat->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="delete-cat"
                                onclick="confirmDeleteCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
{{-- Add/Edit Category Modal --}}
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-tag"></i> Add New Category</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
<form method="POST" action="{{ route('finance.expense-categories.store') }}" id="categoryForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="category_id" id="categoryId">

            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name <span style="color:var(--fin-red);">*</span></label>
                    <input type="text" name="name" id="catName" required
                        placeholder="e.g., Salaries, Utilities, Maintenance">
                </div>

                <div class="form-group">
                    <label>Color</label>
                    <div class="color-preview-row">
                        <input type="color" name="color" id="catColor" value="#2f2ccb"
                            style="width: 50px; height: 50px; border-radius: 10px; border: 1.5px solid var(--border); cursor: pointer;">
                        <span id="colorHex" class="color-hex">#2f2ccb</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Icon</label>
                    <input type="text" name="icon" id="catIcon" value="fa-receipt" placeholder="fa-icon-name"
                        style="font-family:'DM Mono',monospace;">
                    <small>Click on an icon below to select</small>

                    <div class="icon-selector" id="iconSelector">
                        <div class="icon-option" data-icon="fa-receipt"><i class="fas fa-receipt"></i></div>
                        <div class="icon-option" data-icon="fa-money-bill-wave"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="icon-option" data-icon="fa-building"><i class="fas fa-building"></i></div>
                        <div class="icon-option" data-icon="fa-chalkboard-user"><i class="fas fa-chalkboard-user"></i></div>
                        <div class="icon-option" data-icon="fa-book"><i class="fas fa-book"></i></div>
                        <div class="icon-option" data-icon="fa-laptop"><i class="fas fa-laptop"></i></div>
                        <div class="icon-option" data-icon="fa-tools"><i class="fas fa-tools"></i></div>
                        <div class="icon-option" data-icon="fa-utensils"><i class="fas fa-utensils"></i></div>
                        <div class="icon-option" data-icon="fa-bus"><i class="fas fa-bus"></i></div>
                        <div class="icon-option" data-icon="fa-medkit"><i class="fas fa-medkit"></i></div>
                        <div class="icon-option" data-icon="fa-futbol"><i class="fas fa-futbol"></i></div>
                        <div class="icon-option" data-icon="fa-chart-line"><i class="fas fa-chart-line"></i></div>
                        <div class="icon-option" data-icon="fa-coffee"><i class="fas fa-coffee"></i></div>
                        <div class="icon-option" data-icon="fa-graduation-cap"><i class="fas fa-graduation-cap"></i></div>
                        <div class="icon-option" data-icon="fa-chalkboard"><i class="fas fa-chalkboard"></i></div>
                        <div class="icon-option" data-icon="fa-desktop"><i class="fas fa-desktop"></i></div>
                        <div class="icon-option" data-icon="fa-print"><i class="fas fa-print"></i></div>
                        <div class="icon-option" data-icon="fa-wifi"><i class="fas fa-wifi"></i></div>
                        <div class="icon-option" data-icon="fa-tint"><i class="fas fa-tint"></i></div>
                        <div class="icon-option" data-icon="fa-bolt"><i class="fas fa-bolt"></i></div>
                        <div class="icon-option" data-icon="fa-car"><i class="fas fa-car"></i></div>
                        <div class="icon-option" data-icon="fa-plane"><i class="fas fa-plane"></i></div>
                        <div class="icon-option" data-icon="fa-hotel"><i class="fas fa-hotel"></i></div>
                        <div class="icon-option" data-icon="fa-gift"><i class="fas fa-gift"></i></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="description" id="catDesc" rows="2"
                        placeholder="Brief description of this category..."></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="catActive" value="1" checked>
                        <span>Active</span>
                    </label>
                    <small>Inactive categories won't appear in expense forms</small>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-fin btn-outline-fin" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-fin btn-primary-fin" id="saveCategoryBtn">Save Category</button>
            </div>
        </form>
    </div>
</div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const modal = document.getElementById('categoryModal');
        const modalTitle = document.getElementById('modalTitle');
        const categoryForm = document.getElementById('categoryForm');
        const formMethod = document.getElementById('formMethod');
        const categoryId = document.getElementById('categoryId');
        const catName = document.getElementById('catName');
        const catColor = document.getElementById('catColor');
        const colorHex = document.getElementById('colorHex');
        const catIcon = document.getElementById('catIcon');
        const catDesc = document.getElementById('catDesc');
        const catActive = document.getElementById('catActive');

        // Color picker sync
        catColor.addEventListener('input', function () {
            colorHex.textContent = this.value;
        });

        // Icon selector
        document.querySelectorAll('.icon-option').forEach(icon => {
            icon.addEventListener('click', function () {
                const iconClass = this.dataset.icon;
                catIcon.value = iconClass;

                // Highlight selected
                document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        function openAddModal() {
            modalTitle.textContent = 'Add New Category';
            formMethod.value = 'POST';
            categoryId.value = '';
            catName.value = '';
            catColor.value = '#2f2ccb';
            colorHex.textContent = '#2f2ccb';
            catIcon.value = 'fa-receipt';
            catDesc.value = '';
            catActive.checked = true;
            categoryForm.action = '{{ route('finance.expense-categories.store') }}';

            // Reset icon selection
            document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector('.icon-option[data-icon="fa-receipt"]').classList.add('selected');

            modal.style.display = 'flex';
        }

        function openEditModal(id, name, color, icon, description, isActive) {
            modalTitle.textContent = 'Edit Category';
            formMethod.value = 'PUT';
            categoryId.value = id;
            catName.value = name;
            catColor.value = color;
            colorHex.textContent = color;
            catIcon.value = icon;
            catDesc.value = description || '';
            catActive.checked = isActive;
    categoryForm.action = `{{ url('finance/expense-categories') }}/${id}`;

            // Highlight selected icon
            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.dataset.icon === icon) {
                    opt.classList.add('selected');
                }
            });

            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // SweetAlert confirmation for delete
        function confirmDeleteCategory(id, name) {
            Swal.fire({
                title: 'Delete Category?',
                html: `<span style="color:#475569;">Are you sure you want to delete category <strong>${name}</strong>?<br><br>This will not delete existing expenses, but they will become uncategorized.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('finance/expense-categories') }}/${id}`;
                    form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                    document.body.appendChild(form);

                    Swal.fire({
                        title: 'Processing...',
                        text: 'Deleting category...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            form.submit();
                        }
                    });
                }
            });
        }

        // Form submit with SweetAlert confirmation
        categoryForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const isEdit = formMethod.value === 'PUT';
            const title = isEdit ? 'Update Category?' : 'Create New Category?';
            const text = isEdit ? 'Are you sure you want to update this category?' : 'Are you sure you want to create this category?';

            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#dc2626',
                confirmButtonText: isEdit ? 'Yes, update!' : 'Yes, create!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: isEdit ? 'Updating category...' : 'Creating category...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            categoryForm.submit();
                        }
                    });
                }
            });
        });

        // Pre-select default icon on page load
        document.querySelector('.icon-option[data-icon="fa-receipt"]').classList.add('selected');

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2f2ccb',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#2f2ccb'
            });
        @endif
    </script>
@endsection