{{-- resources/views/Finance/budget-detail.blade.php --}}
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

        /* Stat Grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

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

        .stat-card .sub {
            font-size: .7rem;
            margin-top: .2rem;
        }

        .progress-bar {
            height: 5px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
            margin-top: .5rem;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .3s;
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

        .btn-success-fin {
            background: var(--fin-green);
            color: #fff;
        }

        .btn-success-fin:hover {
            background: #047857;
        }

        .btn-warning-fin {
            background: var(--fin-amber);
            color: #fff;
        }

        .btn-warning-fin:hover {
            background: #b45309;
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

        .variance-up {
            color: var(--fin-green);
        }

        .variance-down {
            color: var(--fin-red);
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead tr {
            background: #2c29ca;
        }

        .items-table th {
            padding: .8rem 1rem;
            font-size: .72rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
            text-align: left;
        }

        .items-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .items-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        .items-table td {
            padding: .8rem 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: .85rem;
            color: var(--text-1);
            vertical-align: middle;
        }

        .items-table tr:hover td {
            background: #f5f6ff;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .items-table tfoot tr {
            background: #f5f6ff;
            font-weight: 700;
        }

        .items-table tfoot td {
            border-top: 2px solid var(--border);
        }

        /* Section Divider */
        .section-divider {
            font-size: .9rem;
            font-weight: 700;
            color: var(--text-1);
            margin: 1.5rem 0 1rem;
            padding-bottom: .5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Responsive */
        @media(max-width:900px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

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

            .fin-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .items-table {
                min-width: 600px;
                display: block;
                overflow-x: auto;
            }
        }

        /* Print Styles */
        @media print {

            .fin-hero,
            .stat-grid .btn-fin,
            .action-buttons,
            .form-actions,
            .fin-card-header .btn-fin {
                display: none !important;
            }

            .fin-card {
                break-inside: avoid;
                margin-bottom: 1rem;
            }

            .stat-card {
                border: 1px solid #ddd;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-chart-line"></i> Budget Details</div>
            <h1>{{ $budget->title }}</h1>
            <p>{{ $budget->academic_year }} @if($budget->term) • Term {{ $budget->term }} @else • Full Year @endif</p>
        </div>
    </div>
@endsection

@section('content')

    @php
        // Calculate actual income from fee payments
        $actualIncome = \App\Models\FeePayment::where('school_id', session('LoggedSchool'))
            ->where('academic_year', $budget->academic_year)
            ->where('status', 'confirmed')
            ->when($budget->term, fn($q) => $q->where('term', $budget->term))
            ->sum('amount_paid');

        // Calculate actual expenses
        $actualExpense = \App\Models\Expense::where('school_id', session('LoggedSchool'))
            ->where('academic_year', $budget->academic_year)
            ->whereIn('status', ['approved', 'paid'])
            ->when($budget->term, fn($q) => $q->where('term', $budget->term))
            ->sum('amount');

        // Calculate actual payroll
        $actualPayroll = \App\Models\PayrollSlip::where('school_id', session('LoggedSchool'))
            ->whereHas('period', function ($q) use ($budget) {
                $q->where('academic_year', $budget->academic_year)
                    ->where('status', 'paid');
                if ($budget->term)
                    $q->where('term', $budget->term);
            })
            ->sum('net_pay');

        $totalActualExpense = $actualExpense + $actualPayroll;

        $incomeVariance = $actualIncome - $budget->total_income_budget;
        $expenseVariance = $totalActualExpense - $budget->total_expense_budget;
        $netActual = $actualIncome - $totalActualExpense;
        $netBudget = $budget->total_income_budget - $budget->total_expense_budget;
        $netVariance = $netActual - $netBudget;

        $incomePercent = $budget->total_income_budget > 0 ? round(($actualIncome / $budget->total_income_budget) * 100) : 0;
        $expensePercent = $budget->total_expense_budget > 0 ? round(($totalActualExpense / $budget->total_expense_budget) * 100) : 0;
    @endphp

    {{-- Summary Stats --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="value">UGX {{ number_format($actualIncome, 0) }}</div>
            <div class="label">Actual Income</div>
            <div class="sub">Budgeted: UGX {{ number_format($budget->total_income_budget, 0) }}</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ min(100, $incomePercent) }}%;background:#059669;"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="value">UGX {{ number_format($totalActualExpense, 0) }}</div>
            <div class="label">Actual Expenses</div>
            <div class="sub">Budgeted: UGX {{ number_format($budget->total_expense_budget, 0) }}</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ min(100, $expensePercent) }}%;background:#dc2626;"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="value" style="color:{{ $netActual >= 0 ? '#059669' : '#2f2ccb' }};">UGX
                {{ number_format($netActual, 0) }}</div>
            <div class="label">Net Actual</div>
            <div class="sub">Budgeted: UGX {{ number_format($netBudget, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="value" style="color:{{ $netVariance >= 0 ? '#059669' : '#dc2626' }};">UGX
                {{ number_format($netVariance, 0) }}</div>
            <div class="label">Variance</div>
            <div class="sub">{{ $netVariance >= 0 ? 'Favorable' : 'Unfavorable' }}</div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="margin-bottom:1.5rem;display:flex;gap:.75rem;justify-content:flex-end;flex-wrap:wrap;">
        <a href="{{ route('finance.budgets.index') }}" class="btn-fin btn-outline-fin">
            <i class="fas fa-arrow-left"></i> Back to Budgets
        </a>
        @if($budget->status == 'draft')
            <button class="btn-fin btn-success-fin" onclick="confirmApproveBudget({{ $budget->id }})">
                <i class="fas fa-check-circle"></i> Approve Budget
            </button>
            <form id="approveBudgetForm{{ $budget->id }}" method="POST"
                action="{{ route('finance.budgets.approve', $budget->id) }}" style="display:none;">
                @csrf
            </form>
        @endif
        <button onclick="window.print()" class="btn-fin btn-outline-fin">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    {{-- Budget Performance Card --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-chart-line"></i> Budget vs Actual Summary</h3>
            <span
                class="badge-fin {{ $budget->status == 'approved' ? 'badge-green' : ($budget->status == 'draft' ? 'badge-amber' : 'badge-blue') }}">
                <i
                    class="fas fa-{{ $budget->status == 'approved' ? 'check-circle' : ($budget->status == 'draft' ? 'pen' : 'archive') }}"></i>
                {{ ucfirst($budget->status) }}
            </span>
        </div>
        <div style="padding:1.5rem;">
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1.5rem;">
                {{-- Income Section --}}
                <div>
                    <div style="font-weight:700;margin-bottom:.5rem;color:#059669;">
                        <i class="fas fa-arrow-down"></i> Income
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.4rem 0;">
                        <span>Budgeted Income:</span>
                        <span class="amount-mono">UGX {{ number_format($budget->total_income_budget, 0) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.4rem 0;">
                        <span>Actual Income:</span>
                        <span class="amount-mono">UGX {{ number_format($actualIncome, 0) }}</span>
                    </div>
                    <div
                        style="display:flex;justify-content:space-between;padding:.4rem 0;border-top:1px solid var(--border);margin-top:.3rem;padding-top:.6rem;">
                        <span>Variance:</span>
                        <span class="amount-mono {{ $incomeVariance >= 0 ? 'variance-up' : 'variance-down' }}">
                            {{ $incomeVariance >= 0 ? '+' : '' }}UGX {{ number_format($incomeVariance, 0) }}
                            ({{ $incomePercent }}%)
                        </span>
                    </div>
                </div>

                {{-- Expense Section --}}
                <div>
                    <div style="font-weight:700;margin-bottom:.5rem;color:#dc2626;">
                        <i class="fas fa-arrow-up"></i> Expenses
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.4rem 0;">
                        <span>Budgeted Expenses:</span>
                        <span class="amount-mono">UGX {{ number_format($budget->total_expense_budget, 0) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.4rem 0;">
                        <span>Actual Expenses:</span>
                        <span class="amount-mono">UGX {{ number_format($totalActualExpense, 0) }}</span>
                    </div>
                    <div
                        style="display:flex;justify-content:space-between;padding:.4rem 0;border-top:1px solid var(--border);margin-top:.3rem;padding-top:.6rem;">
                        <span>Variance:</span>
                        <span class="amount-mono {{ $expenseVariance <= 0 ? 'variance-up' : 'variance-down' }}">
                            {{ $expenseVariance >= 0 ? '+' : '' }}UGX {{ number_format($expenseVariance, 0) }}
                            ({{ $expensePercent }}%)
                        </span>
                    </div>
                </div>
            </div>

            {{-- Net Summary --}}
            <div style="margin-top:1.2rem;padding-top:1rem;border-top:2px solid var(--border);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:800;font-size:.9rem;">Net Budget Position:</span>
                    <div style="text-align:right;">
                        <div class="amount-mono"
                            style="font-size:1.2rem;color:{{ $netVariance >= 0 ? '#059669' : '#dc2626' }};">
                            UGX {{ number_format($netActual, 0) }}
                        </div>
                        <div style="font-size:.7rem;color:var(--text-3);">
                            Budgeted: UGX {{ number_format($netBudget, 0) }}
                            ({{ $netVariance >= 0 ? '+' : '' }}UGX {{ number_format($netVariance, 0) }} variance)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Income Items Breakdown --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-arrow-down" style="color:#059669;"></i> Income Items Breakdown</h3>
        </div>
        <div style="overflow-x:auto;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Budgeted Amount</th>
                        <th>Actual Amount</th>
                        <th>Variance</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $incomeItems = $budget->items->where('type', 'income');
                    @endphp
                    @forelse($incomeItems as $item)
                        @php
                            $actualItemAmount = 0;
                            $itemVariance = $actualItemAmount - $item->budgeted_amount;
                            $itemPercent = $item->budgeted_amount > 0 ? round(($actualItemAmount / $item->budgeted_amount) * 100) : 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $item->item_name }}</strong></td>
                            <td>{{ $item->category->name ?? '—' }}</td>
                            <td class="amount-mono">UGX {{ number_format($item->budgeted_amount, 0) }}</td>
                            <td class="amount-mono">UGX {{ number_format($actualItemAmount, 0) }}</td>
                            <td class="amount-mono {{ $itemVariance >= 0 ? 'variance-up' : 'variance-down' }}">
                                {{ $itemVariance >= 0 ? '+' : '' }}UGX {{ number_format($itemVariance, 0) }}
                            </td>
                            <td style="min-width:100px;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:{{ min(100, $itemPercent) }}%;background:#059669;">
                                    </div>
                                </div>
                                <div style="font-size:.65rem;text-align:center;margin-top:.2rem;">{{ $itemPercent }}%</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;">
                                <i class="fas fa-inbox"
                                    style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                                No income items defined
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($incomeItems->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="2"><strong>TOTAL INCOME</strong></td>
                            <td class="amount-mono"><strong>UGX {{ number_format($budget->total_income_budget, 0) }}</strong>
                            </td>
                            <td class="amount-mono"><strong>UGX {{ number_format($actualIncome, 0) }}</strong></td>
                            <td class="amount-mono {{ $incomeVariance >= 0 ? 'variance-up' : 'variance-down' }}">
                                <strong>{{ $incomeVariance >= 0 ? '+' : '' }}UGX
                                    {{ number_format($incomeVariance, 0) }}</strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Expense Items Breakdown --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-arrow-up" style="color:#dc2626;"></i> Expense Items Breakdown</h3>
        </div>
        <div style="overflow-x:auto;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Budgeted Amount</th>
                        <th>Actual Amount</th>
                        <th>Variance</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $expenseItems = $budget->items->where('type', 'expense');
                    @endphp
                    @forelse($expenseItems as $item)
                        @php
                            $actualItemAmount = 0;
                            $itemVariance = $actualItemAmount - $item->budgeted_amount;
                            $itemPercent = $item->budgeted_amount > 0 ? round(($actualItemAmount / $item->budgeted_amount) * 100) : 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $item->item_name }}</strong></td>
                            <td>{{ $item->category->name ?? '—' }}</td>
                            <td class="amount-mono">UGX {{ number_format($item->budgeted_amount, 0) }}</td>
                            <td class="amount-mono">UGX {{ number_format($actualItemAmount, 0) }}</td>
                            <td class="amount-mono {{ $itemVariance <= 0 ? 'variance-up' : 'variance-down' }}">
                                {{ $itemVariance >= 0 ? '+' : '' }}UGX {{ number_format($itemVariance, 0) }}
                            </td>
                            <td style="min-width:100px;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:{{ min(100, $itemPercent) }}%;background:#dc2626;">
                                    </div>
                                </div>
                                <div style="font-size:.65rem;text-align:center;margin-top:.2rem;">{{ $itemPercent }}%</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;">
                                <i class="fas fa-inbox"
                                    style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                                No expense items defined
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($expenseItems->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="2"><strong>TOTAL EXPENSES</strong></td>
                            <td class="amount-mono"><strong>UGX {{ number_format($budget->total_expense_budget, 0) }}</strong>
                            </td>
                            <td class="amount-mono"><strong>UGX {{ number_format($totalActualExpense, 0) }}</strong></td>
                            <td class="amount-mono {{ $expenseVariance <= 0 ? 'variance-up' : 'variance-down' }}">
                                <strong>{{ $expenseVariance >= 0 ? '+' : '' }}UGX
                                    {{ number_format($expenseVariance, 0) }}</strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($budget->notes)
        <div class="fin-card">
            <div class="fin-card-header">
                <h3><i class="fas fa-sticky-note"></i> Budget Notes</h3>
            </div>
            <div
                style="padding:1rem 1.5rem;background:rgba(47,44,203,.05);border-radius:0 0 var(--radius) var(--radius);border-top:1px solid var(--border);">
                <i class="fas fa-align-left" style="color:#2f2ccb;margin-right:.5rem;"></i>
                {{ $budget->notes }}
            </div>
        </div>
    @endif
 </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmApproveBudget(budgetId) {
            Swal.fire({
                title: 'Approve Budget?',
                html: `<span style="color:#475569;">Once approved, this budget will be used for financial reporting and cannot be edited.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, approve',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Approving budget...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('approveBudgetForm' + budgetId).submit();
                        }
                    });
                }
            });
        }

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