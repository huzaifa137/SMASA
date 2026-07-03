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
            --fin-gray: #64748b;
            --fin-gray-l: rgba(100, 116, 139, .10);
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

        * { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }
        body { background: var(--bg); }

        .fin-hero {
            background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
            border-radius: 24px; padding: 2rem 2.5rem; margin-bottom: 1.75rem;
            position: relative; overflow: hidden;
        }
        .fin-hero::before {
            content: ''; position: absolute; top: -60px; right: -60px; width: 260px; height: 260px;
            border-radius: 50%; background: radial-gradient(circle, rgba(165, 180, 252, .2) 0%, transparent 70%);
        }
        .fin-hero h1 { color: #fff; font-size: 1.5rem; font-weight: 700; margin: 0; }
        .fin-hero p { color: #c7d2fe; margin: .2rem 0 0; font-size: .88rem; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(47, 44, 203, .25); border: 1px solid rgba(165, 180, 252, .4);
            color: #a5b4fc; padding: .25rem .75rem; border-radius: 20px;
            font-size: .75rem; font-weight: 600; margin-bottom: .6rem;
        }

        .fin-card {
            background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border);
            box-shadow: var(--shadow); overflow: hidden; margin-bottom: 1.5rem;
        }
        .fin-card-header {
            padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            background: #fafbff; flex-wrap: wrap; gap: 1rem;
        }
        .fin-card-header h3 {
            margin: 0; font-size: .95rem; font-weight: 700; color: var(--text-1);
            display: flex; align-items: center; gap: .6rem;
        }

        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card {
            background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border);
            padding: 1.2rem; text-align: center; transition: all .2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .stat-card .value { font-size: 1.5rem; font-weight: 800; color: var(--text-1); font-family: 'DM Mono', monospace; }
        .stat-card .label { font-size: .75rem; color: var(--text-3); margin-top: .3rem; font-weight: 500; }

        .btn-fin {
            display: inline-flex; align-items: center; gap: .45rem; padding: .6rem 1.25rem;
            border-radius: 10px; font-size: .875rem; font-weight: 600; border: none;
            cursor: pointer; text-decoration: none; transition: all .18s;
        }
        .btn-sm { padding: .4rem .85rem; font-size: .8rem; }
        .btn-primary-fin { background: #2f2ccb; color: #fff; }
        .btn-primary-fin:hover { background: #2420a8; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(47, 44, 203, .35); color:#fff; }
        .btn-outline-fin { background: transparent; border: 1.5px solid var(--border); color: var(--text-2); }
        .btn-outline-fin:hover { border-color: #2f2ccb; color: #2f2ccb; }
        .btn-success-fin { background: var(--fin-green); color: #fff; }
        .btn-success-fin:hover { background: #047857; color:#fff; }
        .btn-warning-fin { background: var(--fin-amber); color: #fff; }
        .btn-warning-fin:hover { background: #b45309; color:#fff; }
        .btn-danger-fin { background: var(--fin-red); color: #fff; }
        .btn-danger-fin:hover { background: #b91c1c; color:#fff; }

        .badge-fin { display: inline-flex; align-items: center; gap: .3rem; padding: .25rem .7rem; border-radius: 20px; font-size: .74rem; font-weight: 600; }
        .badge-green { background: var(--fin-green-l); color: var(--fin-green); }
        .badge-red { background: var(--fin-red-l); color: var(--fin-red); }
        .badge-amber { background: var(--fin-amber-l); color: var(--fin-amber); }
        .badge-blue { background: rgba(47, 44, 203, .1); color: #2f2ccb; }
        .badge-purple { background: rgba(124, 58, 237, .1); color: #7c3aed; }
        .badge-teal { background: rgba(13, 148, 136, .1); color: #0d9488; }
        .badge-gray { background: #f1f5f9; color: var(--text-2); }

        .amount-mono { font-family: 'DM Mono', monospace; font-weight: 600; }

        .filters { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; padding: 0 1.5rem 1.5rem; }
        .filter-group { display: flex; flex-direction: column; gap: .5rem; flex: 1; min-width: 140px; }
        .filter-group label { font-size: .7rem; font-weight: 700; color: var(--text-3); text-transform: uppercase; letter-spacing: .05em; }
        .filter-group select, .filter-group input {
            padding: .65rem .85rem; border-radius: 10px; border: 1.5px solid var(--border);
            font-size: .85rem; background: var(--surface); transition: all .15s; width: 100%;
        }
        .filter-group select:focus, .filter-group input:focus {
            outline: none; border-color: #2f2ccb; box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }
        .filter-actions { display: flex; gap: .5rem; }

        .table-wrapper { overflow-x: auto; margin: 0; border-radius: 12px; }
        .data-table { width: 100%; min-width: 900px; border-collapse: collapse; }
        .data-table th {
            background: #2c29ca; padding: .8rem 1rem; font-size: .72rem; font-weight: 700; color: #fff;
            text-transform: uppercase; letter-spacing: .05em; border-bottom: none; text-align: left;
        }
        .data-table th:first-child { border-radius: 10px 0 0 0; }
        .data-table th:last-child { border-radius: 0 10px 0 0; }
        .data-table td { padding: .9rem 1rem; border-bottom: 1px solid #f8fafc; font-size: .85rem; color: var(--text-1); vertical-align: middle; }
        .data-table tr:hover td { background: #f5f6ff; }
        .data-table tr:last-child td { border-bottom: none; }

        .empty-state { text-align: center; padding: 3rem; color: var(--text-2); }
        .empty-state i { font-size: 3rem; opacity: .3; display: block; margin-bottom: 1rem; }

        @media(max-width:900px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
        @media(max-width:768px) {
            .fin-hero { padding: 1.5rem; }
            .fin-hero h1 { font-size: 1.3rem; }
            .stat-grid { grid-template-columns: 1fr; }
            .filters { flex-direction: column; }
            .filter-group { width: 100%; }
            .filter-actions { width: 100%; justify-content: stretch; }
            .filter-actions .btn-fin { flex: 1; justify-content: center; }
            .fin-card-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
@endsection
@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-scale-balanced"></i> Finance — Ledgers</div>
            <h1>Trial Balance & Income / Expenditure</h1>
            <p>A snapshot of every account balance, plus the school's surplus or deficit for the period</p>
        </div>
    </div>
@endsection

@section('content')

    {{-- Ledger sub-navigation --}}
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
        <a href="{{ route('finance.ledger.accounts.index') }}" class="btn-fin btn-outline-fin btn-sm"><i class="fas fa-book"></i> Chart of Accounts</a>
        <a href="{{ route('finance.ledger.general') }}" class="btn-fin btn-outline-fin btn-sm"><i class="fas fa-list-ul"></i> General Ledger</a>
        <a href="{{ route('finance.ledger.student-fees') }}" class="btn-fin btn-outline-fin btn-sm"><i class="fas fa-user-graduate"></i> Student Fee Ledger</a>
        <a href="{{ route('finance.ledger.trial-balance') }}" class="btn-fin btn-primary-fin btn-sm"><i class="fas fa-scale-balanced"></i> Trial Balance</a>
    </div>

    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-filter"></i> Period</h3>
            <button class="btn-fin btn-outline-fin btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
        <form method="GET" class="filters">
            <div class="filter-group">
                <label>Academic Year</label>
                <select name="year" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="filter-group">
                <label>Term</label>
                <select name="term" onchange="this.form.submit()">
                    <option value="">Full Year</option>
                    <option value="1" @selected($term == '1')>Term 1</option>
                    <option value="2" @selected($term == '2')>Term 2</option>
                    <option value="3" @selected($term == '3')>Term 3</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Income & Expenditure Summary --}}
    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="value" style="color:var(--fin-green);">UGX {{ number_format($totalIncome, 0) }}</div>
            <div class="label">Total Income</div>
        </div>
        <div class="stat-card">
            <div class="value" style="color:var(--fin-red);">UGX {{ number_format($totalExpense, 0) }}</div>
            <div class="label">Total Expenditure</div>
        </div>
        <div class="stat-card">
            <div class="value" style="color:{{ $surplusDeficit >= 0 ? 'var(--fin-green)' : 'var(--fin-red)' }};">
                UGX {{ number_format($surplusDeficit, 0) }}
            </div>
            <div class="label">{{ $surplusDeficit >= 0 ? 'Surplus' : 'Deficit' }}</div>
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-table"></i> Trial Balance — {{ $year }}{{ $term ? ' · Term '.$term : ' · Full Year' }}</h3>
        </div>

        @if(empty($rows))
            <div class="empty-state">
                <i class="fas fa-table"></i>
                <p>No posted transactions found for this period yet.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td class="amount-mono">{{ $row['account']->account_code }}</td>
                                <td>{{ $row['account']->name }}</td>
                                <td><span class="badge-fin badge-{{ $row['account']->typeBadge() }}">{{ ucfirst($row['account']->type) }}</span></td>
                                <td class="amount-mono">{{ $row['debit'] ? 'UGX ' . number_format($row['debit'], 0) : '—' }}</td>
                                <td class="amount-mono">{{ $row['credit'] ? 'UGX ' . number_format($row['credit'], 0) : '—' }}</td>
                            </tr>
                        @endforeach
                        <tr style="background:#fafbff;font-weight:800;">
                            <td colspan="3">TOTAL</td>
                            <td class="amount-mono">UGX {{ number_format($totalDebit, 0) }}</td>
                            <td class="amount-mono">UGX {{ number_format($totalCredit, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if(round($totalDebit, 2) !== round($totalCredit, 2))
                <div style="padding:1rem 1.5rem;">
                    <span class="badge-fin badge-amber">
                        <i class="fas fa-triangle-exclamation"></i>
                        Debits and credits don't balance yet — expected while the ledger only covers fee payments, expenses and payroll (single-entry). Full double-entry postings (e.g. matching Cash/Bank entries) can be added later.
                    </span>
                </div>
            @endif
        @endif
    </div>
        </div>
    </div>
@endsection
