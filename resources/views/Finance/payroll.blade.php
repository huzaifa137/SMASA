{{-- resources/views/Finance/payroll.blade.php --}}
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

        /* Payroll Grid */
        .payroll-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }

        .period-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1.2rem;
            transition: all .2s;
            cursor: pointer;
        }

        .period-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
            border-color: rgba(47, 44, 203, .2);
        }

        .period-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: .8rem;
        }

        .period-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-1);
        }

        .period-date {
            font-size: .7rem;
            color: var(--text-3);
            margin-top: .2rem;
        }

        .period-stats {
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
            height: 4px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
            margin-top: .5rem;
        }

        .progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .3s;
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

            .payroll-grid {
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
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-users"></i> HR & Payroll — Payroll Management</div>
            <h1>Payroll Periods</h1>
            <p>Create, manage, and process teacher payroll periods with automated calculations</p>
        </div>
    </div>
@endsection

@section('content')

    {{-- Stats Summary --}}
    @php
        $totalPeriods = $periods->count();
        $draftPeriods = $periods->where('status', 'draft')->count();
        $processingPeriods = $periods->where('status', 'processing')->count();
        $approvedPeriods = $periods->where('status', 'approved')->count();
        $paidPeriods = $periods->where('status', 'paid')->count();
        $totalDisbursed = $periods->where('status', 'paid')->sum('total_net');
    @endphp

    <div class="stat-grid">
        <div class="stat-card">
            <div class="value">{{ $totalPeriods }}</div>
            <div class="label">Total Periods</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $draftPeriods + $processingPeriods }}</div>
            <div class="label">Active/Processing</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $paidPeriods }}</div>
            <div class="label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="value">UGX {{ number_format($totalDisbursed, 0) }}</div>
            <div class="label">Total Disbursed</div>
        </div>
    </div>

    {{-- Actions --}}
    <div
        style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div>
            <a href="{{ route('finance.salary-structures') }}" class="btn-fin btn-outline-fin">
                <i class="fas fa-money-bill-wave"></i> Manage Salary Structures
            </a>
        </div>
        <a href="{{ route('finance.payroll.create') }}" class="btn-fin btn-primary-fin">
            <i class="fas fa-plus"></i> Create New Payroll Period
        </a>
    </div>

    {{-- Payroll Periods Grid --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-calendar-alt"></i> Payroll Periods</h3>
            <span style="font-size:.75rem;color:var(--text-3);">{{ $periods->count() }} periods</span>
        </div>
        @if($periods->isEmpty())
            <div style="text-align:center;padding:3rem;">
                <i class="fas fa-calendar-times" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
                <p style="margin-bottom:1rem;">No payroll periods created yet.</p>
                <a href="{{ route('finance.payroll.create') }}" class="btn-fin btn-primary-fin">
                    <i class="fas fa-plus"></i> Create First Payroll Period
                </a>
            </div>
        @else
            <div class="payroll-grid">
                @foreach($periods as $period)
                    @php
                        $statusColor = match ($period->status) {
                            'draft' => 'badge-amber',
                            'processing' => 'badge-blue',
                            'approved' => 'badge-teal',
                            'paid' => 'badge-green',
                            default => 'badge-gray'
                        };
                        $statusIcon = match ($period->status) {
                            'draft' => 'fa-pen',
                            'processing' => 'fa-spinner fa-pulse',
                            'approved' => 'fa-check-circle',
                            'paid' => 'fa-check-double',
                            default => 'fa-circle'
                        };
                        $slipCount = $period->slips->count();
                        $processedCount = $period->slips->whereIn('status', ['approved', 'paid'])->count();
                        $progressPercent = $slipCount > 0 ? round(($processedCount / $slipCount) * 100) : 0;
                    @endphp
                    <div class="period-card" onclick="window.location='{{ route('finance.payroll.show', $period->id) }}'">
                        <div class="period-header">
                            <div>
                                <div class="period-name">{{ $period->period_name }}</div>
                                <div class="period-date">
                                    {{ $period->period_start ? $period->period_start->format('d M Y') : '—' }} -
                                    {{ $period->period_end ? $period->period_end->format('d M Y') : '—' }}
                                </div>
                            </div>
                            <div class="badge-fin {{ $statusColor }}">
                                <i class="fas {{ $statusIcon }}"></i> {{ ucfirst($period->status) }}
                            </div>
                        </div>

                        <div class="period-stats">
                            <div>
                                <div style="font-size:.7rem;color:var(--text-3);">Teachers</div>
                                <div style="font-weight:700;">{{ $slipCount }}</div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;color:var(--text-3);">Gross Pay</div>
                                <div class="amount-mono" style="font-weight:700;">UGX {{ number_format($period->total_gross, 0) }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;color:var(--text-3);">Net Pay</div>
                                <div class="amount-mono" style="font-weight:700;color:#2f2ccb;">UGX
                                    {{ number_format($period->total_net, 0) }}</div>
                            </div>
                        </div>

                        <div class="stat-row">
                            <span class="stat-label">Processing Progress</span>
                            <span class="stat-value">{{ $processedCount }}/{{ $slipCount }}</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill"
                                style="width:{{ $progressPercent }}%;background:{{ $period->status == 'paid' ? '#059669' : '#2f2ccb' }};">
                            </div>
                        </div>

                        <div style="margin-top:.8rem;display:flex;gap:.5rem;justify-content:flex-end;">
                            @if($period->status == 'draft')
                                <button class="btn-fin btn-sm btn-warning-fin"
                                    onclick="event.stopPropagation(); confirmApprove({{ $period->id }})">
                                    <i class="fas fa-play"></i> Process
                                </button>
                                <form id="processForm{{ $period->id }}" method="POST"
                                    action="{{ route('finance.payroll.approve', $period->id) }}" style="display:none;">
                                    @csrf
                                </form>
                            @elseif($period->status == 'approved')
                                <button class="btn-fin btn-sm btn-success-fin"
                                    onclick="event.stopPropagation(); confirmMarkPaid({{ $period->id }})">
                                    <i class="fas fa-money-bill-wave"></i> Mark as Paid
                                </button>
                                <form id="paidForm{{ $period->id }}" method="POST"
                                    action="{{ route('finance.payroll.mark-paid', $period->id) }}" style="display:none;">
                                    @csrf
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Quick Info Card --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-info-circle"></i> Payroll Process Guide</h3>
        </div>
        <div style="padding:1.5rem;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                <div style="display:flex;align-items:center;gap:.8rem;">
                    <div
                        style="width:32px;height:32px;background:var(--fin-amber-l);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--fin-amber);">
                        <i class="fas fa-pen"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem;">1. Draft</div>
                        <div style="font-size:.7rem;color:var(--text-3);">Period created, payslips generated</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.8rem;">
                    <div
                        style="width:32px;height:32px;background:rgba(47,44,203,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#2f2ccb;">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem;">2. Approved</div>
                        <div style="font-size:.7rem;color:var(--text-3);">Payroll verified and approved</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.8rem;">
                    <div
                        style="width:32px;height:32px;background:var(--fin-green-l);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--fin-green);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem;">3. Paid</div>
                        <div style="font-size:.7rem;color:var(--text-3);">Salaries disbursed, transactions logged</div>
                    </div>
                </div>
            </div>
            <div
                style="margin-top:1rem;padding:.8rem;background:rgba(47,44,203,.08);border-radius:var(--radius-sm);font-size:.75rem;color:#2f2ccb;border:1px solid rgba(47,44,203,.15);">
                <i class="fas fa-lightbulb"></i>
                <strong>Note:</strong> Payroll periods automatically generate payslips for all teachers with active salary
                structures. NSSF and PAYE are calculated automatically based on Ugandan tax bands.
            </div>
        </div>
    </div>

     </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmApprove(periodId) {
            Swal.fire({
                title: 'Approve Payroll?',
                html: `<span style="color:#475569;">Once approved, you will be able to mark this payroll as paid.<br>This action can be reversed if needed.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, approve payroll',
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
                            document.getElementById('processForm' + periodId).submit();
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