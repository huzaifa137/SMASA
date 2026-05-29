@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --g: #059669;
            --gl: rgba(5, 150, 105, .10);
            --r: #dc2626;
            --rl: rgba(220, 38, 38, .10);
            --b: #2563eb;
            --bl: rgba(37, 99, 235, .10);
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

        * {
            font-family: 'DM Sans', sans-serif;
            box-sizing: border-box
        }

        body {
            background: var(--bg)
        }

        .fin-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f2d4a 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(5, 150, 105, .25) 0%, transparent 70%)
        }

        .fin-hero h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0
        }

        .fin-hero p {
            color: #94a3b8;
            margin: .2rem 0 0;
            font-size: .88rem
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(5, 150, 105, .2);
            border: 1px solid rgba(5, 150, 105, .35);
            color: #34d399;
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: .6rem
        }

        .card {
            background: var(--surf);
            border-radius: var(--rad);
            border: 1px solid var(--brd);
            box-shadow: var(--sh);
            overflow: hidden;
            margin-bottom: 1.5rem
        }

        .card-hd {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--brd);
            display: flex;
            align-items: center;
            gap: .6rem
        }

        .card-hd h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--t1)
        }

        .card-bd {
            padding: 1.5rem
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--t2);
            margin-bottom: .4rem;
            letter-spacing: .02em
        }

        .form-control {
            width: 100%;
            padding: .6rem .9rem;
            border: 1.5px solid var(--brd);
            border-radius: var(--rads);
            font-size: .875rem;
            color: var(--t1);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            outline: none
        }

        .form-control:focus {
            border-color: var(--b);
            box-shadow: 0 0 0 3px var(--bl)
        }

        .form-control.is-invalid {
            border-color: var(--r)
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.25rem
        }

        .form-group {
            margin-bottom: 1rem
        }

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
            transition: all .18s
        }

        .btn-primary {
            background: var(--g);
            color: #fff
        }

        .btn-primary:hover {
            background: #047857;
            color: #fff;
            transform: translateY(-1px)
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--brd);
            color: var(--t2)
        }

        .btn-outline:hover {
            border-color: var(--b);
            color: var(--b)
        }

        .btn-danger {
            background: var(--rl);
            color: var(--r);
            border: 1px solid rgba(220, 38, 38, .2)
        }

        .btn-danger:hover {
            background: var(--r);
            color: #fff
        }

        .btn-add {
            background: var(--bl);
            color: var(--b);
            border: 1px solid rgba(37, 99, 235, .2)
        }

        .btn-add:hover {
            background: var(--b);
            color: #fff
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse
        }

        .items-table th {
            background: #f8fafc;
            padding: .7rem 1rem;
            font-size: .73rem;
            font-weight: 700;
            color: var(--t3);
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid var(--brd)
        }

        .items-table td {
            padding: .6rem .75rem;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle
        }

        .items-table tr:last-child td {
            border-bottom: none
        }

        .item-row:hover td {
            background: #fafbfc
        }

        .total-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(90deg, var(--gl), transparent);
            border: 1px solid rgba(5, 150, 105, .2);
            border-radius: var(--rads);
            padding: 1rem 1.5rem;
            margin-top: 1rem
        }

        .total-label {
            font-size: .85rem;
            font-weight: 600;
            color: var(--t2)
        }

        .total-value {
            font-family: 'DM Mono', monospace;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--g)
        }

        .invalid-feedback {
            font-size: .75rem;
            color: var(--r);
            margin-top: .3rem
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .75rem center;
            padding-right: 2rem
        }

        .drag-handle {
            cursor: grab;
            color: var(--t3);
            padding: 0 .25rem
        }

        .drag-handle:active {
            cursor: grabbing
        }

        @media(max-width:768px) {

            .form-grid,
            .form-grid-3 {
                grid-template-columns: 1fr
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero">
        <div style="position:relative;z-index:1">
            <div class="hero-badge"><i class="fas fa-layer-group"></i> Finance — Fee Structures</div>
            <h1>{{ isset($structure) ? 'Edit Fee Structure' : 'New Fee Structure' }}</h1>
            <p>{{ isset($structure) ? 'Update the fee template and its line items' : 'Define a fee template with itemised charges for a class/term' }}
            </p>
        </div>
    </div>
@endsection

@section('content')
    @php $isEdit = isset($structure); @endphp

    <form method="POST"
        action="{{ $isEdit ? route('finance.fee-structures.update', $structure->id) : route('finance.fee-structures.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Meta --}}
        <div class="card">
            <div class="card-hd">
                <i class="fas fa-info-circle" style="color:var(--b)"></i>
                <h3>Structure Details</h3>
            </div>
            <div class="card-bd">
                <div class="form-grid" style="margin-bottom:1.25rem">
                    <div class="form-group">
                        <label class="form-label">Structure Name <span style="color:var(--r)">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $structure->name ?? '') }}" placeholder="e.g. S.1 Boarding Term 1 — 2026">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Class Level</label>
                        <input type="text" name="class_level" class="form-control"
                            value="{{ old('class_level', $structure->class_level ?? '') }}"
                            placeholder="e.g. S.1, P.6, All">
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
                                    {{ $l }}</option>
                            @endforeach
                        </select>
                        @if($isEdit)<input type="hidden" name="term" value="{{ $structure->term }}">@endif
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
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"
                        placeholder="Optional notes about this fee structure…">{{ old('notes', $structure->notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Line Items --}}
        <div class="card">
            <div class="card-hd" style="justify-content:space-between">
                <div style="display:flex;align-items:center;gap:.6rem">
                    <i class="fas fa-list" style="color:var(--g)"></i>
                    <h3>Fee Line Items</h3>
                </div>
                <button type="button" class="btn btn-add btn-sm" id="addItem">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
            <div class="card-bd" style="padding:0">
                <div style="overflow-x:auto">
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:2rem"></th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th style="width:160px">Amount (UGX)</th>
                                <th style="width:90px;text-align:center">Mandatory</th>
                                <th style="width:2.5rem"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @php $items = old('items', isset($structure) ? $structure->items->toArray() : []); @endphp
                            @forelse($items as $i => $item)
                                <tr class="item-row" data-index="{{ $i }}">
                                    <td><span class="drag-handle"><i class="fas fa-grip-vertical"></i></span></td>
                                    <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control"
                                            value="{{ $item['item_name'] ?? '' }}" placeholder="e.g. Tuition Fees" required>
                                    </td>
                                    <td>
                                        <select name="items[{{ $i }}][category]" class="form-control">
                                            @foreach(['tuition' => 'Tuition', 'boarding' => 'Boarding', 'activity' => 'Activity', 'library' => 'Library', 'sport' => 'Sports', 'medical' => 'Medical', 'exam' => 'Exams', 'other' => 'Other'] as $cv => $cl)
                                                <option value="{{ $cv }}" {{ ($item['category'] ?? '') == $cv ? 'selected' : '' }}>
                                                    {{ $cl }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[{{ $i }}][amount]" class="form-control item-amount"
                                            value="{{ $item['amount'] ?? 0 }}" min="0" step="100" required
                                            oninput="recalcTotal()"></td>
                                    <td style="text-align:center">
                                        <input type="hidden" name="items[{{ $i }}][is_mandatory]" value="0">
                                        <input type="checkbox" name="items[{{ $i }}][is_mandatory]" value="1" {{ ($item['is_mandatory'] ?? true) ? 'checked' : '' }}
                                            style="width:18px;height:18px;cursor:pointer;accent-color:var(--g)">
                                    </td>
                                    <td><button type="button" class="btn btn-danger remove-item" style="padding:.3rem .6rem"><i
                                                class="fas fa-times"></i></button></td>
                                </tr>
                            @empty
                                <tr class="item-row" data-index="0">
                                    <td><span class="drag-handle"><i class="fas fa-grip-vertical"></i></span></td>
                                    <td><input type="text" name="items[0][item_name]" class="form-control"
                                            placeholder="e.g. Tuition Fees" required></td>
                                    <td>
                                        <select name="items[0][category]" class="form-control">
                                            @foreach(['tuition' => 'Tuition', 'boarding' => 'Boarding', 'activity' => 'Activity', 'library' => 'Library', 'sport' => 'Sports', 'medical' => 'Medical', 'exam' => 'Exams', 'other' => 'Other'] as $cv => $cl)
                                                <option value="{{ $cv }}">{{ $cl }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[0][amount]" class="form-control item-amount" value="0"
                                            min="0" step="100" required oninput="recalcTotal()"></td>
                                    <td style="text-align:center">
                                        <input type="hidden" name="items[0][is_mandatory]" value="0">
                                        <input type="checkbox" name="items[0][is_mandatory]" value="1" checked
                                            style="width:18px;height:18px;cursor:pointer;accent-color:var(--g)">
                                    </td>
                                    <td><button type="button" class="btn btn-danger remove-item" style="padding:.3rem .6rem"><i
                                                class="fas fa-times"></i></button></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="padding:1rem 1.5rem">
                    <div class="total-bar">
                        <span class="total-label"><i class="fas fa-sigma" style="color:var(--g);margin-right:.4rem"></i>
                            Total Fee Amount</span>
                        <span class="total-value">UGX <span
                                id="totalDisplay">{{ number_format(isset($structure) ? $structure->total_amount : 0, 0) }}</span></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-bottom:2rem">
            <a href="{{ route('finance.fee-structures.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Update Structure' : 'Create Structure' }}
            </button>
        </div>
    </form>

    @php
    $isEdit = isset($structure);
    $categories = [
        'tuition'  => 'Tuition',
        'boarding' => 'Boarding',
        'activity' => 'Activity',
        'library'  => 'Library',
        'sport'    => 'Sports',
        'medical'  => 'Medical',
        'exam'     => 'Exams',
        'other'    => 'Other',
    ];
@endphp

@endsection

@section('scripts')
    <script>
        let itemIndex = {{ count(old('items', isset($structure) ? $structure->items->toArray() : [['x']])) }};

const categoryOptions = @json($categories);

        function buildRow(idx) {
            let opts = Object.entries(categoryOptions).map(([v, l]) => `<option value="${v}">${l}</option>`).join('');
            return `<tr class="item-row" data-index="${idx}">
            <td><span class="drag-handle"><i class="fas fa-grip-vertical"></i></span></td>
            <td><input type="text" name="items[${idx}][item_name]" class="form-control" placeholder="e.g. Boarding Fees" required></td>
            <td><select name="items[${idx}][category]" class="form-control">${opts}</select></td>
            <td><input type="number" name="items[${idx}][amount]" class="form-control item-amount" value="0" min="0" step="100" required oninput="recalcTotal()"></td>
            <td style="text-align:center">
                <input type="hidden" name="items[${idx}][is_mandatory]" value="0">
                <input type="checkbox" name="items[${idx}][is_mandatory]" value="1" checked style="width:18px;height:18px;cursor:pointer;accent-color:var(--g)">
            </td>
            <td><button type="button" class="btn btn-danger remove-item" style="padding:.3rem .6rem"><i class="fas fa-times"></i></button></td>
        </tr>`;
        }

        document.getElementById('addItem').addEventListener('click', function () {
            document.getElementById('itemsBody').insertAdjacentHTML('beforeend', buildRow(itemIndex++));
            recalcTotal();
        });

        document.getElementById('itemsBody').addEventListener('click', function (e) {
            if (e.target.closest('.remove-item')) {
                const rows = document.querySelectorAll('.item-row');
                if (rows.length > 1) { e.target.closest('tr').remove(); recalcTotal(); }
            }
        });

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('.item-amount').forEach(i => total += parseFloat(i.value) || 0);
            document.getElementById('totalDisplay').textContent = total.toLocaleString('en-UG', { maximumFractionDigits: 0 });
        }

        recalcTotal();
    </script>
@endsection