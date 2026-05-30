{{-- resources/views/Finance/expenses.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root {
    --fin-green: #059669;
    --fin-green-l: rgba(5,150,105,.10);
    --fin-red: #dc2626;
    --fin-red-l: rgba(220,38,38,.10);
    --fin-blue: #2f2ccb;
    --fin-blue-l: rgba(47,44,203,.10);
    --fin-amber: #d97706;
    --fin-amber-l: rgba(217,119,6,.10);
    --fin-purple: #7c3aed;
    --fin-purple-l: rgba(124,58,237,.10);
    --fin-gray: #64748b;
    --fin-gray-l: rgba(100,116,139,.10);
    --surface: #ffffff;
    --bg: #f0f4f8;
    --border: #e2e8f0;
    --text-1: #0f172a;
    --text-2: #475569;
    --text-3: #94a3b8;
    --radius: 16px;
    --radius-sm: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.05);
    --shadow-lg: 0 8px 32px rgba(0,0,0,.10);
}

*{font-family:'DM Sans',sans-serif;box-sizing:border-box;}
body{background:var(--bg);}

/* Hero Section */
.fin-hero{
    background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
    border-radius: 24px;
    padding: 2rem 2.5rem;
    margin-bottom: 1.75rem;
    position: relative;
    overflow: hidden;
}
.fin-hero::before{
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(165,180,252,.2) 0%, transparent 70%);
}
.fin-hero h1{color:#fff;font-size:1.5rem;font-weight:700;margin:0;}
.fin-hero p{color:#c7d2fe;margin:.2rem 0 0;font-size:.88rem;}
.hero-badge{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    background:rgba(47,44,203,.25);
    border:1px solid rgba(165,180,252,.4);
    color:#a5b4fc;
    padding:.25rem .75rem;
    border-radius:20px;
    font-size:.75rem;
    font-weight:600;
    margin-bottom:.6rem;
}

/* Cards */
.fin-card{
    background:var(--surface);
    border-radius:var(--radius);
    border:1px solid var(--border);
    box-shadow:var(--shadow);
    overflow:hidden;
    margin-bottom:1.5rem;
}
.fin-card-header{
    padding:1.1rem 1.5rem;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:#fafbff;
    flex-wrap:wrap;
    gap:1rem;
}
.fin-card-header h3{
    margin:0;
    font-size:.95rem;
    font-weight:700;
    color:var(--text-1);
    display:flex;
    align-items:center;
    gap:.6rem;
}
.sec-link{
    font-size:.75rem;
    color:#2f2ccb;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:.3rem;
}
.sec-link:hover{text-decoration:underline;}

/* Buttons */
.btn-fin{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.6rem 1.25rem;
    border-radius:10px;
    font-size:.875rem;
    font-weight:600;
    border:none;
    cursor:pointer;
    text-decoration:none;
    transition:all .18s;
}
.btn-sm{padding:.4rem .85rem;font-size:.8rem;}
.btn-primary-fin{background:#2f2ccb;color:#fff;}
.btn-primary-fin:hover{background:#2420a8;transform:translateY(-1px);box-shadow:0 4px 14px rgba(47,44,203,.35);}
.btn-outline-fin{background:transparent;border:1.5px solid var(--border);color:var(--text-2);}
.btn-outline-fin:hover{border-color:#2f2ccb;color:#2f2ccb;}
.btn-danger-fin{background:var(--fin-red-l);color:var(--fin-red);border:1px solid rgba(220,38,38,.2);}
.btn-danger-fin:hover{background:var(--fin-red);color:#fff;}

/* Badges */
.badge-fin{
    display:inline-flex;
    align-items:center;
    gap:.3rem;
    padding:.25rem .7rem;
    border-radius:20px;
    font-size:.74rem;
    font-weight:600;
}
.badge-green{background:var(--fin-green-l);color:var(--fin-green);}
.badge-red{background:var(--fin-red-l);color:var(--fin-red);}
.badge-amber{background:var(--fin-amber-l);color:var(--fin-amber);}
.badge-blue{background:rgba(47,44,203,.1);color:#2f2ccb;}
.badge-gray{background:#f1f5f9;color:var(--text-2);}
.badge-purple{background:rgba(124,58,237,.1);color:#7c3aed;}

.amount-mono{font-family:'DM Mono',monospace;font-weight:600;}

/* Stat Grid */
.stat-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:1rem;
    margin-bottom:1.5rem;
}
.stat-card{
    background:var(--surface);
    border-radius:var(--radius);
    border:1px solid var(--border);
    padding:1.2rem;
    text-align:center;
    transition:all .2s;
}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow);}
.stat-card .value{font-size:1.5rem;font-weight:800;color:var(--text-1);font-family:'DM Mono',monospace;}
.stat-card .label{font-size:.75rem;color:var(--text-3);margin-top:.3rem;font-weight:500;}

/* Filters */
.filters{
    display:flex;
    gap:1rem;
    flex-wrap:wrap;
    align-items:flex-end;
    padding:0 1.5rem 1.5rem;
}
.filter-group{
    display:flex;
    flex-direction:column;
    gap:.5rem;
    flex:1;
    min-width:140px;
}
.filter-group label{
    font-size:.7rem;
    font-weight:700;
    color:var(--text-3);
    text-transform:uppercase;
    letter-spacing:.05em;
}
.filter-group select,
.filter-group input{
    padding:.65rem .85rem;
    border-radius:10px;
    border:1.5px solid var(--border);
    font-size:.85rem;
    background:var(--surface);
    transition:all .15s;
    width:100%;
    cursor:pointer;
}
.filter-group select:focus,
.filter-group input:focus{
    outline:none;
    border-color:#2f2ccb;
    box-shadow:0 0 0 3px rgba(47,44,203,.1);
}
.search-group{flex:2;min-width:200px;}
.filter-actions{display:flex;gap:.5rem;}

/* Table */
.table-wrapper{
    overflow-x:auto;
    margin:0;
}
.data-table{
    width:100%;
    min-width:900px;
    border-collapse:collapse;
}
.data-table th{
    background:#2c29ca;
    padding:.8rem 1rem;
    font-size:.72rem;
    font-weight:700;
    color:#fff;
    text-transform:uppercase;
    letter-spacing:.05em;
    border-bottom:none;
    text-align:left;
}
.data-table th:first-child{border-radius:10px 0 0 0;}
.data-table th:last-child{border-radius:0 10px 0 0;}
.data-table td{
    padding:.9rem 1rem;
    border-bottom:1px solid #f8fafc;
    font-size:.85rem;
    color:var(--text-1);
    vertical-align:middle;
}
.data-table tr:hover td{background:#f5f6ff;}
.data-table tr:last-child td{border-bottom:none;}

/* Category Badge */
.category-badge{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.25rem .7rem;
    border-radius:20px;
    font-size:.7rem;
    font-weight:600;
}
.category-dot{width:8px;height:8px;border-radius:50%;display:inline-block;}

/* Action Icons */
.action-icons{
    display:flex;
    gap:.5rem;
    align-items:center;
}
.action-icons a,
.action-icons button{
    background:transparent;
    border:none;
    cursor:pointer;
    width:32px;
    height:32px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    transition:all .2s;
}
.action-icons a i,
.action-icons button i{font-size:.9rem;}
.action-icons a:hover,
.action-icons button:hover{transform:scale(1.05);}
.action-icons a:first-child:hover{background:rgba(47,44,203,.1);}
.action-icons button:hover{background:rgba(220,38,38,.1);}

/* Pagination */
.pagination-container{
    padding:1rem 1.5rem;
    border-top:1px solid var(--border);
    background:#fafbff;
    border-radius:0 0 var(--radius) var(--radius);
}

/* Responsive */
@media(max-width:900px){
    .stat-grid{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:768px){
    .fin-hero{padding:1.5rem;}
    .fin-hero h1{font-size:1.3rem;}
    .stat-grid{grid-template-columns:1fr;}
    .filters{flex-direction:column;}
    .filter-group{width:100%;}
    .filter-actions{width:100%;justify-content:stretch;}
    .filter-actions .btn-fin{flex:1;justify-content:center;}
    .fin-card-header{flex-direction:column;align-items:flex-start;}
    .fin-card-header .btn-fin{width:100%;justify-content:center;}
}

/* Category breakdown grid */
.category-breakdown-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(220px,1fr));
    gap:1rem;
    padding:1.5rem;
}
.category-item{
    background:#f8fafc;
    border-radius:12px;
    padding:.8rem 1rem;
    transition:all .2s;
}
.category-item:hover{background:#f5f6ff;transform:translateY(-2px);}
</style>
@endsection

@section('page-header')
<div class="fin-hero mt-5">
    <div style="position:relative;z-index:1;">
        <div class="hero-badge"><i class="fas fa-arrow-up"></i> Finance — Expenses</div>
        <h1>Expense Management</h1>
        <p>Track school operational expenses, categorize spending, and monitor budgets</p>
    </div>
</div>
@endsection

@section('content')

{{-- Stats Summary --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="value">UGX {{ number_format($totals->total ?? 0, 0) }}</div>
        <div class="label">Total Expenses ({{ $year }})</div>
    </div>
    <div class="stat-card">
        <div class="value">{{ number_format($totals->count ?? 0) }}</div>
        <div class="label">Total Transactions</div>
    </div>
    <div class="stat-card">
        <div class="value">UGX {{ number_format(($totals->count ?? 1) > 0 ? ($totals->total ?? 0) / ($totals->count ?? 1) : 0, 0) }}</div>
        <div class="label">Average Expense</div>
    </div>
    <div class="stat-card">
        <div class="value">{{ $expenses->total() }}</div>
        <div class="label">Showing Records</div>
    </div>
</div>

{{-- Filters & Actions --}}
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-search"></i> Search & Filter</h3>
        <a href="{{ route('finance.expenses.create') }}" class="btn-fin btn-primary-fin">
            <i class="fas fa-plus"></i> Add Expense
        </a>
    </div>
    <div class="filters">
        <div class="filter-group">
            <label>Academic Year</label>
            <select id="filterYear" onchange="applyFilters()">
                <option value="{{ date('Y') }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                <option value="{{ date('Y')-1 }}" {{ $year == date('Y')-1 ? 'selected' : '' }}>{{ date('Y')-1 }}</option>
                <option value="{{ date('Y')-2 }}" {{ $year == date('Y')-2 ? 'selected' : '' }}>{{ date('Y')-2 }}</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Category</label>
            <select id="filterCategory" onchange="applyFilters()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group search-group">
            <label>Search</label>
            <input type="text" id="searchInput" placeholder="Title, payee, expense #..." value="{{ $search }}" onkeypress="if(event.key==='Enter') applyFilters()">
        </div>
        <div class="filter-actions">
            <button class="btn-fin btn-primary-fin" onclick="applyFilters()">
                <i class="fas fa-search"></i> Search
            </button>
            <button class="btn-fin btn-outline-fin" onclick="resetFilters()">
                <i class="fas fa-undo"></i> Reset
            </button>
            <a href="{{ route('finance.expense-categories.index') }}" class="btn-fin btn-outline-fin">
                <i class="fas fa-tags"></i> Categories
            </a>
        </div>
    </div>
</div>

{{-- Expenses Table --}}
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-list"></i> Expense History</h3>
        <span style="font-size:.75rem;color:var(--text-3);">{{ $expenses->total() }} records</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Expense #</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Payee</th>
                    <th>Amount (UGX)</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>
                        <div style="font-weight:700;font-family:'DM Mono',monospace;font-size:.8rem;">{{ $expense->expense_number }}</div>
                    </td>
                    <td>
                        @if($expense->category)
                        <div class="category-badge" style="background:{{ $expense->category->color }}15; color:{{ $expense->category->color }};">
                            <span class="category-dot" style="background:{{ $expense->category->color }};"></span>
                            {{ $expense->category->name }}
                        </div>
                        @else
                        <span class="badge-fin badge-gray">Uncategorized</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;">{{ $expense->title }}</div>
                        @if($expense->description)
                        <div style="font-size:.7rem;color:var(--text-3);margin-top:2px;">{{ Str::limit($expense->description, 40) }}</div>
                        @endif
                    </td>
                    <td>{{ $expense->payee_name ?? '—' }}</td>
                    <td class="amount-mono" style="color:#dc2626;font-weight:700;">UGX {{ number_format($expense->amount, 0) }}</td>
                    <td style="white-space:nowrap;">{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : '—' }}</td>
                    <td>
                        <span class="badge-fin badge-purple">
                            @switch($expense->payment_method)
                                @case('cash') 💵 Cash @break
                                @case('bank_transfer') 🏦 Bank Transfer @break
                                @case('mobile_money') 📱 Mobile Money @break
                                @case('cheque') 📝 Cheque @break
                                @default 🔄 Other
                            @endswitch
                        </span>
                        @if($expense->transaction_reference)
                        <div style="font-size:.65rem;color:var(--text-3);margin-top:2px;">{{ $expense->transaction_reference }}</div>
                        @endif
                    </td>
                    <td>
                        @if($expense->status == 'paid')
                            <span class="badge-fin badge-green"><i class="fas fa-check-circle"></i> Paid</span>
                        @elseif($expense->status == 'approved')
                            <span class="badge-fin badge-blue"><i class="fas fa-check"></i> Approved</span>
                        @elseif($expense->status == 'draft')
                            <span class="badge-fin badge-amber"><i class="fas fa-pen"></i> Draft</span>
                        @else
                            <span class="badge-fin badge-red"><i class="fas fa-ban"></i> Cancelled</span>
                        @endif
                    </td>
                    <td class="action-icons">
                        <a href="{{ route('finance.expenses.edit', $expense->id) }}" title="Edit Expense">
                            <i class="fas fa-edit" style="color:#2f2ccb;"></i>
                        </a>
                        <form method="POST" action="{{ route('finance.expenses.destroy', $expense->id) }}" class="delete-expense-form" data-title="{{ $expense->title }}" data-amount="{{ number_format($expense->amount, 0) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="delete-expense-btn" title="Delete Expense" onclick="confirmDeleteExpense(this)">
                                <i class="fas fa-trash" style="color:#dc2626;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem;">
                        <i class="fas fa-receipt" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        <p style="margin:0 0 1rem 0;">No expenses recorded yet.</p>
                        <a href="{{ route('finance.expenses.create') }}" class="btn-fin btn-primary-fin">
                            <i class="fas fa-plus"></i> Record First Expense
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="pagination-container">
        {{ $expenses->appends(['year' => $year, 'category_id' => $categoryId, 'search' => $search])->links() }}
    </div>
    @endif
</div>

{{-- Category Breakdown Card --}}
@if($categories->count() > 0)
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-chart-pie"></i> Expense Breakdown by Category ({{ $year }})</h3>
        <a href="{{ route('finance.expense-categories.index') }}" class="sec-link">Manage Categories <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="category-breakdown-grid">
        @foreach($categories as $cat)
        @php
            $catTotal = $cat->expenses->where('academic_year', $year)->whereIn('status', ['approved', 'paid'])->sum('amount');
            $percentage = $totals->total > 0 ? round(($catTotal / $totals->total) * 100) : 0;
        @endphp
        @if($catTotal > 0)
        <div class="category-item">
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                <span class="category-dot" style="background:{{ $cat->color }};width:10px;height:10px;"></span>
                <span style="font-weight:600;font-size:.85rem;">{{ $cat->name }}</span>
                <span style="margin-left:auto;font-weight:700;color:#dc2626;">UGX {{ number_format($catTotal, 0) }}</span>
            </div>
            <div style="height:4px;background:#e2e8f0;border-radius:99px;overflow:hidden;">
                <div style="height:100%;width:{{ $percentage }}%;background:{{ $cat->color }};"></div>
            </div>
            <div style="font-size:.7rem;color:var(--text-3);margin-top:.3rem;">{{ $percentage }}% of total expenses</div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function applyFilters() {
    let year = document.getElementById('filterYear').value;
    let category = document.getElementById('filterCategory').value;
    let search = document.getElementById('searchInput').value;
    let url = "{{ route('finance.expenses.index') }}?year=" + year;
    if(category) url += "&category_id=" + category;
    if(search) url += "&search=" + encodeURIComponent(search);
    window.location.href = url;
}

function resetFilters() {
    window.location.href = "{{ route('finance.expenses.index') }}";
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') applyFilters();
});

// SweetAlert confirmation for deleting expense
function confirmDeleteExpense(button) {
    const form = button.closest('.delete-expense-form');
    const title = form.dataset.title || 'this expense';
    const amount = form.dataset.amount || '0';
    
    Swal.fire({
        title: 'Delete Expense?',
        html: `
            <div style="text-align: left; padding: 10px 0;">
                <p><strong>Title:</strong> ${title}</p>
                <p><strong>Amount:</strong> UGX ${amount}</p>
            </div>
            <hr>
            <p style="color: #dc2626; font-weight: 600;">
                <i class="fas fa-exclamation-triangle"></i> This action cannot be undone!
            </p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, delete expense!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Deleting expense record...',
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