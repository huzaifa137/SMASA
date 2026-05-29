@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --fin-green: #059669;
            --fin-green-l: rgba(5, 150, 105, .10);
            --fin-red: #dc2626;
            --fin-red-l: rgba(220, 38, 38, .10);
            --fin-blue: #2563eb;
            --fin-blue-l: rgba(37, 99, 235, .10);
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
            --radius-sm: 10px;
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

        /* ── Hero Header ── */
        .fin-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f2d4a 100%);
            border-radius: 24px;
            padding: 2.5rem 2.5rem 2rem;
            margin-bottom: 2rem;
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
            background: radial-gradient(circle, rgba(5, 150, 105, .25) 0%, transparent 70%);
        }

        .fin-hero::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: 10%;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, .18) 0%, transparent 70%);
        }

        .fin-hero h1 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .fin-hero p {
            color: #94a3b8;
            margin: .25rem 0 0;
            font-size: .95rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(5, 150, 105, .2);
            border: 1px solid rgba(5, 150, 105, .35);
            color: #34d399;
            padding: .3rem .8rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
            margin-bottom: .8rem;
        }

        /* ── KPI Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: transform .25s, box-shadow .25s;
            cursor: default;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .kpi-card .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .kpi-card .kpi-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--text-1);
            line-height: 1;
            font-family: 'DM Mono', monospace;
        }

        .kpi-card .kpi-value small {
            font-size: .9rem;
            font-weight: 400;
            color: var(--text-3);
        }

        .kpi-card .kpi-label {
            font-size: .82rem;
            color: var(--text-2);
            margin-top: .35rem;
            font-weight: 500;
        }

        .kpi-stripe {
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        .kpi-trend {
            display: flex;
            align-items: center;
            gap: .3rem;
            font-size: .78rem;
            margin-top: .6rem;
            font-weight: 500;
        }

        .trend-up {
            color: var(--fin-green);
        }

        .trend-down {
            color: var(--fin-red);
        }

        /* ── Section Cards ── */
        .fin-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .fin-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fin-card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-1);
            margin: 0;
        }

        .fin-card-body {
            padding: 1.5rem;
        }

        /* ── Progress bar ── */
        .progress-slim {
            height: 6px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            margin: .5rem 0 .2rem;
        }

        .progress-slim-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .6s cubic-bezier(.4, 0, .2, 1);
        }

        /* ── Transaction List ── */
        .txn-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: .85rem 0;
            border-bottom: 1px solid #f8fafc;
            transition: background .15s;
        }

        .txn-row:last-child {
            border-bottom: none;
        }

        .txn-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .txn-name {
            font-size: .88rem;
            font-weight: 600;
            color: var(--text-1);
        }

        .txn-meta {
            font-size: .78rem;
            color: var(--text-3);
        }

        .txn-amount {
            font-family: 'DM Mono', monospace;
            font-size: .9rem;
            font-weight: 600;
        }

        /* ── Quick Action buttons ── */
        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .6rem;
            padding: 1.2rem .8rem;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
            text-align: center;
        }

        .quick-action:hover {
            background: #fff;
            border-color: var(--fin-blue);
            box-shadow: 0 0 0 3px var(--fin-blue-l), var(--shadow);
            transform: translateY(-2px);
        }

        .quick-action .qa-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .quick-action span {
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-2);
            line-height: 1.3;
        }

        /* ── Donut chart (pure CSS) ── */
        .donut-wrap {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto;
        }

        .donut-wrap canvas {
            width: 140px !important;
            height: 140px !important;
        }

        /* ── Alert badges ── */
        .alert-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .85rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        @media(max-width:900px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:560px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-shield-alt"></i> Finance Module</div>
            <h1>Financial Overview</h1>
            <p>Academic Year {{ $year }} — All figures in UGX</p>
        </div>
        @if($pendingExpenses > 0 || $pendingPayroll > 0)
            <div style="position:relative;z-index:1;margin-top:1rem;display:flex;gap:.6rem;flex-wrap:wrap;">
                @if($pendingExpenses > 0)
                    <a href="{{ route('finance.expenses.index') }}" class="alert-pill"
                        style="background:rgba(217,119,6,.25);color:#fcd34d;border:1px solid rgba(217,119,6,.4);">
                        <i class="fas fa-exclamation-triangle"></i> {{ $pendingExpenses }} expense(s) pending
                    </a>
                @endif
                @if($pendingPayroll > 0)
                    <a href="{{ route('finance.payroll.index') }}" class="alert-pill"
                        style="background:rgba(37,99,235,.25);color:#93c5fd;border:1px solid rgba(37,99,235,.4);">
                        <i class="fas fa-clock"></i> {{ $pendingPayroll }} payroll(s) awaiting approval
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection

@section('content')

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        {{-- Total Income --}}
        <div class="kpi-card">
            <div class="kpi-stripe" style="background:var(--fin-green);"></div>
            <div class="kpi-icon" style="background:var(--fin-green-l);color:var(--fin-green);">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="kpi-value"><small>UGX </small>{{ number_format($totalIncome, 0) }}</div>
            <div class="kpi-label">Total Fee Collections</div>
            <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> {{ $year }}</div>
        </div>

        {{-- Outstanding --}}
        <div class="kpi-card">
            <div class="kpi-stripe" style="background:var(--fin-red);"></div>
            <div class="kpi-icon" style="background:var(--fin-red-l);color:var(--fin-red);">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="kpi-value"><small>UGX </small>{{ number_format(max(0, $outstanding), 0) }}</div>
            <div class="kpi-label">Outstanding Fees</div>
            <div class="kpi-trend trend-down"><i class="fas fa-user-times"></i>
                {{ $feeStats->unpaid ?? 0 }} unpaid &bull; {{ $feeStats->partial ?? 0 }} partial
            </div>
        </div>

        {{-- Total Expenses --}}
        <div class="kpi-card">
            <div class="kpi-stripe" style="background:var(--fin-amber);"></div>
            <div class="kpi-icon" style="background:var(--fin-amber-l);color:var(--fin-amber);">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="kpi-value"><small>UGX </small>{{ number_format($totalExpenses, 0) }}</div>
            <div class="kpi-label">Total Expenses</div>
            <div class="kpi-trend" style="color:var(--text-3);">All operational costs</div>
        </div>

        {{-- Payroll --}}
        <div class="kpi-card">
            <div class="kpi-stripe" style="background:var(--fin-purple);"></div>
            <div class="kpi-icon" style="background:var(--fin-purple-l);color:var(--fin-purple);">
                <i class="fas fa-users"></i>
            </div>
            <div class="kpi-value"><small>UGX </small>{{ number_format($totalPayroll, 0) }}</div>
            <div class="kpi-label">Teacher Payroll Paid</div>
            <div class="kpi-trend" style="color:var(--text-3);">Net salaries disbursed</div>
        </div>
    </div>

    <div class="row" style="margin:0 -10px;">
        {{-- Monthly Trend + Quick Actions --}}
        <div class="col-lg-8" style="padding:0 10px 20px;">
            <div class="fin-card">
                <div class="fin-card-header">
                    <h3><i class="fas fa-chart-line" style="color:var(--fin-blue);margin-right:.5rem;"></i>Monthly Fee
                        Collections</h3>
                    <span style="font-size:.78rem;color:var(--text-3);">Last 6 months</span>
                </div>
                <div class="fin-card-body">
                    <canvas id="monthlyChart" height="90"></canvas>
                </div>
            </div>
        </div>

        {{-- Fee Collection Status Donut --}}
        <div class="col-lg-4" style="padding:0 10px 20px;">
            <div class="fin-card h-100">
                <div class="fin-card-header">
                    <h3><i class="fas fa-pie-chart" style="color:var(--fin-purple);margin-right:.5rem;"></i>Fee Status</h3>
                </div>
                <div class="fin-card-body">
                    <canvas id="feeStatusChart" height="180"></canvas>
                    <div style="margin-top:1.2rem;">
                        @php
                            $total = max(1, ($feeStats->total_students ?? 0));
                            $paidPct = round(($feeStats->fully_paid ?? 0) / $total * 100);
                            $partialPct = round(($feeStats->partial ?? 0) / $total * 100);
                            $unpaidPct = round(($feeStats->unpaid ?? 0) / $total * 100);
                        @endphp
                        <div style="display:flex;flex-direction:column;gap:.6rem;">
                            <div>
                                <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:3px;">
                                    <span style="color:var(--fin-green);font-weight:600;"><i class="fas fa-circle"
                                            style="font-size:.5rem;"></i> Fully Paid</span>
                                    <span>{{ $feeStats->fully_paid ?? 0 }} ({{ $paidPct }}%)</span>
                                </div>
                                <div class="progress-slim">
                                    <div class="progress-slim-fill"
                                        style="width:{{$paidPct}}%;background:var(--fin-green);"></div>
                                </div>
                            </div>
                            <div>
                                <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:3px;">
                                    <span style="color:var(--fin-amber);font-weight:600;"><i class="fas fa-circle"
                                            style="font-size:.5rem;"></i> Partial</span>
                                    <span>{{ $feeStats->partial ?? 0 }} ({{ $partialPct }}%)</span>
                                </div>
                                <div class="progress-slim">
                                    <div class="progress-slim-fill"
                                        style="width:{{$partialPct}}%;background:var(--fin-amber);"></div>
                                </div>
                            </div>
                            <div>
                                <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:3px;">
                                    <span style="color:var(--fin-red);font-weight:600;"><i class="fas fa-circle"
                                            style="font-size:.5rem;"></i> Unpaid</span>
                                    <span>{{ $feeStats->unpaid ?? 0 }} ({{ $unpaidPct }}%)</span>
                                </div>
                                <div class="progress-slim">
                                    <div class="progress-slim-fill"
                                        style="width:{{$unpaidPct}}%;background:var(--fin-red);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin:0 -10px;">
        {{-- Recent Payments --}}
        <div class="col-lg-7" style="padding:0 10px 20px;">
            <div class="fin-card">
                <div class="fin-card-header">
                    <h3><i class="fas fa-receipt" style="color:var(--fin-green);margin-right:.5rem;"></i>Recent Payments
                    </h3>
                    <a href="{{ route('finance.payments.index') }}"
                        style="font-size:.8rem;color:var(--fin-blue);font-weight:600;text-decoration:none;">View All →</a>
                </div>
                <div class="fin-card-body" style="padding-top:.5rem;padding-bottom:.5rem;">
                    @forelse($recentPayments as $pmt)
                        <div class="txn-row">
                            <div class="txn-icon" style="background:var(--fin-green-l);color:var(--fin-green);">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="txn-name">{{ optional($pmt->student)->firstname }}
                                    {{ optional($pmt->student)->lastname }}</div>
                                <div class="txn-meta">{{ $pmt->receipt_number }} &bull;
                                    {{ $pmt->payment_date?->format('M d, Y') }} &bull; {{ $pmt->methodLabel() }}</div>
                            </div>
                            <div class="txn-amount" style="color:var(--fin-green);">+{{ number_format($pmt->amount_paid, 0) }}
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:2rem;color:var(--text-3);">
                            <i class="fas fa-receipt" style="font-size:2rem;opacity:.3;"></i>
                            <p style="margin:.5rem 0 0;">No payments recorded yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Expense Breakdown + Quick Actions --}}
        <div class="col-lg-5" style="padding:0 10px 20px;">
            {{-- Expense Breakdown --}}
            <div class="fin-card" style="margin-bottom:1.25rem;">
                <div class="fin-card-header">
                    <h3><i class="fas fa-layer-group" style="color:var(--fin-amber);margin-right:.5rem;"></i>Expense
                        Breakdown</h3>
                </div>
                <div class="fin-card-body" style="padding-top:.5rem;padding-bottom:.5rem;">
                    @forelse($expenseBreakdown as $eb)
                        @php $pct = $totalExpenses > 0 ? round($eb->total / $totalExpenses * 100) : 0; @endphp
                        <div style="padding:.7rem 0;border-bottom:1px solid #f8fafc;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-size:.83rem;font-weight:600;color:var(--text-1);">{{ $eb->cat_name }}</span>
                                <span
                                    style="font-size:.82rem;font-family:'DM Mono',monospace;color:var(--text-2);">{{ number_format($eb->total, 0) }}</span>
                            </div>
                            <div class="progress-slim">
                                <div class="progress-slim-fill"
                                    style="width:{{ $pct }}%;background:{{ $eb->cat_color ?? '#6366f1' }};"></div>
                            </div>
                            <span style="font-size:.72rem;color:var(--text-3);">{{ $pct }}% of total</span>
                        </div>
                    @empty
                        <div style="text-align:center;padding:1.5rem;color:var(--text-3);font-size:.85rem;">No expenses recorded
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="fin-card">
                <div class="fin-card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="fin-card-body">
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
                        <a href="{{ route('finance.payments.create') }}" class="quick-action">
                            <div class="qa-icon" style="background:var(--fin-green-l);color:var(--fin-green);"><i
                                    class="fas fa-plus"></i></div>
                            <span>Record Payment</span>
                        </a>
                        <a href="{{ route('finance.expenses.create') }}" class="quick-action">
                            <div class="qa-icon" style="background:var(--fin-red-l);color:var(--fin-red);"><i
                                    class="fas fa-file-invoice-dollar"></i></div>
                            <span>Add Expense</span>
                        </a>
                        <a href="{{ route('finance.payroll.create') }}" class="quick-action">
                            <div class="qa-icon" style="background:var(--fin-purple-l);color:var(--fin-purple);"><i
                                    class="fas fa-users"></i></div>
                            <span>Run Payroll</span>
                        </a>
                        <a href="{{ route('finance.fee-structures.index') }}" class="quick-action">
                            <div class="qa-icon" style="background:var(--fin-blue-l);color:var(--fin-blue);"><i
                                    class="fas fa-layer-group"></i></div>
                            <span>Fee Structures</span>
                        </a>
                        <a href="{{ route('finance.outstanding-fees') }}" class="quick-action">
                            <div class="qa-icon" style="background:var(--fin-amber-l);color:var(--fin-amber);"><i
                                    class="fas fa-exclamation-triangle"></i></div>
                            <span>Defaulters</span>
                        </a>
                        <a href="{{ route('finance.reports') }}" class="quick-action">
                            <div class="qa-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-chart-bar"></i>
                            </div>
                            <span>Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ── Monthly Fee Collections Chart ──────────────────────────────────────────
        const monthlyData = @json($monthlyTrend);
        const labels = monthlyData.map(d => {
            const [y, m] = d.month.split('-');
            return new Date(y, m - 1).toLocaleDateString('en-UG', { month: 'short', year: '2-digit' });
        });
        const values = monthlyData.map(d => parseFloat(d.total));

        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Collections (UGX)',
                    data: values,
                    backgroundColor: 'rgba(5,150,105,.15)',
                    borderColor: '#059669',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: {
                            font: { family: 'DM Mono', size: 11 },
                            callback: v => 'UGX ' + (v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v.toLocaleString())
                        }
                    },
                    x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 11 } } }
                }
            }
        });

        // ── Fee Status Donut ────────────────────────────────────────────────────────
        new Chart(document.getElementById('feeStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Fully Paid', 'Partial', 'Unpaid'],
                datasets: [{
                    data: [
                    {{ $feeStats->fully_paid ?? 0 }},
                    {{ $feeStats->partial ?? 0 }},
                    {{ $feeStats->unpaid ?? 0 }},
                    ],
                    backgroundColor: ['#059669', '#d97706', '#dc2626'],
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                cutout: '72%',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' students' }
                    }
                }
            }
        });
    </script>
@endsection