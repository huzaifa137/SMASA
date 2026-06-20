@extends('layouts-side-bar.master')
<?php use App\Helpers\PermissionHelper; ?>

@section('css')

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --navy: #0A2463;
            --electric: #3E92CC;
            --sky: #A7D3FF;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.10);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--light-gray);
        }

        /* HERO SECTION */
        .fin-hero {
            background: linear-gradient(135deg, var(--navy) 0%, #1a2744 55%, var(--navy) 100%);
            border-radius: 0 0 var(--radius) var(--radius);
            padding: 2.5rem 2rem 4rem;
            margin-bottom: -2rem;
            margin-top: 1.5rem;
            position: relative;
            overflow: hidden;
            color: var(--white);
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(62, 146, 204, 0.20) 0%, transparent 70%);
        }

        .fin-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 5%;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(10, 36, 99, 0.15) 0%, transparent 70%);
        }

        .fin-hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .fin-hero p {
            color: rgba(255, 255, 255, 0.7);
            margin: 0.25rem 0 0;
            font-size: 0.92rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(62, 146, 204, 0.2);
            border: 1px solid rgba(62, 146, 204, 0.35);
            color: var(--sky);
            padding: 0.28rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.7rem;
        }

        .hero-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.82rem;
        }

        .hero-stat strong {
            color: var(--white);
            font-weight: 600;
        }

        /* KPI GRID */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .kpi {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.4rem 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            cursor: default;
        }

        .kpi:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .kpi-accent {
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            border-radius: 0 var(--radius) var(--radius) 0;
            background: var(--electric);
        }

        .kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.9rem;
            background: rgba(62, 146, 204, 0.1);
            color: var(--electric);
        }

        .kpi-val {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            font-family: 'JetBrains Mono', monospace;
        }

        .kpi-val small {
            font-size: 0.85rem;
            font-weight: 400;
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
        }

        .kpi-lbl {
            font-size: 0.78rem;
            color: var(--text-secondary);
            margin-top: 0.28rem;
            font-weight: 500;
        }

        .kpi-foot {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.74rem;
            margin-top: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .trend-up {
            color: var(--electric);
        }

        .trend-dn {
            color: #dc2626;
        }

        .trend-nu {
            color: var(--text-muted);
        }

        /* SECTION CARDS */
        .fc {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .fc-hd {
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fc-hd h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Poppins', sans-serif;
        }

        .fc-bd {
            padding: 1.4rem;
        }

        .sec-link {
            font-size: 0.78rem;
            color: var(--electric);
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            transition: var(--transition);
        }

        .sec-link:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        /* PROGRESS BARS */
        .bar-track {
            height: 7px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            margin: 0.4rem 0 0.15rem;
        }

        .bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* TRANSACTION ROW */
        .txn {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.12s;
        }

        .txn:last-child {
            border-bottom: none;
        }

        .txn:hover {
            background: rgba(62, 146, 204, 0.03);
        }

        .txn-ico {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
            flex-shrink: 0;
            background: rgba(62, 146, 204, 0.1);
            color: var(--electric);
        }

        .txn-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .txn-meta {
            font-size: 0.74rem;
            color: var(--text-muted);
            margin-top: 0.05rem;
        }

        .txn-amt {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.88rem;
            font-weight: 600;
            white-space: nowrap;
            color: var(--electric);
        }

        /* QUICK ACTIONS */
        .qa-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.7rem;
        }

        .qa {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1.1rem 0.7rem;
            background: var(--light-gray);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            text-align: center;
        }

        .qa:hover {
            background: var(--white);
            border-color: var(--electric);
            box-shadow: 0 0 0 3px rgba(62, 146, 204, 0.1), var(--shadow);
            transform: translateY(-2px);
        }

        .qa-ico {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            background: rgba(62, 146, 204, 0.1);
            color: var(--electric);
        }

        .qa span {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-secondary);
            line-height: 1.25;
        }

        /* ALERT PILLS */
        .alert-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .alert-pill:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        /* NET BALANCE BANNER */
        .fin-balance {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem 1.4rem;
            background: linear-gradient(90deg, rgba(62, 146, 204, 0.06), transparent);
            border-left: 3px solid var(--electric);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            margin-bottom: 1.2rem;
            transition: var(--transition);
        }

        .fin-balance.negative {
            background: linear-gradient(90deg, rgba(220, 38, 38, 0.06), transparent);
            border-left-color: #dc2626;
        }

        /* NAV TABS */
        .fin-tabs {
            display: flex;
            gap: 0.4rem;
            padding: 0.6rem 0.6rem 0;
        }

        .fin-tab {
            padding: 0.45rem 1rem;
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            border: none;
            background: transparent;
            transition: var(--transition);
        }

        .fin-tab.active {
            background: var(--white);
            color: var(--electric);
            border: 1px solid var(--border);
            border-bottom-color: var(--white);
            margin-bottom: -1px;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 560px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .qa-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.5rem;
            }
        }

        .kpi {
    background: var(--white);
    border-radius: var(--radius);
    padding: 1.4rem 1.5rem;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
    transition: var(--transition);
    cursor: pointer;  /* Add this line */
}

/* Finance Dashboard - Stack layout on mobile */

/* Hero section */
.fin-hero {
    padding: 2.5rem 2rem 4rem;
    margin-top: 1.5rem;
}

.fin-hero h1 {
    font-size: 1.8rem;
}

.hero-badge {
    font-size: 0.75rem;
    padding: 0.28rem 0.75rem;
}

.hero-stat {
    font-size: 0.82rem;
}

/* KPI Grid */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.kpi {
    padding: 1.4rem 1.5rem;
    cursor: pointer;
}

.kpi-icon {
    width: 46px;
    height: 46px;
    font-size: 1.2rem;
    margin-bottom: 0.9rem;
}

.kpi-val {
    font-size: 1.6rem;
}

.kpi-val small {
    font-size: 0.85rem;
}

.kpi-lbl {
    font-size: 0.78rem;
}

.kpi-foot {
    font-size: 0.74rem;
}

/* Quick Actions */
.qa-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.7rem;
}

.qa {
    padding: 1.1rem 0.7rem;
}

.qa-ico {
    width: 42px;
    height: 42px;
    font-size: 1rem;
}

.qa span {
    font-size: 0.72rem;
}

/* Net Balance Banner */
.fin-balance {
    padding: 1rem 1.4rem;
    gap: 1.5rem;
    margin-bottom: 1.2rem;
}

.fin-balance div:first-child div:first-child {
    font-size: 0.75rem;
}

.fin-balance div:first-child div:last-child {
    font-size: 1.5rem;
}

/* Charts */
.fc-hd {
    padding: 1.1rem 1.4rem;
}

.fc-hd h3 {
    font-size: 0.95rem;
}

.fc-bd {
    padding: 1.4rem;
}

/* Row layouts */
.row[style*="display:flex;"] {
    display: flex;
    flex-wrap: wrap;
}

.row[style*="display:flex;"] .col-lg-8,
.row[style*="display:flex;"] .col-lg-7,
.row[style*="display:flex;"] .col-lg-5,
.row[style*="display:flex;"] .col-lg-4 {
    flex: 1 1 100%;
    max-width: 100%;
}

/* Tablet - reduce to 2 columns */
@media (max-width: 992px) {
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .qa-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .fin-hero {
        padding: 2rem 1.5rem 3rem;
    }
    
    .fin-hero h1 {
        font-size: 1.5rem;
    }
    
    .fin-hero [style*="display:flex;gap:2rem;"] {
        gap: 1.5rem;
    }
}

/* Tablet - stack columns */
@media (max-width: 768px) {
    .fin-hero {
        padding: 1.5rem 1.25rem 2.5rem;
        border-radius: 0 0 var(--radius) var(--radius);
    }
    
    .fin-hero h1 {
        font-size: 1.3rem;
    }
    
    .fin-hero p {
        font-size: 0.85rem;
    }
    
    .fin-hero [style*="display:flex;gap:2rem;"] {
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0.8rem;
    }
    
    .fin-hero [style*="position:relative;z-index:1;margin-top:1.2rem;"] {
        margin-top: 0.8rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .hero-stat {
        font-size: 0.78rem;
    }
    
    /* KPI cards */
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .kpi {
        padding: 1rem 1.2rem;
    }
    
    .kpi-icon {
        width: 38px;
        height: 38px;
        font-size: 1rem;
        margin-bottom: 0.6rem;
    }
    
    .kpi-val {
        font-size: 1.3rem;
    }
    
    .kpi-val small {
        font-size: 0.75rem;
    }
    
    .kpi-lbl {
        font-size: 0.72rem;
    }
    
    .kpi-foot {
        font-size: 0.68rem;
    }
    
    /* Quick Actions */
    .qa-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    
    .qa {
        padding: 0.8rem 0.4rem;
    }
    
    .qa-ico {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
    
    .qa span {
        font-size: 0.65rem;
    }
    
    /* Net Balance */
    .fin-balance {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
        padding: 0.8rem 1rem;
        margin-bottom: 1rem;
    }
    
    .fin-balance > div:last-child {
        margin-left: 0 !important;
        text-align: left !important;
    }
    
    .fin-balance div:first-child div:last-child {
        font-size: 1.25rem;
    }
    
    /* Charts container */
    .row[style*="display:flex;"] {
        flex-direction: column;
    }
    
    .row[style*="display:flex;"] .col-lg-8,
    .row[style*="display:flex;"] .col-lg-7,
    .row[style*="display:flex;"] .col-lg-5,
    .row[style*="display:flex;"] .col-lg-4 {
        padding: 0 10px 15px !important;
        flex: 1 1 100%;
        max-width: 100%;
    }
    
    /* Card headers on mobile */
    .fc-hd {
        padding: 0.8rem 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .fc-hd h3 {
        font-size: 0.85rem;
    }
    
    .fc-bd {
        padding: 0.8rem 1rem;
    }
    
    /* Charts */
    #monthlyChart {
        height: 160px !important;
    }
    
    #feeStatusChart {
        width: 110px !important;
        height: 110px !important;
    }
}

/* Mobile */
@media (max-width: 576px) {
    .fin-hero {
        padding: 1rem 1rem 2rem;
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
    }
    
    .fin-hero h1 {
        font-size: 1.1rem;
    }
    
    .fin-hero p {
        font-size: 0.78rem;
        margin-top: 0.2rem;
    }
    
    .hero-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.6rem;
        margin-bottom: 0.5rem;
    }
    
    .hero-stat {
        font-size: 0.72rem;
    }
    
    /* KPI - single column */
    .kpi-grid {
        grid-template-columns: 1fr;
        gap: 0.6rem;
    }
    
    .kpi {
        padding: 0.8rem 1rem;
    }
    
    .kpi-icon {
        width: 34px;
        height: 34px;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }
    
    .kpi-val {
        font-size: 1.1rem;
    }
    
    .kpi-val small {
        font-size: 0.7rem;
    }
    
    .kpi-lbl {
        font-size: 0.68rem;
    }
    
    .kpi-foot {
        font-size: 0.65rem;
    }
    
    /* Quick Actions - 2 columns */
    .qa-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.4rem;
    }
    
    .qa {
        padding: 0.6rem 0.3rem;
        border-radius: var(--radius-sm);
    }
    
    .qa-ico {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .qa span {
        font-size: 0.6rem;
    }
    
    /* Net Balance */
    .fin-balance div:first-child div:last-child {
        font-size: 1.1rem;
    }
    
    /* Charts */
    #monthlyChart {
        height: 140px !important;
    }
    
    #feeStatusChart {
        width: 90px !important;
        height: 90px !important;
    }
    
    /* Transactions */
    .txn {
        padding: 0.5rem 0;
        gap: 0.6rem;
    }
    
    .txn-ico {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .txn-name {
        font-size: 0.78rem;
    }
    
    .txn-meta {
        font-size: 0.65rem;
    }
    
    .txn-amt {
        font-size: 0.78rem;
    }
    
    /* Expense breakdown */
    [style*="padding:0.65rem 0;"] {
        padding: 0.5rem 0 !important;
    }
    
    [style*="font-size:0.82rem;"] {
        font-size: 0.75rem !important;
    }
    
    [style*="font-size:0.78rem;"] {
        font-size: 0.7rem !important;
    }
    
    /* Bar tracks */
    .bar-track {
        height: 5px;
    }
}

/* Very small screens */
@media (max-width: 400px) {
    .fin-hero {
        padding: 0.8rem 0.8rem 1.5rem;
    }
    
    .fin-hero h1 {
        font-size: 1rem;
    }
    
    .kpi-val {
        font-size: 1rem;
    }
    
    .qa-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .qa {
        padding: 0.5rem 0.2rem;
    }
    
    .qa-ico {
        width: 28px;
        height: 28px;
        font-size: 0.65rem;
    }
    
    .qa span {
        font-size: 0.55rem;
    }
    
    #monthlyChart {
        height: 120px !important;
    }
    
    #feeStatusChart {
        width: 80px !important;
        height: 80px !important;
    }
    
    .fc-hd h3 {
        font-size: 0.78rem;
    }
    
    .txn-amt {
        font-size: 0.7rem;
    }
}

/* Fix scroll on mobile */
@media (max-width: 768px) {
    [style*="overflow:"] {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
}

/* Improve touch targets on mobile */
@media (max-width: 576px) {
    .kpi,
    .qa,
    .alert-pill,
    .btn-lib {
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }
    
    .kpi:active,
    .qa:active {
        transform: scale(0.97);
    }
    
    /* Make sure all clickable elements have adequate touch targets */
    .sec-link,
    .alert-pill {
        padding: 0.4rem 0.6rem;
        min-height: 36px;
    }
}

/* Smooth transitions */
.kpi,
.qa,
.fin-balance,
.fc,
.btn-lib {
    transition: all 0.2s ease;
}

/* Fix chart container on mobile */
@media (max-width: 576px) {
    .fc-bd canvas {
        max-width: 100% !important;
        height: auto !important;
    }
    
    .fc-bd div[style*="position:relative;width:130px;height:130px;"] {
        width: 100px !important;
        height: 100px !important;
        margin: 0 auto 0.2rem !important;
    }
    
    .fc-bd div[style*="position:relative;width:130px;height:130px;"] div div:first-child {
        font-size: 1.1rem !important;
    }
    
    .fc-bd div[style*="position:relative;width:130px;height:130px;"] div div:last-child {
        font-size: 0.55rem !important;
    }
}
    </style>
@endsection

@section('page-header')
    <div class="fin-hero">
        <div style="position: relative; z-index: 1;">
            <div class="hero-badge"><i class="fas fa-chart-line"></i> Finance Module</div>
            <h1><i class="fas fa-university" style="opacity: 0.8;"></i> Financial Overview</h1>
            <p class="mt-2">Academic Year {{ $year }} &nbsp;·&nbsp; All figures in UGX</p>
            <div style="display: flex; gap: 2rem; margin-top: 1.2rem; flex-wrap: wrap;">
                <div class="hero-stat"><i class="fas fa-circle" style="font-size: 0.5rem; color: var(--sky);"></i>
                    <strong>UGX {{ number_format($totalIncome, 0) }}</strong> collected</div>
                <div class="hero-stat"><i class="fas fa-circle" style="font-size: 0.5rem; color: #f87171;"></i> <strong>UGX
                        {{ number_format($outstanding, 0) }}</strong> outstanding</div>
                <div class="hero-stat"><i class="fas fa-circle" style="font-size: 0.5rem; color: #fbbf24;"></i> <strong>UGX
                        {{ number_format($totalExpenses + $totalPayroll, 0) }}</strong> spent</div>
            </div>
        </div>
        @if($pendingExpenses > 0 || $pendingPayroll > 0)
            <div style="position: relative; z-index: 1; margin-top: 1.2rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                @if($pendingExpenses > 0 && PermissionHelper::canFeature('manage_expenses'))
                    <a href="{{ route('finance.expenses.index') }}" class="alert-pill"
                        style="background: rgba(217, 119, 6, 0.25); color: #fcd34d; border: 1px solid rgba(217, 119, 6, 0.4);">
                        <i class="fas fa-exclamation-triangle"></i> {{ $pendingExpenses }} expense(s) pending
                    </a>
                @endif
                @if($pendingPayroll > 0 && PermissionHelper::canFeature('manage_payroll'))
                    <a href="{{ route('finance.payroll.index') }}" class="alert-pill"
                        style="background: rgba(62, 146, 204, 0.25); color: var(--sky); border: 1px solid rgba(62, 146, 204, 0.4);">
                        <i class="fas fa-clock"></i> {{ $pendingPayroll }} payroll(s) awaiting approval
                    </a>
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
        <a href="{{ route('finance.payments.index') }}" style="text-decoration: none;">
            <div class="kpi">
                <div class="kpi-accent" style="background: var(--electric);"></div>
                <div class="kpi-icon"><i class="fas fa-arrow-down"></i></div>
                <div class="kpi-val"><small>UGX </small>{{ number_format($totalIncome, 0) }}</div>
                <div class="kpi-lbl">Total Fee Collections</div>
                <div class="kpi-foot trend-up"><i class="fas fa-check-circle"></i> {{ $collectionRate }}% collection rate</div>
            </div>
        </a>
        <a href="{{ route('finance.outstanding-fees') }}" style="text-decoration: none;">
            <div class="kpi">
                <div class="kpi-accent" style="background: #dc2626;"></div>
                <div class="kpi-icon" style="background: rgba(220, 38, 38, 0.1); color: #dc2626;"><i class="fas fa-exclamation-circle"></i></div>
                <div class="kpi-val"><small>UGX </small>{{ number_format(max(0, $outstanding), 0) }}</div>
                <div class="kpi-lbl">Outstanding Fees</div>
                <div class="kpi-foot trend-dn"><i class="fas fa-user-times"></i> {{ $feeStats->unpaid ?? 0 }} unpaid · {{ $feeStats->partial ?? 0 }} partial</div>
            </div>
        </a>
        @if(PermissionHelper::canFeature('manage_expenses'))
            <a href="{{ route('finance.expenses.index') }}" style="text-decoration: none;">
                <div class="kpi">
                    <div class="kpi-accent" style="background: #d97706;"></div>
                    <div class="kpi-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;"><i class="fas fa-arrow-up"></i></div>
                    <div class="kpi-val"><small>UGX </small>{{ number_format($totalExpenses, 0) }}</div>
                    <div class="kpi-lbl">Total Expenses</div>
                    <div class="kpi-foot trend-nu"><i class="fas fa-tag"></i> Operational costs</div>
                </div>
            </a>
        @endif
        @if(PermissionHelper::canFeature('manage_payroll'))
            <a href="{{ route('finance.payroll.index') }}" style="text-decoration: none;">
                <div class="kpi">
                    <div class="kpi-accent" style="background: var(--navy);"></div>
                    <div class="kpi-icon" style="background: rgba(10, 36, 99, 0.1); color: var(--navy);"><i class="fas fa-users"></i></div>
                    <div class="kpi-val"><small>UGX </small>{{ number_format($totalPayroll, 0) }}</div>
                    <div class="kpi-lbl">Payroll Disbursed</div>
                    <div class="kpi-foot trend-nu"><i class="fas fa-money-check-alt"></i> Net salaries paid</div>
                </div>
            </a>
        @endif
    </div>

    {{-- NET BALANCE BANNER --}}
    <div class="fin-balance {{ $netBalance < 0 ? 'negative' : '' }}">
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                Net Balance (Income − Expenses − Payroll)
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: {{ $netBalance >= 0 ? 'var(--electric)' : '#dc2626' }}; font-family: 'JetBrains Mono', monospace; margin-top: 0.2rem;">
                {{ $netBalance >= 0 ? '+' : '' }}UGX {{ number_format($netBalance, 0) }}
            </div>
        </div>
        <div style="margin-left: auto; text-align: right;">
            <div style="font-size: 0.75rem; color: var(--text-muted);">Total Billed</div>
            <div style="font-size: 1rem; font-weight: 700; color: var(--text-primary); font-family: 'JetBrains Mono', monospace;">
                UGX {{ number_format($totalBilled, 0) }}
            </div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="row" style="margin: 0 -10px;">
        <div class="col-lg-12" style="padding: 0 10px 20px;">
            <div class="fc">
                <div class="fc-hd">
                    <h3><i class="fas fa-bolt" style="color: #d97706;"></i> Quick Actions</h3>
                </div>
                <div class="fc-bd">
                    <div class="qa-grid" style="grid-template-columns: repeat(6, 1fr);">
                        @if(PermissionHelper::canFeature('record_payment'))
                            <a href="{{ route('finance.payments.create') }}" class="qa">
                                <div class="qa-ico"><i class="fas fa-plus"></i></div>
                                <span>Record Payment</span>
                            </a>
                        @endif
                        @if(PermissionHelper::canFeature('manage_expenses'))
                            <a href="{{ route('finance.expenses.create') }}" class="qa">
                                <div class="qa-ico" style="background: rgba(220, 38, 38, 0.1); color: #dc2626;"><i class="fas fa-file-invoice-dollar"></i></div>
                                <span>Add Expense</span>
                            </a>
                        @endif
                        @if(PermissionHelper::canFeature('manage_payroll'))
                            <a href="{{ route('finance.payroll.create') }}" class="qa">
                                <div class="qa-ico" style="background: rgba(10, 36, 99, 0.1); color: var(--navy);"><i class="fas fa-users"></i></div>
                                <span>Run Payroll</span>
                            </a>
                        @endif
                        @if(PermissionHelper::canFeature('manage_fees'))
                            <a href="{{ route('finance.fee-structures.index') }}" class="qa">
                                <div class="qa-ico" style="background: rgba(62, 146, 204, 0.1); color: var(--electric);"><i class="fas fa-layer-group"></i></div>
                                <span>Fee Structures</span>
                            </a>
                        @endif
                        <a href="{{ route('finance.outstanding-fees') }}" class="qa">
                            <div class="qa-ico" style="background: rgba(217, 119, 6, 0.1); color: #d97706;"><i class="fas fa-exclamation-triangle"></i></div>
                            <span>Defaulters</span>
                        </a>
                        @if(PermissionHelper::canFeature('financial_reports'))
                            <a href="{{ route('finance.reports') }}" class="qa">
                                <div class="qa-ico" style="background: rgba(8, 145, 178, 0.1); color: #0891b2;"><i class="fas fa-chart-bar"></i></div>
                                <span>Reports</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW --}}
    <div class="row" style="margin: 0 -10px; display: flex;">
        <div class="col-lg-8" style="padding: 0 10px 20px; display: flex; flex-direction: column;">
            <div class="fc" style="flex: 1;">
                <div class="fc-hd">
                    <h3><i class="fas fa-chart-area" style="color: var(--electric);"></i> Monthly Fee Collections</h3>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Last 6 months</span>
                </div>
                <div class="fc-bd"><canvas id="monthlyChart" height="95"></canvas></div>
            </div>
        </div>

        <div class="col-lg-4" style="padding: 0 10px 20px; display: flex; flex-direction: column;">
            <div class="fc" style="flex: 1;">
                <div class="fc-hd">
                    <h3><i class="fas fa-chart-pie" style="color: var(--navy);"></i> Fee Collection Status</h3>
                </div>
                <div class="fc-bd" style="padding-top: 0.8rem;">
                    <div style="position: relative; width: 130px; height: 130px; margin: 0 auto 0.2rem;">
                        <canvas id="feeStatusChart"></canvas>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none;">
                            <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary); font-family: 'JetBrains Mono', monospace;">{{ $collectionRate }}%</div>
                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 500;">collected</div>
                        </div>
                    </div>
                    @php
                        $tot = max(1, $feeStats->total_students ?? 0);
                        $pp  = round(($feeStats->fully_paid ?? 0) / $tot * 100);
                        $pp2 = round(($feeStats->partial    ?? 0) / $tot * 100);
                        $pp3 = round(($feeStats->unpaid     ?? 0) / $tot * 100);
                    @endphp
                    <div style="display: flex; flex-direction: column; gap: 0.55rem; margin-top: 1rem;">
                        @foreach([
                            ['Fully Paid', $feeStats->fully_paid ?? 0, $pp,  'var(--electric)'],
                            ['Partial',    $feeStats->partial    ?? 0, $pp2, '#d97706'],
                            ['Unpaid',     $feeStats->unpaid     ?? 0, $pp3, '#dc2626'],
                        ] as [$lbl, $n, $pct, $col])
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; margin-bottom: 3px;">
                                    <span style="color: {{ $col }}; font-weight: 600;"><i class="fas fa-circle" style="font-size: 0.45rem; vertical-align: middle;"></i> {{ $lbl }}</span>
                                    <span style="color: var(--text-secondary);">{{ $n }} ({{ $pct }}%)</span>
                                </div>
                                <div class="bar-track"><div class="bar-fill" style="width: {{ $pct }}%; background: {{ $col }};"></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PAYMENTS & EXPENSES ROW --}}
    <div class="row" style="margin: 0 -10px; display: flex;">
        <div class="col-lg-7" style="padding: 0 10px 20px; display: flex; flex-direction: column;">
            <div class="fc" style="flex: 1;">
                <div class="fc-hd">
                    <h3><i class="fas fa-receipt" style="color: var(--electric);"></i> Recent Payments</h3>
                    <a href="{{ route('finance.payments.index') }}" class="sec-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="fc-bd" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                    @forelse($recentPayments as $pmt)
                        <div class="txn">
                            <div class="txn-ico"><i class="fas fa-{{ $pmt->methodIcon() }}"></i></div>
                            <div style="flex: 1; min-width: 0;">
                                <div class="txn-name">{{ optional($pmt->student)->firstname }} {{ optional($pmt->student)->lastname }}</div>
                                <div class="txn-meta">{{ $pmt->receipt_number }} &bull; {{ $pmt->payment_date?->format('M d, Y') }} &bull; {{ $pmt->methodLabel() }}</div>
                            </div>
                            <div class="txn-amt">+{{ number_format($pmt->amount_paid, 0) }}</div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                            <i class="fas fa-receipt" style="font-size: 2rem; opacity: 0.25; display: block; margin-bottom: 0.5rem;"></i>
                            No payments recorded yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5" style="padding: 0 10px 20px; display: flex; flex-direction: column;">
            <div class="fc" style="flex: 1;">
                <div class="fc-hd">
                    <h3><i class="fas fa-layer-group" style="color: #d97706;"></i> Expense Breakdown</h3>
                    @if(PermissionHelper::canFeature('manage_expenses'))
                        <a href="{{ route('finance.expenses.index') }}" class="sec-link">Details <i class="fas fa-arrow-right"></i></a>
                    @endif
                </div>
                <div class="fc-bd" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                    @forelse($expenseBreakdown as $eb)
                        @php $pct2 = $totalExpenses > 0 ? round($eb->total / $totalExpenses * 100) : 0; @endphp
                        <div style="padding: 0.65rem 0; border-bottom: 1px solid #f8fafc;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px;">
                                <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-primary);">{{ $eb->cat_name }}</span>
                                <span style="font-size: 0.78rem; font-family: 'JetBrains Mono', monospace; color: var(--text-secondary);">{{ number_format($eb->total, 0) }}</span>
                            </div>
                            <div class="bar-track"><div class="bar-fill" style="width: {{ $pct2 }}%; background: {{ $eb->cat_color ?? '#6366f1' }};"></div></div>
                            <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $pct2 }}% of total expenses</span>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: 0.82rem;">No expenses recorded</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    </div>
        </div>
    </div>

@endsection
    
    @section('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
        const mD    ata = @json($monthlyTrend);
          new Chart(do  cument.getElementById('monthlyChart'), {
               type: 'b ar',
              data  : {
                    labels: mData.map(d => {
                        const [y, m] = d.month.split('-');
                        return new Date(y, m - 1).toLocaleDateString('en-UG', { month: 'short', year: '2-digit' });
                }),
                 data   sets: [{
                        label: 'Collections (UGX)',
                        data: mData.map(d => parseFloat(d.total)),
                        backgroundColor: 'rgba(62, 146, 204, 0.12)',
                        borderColor: '#3E92CC',
                        borderWidth: 2.5,
                        borderRadius: 8,
                        borderSkipped: false,
                        hoverBackgroundColor: 'rgba(62, 146, 204, 0.22)'
                    }]
            },
                 options:    {
                  responsi  ve: true,
                   plugins: {
                       lege  nd: { display: false },
                        tooltip: {
                            callbacks: {
                                label: c => 'UGX ' + c.raw.toLocaleString()
                            }
                     }
                      },
                     scales: {
                    y: {
                              begi  nAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { family: 'JetBrains Mono', size: 10 },
                                callback: v => 'UGX ' + (v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v >= 1e3 ? (v / 1e3).toFixed(0) + 'K' : v)
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Inter', size: 11 } }
                    }
                    }
                }
           });

           new Char   t(document.getElementById('feeStatusChart'), {
              type: 'd  oughnut',
                data: {
                labe    ls: ['Fully Paid', 'Partial', 'Unpaid'],
                 data   sets: [{
                        data: [{{ $feeStats->fully_paid ?? 0 }}, {{ $feeStats->partial ?? 0 }}, {{ $feeStats->unpaid ?? 0 }}],
                        backgroundColor: ['#3E92CC', '#d97706', '#dc2626'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
              },
               options:    {
                  cuto  ut: '76%',
                    responsive: true,
                plugins: {
                         lege   nd: { display: false },
                        tooltip: {
                            callbacks: {
                                label: c => c.label + ': ' + c.raw + ' students'
                            }
                    }
                }
            }
        });
        </script>
    @endsection