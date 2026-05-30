{{-- resources/views/Finance/payroll-period.blade.php --}}
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
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, .12);
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

        /* Summary Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 1.2rem;
            text-align: center;
            transition: all .2s;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .summary-card .value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-1);
            font-family: 'DM Mono', monospace;
        }

        .summary-card .label {
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

        .btn-outline-fin {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-2);
        }

        .btn-outline-fin:hover {
            border-color: #2f2ccb;
            color: #2f2ccb;
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

        .badge-teal {
            background: rgba(13, 148, 136, .1);
            color: #0d9488;
        }

        .badge-purple {
            background: rgba(124, 58, 237, .1);
            color: #7c3aed;
        }

        .amount-mono {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
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

        /* Modal */
        .payslip-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--surface);
            border-radius: 24px;
            max-width: 600px;
            width: 90%;
            padding: 0;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.25s ease-out;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, #2f2ccb 0%, #2420a8 100%) !important;
            border-radius: 24px 24px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .close-modal {
            cursor: pointer;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.2s;
            line-height: 1;
        }

        .close-modal:hover {
            color: #fff;
            transform: scale(1.1);
        }

        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .modal-actions {
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border);
            background: #fafbff;
            border-radius: 0 0 24px 24px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: .5rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-2);
        }

        .detail-value {
            font-weight: 700;
            color: var(--text-1);
            font-family: 'DM Mono', monospace;
        }

        .total-row {
            background: #f5f6ff;
            padding: .6rem;
            border-radius: 8px;
            margin-top: .5rem;
            font-weight: 800;
            color: #2f2ccb;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #2f2ccb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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
            .summary-grid {
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

            .summary-grid {
                grid-template-columns: 1fr;
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
            <div class="hero-badge"><i class="fas fa-file-invoice-dollar"></i> Payroll Period Details</div>
            <h1>{{ $period->period_name }}</h1>
            <p>{{ $period->period_start ? $period->period_start->format('d F Y') : '—' }} —
                {{ $period->period_end ? $period->period_end->format('d F Y') : '—' }}</p>
        </div>
    </div>
@endsection

@section('content')

    {{-- Summary Stats --}}
    <div class="summary-grid">
        <div class="summary-card">
            <div class="value">{{ $period->slips->count() }}</div>
            <div class="label">Teachers</div>
        </div>
        <div class="summary-card">
            <div class="value">UGX {{ number_format($period->total_gross, 0) }}</div>
            <div class="label">Gross Payroll</div>
        </div>
        <div class="summary-card">
            <div class="value">UGX {{ number_format($period->total_deductions, 0) }}</div>
            <div class="label">Total Deductions</div>
        </div>
        <div class="summary-card">
            <div class="value" style="color:#2f2ccb;">UGX {{ number_format($period->total_net, 0) }}</div>
            <div class="label">Net Payroll</div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="margin-bottom:1.5rem;display:flex;gap:.75rem;justify-content:flex-end;flex-wrap:wrap;">
        @if($period->status == 'draft')
            <button class="btn-fin btn-success-fin" onclick="confirmApprove({{ $period->id }})">
                <i class="fas fa-check-circle"></i> Approve Payroll
            </button>
            <form id="approveForm{{ $period->id }}" method="POST" action="{{ route('finance.payroll.approve', $period->id) }}"
                style="display:none;">
                @csrf
            </form>
        @elseif($period->status == 'approved')
            <button class="btn-fin btn-primary-fin" onclick="confirmMarkPaid({{ $period->id }})">
                <i class="fas fa-money-bill-wave"></i> Mark as Paid
            </button>
            <form id="paidForm{{ $period->id }}" method="POST" action="{{ route('finance.payroll.mark-paid', $period->id) }}"
                style="display:none;">
                @csrf
            </form>
        @endif
        <a href="{{ route('finance.payroll.index') }}" class="btn-fin btn-outline-fin">
            <i class="fas fa-arrow-left"></i> Back to Periods
        </a>
    </div>

    {{-- Payslips Table --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-list"></i> Teacher Payslips</h3>
            <span style="font-size:.75rem;color:var(--text-3);">{{ $period->slips->count() }} payslips</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payslip #</th>
                        <th>Teacher Name</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Gross Pay</th>
                        <th>PAYE Tax</th>
                        <th>NSSF (Emp)</th>
                        <th>Other Ded.</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($period->slips as $slip)
                        @php
                            $statusColor = match ($slip->status) {
                                'paid' => 'badge-green',
                                'approved' => 'badge-teal',
                                'draft' => 'badge-amber',
                                default => 'badge-blue'
                            };
                            $statusIcon = match ($slip->status) {
                                'paid' => 'fa-check-double',
                                'approved' => 'fa-check-circle',
                                'draft' => 'fa-pen',
                                default => 'fa-circle'
                            };
                        @endphp
                        <tr>
                            <td><span class="amount-mono" style="font-size:.8rem;">{{ $slip->payslip_number }}</span></td>
                            <td>
                                <div style="font-weight:600;">{{ $slip->teacher->firstname ?? 'N/A' }}
                                    {{ $slip->teacher->lastname ?? '' }}</div>
                                <div style="font-size:.7rem;color:var(--text-3);">{{ $slip->teacher->employee_number ?? '—' }}
                                </div>
                            </td>
                            <td class="amount-mono">UGX {{ number_format($slip->basic_salary, 0) }}</td>
                            <td class="amount-mono">UGX
                                {{ number_format($slip->housing_allowance + $slip->transport_allowance + $slip->other_allowances, 0) }}
                            </td>
                            <td class="amount-mono" style="font-weight:700;">UGX {{ number_format($slip->gross_pay, 0) }}</td>
                            <td class="amount-mono" style="color:#dc2626;">UGX {{ number_format($slip->paye_tax, 0) }}</td>
                            <td class="amount-mono">UGX {{ number_format($slip->nssf_employee, 0) }}</td>
                            <td class="amount-mono">UGX {{ number_format($slip->loan_deduction + $slip->other_deductions, 0) }}
                            </td>
                            <td class="amount-mono" style="color:#2f2ccb;font-weight:700;">UGX
                                {{ number_format($slip->net_pay, 0) }}</td>
                            <td><span class="badge-fin {{ $statusColor }}"><i class="fas {{ $statusIcon }}"></i>
                                    {{ ucfirst($slip->status) }}</span></td>
                            <td>
                                <button class="btn-fin btn-sm btn-outline-fin" onclick="viewPayslip({{ $slip->id }})"
                                    style="padding:.3rem .7rem;">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="scroll-hint">
            <i class="fas fa-arrows-alt-h"></i>
            <span>Swipe or scroll horizontally to see more columns</span>
            <i class="fas fa-arrows-alt-h"></i>
        </div>
    </div>

    {{-- Payslip View Modal --}}
    <div id="payslipModal" class="payslip-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-file-invoice"></i> Payslip Details</h3>
                <span class="close-modal" onclick="closePayslipModal()">&times;</span>
            </div>
            <div class="modal-body" id="payslipContent">
                <div style="text-align:center;padding:2rem;">
                    <div class="loading-spinner" style="margin:0 auto;"></div>
                    <p style="margin-top:1rem;">Loading payslip details...</p>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-fin btn-outline-fin" onclick="closePayslipModal()">Close</button>
                <button class="btn-fin btn-primary-fin" onclick="printPayslip()" id="printBtn" style="display:none;">
                    <i class="fas fa-print"></i> Print Payslip
                </button>
            </div>
        </div>
    </div>
     </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmApprove(periodId) {
            Swal.fire({
                title: 'Approve Payroll?',
                html: `<span style="color:#475569;">This will mark all payslips as approved.<br>You will be able to mark the payroll as paid after approval.</span>`,
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
                        text: 'Approving payroll...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('approveForm' + periodId).submit();
                        }
                    });
                }
            });
        }

        function confirmMarkPaid(periodId) {
            Swal.fire({
                title: 'Mark as Paid?',
                html: `<span style="color:#475569;">This will record all salary payments as expenses.<br>This action cannot be undone.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, mark as paid',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Recording payments...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            document.getElementById('paidForm' + periodId).submit();
                        }
                    });
                }
            });
        }

        // View payslip modal
        async function viewPayslip(slipId) {
            const modal = document.getElementById('payslipModal');
            const content = document.getElementById('payslipContent');
            const printBtn = document.getElementById('printBtn');

            modal.style.display = 'flex';
            content.innerHTML = '<div style="text-align:center;padding:2rem;"><div class="loading-spinner" style="margin:0 auto;"></div><p style="margin-top:1rem;">Loading payslip details...</p></div>';
            printBtn.style.display = 'none';

            try {
                const response = await fetch(`/finance/payroll/payslip/${slipId}`);
                const data = await response.json();

                if (response.ok) {
                    content.innerHTML = `
                    <div style="margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border);">
                        <div class="detail-row">
                            <span class="detail-label">Payslip Number:</span>
                            <span class="detail-value">${data.payslip_number}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Teacher:</span>
                            <span class="detail-value">${data.teacher_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Period:</span>
                            <span class="detail-value">${data.period_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">${data.status}</span>
                        </div>
                    </div>

                    <div style="margin:1rem 0;">
                        <h4 style="font-size:.9rem;margin-bottom:.5rem;color:#2f2ccb;">Earnings</h4>
                        <div class="detail-row">
                            <span class="detail-label">Basic Salary:</span>
                            <span class="detail-value">UGX ${data.basic_salary.toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Housing Allowance:</span>
                            <span class="detail-value">UGX ${data.housing_allowance.toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Transport Allowance:</span>
                            <span class="detail-value">UGX ${data.transport_allowance.toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Other Allowances:</span>
                            <span class="detail-value">UGX ${data.other_allowances.toLocaleString()}</span>
                        </div>
                        <div class="detail-row total-row">
                            <span class="detail-label">Gross Pay:</span>
                            <span class="detail-value">UGX ${data.gross_pay.toLocaleString()}</span>
                        </div>
                    </div>

                    <div style="margin:1rem 0;">
                        <h4 style="font-size:.9rem;margin-bottom:.5rem;color:#dc2626;">Deductions</h4>
                        <div class="detail-row">
                            <span class="detail-label">PAYE Tax:</span>
                            <span class="detail-value" style="color:#dc2626;">UGX ${data.paye_tax.toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">NSSF (Employee):</span>
                            <span class="detail-value">UGX ${data.nssf_employee.toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Loan Deduction:</span>
                            <span class="detail-value">UGX ${data.loan_deduction.toLocaleString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Other Deductions:</span>
                            <span class="detail-value">UGX ${data.other_deductions.toLocaleString()}</span>
                        </div>
                        <div class="detail-row total-row">
                            <span class="detail-label">Total Deductions:</span>
                            <span class="detail-value">UGX ${data.total_deductions.toLocaleString()}</span>
                        </div>
                    </div>

                    <div class="detail-row total-row" style="background:#f5f6ff;margin-top:1rem;">
                        <span class="detail-label" style="font-size:1rem;">NET PAY</span>
                        <span class="detail-value" style="font-size:1.2rem;color:#2f2ccb;">UGX ${data.net_pay.toLocaleString()}</span>
                    </div>

                    ${data.notes ? `<div style="margin-top:1rem;padding:.5rem;background:#f8fafc;border-radius:8px;font-size:.75rem;">
                        <strong>Notes:</strong> ${data.notes}
                    </div>` : ''}
                `;
                    printBtn.style.display = 'inline-flex';
                    window.currentPayslipId = slipId;
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:2rem;color:#dc2626;">
                    <i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i>
                    <p>Error loading payslip details</p>
                </div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:2rem;color:#dc2626;">
                <i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i>
                <p>Failed to load payslip details</p>
            </div>`;
            }
        }

        function closePayslipModal() {
            document.getElementById('payslipModal').style.display = 'none';
        }

        function printPayslip() {
            const modalContent = document.querySelector('#payslipModal .modal-body').cloneNode(true);
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Payslip</title>
                <style>
                    body{font-family:'DM Sans',sans-serif;padding:20px;max-width:600px;margin:0 auto;}
                    .detail-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0;}
                    .total-row{background:#f5f6ff;padding:10px;border-radius:8px;margin-top:8px;font-weight:700;}
                    h4{color:#2f2ccb;margin-top:16px;}
                    @media print{
                        body{margin:0;padding:15px;}
                    }
                </style>
            </head>
            <body>
                ${modalContent.innerHTML}
            </body>
            </html>
        `);
            printWindow.document.close();
            printWindow.print();
        }

        // Close modal on outside click
        window.onclick = function (event) {
            const modal = document.getElementById('payslipModal');
            if (event.target == modal) {
                closePayslipModal();
            }
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