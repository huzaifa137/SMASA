{{-- resources/views/Finance/budgets.blade.php --}}
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
            --fin-teal: #0d9488;
            --fin-teal-l: rgba(13, 148, 136, .10);
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

        .badge-teal {
            background: rgba(13, 148, 136, .1);
            color: #0d9488;
        }

        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
        }

        /* Budget Grid */
        .budget-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }

        .budget-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1.2rem;
            transition: all .2s;
            cursor: pointer;
        }

        .budget-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
            border-color: rgba(47, 44, 203, .2);
        }

        .budget-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: .8rem;
        }

        .budget-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-1);
        }

        .budget-year {
            font-size: .7rem;
            color: var(--text-3);
            margin-top: .2rem;
        }

        .budget-stats {
            display: flex;
            justify-content: space-between;
            margin: .8rem 0;
            padding: .5rem 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .5rem;
        }

        .stat-label {
            font-size: .7rem;
            color: var(--text-3);
        }

        .stat-value {
            font-weight: 700;
            font-size: .85rem;
        }

        .progress-bar {
            height: 5px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
            margin: .3rem 0;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .3s;
        }

        .variance-up {
            color: var(--fin-green);
        }

        .variance-down {
            color: var(--fin-red);
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

            .budget-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .fin-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .fin-card-header .btn-fin {
                width: 100%;
                justify-content: center;
            }

            .budget-card {
                padding: 1rem;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-chart-line"></i> Finance — Budget Planning</div>
            <h1>Annual & Term Budgets</h1>
            <p>Plan income and expenses, track budget vs actual performance</p>
        </div>
    </div>
@endsection

@section('content')

    @php
        $totalBudgets = $budgets->count();
        $approvedBudgets = $budgets->where('status', 'approved')->count();
        $draftBudgets = $budgets->where('status', 'draft')->count();
        $totalBudgetedIncome = $budgets->sum('total_income_budget');
        $totalBudgetedExpense = $budgets->sum('total_expense_budget');
    @endphp

    {{-- Stats Summary --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="value">{{ $totalBudgets }}</div>
            <div class="label">Total Budgets</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $approvedBudgets }}</div>
            <div class="label">Approved</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $draftBudgets }}</div>
            <div class="label">Draft</div>
        </div>
        <div class="stat-card">
            <div class="value">UGX {{ number_format($totalBudgetedIncome - $totalBudgetedExpense, 0) }}</div>
            <div class="label">Net Budgeted</div>
        </div>
    </div>

    {{-- Actions --}}
    <div
        style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <button class="btn-fin btn-outline-fin" onclick="filterBudgets('all')">
                <i class="fas fa-list"></i> All
            </button>
            <button class="btn-fin btn-outline-fin" onclick="filterBudgets('draft')">
                <i class="fas fa-pen"></i> Draft
            </button>
            <button class="btn-fin btn-outline-fin" onclick="filterBudgets('approved')">
                <i class="fas fa-check-circle"></i> Approved
            </button>
            <button class="btn-fin btn-outline-fin" onclick="filterBudgets('closed')">
                <i class="fas fa-archive"></i> Closed
            </button>
        </div>
        <a href="{{ route('finance.budgets.create') }}" class="btn-fin btn-primary-fin">
            <i class="fas fa-plus"></i> Create New Budget
        </a>
    </div>

    {{-- Budgets Grid --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-chart-pie"></i> Budget Plans</h3>
            <span style="font-size:.75rem;color:var(--text-3);">{{ $budgets->count() }} budgets</span>
        </div>
        @if($budgets->isEmpty())
            <div style="text-align:center;padding:3rem;">
                <i class="fas fa-chart-line" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
                <p style="margin-bottom:1rem;">No budgets created yet. Create your first budget to start planning.</p>
                <a href="{{ route('finance.budgets.create') }}" class="btn-fin btn-primary-fin">
                    <i class="fas fa-plus"></i> Create Budget
                </a>
            </div>
        @else
            <div class="budget-grid" id="budgetGrid">
                @foreach($budgets as $budget)
                    @php
                        $statusColor = match ($budget->status) {
                            'approved' => 'badge-green',
                            'draft' => 'badge-amber',
                            'closed' => 'badge-blue',
                            default => 'badge-teal'
                        };
                        $statusIcon = match ($budget->status) {
                            'approved' => 'fa-check-circle',
                            'draft' => 'fa-pen',
                            'closed' => 'fa-archive',
                            default => 'fa-circle'
                        };
                        $netBudget = $budget->total_income_budget - $budget->total_expense_budget;
                    @endphp
                    <div class="budget-card" data-status="{{ $budget->status }}"
                        onclick="window.location='{{ route('finance.budgets.show', $budget->id) }}'">
                        <div class="budget-header">
                            <div>
                                <div class="budget-title">{{ $budget->title }}</div>
                                <div class="budget-year">
                                    {{ $budget->academic_year }}
                                    @if($budget->term)
                                        • Term {{ $budget->term }}
                                    @else
                                        • Full Year
                                    @endif
                                </div>
                            </div>
                            <div class="badge-fin {{ $statusColor }}">
                                <i class="fas {{ $statusIcon }}"></i> {{ ucfirst($budget->status) }}
                            </div>
                        </div>

                        <div class="budget-stats">
                            <div>
                                <div style="font-size:.7rem;color:var(--text-3);">Income</div>
                                <div class="amount-mono" style="font-weight:700;color:#059669;">UGX
                                    {{ number_format($budget->total_income_budget, 0) }}</div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;color:var(--text-3);">Expenses</div>
                                <div class="amount-mono" style="font-weight:700;color:#dc2626;">UGX
                                    {{ number_format($budget->total_expense_budget, 0) }}</div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;color:var(--text-3);">Net</div>
                                <div class="amount-mono"
                                    style="font-weight:700;color:{{ $netBudget >= 0 ? '#059669' : '#2f2ccb' }};">
                                    UGX {{ number_format($netBudget, 0) }}
                                </div>
                            </div>
                        </div>

                        @if($budget->notes)
                            <div
                                style="font-size:.7rem;color:var(--text-3);margin:.5rem 0;padding:.3rem 0;border-top:1px dashed var(--border);">
                                <i class="fas fa-align-left" style="margin-right:.3rem;"></i>{{ Str::limit($budget->notes, 80) }}
                            </div>
                        @endif

                       <div style="margin-top:.5rem;display:flex;gap:.5rem;justify-content:flex-end;">
    @if($budget->status == 'draft')
        <a href="{{ route('finance.budgets.edit', $budget->id) }}"
            class="btn-fin btn-sm btn-outline-fin"
            onclick="event.stopPropagation()">
            <i class="fas fa-pen"></i> Edit
        </a>
        <button class="btn-fin btn-sm btn-success-fin"
            onclick="event.stopPropagation(); confirmApproveBudget({{ $budget->id }})">
            <i class="fas fa-check"></i> Approve
        </button>
        <form id="approveBudgetForm{{ $budget->id }}" method="POST"
            action="{{ route('finance.budgets.approve', $budget->id) }}" style="display:none;">
            @csrf
        </form>
    @else
        <a href="{{ route('finance.budgets.show', $budget->id) }}"
            class="btn-fin btn-sm btn-outline-fin"
            onclick="event.stopPropagation()">
            <i class="fas fa-eye"></i> View
        </a>
    @endif
</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Budget Tips Card --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-lightbulb"></i> Budgeting Tips</h3>
        </div>
        <div style="padding:1.5rem;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem;">
                <div style="display:flex;align-items:center;gap:.8rem;">
                    <div
                        style="width:32px;height:32px;background:rgba(47,44,203,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#2f2ccb;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem;">Start with Historical Data</div>
                        <div style="font-size:.7rem;color:var(--text-3);">Use previous year's actuals as baseline</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.8rem;">
                    <div
                        style="width:32px;height:32px;background:var(--fin-amber-l);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--fin-amber);">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem;">Add Contingency</div>
                        <div style="font-size:.7rem;color:var(--text-3);">Include 5-10% for unexpected costs</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.8rem;">
                    <div
                        style="width:32px;height:32px;background:rgba(124,58,237,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#7c3aed;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem;">Review Monthly</div>
                        <div style="font-size:.7rem;color:var(--text-3);">Track budget vs actual regularly</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
     </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function filterBudgets(status) {
            const cards = document.querySelectorAll('.budget-card');
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function confirmApproveBudget(budgetId) {
            Swal.fire({
                title: 'Approve Budget?',
                html: `<span style="color:#475569;">Once approved, this budget will be used for financial reporting.</span>`,
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