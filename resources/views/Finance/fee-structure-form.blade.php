{{-- resources/views/Finance/payment-form.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --g: #2f2ccb;
            --gl: rgba(47, 44, 203, .10);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --b: #2f2ccb;
            --bl: rgba(47, 44, 203, .10);
            --a: #d97706;
            --al: rgba(217, 119, 6, .10);
            --surf: #fff;
            --bg: #f0f4f8;
            --brd: #e2e8f0;
            --t1: #0f172a;
            --t2: #475569;
            --t3: #94a3b8;
            --rad: 16px;
            --rads: 10px;
            --sh: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        *:not(i):not([class*="fa"]) {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
        }

        /* Hero */
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
        .card {
            background: var(--surf);
            border-radius: var(--rad);
            border: 1px solid var(--brd);
            box-shadow: var(--sh);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-hd {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbff;
        }

        .card-hd-left {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .card-hd h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1);
        }

        .card-bd {
            padding: 1.5rem;
        }

        /* Forms */
        .form-label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: var(--t2);
            margin-bottom: .4rem;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            padding: .65rem .9rem;
            border: 1.5px solid var(--brd);
            border-radius: var(--rads);
            font-size: .875rem;
            color: var(--t1);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--b);
            box-shadow: 0 0 0 3px var(--bl);
        }

        .form-control.is-invalid {
            border-color: var(--r);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .75rem center;
            background-size: 12px;
            padding: .65rem 2.5rem .65rem .9rem;
            width: 100%;
            height: auto;
            min-height: 42px;
            line-height: 1.4;
            cursor: pointer;
        }

        /* Optional: Style the dropdown options */
        select.form-control option {
            padding: 8px;
            font-size: .875rem;
        }

        .invalid-feedback {
            font-size: .75rem;
            color: var(--r);
            margin-top: .3rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.25rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.25rem;
            border-radius: var(--rads);
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

        .btn-primary {
            background: #2f2ccb;
            color: #fff;
        }

        .btn-primary:hover {
            background: #2420a8;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(47, 44, 203, .35);
        }

        .btn-outline {
            background: #fff;
            border: 1.5px solid #2420a8;
            color: #2420a8;
            padding: .6rem 1.25rem;
        }

        .btn-outline:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: #fff;
        }

        .btn-danger {
            background: var(--rl);
            color: var(--r);
            border: 1px solid rgba(220, 38, 38, .2);
        }

        .btn-danger:hover {
            background: var(--r);
            color: #fff;
        }

        .btn-add {
            background: rgba(47, 44, 203, .08);
            color: #2f2ccb;
            border: 1.5px dashed rgba(47, 44, 203, .35);
        }

        .btn-add:hover {
            background: #2f2ccb;
            color: #fff;
            border-style: solid;
        }

        /* ── Fee Items Section ── */
        .items-wrapper {
            padding: 1.25rem 1.5rem;
        }

        .items-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .items-header-label {
            font-size: .75rem;
            font-weight: 700;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .items-header-label span {
            background: var(--bl);
            color: var(--b);
            border-radius: 20px;
            padding: .1rem .55rem;
            font-size: .72rem;
            margin-left: .4rem;
        }

        /* Item cards instead of table rows */
        .item-card {
            display: grid;
            grid-template-columns: 24px 1fr 180px 150px 80px 44px;
            /* Changed from 36px to 44px */
            gap: .75rem;
            align-items: center;
            background: #fafbff;
            border: 1.5px solid var(--brd);
            border-radius: 12px;
            padding: .75rem 1rem;
            margin-bottom: .6rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .item-card:hover {
            border-color: rgba(47, 44, 203, .3);
            box-shadow: 0 2px 8px rgba(47, 44, 203, .08);
        }

        .item-card.dragging {
            opacity: .5;
            border-color: var(--b);
        }

        .drag-handle {
            cursor: grab;
            color: var(--t3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .drag-handle:hover {
            color: var(--b);
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* Checkbox toggle */
        .toggle-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-wrap input[type=checkbox] {
            width: 18px;
            height: 18px;
            accent-color: #2f2ccb;
            cursor: pointer;
        }

        /* Empty state */
        .items-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--t3);
            border: 2px dashed var(--brd);
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .items-empty i {
            font-size: 2rem;
            opacity: .3;
            display: block;
            margin-bottom: .5rem;
        }

        /* Total bar */
        .total-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(90deg, rgba(47, 44, 203, .07), transparent);
            border: 1px solid rgba(47, 44, 203, .18);
            border-radius: var(--rads);
            padding: 1rem 1.5rem;
            margin-top: .75rem;
        }

        .total-label {
            font-size: .85rem;
            font-weight: 600;
            color: var(--t2);
        }

        .total-value {
            font-family: 'DM Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2f2ccb;
        }

        /* Column headers for item cards */
        .item-cols-header {
            display: grid;
            grid-template-columns: 24px 1fr 180px 150px 80px 44px;
            /* Changed from 36px to 44px */
            gap: .75rem;
            padding: .3rem 1rem;
            margin-bottom: .35rem;
        }

        .item-col-lbl {
            font-size: .7rem;
            font-weight: 700;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        input[type="checkbox"] {
            accent-color: #2f2ccb;
        }

        @media(max-width:900px) {
            .item-card {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto auto;
            }

            .item-card .drag-handle,
            .item-cols-header {
                display: none;
            }

            .form-grid,
            .form-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width:600px) {
            .item-card {
                grid-template-columns: 1fr;
            }

            .fin-hero {
                padding: 1.5rem;
            }
        }

        .form-grid>*,
        .form-grid-3>* {
            min-width: 0;
        }

        /* Make the remove button red and properly centered */
        .remove-item {
            background: var(--r) !important;
            /* Solid red background */
            color: #fff !important;
            /* White icon */
            border: none !important;
            width: 32px;
            height: 32px;
            padding: 0 !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all .2s;
            margin: 0 auto;
            /* Center horizontally */
        }

        .remove-item:hover {
            background: #b91c1c !important;
            /* Darker red on hover */
            transform: scale(1.05);
        }

        .remove-item i {
            font-size: 0.85rem;
            margin: 0;
        }

        .item-cols-header {
            display: grid;
            grid-template-columns: 24px 1fr 180px 150px 80px 44px;
            gap: .75rem;
            padding: .6rem 1rem;
            margin-bottom: .75rem;
            background: #2c29ca;
            border-radius: 10px;
        }

        .item-cols-header .item-col-lbl {
            font-size: .7rem;
            font-weight: 700;
            color: #fff !important;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1">
            <div class="hero-badge"><i class="fas fa-layer-group"></i> Finance — Fee Structures</div>
            <h1>{{ isset($structure) ? 'Edit Fee Structure' : 'New Fee Structure' }}</h1>
            <p>{{ isset($structure) ? 'Update the fee template and its line items' : 'Define a fee template with itemised charges for a class / term' }}
            </p>
        </div>
    </div>
@endsection

@section('content')
    @php
        $isEdit = isset($structure);
        $categories = [
            'tuition' => 'Tuition',
            'boarding' => 'Boarding',
            'activity' => 'Activity',
            'library' => 'Library',
            'sport' => 'Sports',
            'medical' => 'Medical',
            'exam' => 'Exams',
            'other' => 'Other',
        ];
        $existingItems = old('items', $isEdit ? $structure->items->toArray() : []);
    @endphp

    <form method="POST"
        action="{{ $isEdit ? route('finance.fee-structures.update', $structure->id) : route('finance.fee-structures.store') }}"
        id="feeForm">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- ── Structure Details ── --}}
        <div class="card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <i class="fas fa-info-circle" style="color:var(--b)"></i>
                    <h3>Structure Details</h3>
                </div>
            </div>
            <div class="card-bd">
                <div class="form-grid" style="margin-bottom:1.25rem">
                    <div class="form-group">
                        <label class="form-label">Structure Name <span style="color:var(--r)">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $structure->name ?? '') }}" placeholder="e.g. S.1 Boarding — Term 1 2026">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Class Level</label>
                        @if(isset($classrooms) && $classrooms->count())
                            <select name="class_level" class="form-control">
                                <option value="">— All Classes —</option>
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls['id'] }}" {{ old('class_level', $structure->class_level ?? '') == $cls['id'] ? 'selected' : '' }}>
                                        {{ $cls['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" name="class_level" class="form-control"
                                value="{{ old('class_level', $structure->class_level ?? '') }}"
                                placeholder="e.g. S.1, P.6, All">
                        @endif
                        <small style="color:var(--t3);font-size:.73rem;margin-top:.3rem;display:block;">
                            <i class="fas fa-info-circle"></i> Leave blank to apply to all classes
                        </small>
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="form-label">Academic Year <span style="color:var(--r)">*</span></label>
                        <input type="number" name="academic_year"
                            class="form-control @error('academic_year') is-invalid @enderror"
                            value="{{ old('academic_year', $structure->academic_year ?? date('Y')) }}" min="2000" max="2100"
                            {{ $isEdit ? 'readonly' : '' }}>
                        @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Term <span style="color:var(--r)">*</span></label>
                        <select name="term" class="form-control @error('term') is-invalid @enderror" {{ $isEdit ? 'disabled' : '' }}>
                            <option value="">Select Term</option>
                            @foreach([1 => 'Term 1', 2 => 'Term 2', 3 => 'Term 3'] as $v => $l)
                                <option value="{{ $v }}" {{ old('term', $structure->term ?? '') == $v ? 'selected' : '' }}>
                                    {{ $l }}
                                </option>
                            @endforeach
                        </select>
                        @if($isEdit)<input type="hidden" name="term" value="{{ $structure->term }}">@endif
                        @error('term')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Student Type <span style="color:var(--r)">*</span></label>
                        <select name="student_type" class="form-control @error('student_type') is-invalid @enderror">
                            @foreach(['all' => 'All Students', 'boarding' => 'Boarding', 'day' => 'Day'] as $v => $l)
                                <option value="{{ $v }}" {{ old('student_type', $structure->student_type ?? 'all') == $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"
                        placeholder="Optional notes about this fee structure…">{{ old('notes', $structure->notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Fee Line Items ── --}}
        <div class="card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <i class="fas fa-list-ul" style="color:var(--b)"></i>
                    <h3>Fee Line Items</h3>
                    <span
                        style="background:var(--bl);color:var(--b);border-radius:20px;padding:.15rem .6rem;font-size:.72rem;font-weight:700;"
                        id="itemCount">0</span>
                </div>
                <button type="button" class="btn btn-add btn-sm" id="addItem">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>

            <div class="items-wrapper">
                {{-- Column headers --}}
                <div class="item-cols-header" id="colHeaders" style="{{ count($existingItems) ? '' : 'display:none' }}">
                    <div></div>
                    <div class="item-col-lbl">Item Name</div>
                    <div class="item-col-lbl">Category</div>
                    <div class="item-col-lbl">Amount (UGX)</div>
                    <div class="item-col-lbl" style="text-align:center">Required</div>
                    <div></div>
                </div>

                {{-- Items container --}}
                <div id="itemsContainer">
                    @forelse($existingItems as $i => $item)
                        <div class="item-card" data-index="{{ $i }}">
                            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                            <input type="text" name="items[{{ $i }}][item_name]" class="form-control"
                                value="{{ $item['item_name'] ?? '' }}" placeholder="e.g. Tuition Fees" required>
                            <select name="items[{{ $i }}][category]" class="form-control">
                                @foreach($categories as $cv => $cl)
                                    <option value="{{ $cv }}" {{ ($item['category'] ?? '') == $cv ? 'selected' : '' }}>{{ $cl }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="items[{{ $i }}][amount]" class="form-control item-amount"
                                value="{{ number_format($item['amount'] ?? 0, 0) }}" required>
                            <div class="toggle-wrap">
                                <input type="hidden" name="items[{{ $i }}][is_mandatory]" value="0">
                                <input type="checkbox" name="items[{{ $i }}][is_mandatory]" value="1" {{ ($item['is_mandatory'] ?? true) ? 'checked' : '' }}>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-item" title="Remove">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    @empty
                        <div class="items-empty" id="emptyState">
                            <i class="fas fa-receipt"></i>
                            <p style="margin:.5rem 0 1rem;font-size:.875rem;">No fee items yet.<br>Click <strong>Add
                                    Item</strong> to start building this fee structure.</p>
                            <button type="button" class="btn btn-add btn-sm" onclick="addNewItem()">
                                <i class="fas fa-plus"></i> Add First Item
                            </button>
                        </div>
                    @endforelse
                </div>

                {{-- Total --}}
                <div class="total-bar" id="totalBar" style="{{ count($existingItems) ? '' : 'display:none' }}">
                    <span class="total-label">
                        <i class="fas fa-calculator" style="color:var(--b);margin-right:.4rem"></i>
                        Total Fee Amount
                    </span>
                    <span class="total-value">UGX&nbsp;<span
                            id="totalDisplay">{{ number_format($isEdit ? $structure->total_amount : 0, 0) }}</span></span>
                </div>
            </div>
        </div>

        {{-- ── Actions ── --}}
        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-bottom:2rem">
            <a href="{{ route('finance.fee-structures.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Update Structure' : 'Create Structure' }}
            </button>
        </div>
    </form>

    </div>
    </div>
    </div>

    @if(session('success'))
        <div style="position:fixed;bottom:1.5rem;right:1.5rem;background:#2f2ccb;color:#fff;padding:.85rem 1.4rem;border-radius:12px;font-size:.875rem;font-weight:600;box-shadow:0 8px 24px rgba(47,44,203,.35);z-index:9999;display:flex;align-items:center;gap:.5rem;"
            id="toast-msg">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        <script>setTimeout(() => { let t = document.getElementById('toast-msg'); if (t) { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; setTimeout(() => t.remove(), 400); } }, 3500);</script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ── Config ──────────────────────────────────────────────────────
        const CATEGORIES = @json($categories);
        let itemIndex = {{ count($existingItems) }};
        const isEdit = {{ $isEdit ? 'true' : 'false' }};

        // ── DOM refs ─────────────────────────────────────────────────────
        const container = document.getElementById('itemsContainer');
        const totalDisp = document.getElementById('totalDisplay');
        const totalBar = document.getElementById('totalBar');
        const colHeaders = document.getElementById('colHeaders');
        const itemCount = document.getElementById('itemCount');
        const emptyState = document.getElementById('emptyState');

        // ── Build category <select> options ──────────────────────────────
        function catOptions(selected = 'tuition') {
            return Object.entries(CATEGORIES)
                .map(([v, l]) => `<option value="${v}"${v === selected ? ' selected' : ''}>${l}</option>`)
                .join('');
        }

        // ── Format amount with commas ──────────────────────────────────────
        function formatNumberWithCommas(value) {
            let numbers = value.toString().replace(/\D/g, '');
            if (numbers === '') return '';
            return parseInt(numbers, 10).toLocaleString('en-US');
        }

        function parseFormattedNumber(value) {
            return value.toString().replace(/,/g, '');
        }

        // ── Build a single item card ──────────────────────────────────────
        function buildItemCard(idx, data = {}) {
            const name = data.item_name ?? '';
            const cat = data.category ?? 'tuition';
            const amount = data.amount ?? 0;
            const mand = data.is_mandatory !== false;
            const formattedAmount = amount ? formatNumberWithCommas(amount) : '';

            const card = document.createElement('div');
            card.className = 'item-card';
            card.dataset.index = idx;
            card.innerHTML = `
                <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                <input type="text" name="items[${idx}][item_name]" class="form-control"
                    value="${name.replace(/"/g, '&quot;')}" placeholder="e.g. Tuition Fees" required>
                <select name="items[${idx}][category]" class="form-control">
                    ${catOptions(cat)}
                </select>
                <input type="text" name="items[${idx}][amount]" class="form-control item-amount"
                    value="${formattedAmount}" required>
                <div class="toggle-wrap">
                    <input type="hidden" name="items[${idx}][is_mandatory]" value="0">
                    <input type="checkbox" name="items[${idx}][is_mandatory]" value="1" ${mand ? 'checked' : ''}>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-item" title="Remove">
                    <i class="fas fa-trash-alt"></i>
                </button>`;
            return card;
        }

        // ── Add a new blank item ──────────────────────────────────────────
        function addNewItem() {
            const empty = document.getElementById('emptyState');
            if (empty) empty.remove();

            const card = buildItemCard(itemIndex++);
            container.appendChild(card);

            card.style.opacity = '0';
            card.style.transform = 'translateY(-8px)';
            requestAnimationFrame(() => {
                card.style.transition = 'opacity .2s, transform .2s';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            });

            card.querySelector('input[type=text]').focus();
            updateUI();
        }

        // ── Remove item ───────────────────────────────────────────────────
        container.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-item');
            if (!btn) return;
            const card = btn.closest('.item-card');
            if (!card) return;

            Swal.fire({
                title: 'Remove Item?',
                text: 'This fee item will be removed from the structure.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    card.style.transition = 'opacity .15s, transform .15s';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(12px)';
                    setTimeout(() => {
                        card.remove();
                        updateUI();
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: 'Fee item has been removed.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }, 150);
                }
            });
        });

        // ── Handle amount input formatting ──────────────────────────────────
        container.addEventListener('input', function (e) {
            if (e.target.classList.contains('item-amount')) {
                const input = e.target;
                const rawValue = input.value;
                const numericValue = parseFormattedNumber(rawValue);

                input.setAttribute('data-raw-value', numericValue);

                if (numericValue !== '') {
                    input.value = formatNumberWithCommas(numericValue);
                } else {
                    input.value = '';
                }

                const newLength = input.value.length;
                input.setSelectionRange(newLength, newLength);
                recalcTotal();
            }
        });

        // ── Recalc total ──────────────────────────────────────────────────
        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('.item-amount').forEach(input => {
                const rawValue = parseFormattedNumber(input.value);
                total += parseFloat(rawValue) || 0;
            });
            totalDisp.textContent = total.toLocaleString('en-UG', { maximumFractionDigits: 0 });
        }

        // ── Update UI state ──────────────────────────────────────────────
        function updateUI() {
            const cards = container.querySelectorAll('.item-card');
            const count = cards.length;

            itemCount.textContent = count;
            totalBar.style.display = count ? '' : 'none';
            colHeaders.style.display = count ? '' : 'none';

            if (count === 0 && !document.getElementById('emptyState')) {
                container.innerHTML = `
                    <div class="items-empty" id="emptyState">
                        <i class="fas fa-receipt"></i>
                        <p style="margin:.5rem 0 1rem;font-size:.875rem;">No fee items yet.<br>Click <strong>Add Item</strong> to start building this fee structure.</p>
                        <button type="button" class="btn btn-add btn-sm" onclick="addNewItem()">
                            <i class="fas fa-plus"></i> Add First Item
                        </button>
                    </div>`;
            }
            recalcTotal();
        }

        // ── Format existing amount fields ──────────────────────────────────
        document.querySelectorAll('.item-amount').forEach(input => {
            if (input.value && input.value != 0) {
                let rawValue = input.value;
                if (!isNaN(rawValue) && rawValue.indexOf(',') === -1) {
                    input.value = formatNumberWithCommas(rawValue);
                }
                input.setAttribute('data-raw-value', parseFormattedNumber(input.value));
            }
        });

        // ── SweetAlert confirmation on form submit ─────────────────────────
        const feeForm = document.getElementById('feeForm');

        feeForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const cards = container.querySelectorAll('.item-card');
            if (cards.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Fee Items',
                    text: 'Please add at least one fee line item before saving.',
                    confirmButtonColor: '#2f2ccb'
                });
                return;
            }

            document.querySelectorAll('.item-amount').forEach(input => {
                const rawValue = parseFormattedNumber(input.value);
                input.value = rawValue;
            });

            const title = isEdit ? 'Update Fee Structure?' : 'Create Fee Structure?';
            const text = isEdit
                ? 'Are you sure you want to update this fee structure? Changes will affect student fee allocations.'
                : 'Are you sure you want to create this fee structure? It will be available for fee allocation.';
            const confirmButtonText = isEdit ? 'Yes, update it!' : 'Yes, create it!';

            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#dc2626',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: isEdit ? 'Updating fee structure...' : 'Creating fee structure...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    feeForm.submit();
                } else {
                    document.querySelectorAll('.item-amount').forEach(input => {
                        const rawValue = input.getAttribute('data-raw-value');
                        if (rawValue && rawValue !== '') {
                            input.value = formatNumberWithCommas(rawValue);
                        }
                    });
                }
            });
        });

        // ── Wire up the top Add Item button ──────────────────────────────
        document.getElementById('addItem').addEventListener('click', addNewItem);

        // ── Init ──────────────────────────────────────────────────────────
        updateUI();

        // Show success/error messages with SweetAlert
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: true,
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

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please check the form and try again.',
                confirmButtonColor: '#2f2ccb'
            });
        @endif
    </script>
    <script>
            // ── Format amount input with commas as user types ──────────────────
            function formatNumberWithCommas(value) {
                // Remove any non-digit characters
                let numbers = value.toString().replace(/\D/g, '');
                // Convert to number and format with commas
                if (numbers === '') return '';
                return parseInt(numbers, 10).toLocaleString('en-US');
            }

        function parseFormattedNumber(value) {
            // Remove commas and convert to number
            return value.toString().replace(/,/g, '');
        }

        // Add event listener to format amount inputs
        container.addEventListener('input', function (e) {
            if (e.target.classList.contains('item-amount')) {
                const input = e.target;
                const cursorPosition = input.selectionStart;
                const rawValue = input.value;
                const numericValue = parseFormattedNumber(rawValue);

                // Store the raw numeric value as a data attribute
                input.setAttribute('data-raw-value', numericValue);

                // Format with commas for display
                if (numericValue !== '') {
                    input.value = formatNumberWithCommas(numericValue);
                } else {
                    input.value = '';
                }

                // Restore cursor position (roughly)
                const newLength = input.value.length;
                input.setSelectionRange(newLength, newLength);

                // Trigger total recalculation
                recalcTotal();
            }
        });

        // Override the buildItemCard function to initialize amount fields with formatting
        const originalBuildItemCard = buildItemCard;
        window.buildItemCard = function (idx, data = {}) {
            const card = originalBuildItemCard(idx, data);
            const amountInput = card.querySelector('.item-amount');
            if (amountInput && amountInput.value && amountInput.value != 0) {
                amountInput.value = formatNumberWithCommas(amountInput.value);
            }
            return card;
        };

        // Update the recalcTotal function to handle formatted values
        window.recalcTotal = recalcTotal;
        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('.item-amount').forEach(input => {
                const rawValue = parseFormattedNumber(input.value);
                total += parseFloat(rawValue) || 0;
            });
            totalDisp.textContent = total.toLocaleString('en-UG', { maximumFractionDigits: 0 });
        }

        // Also format existing amount fields on page load
        // Also format existing amount fields on page load
        document.querySelectorAll('.item-amount').forEach(input => {
            if (input.value && input.value != 0) {
                // Check if the value is already formatted or raw
                let rawValue = input.value;
                if (!isNaN(rawValue) && rawValue.indexOf(',') === -1) {
                    // Raw number without commas, format it
                    input.value = formatNumberWithCommas(rawValue);
                }
                input.setAttribute('data-raw-value', parseFormattedNumber(input.value));
            }
        });

        // Also update the initial total calculation to handle formatted values
        setTimeout(() => {
            recalcTotal();
        }, 100);

        // Update the form submission to convert formatted values back to raw numbers
        document.getElementById('feeForm').addEventListener('submit', function (e) {
            // Convert all formatted amount inputs back to raw numbers before submit
            document.querySelectorAll('.item-amount').forEach(input => {
                const rawValue = parseFormattedNumber(input.value);
                input.value = rawValue;
            });

            // Original validation
            const cards = container.querySelectorAll('.item-card');
            if (cards.length === 0) {
                e.preventDefault();
                alert('Please add at least one fee line item before saving.');
                return;
            }
        });
    </script>
@endsection