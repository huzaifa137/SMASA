{{-- resources/views/Finance/outstanding-fees.blade.php --}}
@extends('layouts-side-bar.master')
<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
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
            grid-template-columns: repeat(3, 1fr);
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

        .stat-card.danger .value {
            color: #dc2626;
        }

        .stat-card.warning .value {
            color: #d97706;
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

        .btn-danger-fin {
            background: #dc2626;
            color: #fff;
        }

        .btn-danger-fin:hover {
            background: #b91c1c;
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

        .badge-red {
            background: var(--fin-red-l);
            color: var(--fin-red);
        }

        .badge-amber {
            background: var(--fin-amber-l);
            color: var(--fin-amber);
        }

        .badge-blue {
            background: rgba(47, 44, 203, .1);
            color: #2f2ccb;
        }

        .badge-gray {
            background: #f1f5f9;
            color: var(--text-2);
        }

        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
            padding: 0 1.5rem 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            flex: 1;
            min-width: 140px;
        }

        .filter-group label {
            font-size: .7rem;
            font-weight: 700;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .filter-group select,
        .filter-group input {
            padding: .65rem .85rem;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            font-size: .85rem;
            background: var(--surface);
            transition: all .15s;
            width: 100%;
            cursor: pointer;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #2f2ccb;
            box-shadow: 0 0 0 3px rgba(47, 44, 203, .1);
        }

        .filter-actions {
            display: flex;
            gap: .5rem;
        }

        .export-bar {
            display: flex;
            gap: .5rem;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
            margin: 0;
            border-radius: 12px;
        }

        .data-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        .data-table th {
            background: #2c29ca;
            padding: .8rem 1rem;
            font-size: .72rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
            text-align: left;
        }

        .data-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .data-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        .data-table td {
            padding: .9rem 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: .85rem;
            color: var(--text-1);
            vertical-align: middle;
        }

        .data-table tr:hover td {
            background: #f5f6ff;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr.high-risk td {
            background: rgba(220, 38, 38, .05);
        }

        .data-table tr.medium-risk td {
            background: rgba(217, 119, 6, .05);
        }

        /* Progress Bar */
        .progress-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .3s;
        }

        /* Pagination */
        .pagination-container {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            background: #fafbff;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        /* Scroll hint */
        .scroll-hint {
            display: none;
            text-align: center;
            font-size: 0.7rem;
            color: var(--text-3);
            margin-top: 0.5rem;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 1199px) {
            .scroll-hint {
                display: flex;
            }
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

            .filters {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .filter-actions {
                width: 100%;
                justify-content: stretch;
            }

            .filter-actions .btn-fin {
                flex: 1;
                justify-content: center;
            }

            .fin-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .export-bar {
                width: 100%;
            }

            .table-wrapper {
                margin: 0 -0.5rem;
                padding: 0 0.5rem;
            }

            .data-table {
                min-width: 850px;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-exclamation-triangle"></i> Finance — Fee Monitoring</div>
            <h1>Outstanding Fees</h1>
            <p>Students with unpaid or partially paid fees</p>
        </div>
    </div>
@endsection

@section('content')

    {{-- Stats Summary --}}
    <div class="stat-grid">
        <div class="stat-card danger">
            <div class="value">UGX {{ number_format($totalOutstanding, 0) }}</div>
            <div class="label">Total Outstanding</div>
            <div class="sub">Total amount pending collection</div>
        </div>
        <div class="stat-card warning">
            <div class="value">{{ $defaulters->total() }}</div>
            <div class="label">Students with Outstanding</div>
            <div class="sub">Have unpaid or partial balance</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $defaulters->where('payment_status', 'unpaid')->count() }}</div>
            <div class="label">Fully Unpaid</div>
            <div class="sub">No payments made yet</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-filter"></i> Filter Defaulters</h3>
            <div class="export-bar">
                <button onclick="window.print()" class="btn-fin btn-outline-fin">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="filters">
            <div class="filter-group">
                <label>Academic Year</label>
                <select id="filterYear" onchange="applyFilters()">
                    <option value="{{ date('Y') }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                    <option value="{{ date('Y') - 1 }}" {{ $year == date('Y') - 1 ? 'selected' : '' }}>{{ date('Y') - 1 }}</option>
                    <option value="{{ date('Y') - 2 }}" {{ $year == date('Y') - 2 ? 'selected' : '' }}>{{ date('Y') - 2 }}</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Term</label>
                <select id="filterTerm" onchange="applyFilters()">
                    <option value="">All Terms</option>
                    <option value="1" {{ $term == '1' ? 'selected' : '' }}>Term 1</option>
                    <option value="2" {{ $term == '2' ? 'selected' : '' }}>Term 2</option>
                    <option value="3" {{ $term == '3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="filterStatus" onchange="applyFilters()">
                    <option value="">All Outstanding</option>
                    <option value="unpaid">Fully Unpaid</option>
                    <option value="partial">Partially Paid</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Min Balance (UGX)</label>
                <input type="text" id="minBalance" placeholder="e.g., 500,000" step="100000">
            </div>
            <div class="filter-actions">
                <button class="btn-fin btn-primary-fin" onclick="applyFilters()">
                    <i class="fas fa-search"></i> Apply
                </button>
                <button class="btn-fin btn-outline-fin" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Defaulters Table --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-users"></i> Students with Outstanding Fees</h3>
            <span style="font-size:.75rem;color:var(--text-3);">{{ $defaulters->total() }} defaulters</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission #</th>
                        <th>Class</th>
                        <th>Fee Structure</th>
                        <th>Term/Year</th>
                        <th>Total Billed</th>
                        <th>Amount Paid</th>
                        <th>Outstanding Balance</th>
                        <th>Status</th>
                        @if(PermissionHelper::canFeature('record_payment'))
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($defaulters as $index => $alloc)
                        @php
                            $net = $alloc->allocated_amount - $alloc->discount_amount;
                            $paid = $net - $alloc->balance;
                            $percent = $net > 0 ? round(($paid / $net) * 100) : 0;
                            $riskClass = $alloc->balance > 500000 ? 'high-risk' : ($alloc->balance > 200000 ? 'medium-risk' : '');
                            $rowNumber = ($defaulters->currentPage() - 1) * $defaulters->perPage() + $index + 1;
                        @endphp
                        <tr class="{{ $riskClass }}">
                            <td>{{ $rowNumber }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $alloc->student->firstname ?? 'N/A' }}
                                    {{ $alloc->student->lastname ?? '' }}</div>
                                <div style="font-size:.7rem;color:var(--text-3);">Parent:
                                    {{ $alloc->student->parent_name ?? '—' }}</div>
                            </td>
                            <td><span class="badge-fin badge-blue">{{ $alloc->student->admission_number ?? '—' }}</span></td>
                            <td>{{ Helper::recordMdname($alloc->student->senior) ?? '—' }}</td>
                            <td>
                                <div>{{ $alloc->feeStructure->name ?? '—' }}</div>
                                <div style="font-size:.7rem;color:var(--text-3);">
                                    {{ $alloc->feeStructure->student_type ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="badge-fin badge-blue">{{ $alloc->academic_year }}</span>
                                <span class="badge-fin badge-gray">Term {{ $alloc->term }}</span>
                            </td>
                            <td class="amount-mono">UGX {{ number_format($net, 0) }}</td>
                            <td class="amount-mono" style="color:#059669;">UGX {{ number_format($paid, 0) }}
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:{{ $percent }}%;background:#059669;"></div>
                                </div>
                            </td>
                            <td class="amount-mono" style="color:#dc2626;font-weight:700;font-size:1rem;">
                                UGX {{ number_format($alloc->balance, 0) }}
                            </td>
                            <td>
                                @if($alloc->payment_status == 'partial')
                                    <span class="badge-fin badge-amber"><i class="fas fa-hourglass-half"></i> Partial</span>
                                @else
                                    <span class="badge-fin badge-red"><i class="fas fa-times-circle"></i> Unpaid</span>
                                @endif
                            </td>
                            @if(PermissionHelper::canFeature('record_payment'))
                                <td>
                                    <a href="{{ route('finance.payments.create') }}?student_id={{ $alloc->student_id }}"
                                        class="btn-fin btn-sm btn-primary-fin" style="padding:.4rem .8rem;font-size:.75rem;">
                                        <i class="fas fa-plus"></i> Record Payment
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align:center;padding:3rem;">
                                <i class="fas fa-check-circle"
                                    style="font-size:3rem;color:#059669;opacity:.5;display:block;margin-bottom:1rem;"></i>
                                <h3 style="color:var(--text-1);margin-bottom:.5rem;">No Outstanding Fees!</h3>
                                <p style="color:var(--text-2);">All students have fully paid their fees for the selected period.
                                </p>
                                <a href="{{ route('finance.dashboard') }}" class="btn-fin btn-primary-fin"
                                    style="margin-top:1rem;">
                                    <i class="fas fa-home"></i> Back to Dashboard
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="scroll-hint">
            <i class="fas fa-arrows-alt-h"></i>
            <span>Swipe or scroll horizontally to see more columns</span>
            <i class="fas fa-arrows-alt-h"></i>
        </div>
        @if($defaulters->hasPages())
            <div class="pagination-container">
                {{ $defaulters->appends(['year' => $year, 'term' => $term])->links() }}
            </div>
        @endif
    </div>

    {{-- Summary Table Footer --}}
    @if($defaulters->count() > 0)
        <div class="fin-card" style="background:linear-gradient(135deg,rgba(47,44,203,.03),var(--surface));">
            <div class="fin-card-header">
                <h3><i class="fas fa-chart-line"></i> Collection Summary</h3>
            </div>
            <div style="padding:1rem 1.5rem;">
                @php
                    $totalBilledAll = $defaulters->sum(function ($a) {
                        return $a->allocated_amount - $a->discount_amount; });
                    $totalPaidAll = $totalBilledAll - $totalOutstanding;
                    $collectionRate = $totalBilledAll > 0 ? round(($totalPaidAll / $totalBilledAll) * 100) : 0;
                @endphp
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;text-align:center;">
                    <div>
                        <div style="font-size:.75rem;color:var(--text-3);">Total Billed (Selected)</div>
                        <div style="font-size:1.2rem;font-weight:700;">UGX {{ number_format($totalBilledAll, 0) }}</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem;color:var(--text-3);">Total Collected</div>
                        <div style="font-size:1.2rem;font-weight:700;color:#059669;">UGX {{ number_format($totalPaidAll, 0) }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.75rem;color:var(--text-3);">Collection Rate</div>
                        <div style="font-size:1.2rem;font-weight:700;">{{ $collectionRate }}%</div>
                        <div class="progress-bar" style="margin-top:.3rem;">
                            <div class="progress-fill"
                                style="width:{{ $collectionRate }}%;background:{{ $collectionRate >= 80 ? '#059669' : ($collectionRate >= 50 ? '#d97706' : '#dc2626') }};">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

    @endif

                </div>
        </div>
                </div>
            </div>
        </div>

    <script>
        function formatNumberWithCommas(value) {
            let numbers = value.toString().replace(/\D/g, '');
            if (numbers === '') return '';
            return parseInt(numbers, 10).toLocaleString('en-US');
        }

        function parseFormattedNumber(value) {
            return value.toString().replace(/,/g, '');
        }

        // Format min balance input
        const minBalanceInput = document.getElementById('minBalance');
        if (minBalanceInput) {
            minBalanceInput.addEventListener('input', function (e) {
                const rawValue = this.value;
                const numericValue = parseFormattedNumber(rawValue);
                if (numericValue !== '') {
                    this.value = formatNumberWithCommas(numericValue);
                } else {
                    this.value = '';
                }
            });

            // Format initial value if exists
            if (minBalanceInput.value && minBalanceInput.value !== '') {
                const rawValue = parseFormattedNumber(minBalanceInput.value);
                if (rawValue !== '') {
                    minBalanceInput.value = formatNumberWithCommas(rawValue);
                }
            }
        }

        function applyFilters() {
            let year = document.getElementById('filterYear').value;
            let term = document.getElementById('filterTerm').value;
            let status = document.getElementById('filterStatus').value;
            let minBalanceRaw = document.getElementById('minBalance').value;
            let minBalance = minBalanceRaw ? parseFormattedNumber(minBalanceRaw) : '';

            let url = "{{ route('finance.outstanding-fees') }}?year=" + year;
            if (term) url += "&term=" + term;
            if (status) url += "&status=" + status;
            if (minBalance) url += "&min_balance=" + minBalance;

            window.location.href = url;
        }

        function resetFilters() {
            window.location.href = "{{ route('finance.outstanding-fees') }}";
        }

        document.getElementById('minBalance').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') applyFilters();
        });
    </script>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .fin-card,
            .fin-card * {
                visibility: visible;
            }

            .fin-hero,
            .stat-grid,
            .filters,
            .export-bar,
            .pagination-container,
            .btn-fin,
            .action-icons,
            .form-actions,
            .scroll-hint {
                display: none !important;
            }

            .fin-card {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                margin: 0;
                border: none;
                box-shadow: none;
            }

            .data-table th,
            .data-table td {
                border: 1px solid #ddd;
            }
        }
    </style>
@endsection