{{-- resources/views/Finance/budget-form.blade.php --}}
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
            --fin-red: #dc2626;
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

        .form-container {
            padding: 1.5rem;
        }

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

        .form-group label .required {
            color: var(--fin-red);
            margin-left: .2rem;
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
            color: var(--text-1);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

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

        .form-actions {
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            margin-top: 1rem;
        }

        .info-note {
            background: rgba(47, 44, 203, .08);
            border-radius: 12px;
            padding: .8rem 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .8rem;
            color: #2f2ccb;
            border: 1px solid rgba(47, 44, 203, .15);
        }

        .info-note i {
            font-size: 1rem;
        }

        .items-section {
            margin: 1.5rem 0;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .items-header {
            background: #fafbff;
            padding: .8rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .items-header h4 {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            background: #2f2ccb;
            padding: .7rem .8rem;
            font-size: .7rem;
            font-weight: 700;
            color: #FFF;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-align: left;
        }

        .items-table td {
            padding: .6rem .8rem;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .items-table input,
        .items-table select {
            width: 100%;
            padding: .5rem .7rem;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            font-size: .8rem;
            transition: all .15s;
        }

        .items-table input:focus,
        .items-table select:focus {
            outline: none;
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }

        .item-actions {
            text-align: center;
            width: 45px;
        }

        .add-item-btn {
            background: rgba(47, 44, 203, .05);
            color: #2f2ccb;
            border: 1.5px dashed #2f2ccb;
            border-radius: 10px;
            padding: .6rem;
            text-align: center;
            cursor: pointer;
            margin: .5rem .8rem .8rem;
            transition: all .2s;
            font-size: .8rem;
            font-weight: 600;
        }

        .add-item-btn:hover {
            background: #2f2ccb;
            color: #fff;
            border-style: solid;
        }

        .summary-box {
            background: linear-gradient(135deg, rgba(47, 44, 203, .05), rgba(47, 44, 203, .02));
            border-radius: var(--radius-sm);
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid rgba(47, 44, 203, .15);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: .3rem 0;
            font-size: .85rem;
        }

        .summary-row.total {
            font-weight: 800;
            font-size: 1rem;
            color: #2f2ccb;
            border-top: 1px solid rgba(47, 44, 203, .2);
            margin-top: .3rem;
            padding-top: .5rem;
        }

        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
        }

        .badge-fin {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .7rem;
            border-radius: 20px;
            font-size: .74rem;
            font-weight: 600;
        }

        .badge-amber {
            background: var(--fin-amber-l);
            color: var(--fin-amber);
        }

        @media(max-width:768px) {
            .fin-hero { padding: 1.5rem; }
            .fin-hero h1 { font-size: 1.3rem; }
            .form-row { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column-reverse; }
            .form-actions .btn-fin { width: 100%; justify-content: center; }
            .fin-card-header { flex-direction: column; align-items: flex-start; }
            .items-table { min-width: 600px; display: block; overflow-x: auto; }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-plus-circle"></i> Finance — {{ isset($budget) ? 'Edit' : 'New' }} Budget</div>
            <h1>{{ isset($budget) ? 'Edit Budget' : 'Create New Budget' }}</h1>
            <p>{{ isset($budget) ? 'Modify budget details and line items' : 'Plan your income and expenses for the academic year' }}</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-chart-line"></i> Budget Information</h3>
            @if(isset($budget))
                <span style="font-size:.75rem;color:var(--text-3);">Status: <strong class="badge-fin badge-amber">{{ ucfirst($budget->status) }}</strong></span>
            @endif
        </div>

        <form method="POST"
            action="{{ isset($budget) ? route('finance.budgets.update', $budget->id) : route('finance.budgets.store') }}"
            id="budgetForm" novalidate>
            @csrf
            @if(isset($budget)) @method('PUT') @endif

            <div class="form-container">

                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    <span>Create a budget with income and expense items. Actual amounts will be auto-calculated from fee collections and expenses.</span>
                </div>

                <div class="form-group">
                    <label>Budget Title <span class="required">*</span></label>
                    <input type="text" name="title" id="title" required
                        value="{{ old('title', $budget->title ?? '') }}"
                        placeholder="e.g., Annual Budget 2026, Term 1 Budget">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Academic Year <span class="required">*</span></label>
                        <select name="academic_year" id="academic_year" required>
                            <option value="{{ date('Y') }}" {{ (old('academic_year', $budget->academic_year ?? date('Y')) == date('Y')) ? 'selected' : '' }}>{{ date('Y') }}</option>
                            <option value="{{ date('Y') - 1 }}" {{ (old('academic_year', $budget->academic_year ?? '') == date('Y') - 1) ? 'selected' : '' }}>{{ date('Y') - 1 }}</option>
                            <option value="{{ date('Y') - 2 }}" {{ (old('academic_year', $budget->academic_year ?? '') == date('Y') - 2) ? 'selected' : '' }}>{{ date('Y') - 2 }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Term (Optional)</label>
                        <select name="term" id="term">
                            <option value="">-- Full Year Budget --</option>
                            <option value="1" {{ (old('term', $budget->term ?? '') == '1') ? 'selected' : '' }}>Term 1</option>
                            <option value="2" {{ (old('term', $budget->term ?? '') == '2') ? 'selected' : '' }}>Term 2</option>
                            <option value="3" {{ (old('term', $budget->term ?? '') == '3') ? 'selected' : '' }}>Term 3</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Additional notes about this budget...">{{ old('notes', $budget->notes ?? '') }}</textarea>
                </div>

                {{-- ── Income Items ── --}}
                <div class="items-section">
                    <div class="items-header">
                        <h4><i class="fas fa-arrow-down" style="color:#059669;"></i> Income / Revenue Items</h4>
                        <button type="button" class="btn-fin btn-sm btn-outline-fin" onclick="addItem('income')">
                            <i class="fas fa-plus"></i> Add Income Item
                        </button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="items-table" id="incomeTable">
                            <thead>
                                <tr>
                                    <th style="width:40%">Item Name</th>
                                    <th style="width:30%">Category</th>
                                    <th style="width:25%">Budgeted Amount (UGX)</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="incomeItems">
                                @php $incomeItems = isset($budget) ? $budget->items->where('type', 'income') : collect(); @endphp

                                {{-- Hidden template row — NO required attributes --}}
                                <tr id="incomeTemplate" style="display:none;" aria-hidden="true">
                                    <td><input type="text" name="items[income][0][item_name]" placeholder="e.g., School Fees"></td>
                                    <td>
                                        <select name="items[income][0][category_id]">
                                            <option value="">-- Select --</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="items[income][0][budgeted_amount]" class="income-amount" placeholder="0"></td>
                                    <td class="item-actions">
                                        <button type="button" class="btn-fin btn-sm btn-danger-fin" onclick="removeItem(this)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>

                                {{-- Real rows when editing --}}
                                @foreach($incomeItems as $index => $item)
                                    <tr class="income-item">
                                        <td><input type="text" name="items[income][{{ $index }}][item_name]"
                                                value="{{ $item->item_name }}" placeholder="e.g., School Fees" required></td>
                                        <td>
                                            <select name="items[income][{{ $index }}][category_id]">
                                                <option value="">-- Select --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="items[income][{{ $index }}][budgeted_amount]"
                                                value="{{ number_format($item->budgeted_amount, 0) }}"
                                                class="income-amount" placeholder="0" required></td>
                                        <td class="item-actions">
                                            <button type="button" class="btn-fin btn-sm btn-danger-fin" onclick="removeItem(this)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="add-item-btn" onclick="addItem('income')">
                        <i class="fas fa-plus"></i> Add Income Item
                    </div>
                </div>

                {{-- ── Expense Items ── --}}
                <div class="items-section">
                    <div class="items-header">
                        <h4><i class="fas fa-arrow-up" style="color:#dc2626;"></i> Expense Items</h4>
                        <button type="button" class="btn-fin btn-sm btn-outline-fin" onclick="addItem('expense')">
                            <i class="fas fa-plus"></i> Add Expense Item
                        </button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="items-table" id="expenseTable">
                            <thead>
                                <tr>
                                    <th style="width:40%">Item Name</th>
                                    <th style="width:30%">Category</th>
                                    <th style="width:25%">Budgeted Amount (UGX)</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="expenseItems">
                                @php $expenseItems = isset($budget) ? $budget->items->where('type', 'expense') : collect(); @endphp

                                {{-- Hidden template row — NO required attributes --}}
                                <tr id="expenseTemplate" style="display:none;" aria-hidden="true">
                                    <td><input type="text" name="items[expense][0][item_name]" placeholder="e.g., Salaries"></td>
                                    <td>
                                        <select name="items[expense][0][category_id]">
                                            <option value="">-- Select --</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="items[expense][0][budgeted_amount]" class="expense-amount" placeholder="0"></td>
                                    <td class="item-actions">
                                        <button type="button" class="btn-fin btn-sm btn-danger-fin" onclick="removeItem(this)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>

                                {{-- Real rows when editing --}}
                                @foreach($expenseItems as $index => $item)
                                    <tr class="expense-item">
                                        <td><input type="text" name="items[expense][{{ $index }}][item_name]"
                                                value="{{ $item->item_name }}" placeholder="e.g., Salaries" required></td>
                                        <td>
                                            <select name="items[expense][{{ $index }}][category_id]">
                                                <option value="">-- Select --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="items[expense][{{ $index }}][budgeted_amount]"
                                                value="{{ number_format($item->budgeted_amount, 0) }}"
                                                class="expense-amount" placeholder="0" required></td>
                                        <td class="item-actions">
                                            <button type="button" class="btn-fin btn-sm btn-danger-fin" onclick="removeItem(this)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="add-item-btn" onclick="addItem('expense')">
                        <i class="fas fa-plus"></i> Add Expense Item
                    </div>
                </div>

                {{-- Summary --}}
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Total Budgeted Income:</span>
                        <span class="amount-mono" id="totalIncomeDisplay">UGX 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Budgeted Expenses:</span>
                        <span class="amount-mono" id="totalExpenseDisplay">UGX 0</span>
                    </div>
                    <div class="summary-row total">
                        <span>Net Budgeted Surplus / Deficit:</span>
                        <span class="amount-mono" id="netBudgetDisplay">UGX 0</span>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="form-actions">
                    <a href="{{ route('finance.budgets.index') }}" class="btn-fin btn-outline-fin">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-fin btn-primary-fin">
                        <i class="fas fa-save"></i> {{ isset($budget) ? 'Update Budget' : 'Create Budget' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
     </div>
                </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let incomeCounter  = {{ $incomeItems->count() > 0 ? $incomeItems->count() : 1 }};
        let expenseCounter = {{ $expenseItems->count() > 0 ? $expenseItems->count() : 1 }};

        function formatNumberWithCommas(value) {
            let numbers = value.toString().replace(/\D/g, '');
            if (numbers === '' || numbers === '0') return '';
            return parseInt(numbers, 10).toLocaleString('en-US');
        }

        function parseFormattedNumber(value) {
            return value.toString().replace(/,/g, '');
        }

        function attachAmountListeners(input) {
            input.type = 'text';
            input.addEventListener('input', function () {
                const raw = parseFormattedNumber(this.value);
                this.value = raw !== '' ? formatNumberWithCommas(raw) : '';
                updateTotals();
            });
            input.addEventListener('blur', function () {
                const raw = parseFormattedNumber(this.value);
                this.value = raw !== '' ? formatNumberWithCommas(raw) : '';
            });
        }

        function addItem(type) {
            const isIncome   = type === 'income';
            const tbodyId    = isIncome ? 'incomeItems'   : 'expenseItems';
            const templateId = isIncome ? 'incomeTemplate' : 'expenseTemplate';
            const counter    = isIncome ? incomeCounter    : expenseCounter;
            const amountClass= isIncome ? 'income-amount'  : 'expense-amount';
            const rowClass   = isIncome ? 'income-item'    : 'expense-item';

            const tbody   = document.getElementById(tbodyId);
            const template= document.getElementById(templateId);
            const newRow  = template.cloneNode(true);

            newRow.style.display = '';
            newRow.removeAttribute('id');
            newRow.removeAttribute('aria-hidden');
            newRow.classList.add(rowClass);

            // Update name attributes and add required + listeners
            newRow.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace('[0]', `[${counter}]`));
                }
                // Add required to real text inputs
                if (el.tagName === 'INPUT' && el.type === 'text') {
                    el.setAttribute('required', 'required');
                    el.value = '';
                }
                // Attach formatting listeners to amount input
                if (el.classList && el.classList.contains(amountClass)) {
                    attachAmountListeners(el);
                }
            });

            tbody.appendChild(newRow);

            if (isIncome) incomeCounter++;
            else expenseCounter++;

            updateTotals();
        }

        function removeItem(button) {
            button.closest('tr').remove();
            updateTotals();
        }

        function updateTotals() {
            let totalIncome  = 0;
            let totalExpense = 0;

            // Skip hidden template rows
            document.querySelectorAll('#incomeItems tr:not(#incomeTemplate) .income-amount').forEach(input => {
                const val = parseFloat(parseFormattedNumber(input.value));
                if (!isNaN(val)) totalIncome += val;
            });

            document.querySelectorAll('#expenseItems tr:not(#expenseTemplate) .expense-amount').forEach(input => {
                const val = parseFloat(parseFormattedNumber(input.value));
                if (!isNaN(val)) totalExpense += val;
            });

            const net = totalIncome - totalExpense;

            document.getElementById('totalIncomeDisplay').textContent  = `UGX ${totalIncome.toLocaleString()}`;
            document.getElementById('totalExpenseDisplay').textContent = `UGX ${totalExpense.toLocaleString()}`;

            const netEl = document.getElementById('netBudgetDisplay');
            netEl.textContent  = `UGX ${Math.abs(net).toLocaleString()}${net < 0 ? ' (Deficit)' : ''}`;
            netEl.style.color  = net >= 0 ? '#059669' : '#dc2626';
        }

        // Attach listeners to existing real rows (edit mode)
        document.querySelectorAll(
            '#incomeItems tr:not(#incomeTemplate) .income-amount, ' +
            '#expenseItems tr:not(#expenseTemplate) .expense-amount'
        ).forEach(input => {
            // Format existing values
            const raw = parseFormattedNumber(input.value);
            if (raw !== '') input.value = formatNumberWithCommas(raw);
            attachAmountListeners(input);
        });

        updateTotals();

        // ── Form submit ──
        document.getElementById('budgetForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Basic validation
            const title = document.getElementById('title').value.trim();
            if (!title) {
                Swal.fire({ icon: 'error', title: 'Missing Title', text: 'Please enter a budget title.', confirmButtonColor: '#2f2ccb' });
                return;
            }

            const incomeRows  = document.querySelectorAll('#incomeItems tr:not(#incomeTemplate)');
            const expenseRows = document.querySelectorAll('#expenseItems tr:not(#expenseTemplate)');
            if (incomeRows.length === 0 && expenseRows.length === 0) {
                Swal.fire({ icon: 'error', title: 'No Items', text: 'Please add at least one budget item.', confirmButtonColor: '#2f2ccb' });
                return;
            }

            // Validate filled rows manually (since we use novalidate)
            let valid = true;
            document.querySelectorAll(
                '#incomeItems tr:not(#incomeTemplate) input[required], ' +
                '#expenseItems tr:not(#expenseTemplate) input[required]'
            ).forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc2626';
                    valid = false;
                } else {
                    input.style.borderColor = '';
                }
            });

            if (!valid) {
                Swal.fire({ icon: 'error', title: 'Incomplete Items', text: 'Please fill in all item names and amounts.', confirmButtonColor: '#2f2ccb' });
                return;
            }

            // Strip commas before submit
            document.querySelectorAll('.income-amount, .expense-amount').forEach(input => {
                input.value = parseFormattedNumber(input.value);
            });

            Swal.fire({
                title: 'Save Budget?',
                text: 'Are you sure you want to save this budget?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, save!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Saving budget...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('budgetForm').submit();
                        }
                    });
                } else {
                    // Restore formatted values if cancelled
                    document.querySelectorAll('.income-amount, .expense-amount').forEach(input => {
                        if (input.value !== '') input.value = formatNumberWithCommas(input.value);
                    });
                }
            });
        });
    </script>
@endsection