@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --g: #059669; --gl: rgba(5,150,105,.10);
    --r: #dc2626; --rl: rgba(220,38,38,.10);
    --b: #2563eb; --bl: rgba(37,99,235,.10);
    --a: #d97706; --al: rgba(217,119,6,.10);
    --p: #7c3aed; --pl: rgba(124,58,237,.10);
    --c: #0891b2; --cl: rgba(8,145,178,.10);
    --surf: #fff; --bg: #f0f4f8; --brd: #e2e8f0;
    --t1: #0f172a; --t2: #475569; --t3: #94a3b8;
    --rad: 16px; --rads: 10px;
    --sh: 0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.05);
    --shl: 0 8px 32px rgba(0,0,0,.10);
}
*{font-family:'Inter',sans-serif;box-sizing:border-box}
body{background:var(--bg)}

/* HERO */
.fin-hero{background:linear-gradient(135deg,#0f172a 0%,#1a2744 55%,#0c2340 100%);border-radius:0 0 28px 28px;padding:2.5rem 2rem 4rem;margin-bottom:-2rem;position:relative;overflow:hidden}
.fin-hero::before{content:'';position:absolute;top:-80px;right:-80px;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(5,150,105,.20) 0%,transparent 70%)}
.fin-hero::after{content:'';position:absolute;bottom:-50px;left:5%;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.15) 0%,transparent 70%)}
.fin-hero h1{color:#fff;font-size:1.8rem;font-weight:800;margin:0}
.fin-hero p{color:#94a3b8;margin:.25rem 0 0;font-size:.92rem}
.hero-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(5,150,105,.2);border:1px solid rgba(5,150,105,.35);color:#34d399;padding:.28rem .75rem;border-radius:20px;font-size:.75rem;font-weight:600;margin-bottom:.7rem}
.hero-stat{display:flex;align-items:center;gap:.5rem;color:rgba(255,255,255,.55);font-size:.82rem}
.hero-stat strong{color:#fff;font-weight:600}

/* KPI */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:1.5rem}
.kpi{background:var(--surf);border-radius:var(--rad);padding:1.4rem 1.5rem;border:1px solid var(--brd);box-shadow:var(--sh);position:relative;overflow:hidden;transition:transform .22s,box-shadow .22s;cursor:default}
.kpi:hover{transform:translateY(-4px);box-shadow:var(--shl)}
.kpi-accent{position:absolute;top:0;right:0;width:4px;height:100%;border-radius:0 var(--rad) var(--rad) 0}
.kpi-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:.9rem}
.kpi-val{font-size:1.6rem;font-weight:800;color:var(--t1);line-height:1;font-family:'JetBrains Mono',monospace}
.kpi-val small{font-size:.85rem;font-weight:400;color:var(--t3);font-family:'Inter',sans-serif}
.kpi-lbl{font-size:.78rem;color:var(--t2);margin-top:.28rem;font-weight:500}
.kpi-foot{display:flex;align-items:center;gap:.3rem;font-size:.74rem;margin-top:.5rem;font-weight:500}
.trend-up{color:var(--g)} .trend-dn{color:var(--r)} .trend-nu{color:var(--t3)}

/* SECTION CARDS */
.fc{background:var(--surf);border-radius:var(--rad);border:1px solid var(--brd);box-shadow:var(--sh);overflow:hidden}
.fc-hd{padding:1.1rem 1.4rem;border-bottom:1px solid var(--brd);display:flex;align-items:center;justify-content:space-between}
.fc-hd h3{font-size:.95rem;font-weight:700;color:var(--t1);margin:0;display:flex;align-items:center;gap:.5rem}
.fc-bd{padding:1.4rem}
.sec-link{font-size:.78rem;color:var(--b);font-weight:600;text-decoration:none;display:flex;align-items:center;gap:.2rem}
.sec-link:hover{text-decoration:underline}

/* PROGRESS */
.bar-track{height:7px;background:#f1f5f9;border-radius:99px;overflow:hidden;margin:.4rem 0 .15rem}
.bar-fill{height:100%;border-radius:99px;transition:width .6s cubic-bezier(.4,0,.2,1)}

/* TRANSACTION ROW */
.txn{display:flex;align-items:center;gap:.9rem;padding:.75rem 0;border-bottom:1px solid #f8fafc;transition:background .12s}
.txn:last-child{border-bottom:none}
.txn-ico{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:.88rem;flex-shrink:0}
.txn-name{font-size:.85rem;font-weight:600;color:var(--t1)}
.txn-meta{font-size:.74rem;color:var(--t3);margin-top:.05rem}
.txn-amt{font-family:'JetBrains Mono',monospace;font-size:.88rem;font-weight:600;white-space:nowrap}

/* QUICK ACTIONS */
.qa-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.7rem}
.qa{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.1rem .7rem;background:#f8fafc;border:1px solid var(--brd);border-radius:var(--rad);cursor:pointer;text-decoration:none;transition:all .18s;text-align:center}
.qa:hover{background:var(--surf);border-color:var(--b);box-shadow:0 0 0 3px var(--bl),var(--sh);transform:translateY(-2px)}
.qa-ico{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1rem}
.qa span{font-size:.72rem;font-weight:600;color:var(--t2);line-height:1.25}

/* ALERT PILLS */
.alert-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:600;text-decoration:none}

/* SURPLUS/DEFICIT */
.fin-balance{display:flex;align-items:center;gap:1.5rem;padding:1rem 1.4rem;background:linear-gradient(90deg,rgba(5,150,105,.06),transparent);border-left:3px solid var(--g);border-radius:0 var(--rads) var(--rads) 0;margin-bottom:1.2rem}

/* NAV TABS */
.fin-tabs{display:flex;gap:.4rem;padding:.6rem .6rem 0}
.fin-tab{padding:.45rem 1rem;border-radius:8px 8px 0 0;font-size:.8rem;font-weight:600;color:var(--t2);cursor:pointer;border:none;background:transparent;transition:all .15s}
.fin-tab.active{background:var(--surf);color:var(--b);border:1px solid var(--brd);border-bottom-color:var(--surf);margin-bottom:-1px}

/* TOOLTIP */
[data-tooltip]{position:relative;cursor:help}
[data-tooltip]:hover::after{content:attr(data-tooltip);position:absolute;bottom:110%;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;font-size:.72rem;padding:.3rem .6rem;border-radius:6px;white-space:nowrap;pointer-events:none;z-index:99}

@media(max-width:900px){.kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.kpi-grid{grid-template-columns:1fr}.qa-grid{grid-template-columns:repeat(2,1fr)}}
</style>
@endsection

@section('page-header')
<div class="fin-hero">
    <div style="position:relative;z-index:1">
        <div class="hero-badge"><i class="fas fa-chart-line"></i> Finance Module</div>
        <h1><i class="fas fa-university" style="opacity:.6;margin-right:.4rem"></i> Financial Overview</h1>
        <p>Academic Year {{ $year }} &nbsp;·&nbsp; All figures in UGX</p>
        <div style="display:flex;gap:2rem;margin-top:1.2rem;flex-wrap:wrap">
            <div class="hero-stat"><i class="fas fa-circle" style="font-size:.5rem;color:#34d399"></i> <strong>UGX {{ number_format($totalIncome,0) }}</strong> collected</div>
            <div class="hero-stat"><i class="fas fa-circle" style="font-size:.5rem;color:#f87171"></i> <strong>UGX {{ number_format($outstanding,0) }}</strong> outstanding</div>
            <div class="hero-stat"><i class="fas fa-circle" style="font-size:.5rem;color:#fbbf24"></i> <strong>UGX {{ number_format($totalExpenses+$totalPayroll,0) }}</strong> spent</div>
        </div>
    </div>
    @if($pendingExpenses > 0 || $pendingPayroll > 0)
    <div style="position:relative;z-index:1;margin-top:1.2rem;display:flex;gap:.5rem;flex-wrap:wrap">
        @if($pendingExpenses > 0)
        <a href="{{ route('finance.expenses.index') }}" class="alert-pill" style="background:rgba(217,119,6,.25);color:#fcd34d;border:1px solid rgba(217,119,6,.4)"><i class="fas fa-exclamation-triangle"></i> {{ $pendingExpenses }} expense(s) pending</a>
        @endif
        @if($pendingPayroll > 0)
        <a href="{{ route('finance.payroll.index') }}" class="alert-pill" style="background:rgba(37,99,235,.25);color:#93c5fd;border:1px solid rgba(37,99,235,.4)"><i class="fas fa-clock"></i> {{ $pendingPayroll }} payroll(s) awaiting approval</a>
        @endif
    </div>
    @endif
</div>
@endsection

@section('content')
@php
$netBalance = $totalIncome - ($totalExpenses + $totalPayroll);
$collectionRate = $totalBilled > 0 ? round($totalIncome / $totalBilled * 100) : 0;
@endphp

{{-- KPI ROW --}}
<div class="kpi-grid">
    <a href="{{ route('finance.payments.index') }}" style="text-decoration:none">
    <div class="kpi">
        <div class="kpi-accent" style="background:var(--g)"></div>
        <div class="kpi-icon" style="background:var(--gl);color:var(--g)"><i class="fas fa-arrow-down"></i></div>
        <div class="kpi-val"><small>UGX </small>{{ number_format($totalIncome,0) }}</div>
        <div class="kpi-lbl">Total Fee Collections</div>
        <div class="kpi-foot trend-up"><i class="fas fa-check-circle"></i> {{ $collectionRate }}% collection rate</div>
    </div>
    </a>
    <a href="{{ route('finance.outstanding-fees') }}" style="text-decoration:none">
    <div class="kpi">
        <div class="kpi-accent" style="background:var(--r)"></div>
        <div class="kpi-icon" style="background:var(--rl);color:var(--r)"><i class="fas fa-exclamation-circle"></i></div>
        <div class="kpi-val"><small>UGX </small>{{ number_format(max(0,$outstanding),0) }}</div>
        <div class="kpi-lbl">Outstanding Fees</div>
        <div class="kpi-foot trend-dn"><i class="fas fa-user-times"></i> {{ $feeStats->unpaid ?? 0 }} unpaid · {{ $feeStats->partial ?? 0 }} partial</div>
    </div>
    </a>
    <a href="{{ route('finance.expenses.index') }}" style="text-decoration:none">
    <div class="kpi">
        <div class="kpi-accent" style="background:var(--a)"></div>
        <div class="kpi-icon" style="background:var(--al);color:var(--a)"><i class="fas fa-arrow-up"></i></div>
        <div class="kpi-val"><small>UGX </small>{{ number_format($totalExpenses,0) }}</div>
        <div class="kpi-lbl">Total Expenses</div>
        <div class="kpi-foot trend-nu"><i class="fas fa-tag"></i> Operational costs</div>
    </div>
    </a>
    <a href="{{ route('finance.payroll.index') }}" style="text-decoration:none">
    <div class="kpi">
        <div class="kpi-accent" style="background:var(--p)"></div>
        <div class="kpi-icon" style="background:var(--pl);color:var(--p)"><i class="fas fa-users"></i></div>
        <div class="kpi-val"><small>UGX </small>{{ number_format($totalPayroll,0) }}</div>
        <div class="kpi-lbl">Payroll Disbursed</div>
        <div class="kpi-foot trend-nu"><i class="fas fa-money-check-alt"></i> Net salaries paid</div>
    </div>
    </a>
</div>

{{-- NET BALANCE BANNER --}}
<div class="fin-balance" style="{{ $netBalance >= 0 ? '' : 'background:linear-gradient(90deg,rgba(220,38,38,.06),transparent);border-left-color:var(--r)' }}">
    <div>
        <div style="font-size:.75rem;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.05em">Net Balance (Income − Expenses − Payroll)</div>
        <div style="font-size:1.5rem;font-weight:800;color:{{ $netBalance >= 0 ? 'var(--g)' : 'var(--r)' }};font-family:'JetBrains Mono',monospace;margin-top:.2rem">
            {{ $netBalance >= 0 ? '+' : '' }}UGX {{ number_format($netBalance,0) }}
        </div>
    </div>
    <div style="margin-left:auto;text-align:right">
        <div style="font-size:.75rem;color:var(--t3)">Total Billed</div>
        <div style="font-size:1rem;font-weight:700;color:var(--t1);font-family:'JetBrains Mono',monospace">UGX {{ number_format($totalBilled,0) }}</div>
    </div>
</div>

<div class="row" style="margin:0 -10px">
    {{-- CHART --}}
    <div class="col-lg-8" style="padding:0 10px 20px">
        <div class="fc">
            <div class="fc-hd">
                <h3><i class="fas fa-chart-area" style="color:var(--b)"></i> Monthly Fee Collections</h3>
                <span style="font-size:.75rem;color:var(--t3)">Last 6 months</span>
            </div>
            <div class="fc-bd"><canvas id="monthlyChart" height="95"></canvas></div>
        </div>
    </div>

    {{-- FEE STATUS DONUT --}}
    <div class="col-lg-4" style="padding:0 10px 20px">
        <div class="fc">
            <div class="fc-hd"><h3><i class="fas fa-chart-pie" style="color:var(--p)"></i> Fee Collection Status</h3></div>
            <div class="fc-bd" style="padding-top:.8rem">
                <div style="position:relative;width:130px;height:130px;margin:0 auto .2rem">
                    <canvas id="feeStatusChart"></canvas>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
                        <div style="font-size:1.4rem;font-weight:800;color:var(--t1);font-family:'JetBrains Mono',monospace">{{ $collectionRate }}%</div>
                        <div style="font-size:.65rem;color:var(--t3);font-weight:500">collected</div>
                    </div>
                </div>
                @php
                $tot = max(1,$feeStats->total_students ?? 0);
                $pp = round(($feeStats->fully_paid??0)/$tot*100);
                $pp2 = round(($feeStats->partial??0)/$tot*100);
                $pp3 = round(($feeStats->unpaid??0)/$tot*100);
                @endphp
                <div style="display:flex;flex-direction:column;gap:.55rem;margin-top:1rem">
                    @foreach([['Fully Paid',$feeStats->fully_paid??0,$pp,'var(--g)'],['Partial',$feeStats->partial??0,$pp2,'var(--a)'],['Unpaid',$feeStats->unpaid??0,$pp3,'var(--r)']] as [$lbl,$n,$pct,$col])
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:3px">
                            <span style="color:{{ $col }};font-weight:600"><i class="fas fa-circle" style="font-size:.45rem;vertical-align:middle"></i> {{ $lbl }}</span>
                            <span style="color:var(--t2)">{{ $n }} ({{ $pct }}%)</span>
                        </div>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $pct }}%;background:{{ $col }}"></div></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin:0 -10px">
    {{-- RECENT PAYMENTS --}}
    <div class="col-lg-7" style="padding:0 10px 20px">
        <div class="fc">
            <div class="fc-hd">
                <h3><i class="fas fa-receipt" style="color:var(--g)"></i> Recent Payments</h3>
                <a href="{{ route('finance.payments.index') }}" class="sec-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="fc-bd" style="padding-top:.5rem;padding-bottom:.5rem">
                @forelse($recentPayments as $pmt)
                <div class="txn">
                    <div class="txn-ico" style="background:var(--gl);color:var(--g)"><i class="fas fa-{{ $pmt->methodIcon() }}"></i></div>
                    <div style="flex:1;min-width:0">
                        <div class="txn-name">{{ optional($pmt->student)->firstname }} {{ optional($pmt->student)->lastname }}</div>
                        <div class="txn-meta">{{ $pmt->receipt_number }} &bull; {{ $pmt->payment_date?->format('M d, Y') }} &bull; {{ $pmt->methodLabel() }}</div>
                    </div>
                    <div class="txn-amt" style="color:var(--g)">+{{ number_format($pmt->amount_paid,0) }}</div>
                </div>
                @empty
                <div style="text-align:center;padding:2.5rem 1rem;color:var(--t3)">
                    <i class="fas fa-receipt" style="font-size:2rem;opacity:.25;display:block;margin-bottom:.5rem"></i>
                    No payments recorded yet
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-5" style="padding:0 10px 20px">
        {{-- EXPENSE BREAKDOWN --}}
        <div class="fc" style="margin-bottom:1.2rem">
            <div class="fc-hd">
                <h3><i class="fas fa-layer-group" style="color:var(--a)"></i> Expense Breakdown</h3>
                <a href="{{ route('finance.expenses.index') }}" class="sec-link">Details <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="fc-bd" style="padding-top:.5rem;padding-bottom:.5rem">
                @forelse($expenseBreakdown as $eb)
                @php $pct2 = $totalExpenses > 0 ? round($eb->total/$totalExpenses*100) : 0; @endphp
                <div style="padding:.65rem 0;border-bottom:1px solid #f8fafc">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                        <span style="font-size:.82rem;font-weight:600;color:var(--t1)">{{ $eb->cat_name }}</span>
                        <span style="font-size:.78rem;font-family:'JetBrains Mono',monospace;color:var(--t2)">{{ number_format($eb->total,0) }}</span>
                    </div>
                    <div class="bar-track"><div class="bar-fill" style="width:{{ $pct2 }}%;background:{{ $eb->cat_color ?? '#6366f1' }}"></div></div>
                    <span style="font-size:.7rem;color:var(--t3)">{{ $pct2 }}% of total expenses</span>
                </div>
                @empty
                <div style="text-align:center;padding:1.5rem;color:var(--t3);font-size:.82rem">No expenses recorded</div>
                @endforelse
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="fc">
            <div class="fc-hd"><h3><i class="fas fa-bolt" style="color:var(--a)"></i> Quick Actions</h3></div>
            <div class="fc-bd">
                <div class="qa-grid">
                    <a href="{{ route('finance.payments.create') }}" class="qa"><div class="qa-ico" style="background:var(--gl);color:var(--g)"><i class="fas fa-plus"></i></div><span>Record Payment</span></a>
                    <a href="{{ route('finance.expenses.create') }}" class="qa"><div class="qa-ico" style="background:var(--rl);color:var(--r)"><i class="fas fa-file-invoice-dollar"></i></div><span>Add Expense</span></a>
                    <a href="{{ route('finance.payroll.create') }}" class="qa"><div class="qa-ico" style="background:var(--pl);color:var(--p)"><i class="fas fa-users"></i></div><span>Run Payroll</span></a>
                    <a href="{{ route('finance.fee-structures.index') }}" class="qa"><div class="qa-ico" style="background:var(--bl);color:var(--b)"><i class="fas fa-layer-group"></i></div><span>Fee Structures</span></a>
                    <a href="{{ route('finance.outstanding-fees') }}" class="qa"><div class="qa-ico" style="background:var(--al);color:var(--a)"><i class="fas fa-exclamation-triangle"></i></div><span>Defaulters</span></a>
                    <a href="{{ route('finance.reports') }}" class="qa"><div class="qa-ico" style="background:var(--cl);color:var(--c)"><i class="fas fa-chart-bar"></i></div><span>Reports</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const mData = @json($monthlyTrend);
new Chart(document.getElementById('monthlyChart'),{
    type:'bar',
    data:{
        labels:mData.map(d=>{const[y,m]=d.month.split('-');return new Date(y,m-1).toLocaleDateString('en-UG',{month:'short',year:'2-digit'})}),
        datasets:[{
            label:'Collections (UGX)',data:mData.map(d=>parseFloat(d.total)),
            backgroundColor:'rgba(37,99,235,.12)',borderColor:'#2563eb',borderWidth:2.5,
            borderRadius:8,borderSkipped:false,
            hoverBackgroundColor:'rgba(37,99,235,.22)'
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'UGX '+c.raw.toLocaleString()}}},
    scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'},ticks:{font:{family:'JetBrains Mono',size:10},callback:v=>'UGX '+(v>=1e6?(v/1e6).toFixed(1)+'M':v>=1e3?(v/1e3).toFixed(0)+'K':v)}},x:{grid:{display:false},ticks:{font:{family:'Inter',size:11}}}}}
});
new Chart(document.getElementById('feeStatusChart'),{
    type:'doughnut',
    data:{
        labels:['Fully Paid','Partial','Unpaid'],
        datasets:[{data:[{{ $feeStats->fully_paid??0 }},{{ $feeStats->partial??0 }},{{ $feeStats->unpaid??0 }}],
        backgroundColor:['#059669','#d97706','#dc2626'],borderWidth:0,hoverOffset:6}]
    },
    options:{cutout:'76%',responsive:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': '+c.raw+' students'}}}}
});
</script>
@endsection
