@extends('layouts-side-bar.master')

@section('content')

@php
    // ─── Dummy Data for Dashboard ───────────────────────────────────────────
    $totalSchools       = $totalSchools       ?? 248;
    $activeSchools      = $activeSchools      ?? 231;
    $totalStudents      = $totalStudents      ?? 184_320;
    $totalTeachers      = $totalTeachers      ?? 12_480;
    $totalSubscriptions = $totalSubscriptions ?? 248;
    $activeSubscriptions= $activeSubscriptions?? 221;
    $monthlyRevenue     = $monthlyRevenue     ?? 48_650;
    $annualRevenue      = $annualRevenue      ?? 524_300;
    $pendingPayments    = $pendingPayments    ?? 17;
    $totalExams         = $totalExams         ?? 1_842;
    $gradedExams        = $gradedExams        ?? 1_609;
    $pendingApprovals   = $pendingApprovals   ?? 14;
    $systemAlerts       = $systemAlerts       ?? 3;

    $recentSchools = $recentSchools ?? [
        ['name'=>'Al-Noor Academy',        'code'=>'ANA-001','category'=>'TH','students'=>820, 'status'=>'active',   'joined'=>'Jan 2025'],
        ['name'=>'Bright Futures College',  'code'=>'BFC-042','category'=>'ID','students'=>1240,'status'=>'active',   'joined'=>'Feb 2025'],
        ['name'=>'Islamic Heritage School', 'code'=>'IHS-017','category'=>'TH','students'=>630, 'status'=>'pending',  'joined'=>'Mar 2025'],
        ['name'=>'Madrasa Al-Falah',        'code'=>'MAF-031','category'=>'ID','students'=>940, 'status'=>'active',   'joined'=>'Mar 2025'],
        ['name'=>'Crescent Moon Institute', 'code'=>'CMI-058','category'=>'TH','students'=>510, 'status'=>'inactive', 'joined'=>'Apr 2025'],
    ];

    $subscriptionPlans = $subscriptionPlans ?? [
        ['plan'=>'Enterprise','schools'=>48, 'revenue'=>28800,'color'=>'#6c3fc5'],
        ['plan'=>'Professional','schools'=>112,'revenue'=>16800,'color'=>'#5351e4'],
        ['plan'=>'Starter',    'schools'=>88, 'revenue'=>8800, 'color'=>'#e0a020'],
    ];

    $monthlyStats = $monthlyStats ?? [
        ['month'=>'Jan','schools'=>212,'students'=>168000,'revenue'=>41200],
        ['month'=>'Feb','schools'=>220,'students'=>172000,'revenue'=>43500],
        ['month'=>'Mar','schools'=>228,'students'=>176000,'revenue'=>45100],
        ['month'=>'Apr','schools'=>235,'students'=>179000,'revenue'=>46800],
        ['month'=>'May','schools'=>241,'students'=>182000,'revenue'=>47900],
        ['month'=>'Jun','schools'=>248,'students'=>184320,'revenue'=>48650],
    ];

    $topSchools = $topSchools ?? [
        ['rank'=>1,'name'=>'Al-Noor Academy',         'students'=>1840,'pass_rate'=>94.2,'grade'=>'A'],
        ['rank'=>2,'name'=>'Bright Futures College',  'students'=>1620,'pass_rate'=>91.8,'grade'=>'A'],
        ['rank'=>3,'name'=>'Madrasa Al-Falah',        'students'=>1430,'pass_rate'=>89.5,'grade'=>'B+'],
        ['rank'=>4,'name'=>'Crescent Moon Institute', 'students'=>1290,'pass_rate'=>87.1,'grade'=>'B+'],
        ['rank'=>5,'name'=>'Islamic Heritage School', 'students'=>1180,'pass_rate'=>84.6,'grade'=>'B'],
    ];

    $recentActivity = $recentActivity ?? [
        ['icon'=>'fa-school',      'color'=>'#5351e4','text'=>'New school registered: Al-Barakah Institute',           'time'=>'5 min ago'],
        ['icon'=>'fa-file-invoice','color'=>'#3b82f6','text'=>'Subscription renewed: Bright Futures College (Pro)',    'time'=>'22 min ago'],
        ['icon'=>'fa-triangle-exclamation','color'=>'#e0a020','text'=>'Payment overdue: Crescent Moon Institute',      'time'=>'1 hr ago'],
        ['icon'=>'fa-user-plus',   'color'=>'#6c3fc5','text'=>'New admin user created for IHS-017',                   'time'=>'2 hrs ago'],
        ['icon'=>'fa-circle-check','color'=>'#5351e4','text'=>'Exam results approved: MAF-031 — Term 2',              'time'=>'3 hrs ago'],
        ['icon'=>'fa-bell',        'color'=>'#ef4444','text'=>'System alert: Backup completed with 2 warnings',       'time'=>'5 hrs ago'],
    ];

    $categoryBreakdown = $categoryBreakdown ?? [
        ['category'=>'Thanawi (TH)','schools'=>98, 'students'=>72400, 'pct'=>39],
        ['category'=>'Idaad (ID)',  'schools'=>87, 'students'=>68200, 'pct'=>35],
        ['category'=>'Primary',    'schools'=>42, 'students'=>31600, 'pct'=>17],
        ['category'=>'Mixed',      'schools'=>21, 'students'=>12120, 'pct'=>9],
    ];

    $systemHealth = [
        ['label'=>'Server Uptime',      'value'=>'99.98%', 'icon'=>'fa-server',       'status'=>'good'],
        ['label'=>'Database Health',    'value'=>'Optimal','icon'=>'fa-database',     'status'=>'good'],
        ['label'=>'Storage Used',       'value'=>'67%',    'icon'=>'fa-hard-drive',   'status'=>'warn'],
        ['label'=>'Active Sessions',    'value'=>'1,284',  'icon'=>'fa-users',        'status'=>'good'],
        ['label'=>'Pending Backups',    'value'=>'0',      'icon'=>'fa-cloud-arrow-up','status'=>'good'],
        ['label'=>'Failed Jobs',        'value'=>'2',      'icon'=>'fa-bug',          'status'=>'warn'],
    ];
@endphp

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- STYLES                                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

    :root {
        --brand:        #5351e4;
        --brand-light:  #34a85a;
        --brand-dark:   #1a5530;
        --brand-muted:  rgba(40,124,68,.12);
        --accent:       #e0a020;
        --accent-muted: rgba(224,160,32,.12);
        --purple:       #6c3fc5;
        --purple-muted: rgba(108,63,197,.12);
        --danger:       #ef4444;
        --danger-muted: rgba(239,68,68,.12);
        --info:         #3b82f6;
        --info-muted:   rgba(59,130,246,.12);
        --surface:      #ffffff;
        --surface-2:    #f7f9f7;
        --surface-3:    #eef3ef;
        --border:       rgba(40,124,68,.14);
        --text-primary: #0f1f14;
        --text-secondary:#4b6356;
        --text-muted:   #8ca898;
        --shadow-sm:    0 2px 8px rgba(0,0,0,.06);
        --shadow-md:    0 4px 20px rgba(0,0,0,.09);
        --shadow-lg:    0 8px 40px rgba(0,0,0,.12);
        --radius-sm:    10px;
        --radius-md:    16px;
        --radius-lg:    24px;
        --radius-xl:    32px;
        --font:         'Sora', sans-serif;
        --mono:         'JetBrains Mono', monospace;
    }

    *, *::before, *::after { box-sizing: border-box; }

    .adm-wrap {
        font-family: var(--font);
        color: var(--text-primary);
        background: var(--surface-2);
        min-height: 100vh;
        padding: 0 0 60px;
    }

    /* ── Top Hero Bar ──────────────────────────────────────────────────── */
    .hero-bar {
        background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand) 55%, #3db861 100%);
        padding: 32px 36px 80px;
        position: relative;
        overflow: hidden;
        margin-top: 2rem;
    }
    .hero-bar::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .hero-bar::after {
        content: '';
        position: absolute;
        bottom: -2px; left: 0; right: 0;
        height: 60px;
        background: var(--surface-2);
        border-radius: 40px 40px 0 0;
    }
    .hero-title {
        font-size: 1.75rem; font-weight: 800;
        color: #fff; letter-spacing: -.5px;
        margin: 0 0 4px;
    }
    .hero-sub {
        color: rgba(255,255,255,.72);
        font-size: .88rem; font-weight: 400;
    }
    .hero-badges { display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap; }
    .hero-badge {
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.28);
        backdrop-filter: blur(8px);
        color: #fff; font-size: .75rem; font-weight: 600;
        padding: 5px 12px; border-radius: 100px;
        display: flex; align-items: center; gap: 5px;
    }
    .hero-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #6bffaa; }
    .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .hero-btn {
        display: flex; align-items: center; gap: 7px;
        padding: 9px 18px; border-radius: 100px;
        font-size: .82rem; font-weight: 600; font-family: var(--font);
        border: none; cursor: pointer; transition: all .2s;
        text-decoration: none;
    }
    .hero-btn-solid  { background: #fff; color: var(--brand-dark); }
    .hero-btn-outline{ background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.35); }
    .hero-btn:hover  { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.18); }

    /* ── Page Body ─────────────────────────────────────────────────────── */
    .page-body { padding: 0 24px; margin-top: -46px; position: relative; z-index: 2; }

    /* ── KPI Cards ─────────────────────────────────────────────────────── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    @media(max-width:1200px){ .kpi-grid{ grid-template-columns: repeat(2,1fr); } }
    @media(max-width:600px) { .kpi-grid{ grid-template-columns: 1fr; } }

    .kpi-card {
        background: var(--surface);
        border-radius: var(--radius-md);
        padding: 22px 22px 18px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
        transition: box-shadow .25s, transform .25s;
    }
    .kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
    .kpi-accent-bar {
        position: absolute; top: 0; left: 0; right: 0;
        height: 4px; border-radius: 4px 4px 0 0;
    }
    .kpi-icon-ring {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; margin-bottom: 14px;
    }
    .kpi-value {
        font-size: 2.1rem; font-weight: 800; letter-spacing: -1px;
        line-height: 1; margin-bottom: 4px; color: var(--text-primary);
    }
    .kpi-label { font-size: .78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
    .kpi-trend {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: .75rem; font-weight: 600; margin-top: 8px;
        padding: 3px 8px; border-radius: 100px;
    }
    .kpi-trend.up   { background: rgba(40,124,68,.1); color: var(--brand); }
    .kpi-trend.down { background: var(--danger-muted); color: var(--danger); }
    .kpi-trend.flat { background: rgba(139,139,139,.1); color: #888; }

    /* ── Section Header ────────────────────────────────────────────────── */
    .sec-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 16px;
    }
    .sec-title {
        font-size: 1.05rem; font-weight: 700; color: var(--text-primary);
        display: flex; align-items: center; gap: 9px;
    }
    .sec-title .icon-pill {
        width: 30px; height: 30px; border-radius: 8px;
        background: var(--brand-muted); color: var(--brand);
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem;
    }
    .sec-link {
        font-size: .8rem; color: var(--brand); font-weight: 600;
        text-decoration: none; display: flex; align-items: center; gap: 4px;
    }
    .sec-link:hover { color: var(--brand-dark); }

    /* ── Cards ─────────────────────────────────────────────────────────── */
    .card-adm {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .card-adm-body { padding: 20px 22px; }
    .card-adm-header {
        padding: 16px 22px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }

    /* ── Two/Three Column Grids ────────────────────────────────────────── */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .grid-3-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; }
    .grid-1-2 { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 24px; }
    @media(max-width:1100px){
        .grid-2, .grid-3, .grid-3-1, .grid-1-2 { grid-template-columns: 1fr; }
    }

    /* ── Micro Chart Bar ───────────────────────────────────────────────── */
    .mini-bar-wrap { display: flex; align-items: flex-end; gap: 5px; height: 48px; margin-top: 10px; }
    .mini-bar {
        flex: 1; border-radius: 4px 4px 0 0;
        background: var(--brand-muted); position: relative;
        transition: background .2s;
        min-height: 4px;
    }
    .mini-bar:hover { background: var(--brand); }
    .mini-bar.active { background: var(--brand); }

    /* ── Table ─────────────────────────────────────────────────────────── */
    .adm-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .adm-table thead th {
        padding: 10px 14px; text-align: left;
        font-size: .72rem; font-weight: 700; letter-spacing: .5px;
        text-transform: uppercase; color: var(--text-muted);
        background: var(--surface-2); border-bottom: 1px solid var(--border);
    }
    .adm-table tbody td {
        padding: 12px 14px; border-bottom: 1px solid rgba(0,0,0,.04);
        vertical-align: middle;
    }
    .adm-table tbody tr:last-child td { border-bottom: none; }
    .adm-table tbody tr:hover td { background: var(--surface-2); }

    /* ── Badges ────────────────────────────────────────────────────────── */
    .badge-adm {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 100px;
        font-size: .72rem; font-weight: 700; letter-spacing: .3px;
    }
    .badge-adm::before { content:''; width:5px; height:5px; border-radius:50%; }
    .badge-active  { background: rgba(40,124,68,.12); color: var(--brand); }
    .badge-active::before  { background: var(--brand); }
    .badge-pending { background: var(--accent-muted); color: #c47f00; }
    .badge-pending::before { background: var(--accent); }
    .badge-inactive{ background: rgba(100,100,100,.1); color: #666; }
    .badge-inactive::before{ background: #999; }

    /* ── Activity Feed ─────────────────────────────────────────────────── */
    .activity-item { display: flex; gap: 12px; padding: 13px 0; }
    .activity-item:not(:last-child) { border-bottom: 1px dashed var(--border); }
    .activity-icon {
        width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .82rem;
    }
    .activity-text { font-size: .84rem; font-weight: 500; color: var(--text-primary); line-height: 1.4; }
    .activity-time { font-size: .73rem; color: var(--text-muted); margin-top: 2px; display: flex; align-items: center; gap: 4px; }

    /* ── Donut Chart (CSS) ─────────────────────────────────────────────── */
    .donut-wrap { position: relative; width: 120px; height: 120px; margin: 0 auto; }
    .donut-svg { transform: rotate(-90deg); }
    .donut-label {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        font-family: var(--font);
    }
    .donut-num   { font-size: 1.4rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
    .donut-sub   { font-size: .65rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }

    /* ── Revenue Stat ──────────────────────────────────────────────────── */
    .rev-stat { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; }
    .rev-stat:not(:last-child) { border-bottom: 1px solid var(--border); }
    .rev-bar-track { flex: 1; height: 6px; background: var(--surface-3); border-radius: 3px; margin: 0 14px; overflow: hidden; }
    .rev-bar-fill  { height: 100%; border-radius: 3px; transition: width .6s ease; }
    .rev-label { font-size: .82rem; font-weight: 600; color: var(--text-secondary); min-width: 130px; }
    .rev-value { font-size: .82rem; font-weight: 700; color: var(--text-primary); min-width: 70px; text-align: right; font-family: var(--mono); }

    /* ── System Health ─────────────────────────────────────────────────── */
    .health-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    @media(max-width:600px){ .health-grid { grid-template-columns: 1fr; } }
    .health-item {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 14px; border-radius: var(--radius-sm);
        background: var(--surface-2); border: 1px solid var(--border);
    }
    .health-icon {
        width: 34px; height: 34px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: .82rem; flex-shrink: 0;
    }
    .health-label { font-size: .78rem; color: var(--text-muted); font-weight: 500; }
    .health-val   { font-size: .92rem; font-weight: 700; color: var(--text-primary); font-family: var(--mono); }
    .health-dot   { width: 8px; height: 8px; border-radius: 50%; margin-left: auto; flex-shrink: 0; }
    .health-dot.good { background: var(--brand); box-shadow: 0 0 0 3px rgba(40,124,68,.2); }
    .health-dot.warn { background: var(--accent); box-shadow: 0 0 0 3px rgba(224,160,32,.2); }

    /* ── Rank Badges ───────────────────────────────────────────────────── */
    .rank-badge {
        width: 28px; height: 28px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .78rem; font-weight: 800; flex-shrink: 0;
    }
    .rank-1 { background: linear-gradient(135deg,#ffd700,#f5a623); color: #5a3a00; }
    .rank-2 { background: linear-gradient(135deg,#e8e8e8,#b0b0b0); color: #333; }
    .rank-3 { background: linear-gradient(135deg,#e8ab6e,#c57b3e); color: #fff; }
    .rank-other { background: var(--surface-3); color: var(--text-muted); }

    /* ── Category Progress ─────────────────────────────────────────────── */
    .cat-row { margin-bottom: 14px; }
    .cat-row:last-child { margin-bottom: 0; }
    .cat-top { display: flex; justify-content: space-between; font-size: .82rem; margin-bottom: 5px; font-weight: 600; }
    .cat-track { height: 8px; background: var(--surface-3); border-radius: 4px; overflow: hidden; }
    .cat-fill  { height: 100%; border-radius: 4px; transition: width .7s ease; }

    /* ── Alert Strip ───────────────────────────────────────────────────── */
    .alert-strip {
        display: flex; align-items: center; gap: 12px;
        background: #fffbf0; border: 1px solid #f5d76e;
        border-radius: var(--radius-sm); padding: 12px 16px;
        margin-bottom: 20px; font-size: .84rem; font-weight: 500;
    }
    .alert-strip i { color: var(--accent); font-size: 1rem; }
    .alert-strip a { color: var(--brand); font-weight: 700; text-decoration: none; margin-left: auto; white-space: nowrap; }

    /* ── Circular Progress ─────────────────────────────────────────────── */
    .circle-progress-wrap { display: flex; align-items: center; gap: 16px; }
    .circle-info .ci-val { font-size: 1.6rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
    .circle-info .ci-lbl { font-size: .78rem; color: var(--text-muted); font-weight: 600; }

    /* ── Footer Meta ───────────────────────────────────────────────────── */
    .page-meta {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 24px 0; margin-top: 8px;
        font-size: .76rem; color: var(--text-muted); font-weight: 500;
        border-top: 1px solid var(--border); flex-wrap: wrap; gap: 8px;
    }
    .page-meta span { display: flex; align-items: center; gap: 5px; }

    /* ── Quick Links ───────────────────────────────────────────────────── */
    .quick-links { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; margin-bottom: 24px; }
    @media(max-width:900px){ .quick-links { grid-template-columns: repeat(2,1fr); } }
    @media(max-width:480px){ .quick-links { grid-template-columns: 1fr; } }

    .ql-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-sm); padding: 16px 16px 14px;
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; color: var(--text-primary);
        transition: all .2s; box-shadow: var(--shadow-sm);
    }
    .ql-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); color: var(--brand); border-color: rgba(40,124,68,.3); }
    .ql-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
    .ql-title { font-size: .82rem; font-weight: 700; }
    .ql-desc  { font-size: .72rem; color: var(--text-muted); margin-top: 1px; }

    /* ── Pulse Animation ───────────────────────────────────────────────── */
    @keyframes pulse { 0%,100%{ opacity:1; } 50%{ opacity:.5; } }
    .pulse { animation: pulse 2s infinite; }

    /* ── Entrance Animations ───────────────────────────────────────────── */
    @keyframes fadeUp { from{ opacity:0; transform:translateY(20px); } to{ opacity:1; transform:translateY(0); } }
    .kpi-card      { animation: fadeUp .4s ease both; }
    .kpi-card:nth-child(1){ animation-delay:.05s; }
    .kpi-card:nth-child(2){ animation-delay:.1s; }
    .kpi-card:nth-child(3){ animation-delay:.15s; }
    .kpi-card:nth-child(4){ animation-delay:.2s; }

    /* ── Responsive Tweaks ─────────────────────────────────────────────── */
    @media(max-width:768px){
        .hero-bar { padding: 24px 20px 72px; }
        .page-body { padding: 0 14px; }
        .hero-title { font-size: 1.4rem; }
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="adm-wrap">

    {{-- ───────────────────────── HERO BAR ───────────────────────────── --}}
    <div class="hero-bar">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;z-index:1">
            <div>
                <p class="hero-sub" style="margin:0 0 6px">
                    <i class="fas fa-calendar-days me-1"></i>
                    {{ now()->format('l, d F Y') }} &nbsp;·&nbsp; Academic Year {{ now()->year }}
                </p>
                <h1 class="hero-title">
                    <i class="fas fa-gauge-high me-2" style="font-size:1.4rem;opacity:.85"></i>
                    Admin Command Centre
                </h1>
                <div class="hero-badges">
                    <span class="hero-badge"><span class="dot"></span>{{ $activeSchools }} Schools Online</span>
                    <span class="hero-badge"><i class="fas fa-users" style="font-size:.7rem"></i>{{ number_format($totalStudents) }} Students</span>
                </div>
            </div>
            <div class="hero-actions" style="padding-top:4px">
                <a href="#" class="hero-btn hero-btn-solid"><i class="fas fa-plus"></i> Add School</a>
                <a href="#" class="hero-btn hero-btn-outline"><i class="fas fa-file-export"></i> Export Report</a>
                <a href="#" class="hero-btn hero-btn-outline"><i class="fas fa-gear"></i> Settings</a>
            </div>
        </div>
    </div>

    <div class="page-body">

        {{-- Alert Strip --}}
        @if($pendingPayments > 0 || $pendingApprovals > 0)
        <div class="alert-strip">
            <i class="fas fa-triangle-exclamation"></i>
            <span>
                You have
                @if($pendingPayments > 0)<strong>{{ $pendingPayments }} overdue payments</strong>@endif
                @if($pendingPayments > 0 && $pendingApprovals > 0) and @endif
                @if($pendingApprovals > 0)<strong>{{ $pendingApprovals }} pending approvals</strong>@endif
                requiring attention.
            </span>
            <a href="#">Review &rarr;</a>
        </div>
        @endif

        {{-- ─────────────────────── KPI CARDS ────────────────────────── --}}
        <div class="kpi-grid">

            {{-- Schools --}}
            <div class="kpi-card">
                <div class="kpi-accent-bar" style="background:linear-gradient(90deg,var(--brand),var(--brand-light))"></div>
                <div class="kpi-icon-ring" style="background:var(--brand-muted);color:var(--brand)">
                    <i class="fas fa-school"></i>
                </div>
                <div class="kpi-label">Total Schools</div>
                <div class="kpi-value">{{ number_format($totalSchools) }}</div>
                <div>
                    <span class="kpi-trend up"><i class="fas fa-arrow-up" style="font-size:.65rem"></i>+7 this month</span>
                    <span style="font-size:.73rem;color:var(--text-muted);margin-left:8px">{{ $activeSchools }} active</span>
                </div>
                <div class="mini-bar-wrap">
                    @foreach($monthlyStats as $i => $m)
                    <div class="mini-bar {{ $i === count($monthlyStats)-1 ? 'active':'' }}"
                         style="height:{{ round(($m['schools']/$totalSchools)*100) }}%;max-height:100%"
                         title="{{ $m['month'] }}: {{ $m['schools'] }}"></div>
                    @endforeach
                </div>
            </div>

            {{-- Students --}}
            <div class="kpi-card">
                <div class="kpi-accent-bar" style="background:linear-gradient(90deg,var(--info),#60a5fa)"></div>
                <div class="kpi-icon-ring" style="background:var(--info-muted);color:var(--info)">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="kpi-label">Total Students</div>
                <div class="kpi-value">{{ number_format($totalStudents) }}</div>
                <div>
                    <span class="kpi-trend up"><i class="fas fa-arrow-up" style="font-size:.65rem"></i>+2.4% vs last month</span>
                </div>
                <div class="mini-bar-wrap">
                    @foreach($monthlyStats as $i => $m)
                    <div class="mini-bar {{ $i === count($monthlyStats)-1 ? 'active':'' }}"
                         style="height:{{ round(($m['students']/$totalStudents)*100) }}%;max-height:100%;background:var(--info-muted)"
                         title="{{ $m['month'] }}: {{ number_format($m['students']) }}"></div>
                    @endforeach
                </div>
            </div>

            {{-- Revenue --}}
            <div class="kpi-card">
                <div class="kpi-accent-bar" style="background:linear-gradient(90deg,var(--purple),#9b6fe8)"></div>
                <div class="kpi-icon-ring" style="background:var(--purple-muted);color:var(--purple)">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="kpi-label">Monthly Revenue</div>
                <div class="kpi-value" style="font-family:var(--mono)">${{ number_format($monthlyRevenue) }}</div>
                <div>
                    <span class="kpi-trend up"><i class="fas fa-arrow-up" style="font-size:.65rem"></i>+5.8% vs last month</span>
                    <span style="font-size:.73rem;color:var(--text-muted);margin-left:8px">${{ number_format($annualRevenue) }}/yr</span>
                </div>
                <div class="mini-bar-wrap">
                    @foreach($monthlyStats as $i => $m)
                    <div class="mini-bar {{ $i === count($monthlyStats)-1 ? 'active':'' }}"
                         style="height:{{ round(($m['revenue']/50000)*100) }}%;max-height:100%;background:var(--purple-muted)"
                         title="{{ $m['month'] }}: ${{ number_format($m['revenue']) }}"></div>
                    @endforeach
                </div>
            </div>

            {{-- Teachers --}}
            <div class="kpi-card">
                <div class="kpi-accent-bar" style="background:linear-gradient(90deg,var(--accent),#f5c842)"></div>
                <div class="kpi-icon-ring" style="background:var(--accent-muted);color:#b07d00">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="kpi-label">Total Teachers</div>
                <div class="kpi-value">{{ number_format($totalTeachers) }}</div>
                <div>
                    <span class="kpi-trend up"><i class="fas fa-arrow-up" style="font-size:.65rem"></i>+148 this term</span>
                    <span style="font-size:.73rem;color:var(--text-muted);margin-left:8px">≈15 per school</span>
                </div>
                <div class="mini-bar-wrap">
                    @for($b=0; $b<6; $b++)
                    <div class="mini-bar {{ $b===5?'active':'' }}"
                         style="height:{{ 55 + $b*7 }}%;max-height:100%;background:var(--accent-muted)"></div>
                    @endfor
                </div>
            </div>

        </div>{{-- /kpi-grid --}}

        {{-- ─────────────────── QUICK LINKS ───────────────────────────── --}}
        <div class="quick-links">
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:var(--brand-muted);color:var(--brand)"><i class="fas fa-school-flag"></i></div>
                <div><div class="ql-title">Manage Schools</div><div class="ql-desc">Add, edit, suspend</div></div>
            </a>
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:var(--info-muted);color:var(--info)"><i class="fas fa-file-invoice-dollar"></i></div>
                <div><div class="ql-title">Subscriptions</div><div class="ql-desc">Plans & billing</div></div>
            </a>
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:var(--purple-muted);color:var(--purple)"><i class="fas fa-chart-bar"></i></div>
                <div><div class="ql-title">Exam Reports</div><div class="ql-desc">Grades & results</div></div>
            </a>
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:var(--accent-muted);color:#b07d00"><i class="fas fa-user-shield"></i></div>
                <div><div class="ql-title">Admin Users</div><div class="ql-desc">Roles & permissions</div></div>
            </a>
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:var(--danger-muted);color:var(--danger)"><i class="fas fa-bell-exclamation"></i></div>
                <div><div class="ql-title">Alerts & Logs</div><div class="ql-desc">System monitoring</div></div>
            </a>
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:var(--brand-muted);color:var(--brand)"><i class="fas fa-calendar-check"></i></div>
                <div><div class="ql-title">Academic Calendar</div><div class="ql-desc">Terms & holidays</div></div>
            </a>
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:var(--info-muted);color:var(--info)"><i class="fas fa-cloud-upload-alt"></i></div>
                <div><div class="ql-title">Data Import</div><div class="ql-desc">Bulk school data</div></div>
            </a>
            <a href="#" class="ql-card">
                <div class="ql-icon" style="background:rgba(100,100,100,.1);color:#555"><i class="fas fa-gear"></i></div>
                <div><div class="ql-title">System Settings</div><div class="ql-desc">Config & preferences</div></div>
            </a>
        </div>

        {{-- ─────────────────── ROW: Schools Table + Activity ─────────── --}}
        <div class="grid-3-1">

            {{-- Recent Schools --}}
            <div class="card-adm">
                <div class="card-adm-header">
                    <div class="sec-title" style="margin:0">
                        <span class="icon-pill"><i class="fas fa-school"></i></span>
                        Recently Registered Schools
                    </div>
                    <a href="#" class="sec-link">View all <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x:auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>Code</th>
                                <th>Cat.</th>
                                <th>Students</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSchools as $s)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:9px">
                                        <div style="width:32px;height:32px;border-radius:8px;background:var(--brand-muted);color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0">
                                            {{ strtoupper(substr($s['name'],0,2)) }}
                                        </div>
                                        <span style="font-weight:600;font-size:.84rem">{{ $s['name'] }}</span>
                                    </div>
                                </td>
                                <td style="font-family:var(--mono);font-size:.78rem;color:var(--text-muted)">{{ $s['code'] }}</td>
                                <td>
                                    <span class="badge-adm" style="background:var(--brand-muted);color:var(--brand);font-size:.7rem">{{ $s['category'] }}</span>
                                </td>
                                <td style="font-weight:700;font-family:var(--mono);font-size:.84rem">{{ number_format($s['students']) }}</td>
                                <td>
                                    <span class="badge-adm badge-{{ $s['status'] }}">{{ ucfirst($s['status']) }}</span>
                                </td>
                                <td style="font-size:.78rem;color:var(--text-muted)">{{ $s['joined'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Activity Feed --}}
            <div class="card-adm">
                <div class="card-adm-header">
                    <div class="sec-title" style="margin:0">
                        <span class="icon-pill" style="background:var(--info-muted);color:var(--info)"><i class="fas fa-bolt"></i></span>
                        Live Activity
                    </div>
                    <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.2)" class="pulse"></span>
                </div>
                <div class="card-adm-body">
                    @foreach($recentActivity as $act)
                    <div class="activity-item">
                        <div class="activity-icon" style="background:{{ $act['color'] }}1a;color:{{ $act['color'] }}">
                            <i class="fas {{ $act['icon'] }}"></i>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div class="activity-text">{{ $act['text'] }}</div>
                            <div class="activity-time"><i class="fas fa-clock" style="font-size:.65rem"></i>{{ $act['time'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>{{-- /grid-3-1 --}}

        {{-- ─────────────────── ROW: Revenue + Categories + Top Schools ── --}}
        <div class="grid-3">

            {{-- Subscription Revenue --}}
            <div class="card-adm">
                <div class="card-adm-header">
                    <div class="sec-title" style="margin:0">
                        <span class="icon-pill" style="background:var(--purple-muted);color:var(--purple)"><i class="fas fa-credit-card"></i></span>
                        Revenue by Plan
                    </div>
                </div>
                <div class="card-adm-body">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
                        <div>
                            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Total MRR</div>
                            <div style="font-size:1.9rem;font-weight:800;font-family:var(--mono);color:var(--text-primary)">${{ number_format($monthlyRevenue) }}</div>
                        </div>
                        <div>
                            <svg class="donut-svg" viewBox="0 0 36 36" width="80" height="80">
                                @php
                                    $total   = array_sum(array_column($subscriptionPlans,'revenue'));
                                    $offset  = 0;
                                    $circum  = 2 * M_PI * 14;
                                @endphp
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#eef3ef" stroke-width="4"/>
                                @foreach($subscriptionPlans as $sp)
                                @php
                                    $pct  = $sp['revenue'] / $total;
                                    $dash = $circum * $pct;
                                @endphp
                                <circle cx="18" cy="18" r="14" fill="none"
                                    stroke="{{ $sp['color'] }}" stroke-width="4"
                                    stroke-dasharray="{{ number_format($dash,2) }} {{ number_format($circum - $dash,2) }}"
                                    stroke-dashoffset="{{ number_format(-$offset * $circum / (2*M_PI),2) }}"
                                    stroke-linecap="round"/>
                                @php $offset += $pct * $circum; @endphp
                                @endforeach
                            </svg>
                        </div>
                    </div>
                    @foreach($subscriptionPlans as $sp)
                    @php $pct = round($sp['revenue'] / $total * 100); @endphp
                    <div class="rev-stat">
                        <div style="display:flex;align-items:center;gap:7px;min-width:130px">
                            <span style="width:10px;height:10px;border-radius:3px;background:{{ $sp['color'] }};flex-shrink:0"></span>
                            <span class="rev-label" style="min-width:unset">{{ $sp['plan'] }}</span>
                        </div>
                        <div class="rev-bar-track">
                            <div class="rev-bar-fill" style="width:{{ $pct }}%;background:{{ $sp['color'] }}"></div>
                        </div>
                        <div class="rev-value">${{ number_format($sp['revenue']) }}</div>
                    </div>
                    @endforeach

                    <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div style="text-align:center;padding:10px;background:var(--surface-2);border-radius:var(--radius-sm)">
                            <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);font-family:var(--mono)">{{ $activeSubscriptions }}/{{ $totalSubscriptions }}</div>
                            <div style="font-size:.73rem;color:var(--text-muted);font-weight:600">Active Subs</div>
                        </div>
                        <div style="text-align:center;padding:10px;background:var(--danger-muted);border-radius:var(--radius-sm)">
                            <div style="font-size:1.1rem;font-weight:800;color:var(--danger);font-family:var(--mono)">{{ $pendingPayments }}</div>
                            <div style="font-size:.73rem;color:var(--danger);font-weight:600;opacity:.8">Overdue</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category Breakdown --}}
            <div class="card-adm">
                <div class="card-adm-header">
                    <div class="sec-title" style="margin:0">
                        <span class="icon-pill" style="background:var(--accent-muted);color:#b07d00"><i class="fas fa-layer-group"></i></span>
                        School Categories
                    </div>
                </div>
                <div class="card-adm-body">
                    <div style="display:flex;align-items:center;justify-content:center;margin-bottom:20px">
                        <div class="donut-wrap">
                            <svg class="donut-svg" viewBox="0 0 36 36" width="120" height="120">
                                @php
                                    $catTotal  = array_sum(array_column($categoryBreakdown,'schools'));
                                    $catColors = ['#5351e4','#3b82f6','#e0a020','#6c3fc5'];
                                    $catOff    = 0;
                                    $catCircum = 2 * M_PI * 13;
                                @endphp
                                <circle cx="18" cy="18" r="13" fill="none" stroke="#eef3ef" stroke-width="5"/>
                                @foreach($categoryBreakdown as $ci => $cat)
                                @php
                                    $cpct  = $cat['schools'] / $catTotal;
                                    $cdash = $catCircum * $cpct;
                                @endphp
                                <circle cx="18" cy="18" r="13" fill="none"
                                    stroke="{{ $catColors[$ci % count($catColors)] }}" stroke-width="5"
                                    stroke-dasharray="{{ number_format($cdash,2) }} {{ number_format($catCircum - $cdash,2) }}"
                                    stroke-dashoffset="{{ number_format(-$catOff,2) }}"
                                    stroke-linecap="round"/>
                                @php $catOff += $cpct * $catCircum; @endphp
                                @endforeach
                            </svg>
                            <div class="donut-label">
                                <span class="donut-num">{{ $catTotal }}</span>
                                <span class="donut-sub">Schools</span>
                            </div>
                        </div>
                    </div>

                    @foreach($categoryBreakdown as $ci => $cat)
                    <div class="cat-row">
                        <div class="cat-top">
                            <span style="display:flex;align-items:center;gap:6px">
                                <span style="width:8px;height:8px;border-radius:2px;background:{{ $catColors[$ci % count($catColors)] }};flex-shrink:0"></span>
                                {{ $cat['category'] }}
                            </span>
                            <span style="color:var(--text-muted);font-size:.78rem">{{ $cat['schools'] }} schools &nbsp;·&nbsp; {{ number_format($cat['students']) }} students</span>
                        </div>
                        <div class="cat-track">
                            <div class="cat-fill" style="width:{{ $cat['pct'] }}%;background:{{ $catColors[$ci % count($catColors)] }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Performing Schools --}}
            <div class="card-adm">
                <div class="card-adm-header">
                    <div class="sec-title" style="margin:0">
                        <span class="icon-pill" style="background:#fef3c7;color:#b07d00"><i class="fas fa-trophy"></i></span>
                        Top Performing Schools
                    </div>
                </div>
                <div class="card-adm-body" style="padding:14px 16px">
                    @foreach($topSchools as $ts)
                    <div style="display:flex;align-items:center;gap:11px;padding:11px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                        <span class="rank-badge rank-{{ $ts['rank'] <= 3 ? $ts['rank'] : 'other' }}">{{ $ts['rank'] }}</span>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:.84rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $ts['name'] }}</div>
                            <div style="font-size:.74rem;color:var(--text-muted);margin-top:2px">{{ number_format($ts['students']) }} students</div>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <div style="font-size:.9rem;font-weight:800;color:var(--brand);font-family:var(--mono)">{{ $ts['pass_rate'] }}%</div>
                            <div style="font-size:.72rem;color:var(--text-muted)">Pass rate</div>
                        </div>
                        <div style="width:38px;height:38px;border-radius:10px;background:var(--brand-muted);color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;flex-shrink:0">
                            {{ $ts['grade'] }}
                        </div>
                    </div>
                    @endforeach
                    <a href="#" class="sec-link" style="margin-top:10px;display:inline-flex">Full Rankings <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

        </div>{{-- /grid-3 --}}

        {{-- ─────────────────── ROW: Exam Overview + System Health ────── --}}
        <div class="grid-2">

            {{-- Exam Overview --}}
            <div class="card-adm">
                <div class="card-adm-header">
                    <div class="sec-title" style="margin:0">
                        <span class="icon-pill" style="background:var(--info-muted);color:var(--info)"><i class="fas fa-file-pen"></i></span>
                        Examination Overview — {{ now()->year }}
                    </div>
                    <a href="#" class="sec-link">Reports <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-adm-body">
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
                        @php
                            $examStats = [
                                ['label'=>'Total Exams',  'value'=>$totalExams,         'color'=>'var(--info)',   'icon'=>'fa-file-pen'],
                                ['label'=>'Graded',       'value'=>$gradedExams,         'color'=>'var(--brand)',  'icon'=>'fa-circle-check'],
                                ['label'=>'Pending',      'value'=>$totalExams-$gradedExams,'color'=>'var(--accent)','icon'=>'fa-clock'],
                            ];
                        @endphp
                        @foreach($examStats as $es)
                        <div style="text-align:center;padding:14px 10px;background:var(--surface-2);border-radius:var(--radius-sm);border:1px solid var(--border)">
                            <div style="width:36px;height:36px;border-radius:10px;background:{{ $es['color'] }}1a;color:{{ $es['color'] }};display:flex;align-items:center;justify-content:center;font-size:.85rem;margin:0 auto 8px">
                                <i class="fas {{ $es['icon'] }}"></i>
                            </div>
                            <div style="font-size:1.4rem;font-weight:800;font-family:var(--mono);color:var(--text-primary)">{{ number_format($es['value']) }}</div>
                            <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px">{{ $es['label'] }}</div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Grading progress bar --}}
                    @php $gradePct = round($gradedExams / max($totalExams,1) * 100); @endphp
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.82rem;font-weight:600;margin-bottom:6px">
                            <span>Grading Progress</span>
                            <span style="color:var(--brand);font-family:var(--mono)">{{ $gradePct }}%</span>
                        </div>
                        <div style="height:10px;background:var(--surface-3);border-radius:5px;overflow:hidden">
                            <div style="height:100%;width:{{ $gradePct }}%;background:linear-gradient(90deg,var(--brand),var(--brand-light));border-radius:5px;transition:width .6s ease"></div>
                        </div>
                        <div style="font-size:.74rem;color:var(--text-muted);margin-top:5px">{{ number_format($gradedExams) }} of {{ number_format($totalExams) }} exams graded</div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px">
                        <div style="padding:12px;background:var(--danger-muted);border-radius:var(--radius-sm);display:flex;align-items:center;gap:10px">
                            <i class="fas fa-hourglass-half" style="color:var(--danger);font-size:.9rem"></i>
                            <div>
                                <div style="font-size:1rem;font-weight:800;color:var(--danger);font-family:var(--mono)">{{ $pendingApprovals }}</div>
                                <div style="font-size:.72rem;color:var(--danger);opacity:.8;font-weight:600">Pending Approvals</div>
                            </div>
                        </div>
                        <div style="padding:12px;background:var(--brand-muted);border-radius:var(--radius-sm);display:flex;align-items:center;gap:10px">
                            <i class="fas fa-certificate" style="color:var(--brand);font-size:.9rem"></i>
                            <div>
                                <div style="font-size:1rem;font-weight:800;color:var(--brand);font-family:var(--mono)">{{ number_format($gradedExams) }}</div>
                                <div style="font-size:.72rem;color:var(--brand);opacity:.8;font-weight:600">Results Published</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Health --}}
            <div class="card-adm">
                <div class="card-adm-header">
                    <div class="sec-title" style="margin:0">
                        <span class="icon-pill" style="background:var(--brand-muted);color:var(--brand)"><i class="fas fa-server"></i></span>
                        System Health
                    </div>
                    <span style="font-size:.75rem;color:var(--brand);font-weight:700;display:flex;align-items:center;gap:5px">
                        <span class="pulse" style="width:7px;height:7px;border-radius:50%;background:var(--brand);display:inline-block"></span>
                        All Systems Operational
                    </span>
                </div>
                <div class="card-adm-body">
                    <div class="health-grid">
                        @foreach($systemHealth as $sh)
                        <div class="health-item">
                            <div class="health-icon" style="background:{{ $sh['status']==='good' ? 'var(--brand-muted)' : 'var(--accent-muted)' }};color:{{ $sh['status']==='good' ? 'var(--brand)' : '#b07d00' }}">
                                <i class="fas {{ $sh['icon'] }}"></i>
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="health-label">{{ $sh['label'] }}</div>
                                <div class="health-val">{{ $sh['value'] }}</div>
                            </div>
                            <span class="health-dot {{ $sh['status'] }}"></span>
                        </div>
                        @endforeach
                    </div>

                    <div style="margin-top:16px;padding:14px;background:var(--surface-2);border-radius:var(--radius-sm);border:1px solid var(--border)">
                        <div style="font-size:.8rem;font-weight:700;color:var(--text-primary);margin-bottom:10px;display:flex;align-items:center;gap:6px">
                            <i class="fas fa-hard-drive" style="color:var(--brand)"></i> Storage Usage
                        </div>
                        @php
                            $storages = [
                                ['label'=>'Media & Documents','pct'=>67,'color'=>'var(--brand)'],
                                ['label'=>'Database Backups', 'pct'=>34,'color'=>'var(--info)'],
                                ['label'=>'Exam Files',       'pct'=>48,'color'=>'var(--purple)'],
                            ];
                        @endphp
                        @foreach($storages as $st)
                        <div style="margin-bottom:9px">
                            <div style="display:flex;justify-content:space-between;font-size:.76rem;font-weight:600;margin-bottom:3px">
                                <span style="color:var(--text-secondary)">{{ $st['label'] }}</span>
                                <span style="color:var(--text-primary);font-family:var(--mono)">{{ $st['pct'] }}%</span>
                            </div>
                            <div style="height:5px;background:var(--surface-3);border-radius:3px;overflow:hidden">
                                <div style="height:100%;width:{{ $st['pct'] }}%;background:{{ $st['color'] }};border-radius:3px"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>{{-- /grid-2 --}}

    </div>{{-- /page-body --}}

    {{-- Footer Meta --}}
    <div class="page-meta">
        <span><i class="fas fa-shield-halved" style="color:var(--brand)"></i> Admin Dashboard — School Management System</span>
        <span><i class="fas fa-clock"></i> Last updated: {{ now()->format('H:i:s') }}</span>
        <span><i class="fas fa-code-branch"></i> v3.2.1</span>
        <span><i class="fas fa-server"></i> {{ php_uname('n') ?? 'srv-prod-01' }}</span>
    </div>
    </div>    </div>    </div>
</div>{{-- /adm-wrap --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animate progress bars on scroll / load
    const fills = document.querySelectorAll('.cat-fill, .rev-bar-fill');
    fills.forEach(el => {
        const target = el.style.width;
        el.style.width = '0';
        setTimeout(() => { el.style.width = target; }, 200);
    });
});
</script>

@endsection