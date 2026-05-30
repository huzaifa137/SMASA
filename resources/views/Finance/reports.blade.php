{{-- resources/views/Finance/reports.blade.php --}}
@extends('layouts-side-bar.master')
<?php
use App\Http\Controllers\Helper;
?>
@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
    --fin-teal: #0d9488;
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
.btn-primary-fin{background:#2f2ccb;color:#fff;}
.btn-primary-fin:hover{background:#2420a8;transform:translateY(-1px);box-shadow:0 4px 14px rgba(47,44,203,.35);}
.btn-outline-fin{background:transparent;border:1.5px solid var(--border);color:var(--text-2);}
.btn-outline-fin:hover{border-color:#2f2ccb;color:#2f2ccb;}

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
.badge-amber{background:var(--fin-amber-l);color:var(--fin-amber);}
.badge-blue{background:rgba(47,44,203,.1);color:#2f2ccb;}

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
.stat-card:hover{
    transform:translateY(-2px);
    box-shadow:var(--shadow);
}
.stat-card .value{
    font-size:1.5rem;
    font-weight:800;
    color:var(--text-1);
    font-family:'DM Mono',monospace;
}
.stat-card .label{
    font-size:.75rem;
    color:var(--text-3);
    margin-top:.3rem;
    font-weight:500;
}

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

/* Tables */
.report-table{
    width:100%;
    border-collapse:collapse;
}
.report-table thead tr{
    background:#2c29ca;
}
.report-table th{
    padding:.8rem 1rem;
    font-size:.72rem;
    font-weight:700;
    color:#fff;
    text-transform:uppercase;
    letter-spacing:.05em;
    border-bottom:none;
    text-align:left;
}
.report-table th:first-child{border-radius:10px 0 0 0;}
.report-table th:last-child{border-radius:0 10px 0 0;}
.report-table td{
    padding:.8rem 1rem;
    border-bottom:1px solid #f8fafc;
    font-size:.85rem;
    color:var(--text-1);
    vertical-align:middle;
}
.report-table tr:hover td{
    background:#f5f6ff;
}
.report-table tr:last-child td{
    border-bottom:none;
}
.report-table .total-row{
    background:#f5f6ff;
    font-weight:700;
}

/* Chart Container */
.chart-container{
    height:300px;
    position:relative;
    padding:1rem;
}

/* Progress Bar */
.progress-bar{
    height:5px;
    background:#e2e8f0;
    border-radius:99px;
    overflow:hidden;
    flex:1;
}
.progress-fill{
    height:100%;
    border-radius:99px;
    transition:width .3s;
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
    .report-table{min-width:600px;display:block;overflow-x:auto;}
    .fin-card-header{flex-direction:column;align-items:flex-start;}
}

/* Print Styles */
@media print {
    .fin-hero, .stat-grid, .filters, .btn-fin, .chart-container {
        display: none !important;
    }
    .fin-card {
        break-inside: avoid;
        margin-bottom: 1rem;
    }
    .report-table th {
        background: #ddd !important;
        color: #000 !important;
    }
}
</style>
@endsection

@section('page-header')
<div class="fin-hero mt-5">
    <div style="position:relative;z-index:1;">
        <div class="hero-badge"><i class="fas fa-chart-bar"></i> Finance — Reports</div>
        <h1>Financial Reports</h1>
        <p>Comprehensive financial analysis, income statements, and performance metrics</p>
    </div>
</div>
@endsection

@section('content')

{{-- Filters --}}
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-filter"></i> Report Filters</h3>
        <button onclick="window.print()" class="btn-fin btn-outline-fin">
            <i class="fas fa-print"></i> Print Report
        </button>
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
            <label>Term</label>
            <select id="filterTerm" onchange="applyFilters()">
                <option value="">Full Year</option>
                <option value="1" {{ $term == '1' ? 'selected' : '' }}>Term 1</option>
                <option value="2" {{ $term == '2' ? 'selected' : '' }}>Term 2</option>
                <option value="3" {{ $term == '3' ? 'selected' : '' }}>Term 3</option>
            </select>
        </div>
    </div>
</div>

{{-- Key Metrics --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="value">UGX {{ number_format($incomeTotal, 0) }}</div>
        <div class="label">Total Income</div>
    </div>
    <div class="stat-card">
        <div class="value">UGX {{ number_format($expenseTotal + $payrollTotal, 0) }}</div>
        <div class="label">Total Expenses</div>
    </div>
    <div class="stat-card">
        <div class="value" style="color:{{ ($incomeTotal - $expenseTotal - $payrollTotal) >= 0 ? '#059669' : '#dc2626' }};">
            UGX {{ number_format($incomeTotal - $expenseTotal - $payrollTotal, 0) }}
        </div>
        <div class="label">Net Surplus/Deficit</div>
    </div>
    <div class="stat-card">
        <div class="value">{{ $expenseTotal + $payrollTotal > 0 ? round(($incomeTotal / ($expenseTotal + $payrollTotal)) * 100, 1) : 0 }}%</div>
        <div class="label">Operating Ratio</div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row" style="margin:0 -10px 20px; display:flex; flex-wrap:wrap;">
    <div class="col-lg-6" style="padding:0 10px; flex:1; min-width:300px;">
        <div class="fin-card">
            <div class="fin-card-header">
                <h3><i class="fas fa-chart-line"></i> Monthly Income Trend</h3>
            </div>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6" style="padding:0 10px; flex:1; min-width:300px;">
        <div class="fin-card">
            <div class="fin-card-header">
                <h3><i class="fas fa-chart-pie"></i> Payment Methods</h3>
            </div>
            <div class="chart-container">
                <canvas id="methodChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Income Statement --}}
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-file-invoice"></i> Income Statement</h3>
    </div>
    <div style="overflow-x:auto;">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Amount (UGX)</th>
                    <th>% of Income</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background:#f5f6ff;">
                    <td colspan="3"><strong>INCOME</strong></td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;">School Fees Collection</td>
                    <td class="amount-mono">UGX {{ number_format($incomeTotal, 0) }}</td>
                    <td>100%</td>
                </tr>
                <tr style="background:#f5f6ff;">
                    <td colspan="3"><strong>EXPENSES</strong></td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;">Operational Expenses</td>
                    <td class="amount-mono">UGX {{ number_format($expenseTotal, 0) }}</td>
                    <td>{{ $incomeTotal > 0 ? round(($expenseTotal / $incomeTotal) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;">Staff Payroll</td>
                    <td class="amount-mono">UGX {{ number_format($payrollTotal, 0) }}</td>
                    <td>{{ $incomeTotal > 0 ? round(($payrollTotal / $incomeTotal) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="border-top:2px solid var(--border); background:#f5f6ff;">
                    <td><strong>Total Expenses</strong></td>
                    <td class="amount-mono"><strong>UGX {{ number_format($expenseTotal + $payrollTotal, 0) }}</strong></td>
                    <td><strong>{{ $incomeTotal > 0 ? round((($expenseTotal + $payrollTotal) / $incomeTotal) * 100, 1) : 0 }}%</strong></td>
                </tr>
                <tr style="background:{{ ($incomeTotal - $expenseTotal - $payrollTotal) >= 0 ? 'rgba(5,150,105,.05)' : 'rgba(220,38,38,.05)' }};">
                    <td><strong>NET SURPLUS / (DEFICIT)</strong></td>
                    <td class="amount-mono"><strong>UGX {{ number_format($incomeTotal - $expenseTotal - $payrollTotal, 0) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Fee Collection by Class --}}
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-chart-simple"></i> Fee Collection by Class</h3>
    </div>
    <div style="overflow-x:auto;">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Class Level</th>
                    <th>Students</th>
                    <th>Total Billed (UGX)</th>
                    <th>Total Collected (UGX)</th>
                    <th>Outstanding (UGX)</th>
                    <th>Collection Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byClass as $class)
                @php
                    $collectionRate = $class->billed > 0 ? round(($class->collected / $class->billed) * 100) : 0;
                @endphp
                <tr>
                    <td><strong>{{ Helper::recordMdname($class->class_level) ?? 'N/A' }}</strong></td>
                    <td>{{ $class->students }}</td>
                    <td class="amount-mono">UGX {{ number_format($class->billed, 0) }}</td>
                    <td class="amount-mono" style="color:#059669;">UGX {{ number_format($class->collected, 0) }}</td>
                    <td class="amount-mono" style="color:#dc2626;">UGX {{ number_format($class->outstanding, 0) }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <span style="min-width:45px;">{{ $collectionRate }}%</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:{{ $collectionRate }}%; background:{{ $collectionRate >= 80 ? '#059669' : ($collectionRate >= 50 ? '#d97706' : '#dc2626') }};"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;">No data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Payment Methods Breakdown --}}
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-credit-card"></i> Payment Methods Breakdown</h3>
    </div>
    <div style="overflow-x:auto;">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Transactions Count</th>
                    <th>Total Amount (UGX)</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPaymentsAmount = $byMethod->sum('total');
                @endphp
                @forelse($byMethod as $method)
                @php
                    $percentage = $totalPaymentsAmount > 0 ? round(($method->total / $totalPaymentsAmount) * 100, 1) : 0;
                    $icon = match($method->payment_method) {
                        'cash' => '💵',
                        'bank_transfer' => '🏦',
                        'mobile_money' => '📱',
                        'cheque' => '📝',
                        default => '🔄'
                    };
                @endphp
                <tr>
                    <td><strong>{{ $icon }} {{ ucfirst(str_replace('_', ' ', $method->payment_method)) }}</strong></td>
                    <td>{{ number_format($method->count) }}</td>
                    <td class="amount-mono">UGX {{ number_format($method->total, 0) }}</td>
                    <td>{{ $percentage }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:2rem;">No payment data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<script>
const monthlyData = @json($monthlyPayments);
const methodData = @json($byMethod);

// Monthly Income Chart
if (document.getElementById('monthlyChart')) {
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(d => {
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return monthNames[parseInt(d.month) - 1] || `Month ${d.month}`;
            }),
            datasets: [{
                label: 'Collections (UGX)',
                data: monthlyData.map(d => parseFloat(d.total)),
                backgroundColor: 'rgba(47,44,203,.12)',
                borderColor: '#2f2ccb',
                borderWidth: 2.5,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(47,44,203,.22)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'UGX ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { family: 'DM Mono', size: 10 },
                        callback: function(value) {
                            return 'UGX ' + (value >= 1e6 ? (value/1e6).toFixed(1) + 'M' : value >= 1e3 ? (value/1e3).toFixed(0) + 'K' : value);
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'DM Sans', size: 11 } }
                }
            }
        }
    });
}

// Payment Methods Chart
if (document.getElementById('methodChart')) {
    new Chart(document.getElementById('methodChart'), {
        type: 'doughnut',
        data: {
            labels: methodData.map(d => d.payment_method.replace('_', ' ').toUpperCase()),
            datasets: [{
                data: methodData.map(d => parseFloat(d.total)),
                backgroundColor: ['#2f2ccb', '#059669', '#d97706', '#7c3aed', '#0d9488'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        font: { family: 'DM Sans', size: 11 },
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: UGX ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function applyFilters() {
    let year = document.getElementById('filterYear').value;
    let term = document.getElementById('filterTerm').value;
    let url = "{{ route('finance.reports') }}?year=" + year;
    if(term) url += "&term=" + term;
    window.location.href = url;
}
</script>
@endsection