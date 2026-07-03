{{-- resources/views/Finance/Ledger/chart-of-accounts.blade.php --}}
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
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.65); z-index: 9999; align-items: center; justify-content: center; }
        .modal-content { background: var(--surface); border-radius: 24px; max-width: 550px; width: 90%; padding: 0; box-shadow: 0 25px 50px rgba(0,0,0,.3); animation: modalFadeIn .25s ease-out; max-height: 85vh; display: flex; flex-direction: column; }
        @keyframes modalFadeIn { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, #2f2ccb 0%, #2420a8 100%) !important; border-radius: 24px 24px 0 0; flex-shrink: 0; }
        .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: .5rem; }
        .close-modal { cursor: pointer; font-size: 1.5rem; color: rgba(255,255,255,.7); transition: all .2s; line-height: 1; }
        .close-modal:hover { color: #fff; transform: scale(1.1); }
        .modal-body { flex: 1; overflow-y: auto; padding: 1.5rem; background: var(--surface); max-height: calc(85vh - 130px); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: .5rem; font-weight: 600; color: var(--text-2); font-size: .85rem; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: .7rem .9rem; border-radius: 12px; border: 1.5px solid var(--border);
            font-size: .9rem; transition: all .2s; background: var(--surface);
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #2f2ccb; box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }
        .checkbox-label { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
        .checkbox-label input { width: 18px; height: 18px; cursor: pointer; accent-color: #2f2ccb; }
        .checkbox-label span { font-weight: 500; }
        .modal-actions { flex-shrink: 0; display: flex; gap: .75rem; justify-content: flex-end; padding: 1.1rem 1.5rem; border-top: 1px solid var(--border); background: #fafbff; border-radius: 0 0 24px 24px; }
        .account-indent-1 { padding-left: 1.75rem; }
        .action-icons { display: flex; gap: .4rem; }
        .icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text-2); cursor: pointer; transition: all .15s; text-decoration: none; }
        .icon-btn:hover { border-color: #2f2ccb; color: #2f2ccb; }
        .icon-btn.danger:hover { border-color: var(--fin-red); color: var(--fin-red); background: var(--fin-red-l); }
        .system-tag { font-size: .65rem; color: var(--text-3); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-left: .4rem; }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-book"></i> Finance — Ledgers</div>
            <h1>Chart of Accounts</h1>
            <p>The master list of accounts your school's finances are organized under</p>
        </div>
    </div>
@endsection

@section('content')

    @php
        $totalAccounts = $accounts->count();
        $assetCount = $accounts->where('type', 'asset')->count();
        $incomeCount = $accounts->where('type', 'income')->count();
        $expenseCount = $accounts->where('type', 'expense')->count();
    @endphp

    {{-- Ledger sub-navigation --}}
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
        <a href="{{ route('finance.ledger.accounts.index') }}" class="btn-fin btn-primary-fin btn-sm"><i class="fas fa-book"></i> Chart of Accounts</a>
        <a href="{{ route('finance.ledger.general') }}" class="btn-fin btn-outline-fin btn-sm"><i class="fas fa-list-ul"></i> General Ledger</a>
        <a href="{{ route('finance.ledger.student-fees') }}" class="btn-fin btn-outline-fin btn-sm"><i class="fas fa-user-graduate"></i> Student Fee Ledger</a>
        <a href="{{ route('finance.ledger.trial-balance') }}" class="btn-fin btn-outline-fin btn-sm"><i class="fas fa-scale-balanced"></i> Trial Balance</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius:12px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="border-radius:12px;">{{ session('error') }}</div>
    @endif

    <div class="stat-grid">
        <div class="stat-card"><div class="value">{{ $totalAccounts }}</div><div class="label">Total Accounts</div></div>
        <div class="stat-card"><div class="value">{{ $assetCount }}</div><div class="label">Asset Accounts</div></div>
        <div class="stat-card"><div class="value">{{ $incomeCount }}</div><div class="label">Income Accounts</div></div>
        <div class="stat-card"><div class="value">{{ $expenseCount }}</div><div class="label">Expense Accounts</div></div>
    </div>

    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-sitemap"></i> Accounts</h3>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <form method="GET" style="display:flex;gap:.5rem;">
                    <select name="year" class="form-control" style="border-radius:10px;border:1.5px solid var(--border);padding:.5rem .75rem;font-size:.82rem;" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                    <select name="term" class="form-control" style="border-radius:10px;border:1.5px solid var(--border);padding:.5rem .75rem;font-size:.82rem;" onchange="this.form.submit()">
                        <option value="">All Terms</option>
                        <option value="1" @selected($term == '1')>Term 1</option>
                        <option value="2" @selected($term == '2')>Term 2</option>
                        <option value="3" @selected($term == '3')>Term 3</option>
                    </select>
                </form>
                <button class="btn-fin btn-primary-fin btn-sm" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Account</button>
            </div>
        </div>

        @if($accounts->isEmpty())
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <p>No accounts yet.</p>
                <form method="POST" action="{{ route('finance.ledger.accounts.seed-defaults') }}">
                    @csrf
                    <button class="btn-fin btn-primary-fin"><i class="fas fa-magic"></i> Create Default Chart of Accounts</button>
                </form>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th>Normal Balance</th>
                            <th>Balance ({{ $year }}{{ $term ? ' — Term '.$term : '' }})</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rootAccounts as $root)
                            <tr>
                                <td class="amount-mono">{{ $root->account_code }}</td>
                                <td>
                                    <strong>{{ $root->name }}</strong>
                                    @if($root->is_system)<span class="system-tag">System</span>@endif
                                </td>
                                <td><span class="badge-fin badge-{{ $root->typeBadge() }}"><i class="fas {{ $root->typeIcon() }}"></i> {{ ucfirst($root->type) }}</span></td>
                                <td style="text-transform:capitalize;color:var(--text-3);font-size:.78rem;">{{ $root->normalBalance() }}</td>
                                <td class="amount-mono">UGX {{ number_format($balances[$root->id] ?? 0, 0) }}</td>
                                <td>
                                    @if($root->is_active)
                                        <span class="badge-fin badge-green">Active</span>
                                    @else
                                        <span class="badge-fin badge-gray">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-icons">
                                        <button class="icon-btn" title="Edit" onclick='openEditModal(@json($root))'><i class="fas fa-pen"></i></button>
                                        @if(!$root->is_system)
                                            <form method="POST" action="{{ route('finance.ledger.accounts.destroy', $root->id) }}" onsubmit="return confirm('Delete this account?');">
                                                @csrf @method('DELETE')
                                                <button class="icon-btn danger" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @foreach($root->children as $child)
                                <tr>
                                    <td class="amount-mono account-indent-1">{{ $child->account_code }}</td>
                                    <td class="account-indent-1">
                                        <i class="fas fa-turn-up fa-rotate-90" style="color:var(--text-3);font-size:.7rem;margin-right:.4rem;"></i>{{ $child->name }}
                                        @if($child->is_system)<span class="system-tag">System</span>@endif
                                    </td>
                                    <td><span class="badge-fin badge-{{ $child->typeBadge() }}"><i class="fas {{ $child->typeIcon() }}"></i> {{ ucfirst($child->type) }}</span></td>
                                    <td style="text-transform:capitalize;color:var(--text-3);font-size:.78rem;">{{ $child->normalBalance() }}</td>
                                    <td class="amount-mono">UGX {{ number_format($balances[$child->id] ?? 0, 0) }}</td>
                                    <td>
                                        @if($child->is_active)
                                            <span class="badge-fin badge-green">Active</span>
                                        @else
                                            <span class="badge-fin badge-gray">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-icons">
                                            <button class="icon-btn" title="Edit" onclick='openEditModal(@json($child))'><i class="fas fa-pen"></i></button>
                                            @if(!$child->is_system)
                                                <form method="POST" action="{{ route('finance.ledger.accounts.destroy', $child->id) }}" onsubmit="return confirm('Delete this account?');">
                                                    @csrf @method('DELETE')
                                                    <button class="icon-btn danger" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Add/Edit Account Modal --}}
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="accModalTitle"><i class="fas fa-book"></i> Add Account</h3>
                <span class="close-modal" onclick="closeAccountModal()">&times;</span>
            </div>
            <form method="POST" id="accountForm" action="{{ route('finance.ledger.accounts.store') }}">
                @csrf
                <input type="hidden" name="_method" id="accFormMethod" value="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Account Code <span style="color:var(--fin-red);">*</span></label>
                        <input type="text" name="account_code" id="accCode" required placeholder="e.g. 4003" style="font-family:'DM Mono',monospace;">
                    </div>
                    <div class="form-group">
                        <label>Account Name <span style="color:var(--fin-red);">*</span></label>
                        <input type="text" name="name" id="accName" required placeholder="e.g. Transport Income">
                    </div>
                    <div class="form-group">
                        <label>Type <span style="color:var(--fin-red);">*</span></label>
                        <select name="type" id="accType" required>
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                            <option value="equity">Equity</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Parent Account (optional)</label>
                        <select name="parent_id" id="accParent">
                            <option value="">— None (top level) —</option>
                            @foreach($rootAccounts as $root)
                                <option value="{{ $root->id }}">{{ $root->account_code }} — {{ $root->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="accDesc" rows="2" placeholder="Optional notes about this account..."></textarea>
                    </div>
                    <div class="form-group" id="accActiveWrap" style="display:none;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" id="accActive" value="1" checked>
                            <span>Active</span>
                        </label>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-fin btn-outline-fin" onclick="closeAccountModal()">Cancel</button>
                    <button type="submit" class="btn-fin btn-primary-fin">Save Account</button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </div>
    <script>
    const accountModal = document.getElementById('accountModal');
    const accForm = document.getElementById('accountForm');
    const accFormMethod = document.getElementById('accFormMethod');
    const accModalTitle = document.getElementById('accModalTitle');
    const accCode = document.getElementById('accCode');

    function openAddModal() {
        accForm.reset();
        accForm.action = "{{ route('finance.ledger.accounts.store') }}";
        accFormMethod.value = 'POST';
        accModalTitle.innerHTML = '<i class="fas fa-book"></i> Add Account';
        accCode.readOnly = false;
        document.getElementById('accActiveWrap').style.display = 'none';
        accountModal.style.display = 'flex';
    }

    function openEditModal(account) {
        accForm.reset();
        accForm.action = "{{ url('finance/ledger/accounts') }}/" + account.id;
        accFormMethod.value = 'PUT';
        accModalTitle.innerHTML = '<i class="fas fa-pen"></i> Edit Account';
        accCode.value = account.account_code;
        accCode.readOnly = true; // code is immutable once created
        document.getElementById('accName').value = account.name;
        document.getElementById('accType').value = account.type;
        document.getElementById('accParent').value = account.parent_id ?? '';
        document.getElementById('accDesc').value = account.description ?? '';
        document.getElementById('accActive').checked = !!account.is_active;
        document.getElementById('accActiveWrap').style.display = 'block';
        accountModal.style.display = 'flex';
    }

    function closeAccountModal() {
        accountModal.style.display = 'none';
    }

    window.addEventListener('click', function (e) {
        if (e.target === accountModal) closeAccountModal();
    });
</script>
@endsection