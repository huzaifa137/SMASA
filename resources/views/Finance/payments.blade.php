{{-- resources/views/Finance/payments.blade.php --}}
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
            background: var(--fin-red-l);
            color: var(--fin-red);
            border: 1px solid rgba(220, 38, 38, .2);
        }

        .btn-danger-fin:hover {
            background: var(--fin-red);
            color: #fff;
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

        .badge-purple {
            background: rgba(124, 58, 237, .1);
            color: #7c3aed;
        }

        .badge-gray {
            background: #f1f5f9;
            color: var(--text-2);
        }

        /* Stats Summary */
        .stat-summary {
            background: linear-gradient(135deg, #2f2ccb, #2420a8);
            border-radius: var(--radius);
            padding: 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stat-summary .total {
            font-size: 1.5rem;
            font-weight: 800;
            font-family: 'DM Mono', monospace;
        }

        .stat-summary .label {
            font-size: .75rem;
            opacity: .85;
            margin-bottom: .2rem;
            text-transform: uppercase;
            letter-spacing: .05em;
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

        .search-group {
            flex: 2;
            min-width: 200px;
        }

        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
            margin: 0;
        }

        .data-table {
            width: 100%;
            min-width: 800px;
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

        /* Action Icons */
        .action-icons {
            display: flex;
            gap: .5rem;
            align-items: center;
        }

        .action-icons a,
        .action-icons button {
            background: transparent;
            border: none;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all .2s;
        }

        .action-icons a i,
        .action-icons button i {
            font-size: .9rem;
        }

        .action-icons a:hover,
        .action-icons button:hover {
            transform: scale(1.05);
        }

        .action-icons a:first-child:hover {
            background: rgba(47, 44, 203, .1);
        }

        .action-icons button:hover {
            background: rgba(220, 38, 38, .1);
        }

        /* Pagination */
        .pagination-container {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            background: #fafbff;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        /* Responsive */
        @media(max-width:768px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.3rem;
            }

            .stat-summary {
                flex-direction: column;
                text-align: center;
            }

            .filters {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .fin-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .fin-card-header .btn-fin {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-receipt"></i> Finance — Payments</div>
            <h1>Fee Payments</h1>
            <p>Track all fee collections, view receipts, and manage payment records</p>
        </div>
    </div>
@endsection

@section('content')

    {{-- Summary Stats --}}
    <div class="stat-summary">
        <div>
            <div class="label">Total Collected ({{ $year }})</div>
            <div class="total">UGX {{ number_format($totals->total ?? 0, 0) }}</div>
        </div>
        <div>
            <div class="label">Total Transactions</div>
            <div class="total">{{ number_format($totals->count ?? 0) }}</div>
        </div>
        <div>
            <div class="label">Average Payment</div>
            <div class="total">UGX
                {{ number_format(($totals->count ?? 1) > 0 ? ($totals->total ?? 0) / ($totals->count ?? 1) : 0, 0) }}</div>
        </div>
    </div>

    {{-- Filters & Actions --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-search"></i> Search & Filter</h3>
            <a href="{{ route('finance.payments.create') }}" class="btn-fin btn-primary-fin">
                <i class="fas fa-plus"></i> Record Payment
            </a>
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
            <div class="filter-group search-group">
                <label>Search</label>
                <input type="text" id="searchInput" placeholder="Student name, Receipt #..." value="{{ $search }}"
                    onkeypress="if(event.key==='Enter') applyFilters()">
            </div>
            <div class="filter-group">
                <label>&nbsp;</label>
                <button class="btn-fin btn-primary-fin" onclick="applyFilters()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-list"></i> Payment History</h3>
            <span style="font-size:.75rem;color:#000;">{{ $payments->total() }} records</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Student</th>
                        <th>Year/Term</th>
                        <th>Amount Paid</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div style="font-weight:700;font-family:'DM Mono',monospace;font-size:.85rem;">
                                    {{ $payment->receipt_number }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600;">{{ $payment->student->firstname ?? 'N/A' }}
                                    {{ $payment->student->lastname ?? '' }}</div>
                                <div style="font-size:.7rem;color:var(--text-3);">
                                    {{ $payment->student->admission_number ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="badge-fin badge-blue">{{ $payment->academic_year }}</span>
                                <span class="badge-fin badge-gray">Term {{ $payment->term }}</span>
                            </td>
                            <td class="amount-mono" style="color:#2f2ccb;font-weight:700;">UGX
                                {{ number_format($payment->amount_paid, 0) }}</td>
                            <td>
                                <span class="badge-fin badge-purple">
                                    <i class="fas fa-{{ $payment->methodIcon() }}"></i> {{ $payment->methodLabel() }}
                                </span>
                                @if($payment->bank_name)
                                    <div style="font-size:.65rem;color:var(--text-3);margin-top:2px;">{{ $payment->bank_name }}
                                    </div>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '—' }}</td>
                            <td style="font-size:.75rem;color:var(--text-2);">{{ $payment->transaction_reference ?? '—' }}</td>
                            <td>
                                @if($payment->status == 'confirmed')
                                    <span class="badge-fin badge-green"><i class="fas fa-check-circle"></i> Confirmed</span>
                                @elseif($payment->status == 'pending')
                                    <span class="badge-fin badge-amber"><i class="fas fa-clock"></i> Pending</span>
                                @else
                                    <span class="badge-fin badge-red"><i class="fas fa-ban"></i> Reversed</span>
                                @endif
                            </td>
                            <td class="action-icons">
                                <a href="{{ route('finance.payments.receipt', $payment->id) }}" target="_blank"
                                    title="Download Receipt">
                                    <i class="fas fa-download" style="color:#2f2ccb;"></i>
                                </a>
                               @if($payment->status == 'confirmed')
    <form method="POST" action="{{ route('finance.payments.reverse', $payment->id) }}" class="reverse-form" data-student="{{ $payment->student->firstname ?? '' }} {{ $payment->student->lastname ?? '' }}" data-amount="{{ number_format($payment->amount_paid, 0) }}" data-receipt="{{ $payment->receipt_number }}" style="display:inline;">
        @csrf
        <button type="button" class="reverse-btn" title="Reverse Payment" onclick="confirmReverse(this)">
            <i class="fas fa-undo-alt" style="color:#dc2626;"></i>
        </button>
    </form>
@endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:3rem;">
                                <i class="fas fa-receipt"
                                    style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                                <p style="margin:0 0 1rem 0;">No payments recorded yet.</p>
                                <a href="{{ route('finance.payments.create') }}" class="btn-fin btn-primary-fin">
                                    <i class="fas fa-plus"></i> Record First Payment
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="pagination-container">
                {{ $payments->appends(['year' => $year, 'term' => $term, 'search' => $search])->links() }}
            </div>
        @endif
    </div>
    </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function applyFilters() {
            let year = document.getElementById('filterYear').value;
            let term = document.getElementById('filterTerm').value;
            let search = document.getElementById('searchInput').value;
            let url = "{{ route('finance.payments.index') }}?year=" + year + "&term=" + term;
            if (search) url += "&search=" + encodeURIComponent(search);
            window.location.href = url;
        }

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') applyFilters();
        });

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

        // SweetAlert confirmation for reversing payments
function confirmReverse(button) {
    const form = button.closest('.reverse-form');
    const studentName = form.dataset.student || 'this student';
    const amount = form.dataset.amount || '0';
    const receipt = form.dataset.receipt || 'N/A';
    
    Swal.fire({
        title: 'Reverse Payment?',
        html: `
            <div style="text-align: left; padding: 10px 0;">
                <p><strong>Receipt:</strong> ${receipt}</p>
                <p><strong>Student:</strong> ${studentName}</p>
                <p><strong>Amount:</strong> UGX ${amount}</p>
            </div>
            <hr>
            <p style="color: #dc2626; font-weight: 600;">
                <i class="fas fa-exclamation-triangle"></i> This action cannot be undone!
            </p>
            <p style="color: #475569; font-size: 0.85rem;">
                Reversing will restore the student's outstanding balance.
            </p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, reverse payment!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Reversing payment and updating balance...',
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

    </script>
@endsection