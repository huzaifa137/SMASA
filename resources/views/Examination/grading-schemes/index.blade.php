@extends('layouts-side-bar.master')

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2C29CA;
            --primary-dark: #201ea0;
            --primary-light: #EEEDFC;
            --primary-soft: #F5F4FF;
            --ink: #1B1D28;
            --muted: #6B7280;
            --border: #E6E7EE;
            --surface: #FFFFFF;
            --bg: #F6F7FB;
            --green: #12875A;
            --green-bg: #E6F6EF;
            --amber: #B4770B;
            --amber-bg: #FCF1DC;
            --red: #C4293A;
            --red-bg: #FCEAEC;
            --gray-bg: #F1F2F5;
        }

        * { box-sizing: border-box; }

        .gs-app {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
        }

        /* ── Breadcrumb ────────────────────────────────────────────── */
        .gs-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .4rem;
            font-size: .78rem;
            font-weight: 600;
            margin-bottom: .85rem;
        }

        .gs-breadcrumb a {
            color: var(--muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .2rem .1rem;
            border-radius: .3rem;
        }

        .gs-breadcrumb a:hover {
            color: var(--primary);
        }

        .gs-breadcrumb .crumb-current {
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .gs-breadcrumb i.fa-chevron-right {
            font-size: .6rem;
            color: var(--border);
        }

        .gs-breadcrumb i.fa-house,
        .gs-breadcrumb i.fa-home {
            font-size: .75rem;
        }

        /* ── Top bar (header card) ────────────────────────────────── */
        .gs-topbar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .8rem;
            padding: 1.15rem 1.35rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 2px rgba(27, 29, 40, .03);
        }

        .gs-topbar-title {
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .gs-topbar-icon {
            width: 44px;
            height: 44px;
            border-radius: .65rem;
            background: linear-gradient(135deg, var(--primary) 0%, #5351e4 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .gs-topbar h3 {
            font-weight: 800;
            font-size: 1.35rem;
            margin: 0 0 .15rem;
            color: var(--ink);
        }

        .gs-topbar p {
            margin: 0;
            font-size: .84rem;
            color: var(--muted);
        }

        .gs-topbar-actions {
            display: flex;
            gap: .5rem;
        }

        .btn-gs-outline {
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--ink);
            font-weight: 600;
            font-size: .82rem;
            padding: .5rem .9rem;
            border-radius: .5rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            text-decoration: none;
        }

        .btn-gs-outline:hover { background: var(--bg); color: var(--ink); }

        .btn-gs-primary {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            font-size: .82rem;
            padding: .5rem .9rem;
            border-radius: .5rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .btn-gs-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }

        /* ── Stat strip ────────────────────────────────────────────── */
        .gs-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .gs-stat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .6rem;
            padding: .85rem 1rem;
        }

        .gs-stat .label {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .3rem;
        }

        .gs-stat .value {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--ink);
        }

        .gs-stat .value small {
            font-size: .7rem;
            font-weight: 600;
            color: var(--muted);
        }

        .gs-stat.accent .value { color: var(--primary); }

        /* ── Split layout: list + detail ──────────────────────────── */
        .gs-split {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1.1rem;
            align-items: start;
        }

        .gs-list-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .7rem;
            overflow: hidden;
        }

        .gs-list-panel-head {
            padding: .8rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--muted);
        }

        .gs-scheme-list {
            list-style: none;
            margin: 0;
            padding: .4rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .scheme-list-item {
            display: block;
            width: 100%;
            text-align: left;
            border: 1px solid transparent;
            background: transparent;
            border-radius: .5rem;
            padding: .6rem .7rem;
            margin-bottom: .2rem;
            cursor: pointer;
        }

        .scheme-list-item:hover {
            background: var(--bg);
        }

        .scheme-list-item.active {
            background: var(--primary-light);
            border-color: var(--primary);
        }

        .scheme-list-item .name {
            font-weight: 700;
            font-size: .88rem;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .scheme-list-item .name i.fa-star { color: var(--amber); font-size: .7rem; }

        .scheme-list-item .sub {
            font-size: .72rem;
            color: var(--muted);
            margin-top: .15rem;
        }

        .mini-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: .3rem;
        }

        .mini-dot.on { background: var(--green); }
        .mini-dot.off { background: var(--muted); opacity: .5; }

        /* ── Detail panel ──────────────────────────────────────────── */
        .gs-detail-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .7rem;
            min-height: 70vh;
        }

        .gs-detail {
            display: none;
            padding: 1.4rem 1.5rem;
        }

        .gs-detail.active { display: block; }

        .detail-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .detail-title-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .detail-title-row h4 {
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0;
            color: var(--ink);
        }

        .badge-pill {
            font-size: .68rem;
            font-weight: 700;
            padding: .2rem .55rem;
            border-radius: 1rem;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .badge-default { background: var(--amber-bg); color: var(--amber); }
        .badge-inactive { background: var(--gray-bg); color: var(--muted); }

        .detail-desc {
            color: var(--muted);
            font-size: .87rem;
            margin: .3rem 0 0;
            max-width: 60ch;
        }

        .detail-actions {
            display: flex;
            gap: .45rem;
            flex-shrink: 0;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border-radius: .5rem;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover { background: var(--bg); color: var(--ink); }
        .icon-btn.danger:hover { background: var(--red-bg); color: var(--red); border-color: var(--red-bg); }

        .detail-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.3rem;
        }

        .detail-stat {
            background: var(--bg);
            border-radius: .5rem;
            padding: .65rem .8rem;
        }

        .detail-stat .label {
            font-size: .68rem;
            color: var(--muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: .02em;
        }

        .detail-stat .value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
        }

        .section-label {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--muted);
            margin-bottom: .5rem;
        }

        .band-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }

        .band-table thead th {
            text-align: left;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--muted);
            font-weight: 700;
            padding: .5rem .6rem;
            border-bottom: 1px solid var(--border);
        }

        .band-table tbody td {
            padding: .55rem .6rem;
            border-bottom: 1px solid var(--border);
            color: var(--ink);
        }

        .band-table tbody tr:last-child td { border-bottom: none; }

        .grade-chip {
            display: inline-block;
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 700;
            font-size: .78rem;
            padding: .18rem .55rem;
            border-radius: .35rem;
        }

        .gs-empty-detail {
            padding: 4rem 1rem;
            text-align: center;
            color: var(--muted);
        }

        .gs-empty-detail i { color: var(--border); }

        /* ── Responsive ────────────────────────────────────────────── */
        @media (max-width: 860px) {
            .gs-stats { grid-template-columns: repeat(2, 1fr); }
            .gs-split { grid-template-columns: 1fr; }
            .gs-scheme-list { max-height: none; }
            .detail-stats { grid-template-columns: repeat(2, 1fr); }
        }

        /* ── Band editor rows (inside the SweetAlert modal) ──────────── */
        .band-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1.6fr 1fr auto;
            gap: .5rem;
            align-items: center;
            margin-bottom: .5rem;
        }

        .band-row input { font-size: .82rem; }

        .gs-swal-confirm { background: var(--primary) !important; font-weight: 600 !important; }

        /* ── Top bar (header card) ────────────────────────────────── */
.gs-topbar {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 50%, #DBEAFE 100%);
    border: 1px solid rgba(44, 41, 202, .12);
    border-radius: 1rem;
    padding: 1.25rem 1.75rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(44, 41, 202, .08);
    transition: all .3s ease;
    position: relative;
    overflow: hidden;
}

/* Subtle decorative background pattern */
.gs-topbar::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(44, 41, 202, .06) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.gs-topbar::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: 20%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(99, 102, 241, .05) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.gs-topbar:hover {
    box-shadow: 0 4px 20px rgba(44, 41, 202, .15);
    border-color: rgba(44, 41, 202, .2);
    transform: translateY(-2px);
}

.gs-topbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
    min-width: 200px;
    position: relative;
    z-index: 1;
}

.gs-topbar-icon-wrapper {
    flex-shrink: 0;
}

.gs-topbar-icon {
    width: 50px;
    height: 50px;
    border-radius: .75rem;
    background: linear-gradient(135deg, var(--primary) 0%, #6366F1 50%, #818CF8 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 12px rgba(44, 41, 202, .3);
    transition: all .3s ease;
}

.gs-topbar-icon:hover {
    transform: scale(1.08) rotate(-5deg);
    box-shadow: 0 6px 24px rgba(44, 41, 202, .4);
}

.gs-topbar-content {
    display: flex;
    flex-direction: column;
    gap: .1rem;
}

.gs-topbar-title {
    font-weight: 800;
    font-size: 1.4rem;
    margin: 0;
    color: var(--ink);
    letter-spacing: -.02em;
    line-height: 1.3;
    background: linear-gradient(135deg, #1B1D28 0%, #2C29CA 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.gs-topbar-subtitle {
    margin: 0;
    font-size: .85rem;
    color: #4B5563;
    line-height: 1.4;
    max-width: 48ch;
}

.gs-topbar-actions {
    display: flex;
    gap: .6rem;
    flex-shrink: 0;
    align-items: center;
    position: relative;
    z-index: 1;
}

.btn-gs-secondary {
    border: 1px solid rgba(44, 41, 202, .15);
    background: rgba(255, 255, 255, .7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: var(--ink);
    font-weight: 600;
    font-size: .82rem;
    padding: .55rem 1rem;
    border-radius: .6rem;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    text-decoration: none;
    transition: all .25s ease;
    white-space: nowrap;
}

.btn-gs-secondary:hover {
    background: rgba(255, 255, 255, .95);
    border-color: rgba(44, 41, 202, .3);
    color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 41, 202, .12);
}

.btn-gs-secondary:active {
    transform: translateY(0);
}

.btn-gs-primary {
    border: none;
    background: linear-gradient(135deg, var(--primary) 0%, #6366F1 50%, #818CF8 100%);
    color: #fff;
    font-weight: 600;
    font-size: .82rem;
    padding: .55rem 1.1rem;
    border-radius: .6rem;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    transition: all .25s ease;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(44, 41, 202, .25);
}

.btn-gs-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(44, 41, 202, .35);
    background: linear-gradient(135deg, #201ea0 0%, #4F46E5 50%, #6366F1 100%);
}

.btn-gs-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(44, 41, 202, .2);
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 640px) {
    .gs-topbar {
        padding: 1rem 1.25rem;
        flex-direction: column;
        align-items: stretch;
    }

    .gs-topbar-left {
        gap: .75rem;
    }

    .gs-topbar-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .gs-topbar-title {
        font-size: 1.1rem;
    }

    .gs-topbar-subtitle {
        font-size: .78rem;
        max-width: 100%;
    }

    .gs-topbar-actions {
        flex-wrap: wrap;
        justify-content: stretch;
        gap: .5rem;
        width: 100%;
    }

    .gs-topbar-actions .btn-gs-secondary,
    .gs-topbar-actions .btn-gs-primary {
        flex: 1;
        justify-content: center;
        min-width: 120px;
        font-size: .78rem;
        padding: .5rem .8rem;
    }

    .btn-gs-secondary span,
    .btn-gs-primary span {
        display: inline;
    }
}

@media (max-width: 400px) {
    .gs-topbar-actions .btn-gs-secondary span,
    .gs-topbar-actions .btn-gs-primary span {
        display: none;
    }

    .gs-topbar-actions .btn-gs-secondary,
    .gs-topbar-actions .btn-gs-primary {
        flex: 1;
        justify-content: center;
        min-width: auto;
    }
}

/* ── Professional Table Styles ────────────────────────────── */

/* Table container with rounded corners and shadow */
.gs-detail-panel .table-wrapper {
    background: var(--surface);
    border-radius: .75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(27, 29, 40, .04);
}

/* Enhanced band-table styling */
.band-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: .85rem;
    background: var(--surface);
    border-radius: .75rem;
    overflow: hidden;
}

/* Table header with gradient */
.band-table thead {
    background: linear-gradient(135deg, #F8F7FF 0%, #EEEDFC 100%);
}

.band-table thead th {
    text-align: left;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
    font-weight: 700;
    padding: .75rem .8rem;
    border-bottom: 2px solid var(--primary-light);
    position: relative;
}

.band-table thead th:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background: rgba(44, 41, 202, .08);
}

/* Table rows with hover effects */
.band-table tbody tr {
    transition: all .2s ease;
    border-bottom: 1px solid #F3F4F6;
}

.band-table tbody tr:hover {
    background: #F8F7FF;
    transform: translateX(2px);
}

.band-table tbody tr:last-child {
    border-bottom: none;
}

.band-table tbody td {
    padding: .65rem .8rem;
    color: var(--ink);
    font-weight: 500;
    vertical-align: middle;
}

/* Alternating row colors (zebra stripes) */
.band-table tbody tr:nth-child(even) {
    background: #FAFAFE;
}

.band-table tbody tr:nth-child(even):hover {
    background: #F5F4FF;
}

/* Enhanced grade chips */
.grade-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: linear-gradient(135deg, var(--primary-soft) 0%, #EEEDFC 100%);
    color: var(--primary);
    font-weight: 700;
    font-size: .78rem;
    padding: .25rem .65rem;
    border-radius: .4rem;
    border: 1px solid rgba(44, 41, 202, .08);
    transition: all .2s ease;
}

.grade-chip::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--primary);
    opacity: .3;
}

.grade-chip:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(44, 41, 202, .15);
}

/* Grade colors for different ranges */
.grade-chip.grade-distinction {
    background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
    color: #065F46;
    border-color: rgba(6, 95, 70, .15);
}

.grade-chip.grade-distinction::before {
    background: #065F46;
    opacity: .4;
}

.grade-chip.grade-credit {
    background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
    color: #1E40AF;
    border-color: rgba(30, 64, 175, .15);
}

.grade-chip.grade-credit::before {
    background: #1E40AF;
    opacity: .4;
}

.grade-chip.grade-pass {
    background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
    color: #92400E;
    border-color: rgba(146, 64, 14, .15);
}

.grade-chip.grade-pass::before {
    background: #92400E;
    opacity: .4;
}

.grade-chip.grade-fail {
    background: linear-gradient(135deg, #FEE2E2 0%, #FCA5A5 100%);
    color: #991B1B;
    border-color: rgba(153, 27, 27, .15);
}

.grade-chip.grade-fail::before {
    background: #991B1B;
    opacity: .4;
}

/* Range values styling */
.band-table tbody td:not(:first-child):not(:last-child) {
    font-weight: 600;
    color: var(--ink);
}

.band-table tbody td:first-child {
    font-weight: 600;
}

/* Points column styling */
.band-table tbody td:last-child {
    font-weight: 700;
    color: var(--primary);
    text-align: center;
}

/* Status indicators with icons */
.band-table .status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: .5rem;
}

.band-table .status-dot.active {
    background: var(--green);
    box-shadow: 0 0 8px rgba(18, 135, 90, .3);
}

.band-table .status-dot.inactive {
    background: #FFF;
    color:#FFF;
}

/* Detail panel enhancements */
.gs-detail-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: .8rem;
    min-height: 70vh;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(27, 29, 40, .04);
}

.gs-detail {
    display: none;
    padding: 1.5rem 1.75rem;
}

.gs-detail.active {
    display: block;
    animation: fadeSlideIn .3s ease;
}

@keyframes fadeSlideIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Section label with icon */
.section-label {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
    margin-bottom: .75rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}

.section-label::before {
    content: '';
    width: 3px;
    height: 16px;
    background: var(--primary);
    border-radius: 99px;
}

/* Detail stats cards enhancement */
.detail-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .75rem;
    margin-bottom: 1.5rem;
}

.detail-stat {
    background: linear-gradient(135deg, #F8F7FF 0%, #F3F1FF 100%);
    border-radius: .6rem;
    padding: .7rem .9rem;
    border: 1px solid rgba(44, 41, 202, .06);
    transition: all .2s ease;
}

.detail-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 41, 202, .08);
}

.detail-stat .label {
    font-size: .65rem;
    color: var(--muted);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: .04em;
}

.detail-stat .value {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--ink);
    margin-top: .1rem;
}

/* Detail top section */
.detail-top {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: .75rem;
    border-bottom: 2px solid #F3F4F6;
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
}

.detail-title-row h4 {
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0;
    color: var(--ink);
    letter-spacing: -.02em;
}

.detail-title-row h4::before {
    content: '\f5fd';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    margin-right: .5rem;
    font-size: 1rem;
    color: var(--primary);
}

/* Badge pills enhancement */
.badge-pill {
    font-size: .65rem;
    font-weight: 700;
    padding: .25rem .65rem;
    border-radius: 99px;
    text-transform: uppercase;
    letter-spacing: .04em;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
}

.badge-pill::before {
    content: '';
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
    opacity: .5;
}

.badge-default {
    background: var(--amber-bg);
    color: var(--amber);
    border: 1px solid rgba(180, 119, 11, .15);
}

.badge-inactive {
    background: red;
    color: #FFF;
    border: 1px solid rgba(107, 114, 128, .15);
}

.badge-active {
    background: var(--green-bg);
    color: var(--green);
    border: 1px solid rgba(18, 135, 90, .15);
}

/* List panel enhancements */
.gs-list-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: .8rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(27, 29, 40, .04);
}

.gs-list-panel-head {
    padding: .85rem 1rem;
    border-bottom: 2px solid #F3F4F6;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.gs-list-panel-head .count-badge {
    background: var(--primary-light);
    color: var(--primary);
    padding: .1rem .6rem;
    border-radius: 99px;
    font-size: .7rem;
}

.gs-scheme-list {
    list-style: none;
    margin: 0;
    padding: .5rem;
    max-height: 70vh;
    overflow-y: auto;
}

.scheme-list-item {
    display: block;
    width: 100%;
    text-align: left;
    border: 1px solid transparent;
    background: transparent;
    border-radius: .6rem;
    padding: .7rem .8rem;
    margin-bottom: .2rem;
    cursor: pointer;
    transition: all .2s ease;
}

.scheme-list-item:hover {
    background: #F8F7FF;
    border-color: rgba(44, 41, 202, .08);
    transform: translateX(4px);
}

.scheme-list-item.active {
    background: linear-gradient(135deg, var(--primary-light) 0%, #F0EDFF 100%);
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(44, 41, 202, .08);
}

.scheme-list-item .name {
    font-weight: 700;
    font-size: .88rem;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: .4rem;
}

.scheme-list-item .name i.fa-star {
    color: var(--amber);
    font-size: .7rem;
    animation: starPulse 2s ease-in-out infinite;
}

@keyframes starPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.scheme-list-item .sub {
    font-size: .72rem;
    color: var(--muted);
    margin-top: .15rem;
    display: flex;
    align-items: center;
    gap: .3rem;
}

/* Scrollbar styling for scheme list */
.gs-scheme-list::-webkit-scrollbar {
    width: 4px;
}

.gs-scheme-list::-webkit-scrollbar-track {
    background: #F3F4F6;
    border-radius: 99px;
}

.gs-scheme-list::-webkit-scrollbar-thumb {
    background: var(--primary-light);
    border-radius: 99px;
}

.gs-scheme-list::-webkit-scrollbar-thumb:hover {
    background: var(--primary);
}

/* ── Responsive Table ────────────────────────────────────────── */
@media (max-width: 768px) {
    .band-table {
        font-size: .78rem;
    }

    .band-table thead th,
    .band-table tbody td {
        padding: .5rem .6rem;
    }

    .detail-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .detail-top {
        flex-direction: column;
        align-items: stretch;
    }

    .detail-actions {
        justify-content: flex-end;
    }

    .grade-chip {
        font-size: .7rem;
        padding: .2rem .5rem;
    }
}

@media (max-width: 480px) {
    .band-table {
        font-size: .7rem;
    }

    .band-table thead th,
    .band-table tbody td {
        padding: .4rem .4rem;
    }

    .detail-stats {
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
    }

    .detail-stat .value {
        font-size: .95rem;
    }

    .gs-detail {
        padding: 1rem;
    }
}

.band-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .85rem;
    border-radius: .75rem;
    overflow: hidden;
}

.band-table thead {
    background: #2C29CA;
}

.band-table thead th {
    text-align: left;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #ffffff;
    font-weight: 700;
    padding: .75rem .8rem;
    border-bottom: 2px solid rgba(255, 255, 255, .15);
}

.band-table thead th:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background: rgba(255, 255, 255, .15);
}

.band-table tbody tr {
    transition: all .2s ease;
    border-bottom: 1px solid #F3F4F6;
}

.band-table tbody tr:hover {
    background: #F8F7FF;
    transform: translateX(2px);
}

.band-table tbody tr:last-child {
    border-bottom: none;
}

.band-table tbody td {
    padding: .65rem .8rem;
    color: var(--ink);
    font-weight: 500;
    vertical-align: middle;
}

.band-table tbody tr:nth-child(even) {
    background: #FAFAFE;
}

.band-table tbody tr:nth-child(even):hover {
    background: #F5F4FF;
}

.grade-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: linear-gradient(135deg, var(--primary-soft) 0%, #EEEDFC 100%);
    color: var(--primary);
    font-weight: 700;
    font-size: .78rem;
    padding: .25rem .65rem;
    border-radius: .4rem;
    border: 1px solid rgba(44, 41, 202, .08);
    transition: all .2s ease;
}

.grade-chip::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--primary);
    opacity: .3;
}

.grade-chip:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(44, 41, 202, .15);
}

/* Grade chip variants */
.grade-chip.grade-distinction {
    background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
    color: #065F46;
    border-color: rgba(6, 95, 70, .15);
}

.grade-chip.grade-distinction::before {
    background: #065F46;
    opacity: .4;
}

.grade-chip.grade-credit {
    background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
    color: #1E40AF;
    border-color: rgba(30, 64, 175, .15);
}

.grade-chip.grade-credit::before {
    background: #1E40AF;
    opacity: .4;
}

.grade-chip.grade-pass {
    background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
    color: #92400E;
    border-color: rgba(146, 64, 14, .15);
}

.grade-chip.grade-pass::before {
    background: #92400E;
    opacity: .4;
}

.grade-chip.grade-fail {
    background: linear-gradient(135deg, #FEE2E2 0%, #FCA5A5 100%);
    color: #991B1B;
    border-color: rgba(153, 27, 27, .15);
}

.grade-chip.grade-fail::before {
    background: #991B1B;
    opacity: .4;
}

.band-table tbody td:first-child {
    font-weight: 600;
}

.band-table tbody td:last-child {
    font-weight: 700;
    color: var(--primary);
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .band-table {
        font-size: .78rem;
    }

    .band-table thead th,
    .band-table tbody td {
        padding: .5rem .6rem;
    }

    .grade-chip {
        font-size: .7rem;
        padding: .2rem .5rem;
    }
}

@media (max-width: 480px) {
    .band-table {
        font-size: .7rem;
    }

    .band-table thead th,
    .band-table tbody td {
        padding: .4rem .4rem;
    }
}

/* Edit button - primary color */
.icon-btn.edit-scheme {
    color: #2C29CA;
    border-color: rgba(44, 41, 202, 0.2);
}

.icon-btn.edit-scheme:hover {
    background: rgba(44, 41, 202, 0.1);
    color: #2C29CA;
    border-color: #2C29CA;
}

/* Toggle button - green when active, gray when inactive */
.icon-btn.toggle-scheme[data-active="1"] {
    color: #12875A;
    border-color: rgba(18, 135, 90, 0.2);
}

.icon-btn.toggle-scheme[data-active="1"]:hover {
    background: rgba(18, 135, 90, 0.1);
    color: #12875A;
    border-color: #12875A;
}

.icon-btn.toggle-scheme[data-active="0"] {
    color: #6B7280;
    border-color: rgba(107, 114, 128, 0.2);
}

.icon-btn.toggle-scheme[data-active="0"]:hover {
    background: rgba(107, 114, 128, 0.1);
    color: #6B7280;
    border-color: #6B7280;
}

/* Delete button - red */
.icon-btn.danger.delete-scheme {
    color: #C4293A;
    border-color: rgba(196, 41, 58, 0.2);
}

.icon-btn.danger.delete-scheme:hover {
    background: rgba(196, 41, 58, 0.1);
    color: #C4293A;
    border-color: #C4293A;
}
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="gs-app">

{{-- ── Top bar ───────────────────────────────────────────── --}}
<div class="gs-topbar-v2">
    <div class="gs-topbar-v2-particles"></div>
    <div class="gs-topbar-v2-container">
        <div class="gs-topbar-v2-left">
            <div class="gs-topbar-v2-icon-wrapper">
                <div class="gs-topbar-v2-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>
            </div>
            <div class="gs-topbar-v2-content">
                <div class="gs-topbar-v2-badge">
                    <i class="fas fa-layer-group"></i>
                    <span>Grading Management</span>
                </div>
                <h3 class="gs-topbar-v2-title">Grading Schemes</h3>
                <p class="gs-topbar-v2-subtitle">Define how marks translate into grades — pick a scheme whenever you create an examination.</p>
            </div>
        </div>
        <div class="gs-topbar-v2-actions">
            <a href="{{ route('examination.create') }}" class="btn-gs-v2-glass">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Create Exam</span>
            </a>
            <div class="gs-topbar-v2-action-divider"></div>
            <button type="button" id="btnNewScheme" class="btn-gs-v2-gradient">
                <i class="fas fa-plus-circle"></i>
                <span>New Grading Scheme</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* ── Breadcrumb ────────────────────────────────────────────── */
.gs-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .4rem;
    font-size: .78rem;
    font-weight: 600;
    margin-bottom: .85rem;
}

.gs-breadcrumb a {
    color: var(--muted);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .2rem .1rem;
    border-radius: .3rem;
    transition: color .2s ease;
}

.gs-breadcrumb a:hover {
    color: var(--primary);
}

.gs-breadcrumb .crumb-current {
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}

.gs-breadcrumb i.fa-chevron-right {
    font-size: .6rem;
    color: var(--border);
}

.gs-breadcrumb i.fa-house,
.gs-breadcrumb i.fa-home {
    font-size: .75rem;
}

/* ── Top bar V2: Premium Dark Gradient with Glow ────────────── */
.gs-topbar-v2 {
    background: linear-gradient(135deg, #0F0E1A 0%, #1B1D28 40%, #2C29CA 100%);
    border-radius: 1.25rem;
    padding: 0;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(44, 41, 202, .2);
}

/* Animated particles background */
.gs-topbar-v2-particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,.1), transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,.08), transparent),
        radial-gradient(2px 2px at 50px 160px, rgba(255,255,255,.12), transparent),
        radial-gradient(2px 2px at 90px 40px, rgba(255,255,255,.06), transparent),
        radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,.1), transparent),
        radial-gradient(2px 2px at 160px 30px, rgba(255,255,255,.08), transparent);
    background-size: 200px 200px;
    opacity: 0.5;
    pointer-events: none;
    animation: gsParticleMove 20s linear infinite;
}

@keyframes gsParticleMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-20px, -20px); }
}

/* Decorative glow elements */
.gs-topbar-v2::before {
    content: '';
    position: absolute;
    top: -30%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(99, 102, 241, .15) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.gs-topbar-v2::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -5%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(44, 41, 202, .1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.gs-topbar-v2-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    padding: 1.75rem 2.5rem;
    position: relative;
    z-index: 1;
}

.gs-topbar-v2-left {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    flex: 1;
    min-width: 200px;
}

.gs-topbar-v2-icon-wrapper {
    flex-shrink: 0;
    margin-top: .2rem;
}

.gs-topbar-v2-icon {
    width: 54px;
    height: 54px;
    border-radius: .75rem;
    background: linear-gradient(135deg, #818CF8 0%, #6366F1 50%, #4F46E5 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    box-shadow: 0 4px 16px rgba(99, 102, 241, .3);
    transition: all .3s ease;
}

.gs-topbar-v2-icon:hover {
    transform: scale(1.08) rotate(-5deg);
    box-shadow: 0 6px 24px rgba(99, 102, 241, .4);
}

.gs-topbar-v2-content {
    display: flex;
    flex-direction: column;
    gap: .15rem;
    flex: 1;
}

.gs-topbar-v2-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .08);
    color: rgba(255, 255, 255, .7);
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    padding: .25rem .9rem;
    border-radius: 99px;
    backdrop-filter: blur(10px);
    width: fit-content;
    margin-bottom: .2rem;
    transition: all .3s ease;
}

.gs-topbar-v2-badge:hover {
    background: rgba(255, 255, 255, .1);
    border-color: rgba(255, 255, 255, .15);
}

.gs-topbar-v2-badge i {
    color: #818CF8;
    font-size: .55rem;
}

.gs-topbar-v2-title {
    font-weight: 800;
    font-size: 1.5rem;
    margin: 0;
    color: #ffffff;
    letter-spacing: -.02em;
    line-height: 1.2;
}

.gs-topbar-v2-subtitle {
    margin: 0;
    font-size: .85rem;
    color: rgba(255, 255, 255, .6);
    line-height: 1.4;
    max-width: 48ch;
}

.gs-topbar-v2-actions {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-shrink: 0;
    flex-wrap: wrap;
}

.gs-topbar-v2-action-divider {
    width: 1px;
    height: 30px;
    background: rgba(255, 255, 255, .1);
}

.btn-gs-v2-glass {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .6rem 1.2rem;
    background: rgba(255, 255, 255, .06);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, .08);
    color: rgba(255, 255, 255, .8);
    font-weight: 600;
    font-size: .82rem;
    border-radius: .6rem;
    text-decoration: none;
    transition: all .25s ease;
    white-space: nowrap;
}

.btn-gs-v2-glass:hover {
    background: rgba(255, 255, 255, .12);
    color: #ffffff;
    text-decoration: none;
    transform: translateY(-2px);
    border-color: rgba(255, 255, 255, .15);
    box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
}

.btn-gs-v2-glass:active {
    transform: translateY(0);
}

.btn-gs-v2-gradient {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .6rem 1.4rem;
    background: linear-gradient(135deg, #818CF8 0%, #6366F1 50%, #4F46E5 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: .82rem;
    border: none;
    border-radius: .6rem;
    text-decoration: none;
    transition: all .25s ease;
    box-shadow: 0 4px 20px rgba(99, 102, 241, .3);
    cursor: pointer;
    white-space: nowrap;
}

.btn-gs-v2-gradient:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 28px rgba(99, 102, 241, .4);
    color: #ffffff;
}

.btn-gs-v2-gradient:active {
    transform: translateY(0) scale(1);
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 768px) {
    .gs-topbar-v2-container {
        flex-direction: column;
        align-items: stretch;
        padding: 1.5rem 1.5rem;
        gap: 1.25rem;
    }

    .gs-topbar-v2-left {
        flex-direction: column;
        align-items: flex-start;
        gap: .75rem;
    }

    .gs-topbar-v2-icon {
        width: 44px;
        height: 44px;
        font-size: 1.1rem;
    }

    .gs-topbar-v2-title {
        font-size: 1.25rem;
    }

    .gs-topbar-v2-subtitle {
        font-size: .8rem;
        max-width: 100%;
    }

    .gs-topbar-v2-actions {
        width: 100%;
        flex-direction: column;
    }

    .gs-topbar-v2-action-divider {
        width: 100%;
        height: 1px;
        background: rgba(255, 255, 255, .06);
    }

    .btn-gs-v2-glass,
    .btn-gs-v2-gradient {
        width: 100%;
        justify-content: center;
        padding: .6rem 1.2rem;
    }

    .btn-gs-v2-glass span,
    .btn-gs-v2-gradient span {
        display: inline;
    }
}

@media (max-width: 480px) {
    .gs-topbar-v2-container {
        padding: 1.25rem 1.25rem;
    }

    .gs-topbar-v2-title {
        font-size: 1.1rem;
    }

    .gs-topbar-v2-badge {
        font-size: .6rem;
        padding: .2rem .7rem;
    }

    .btn-gs-v2-glass span,
    .btn-gs-v2-gradient span {
        display: none;
    }

    .btn-gs-v2-glass,
    .btn-gs-v2-gradient {
        justify-content: center;
        min-width: auto;
    }
}
</style>

            @php
                $totalSchemes = $schemes->count();
                $activeSchemes = $schemes->where('is_active', true)->count();
                $defaultScheme = $schemes->firstWhere('is_default', true);
                $examsCovered = $schemes->sum('examinations_count');
            @endphp

            {{-- ── Stat strip ────────────────────────────────────────── --}}
            <div class="gs-stats">
                <div class="gs-stat">
                    <div class="label">Total Schemes</div>
                    <div class="value">{{ $totalSchemes }}</div>
                </div>
                <div class="gs-stat">
                    <div class="label">Active</div>
                    <div class="value">{{ $activeSchemes }} <small>/ {{ $totalSchemes }}</small></div>
                </div>
                <div class="gs-stat accent">
                    <div class="label">Default Scheme</div>
                    <div class="value" style="font-size:.95rem;">{{ $defaultScheme->name ?? 'None set' }}</div>
                </div>
                <div class="gs-stat">
                    <div class="label">Exams Covered</div>
                    <div class="value">{{ $examsCovered }}</div>
                </div>
            </div>

            {{-- ── Split: list + detail ─────────────────────────────── --}}
            @if ($schemes->count())
                <div class="gs-split">
                    <div class="gs-list-panel">
                        <div class="gs-list-panel-head">Schemes ({{ $totalSchemes }})</div>
                        <ul class="gs-scheme-list" id="schemeList">
                            @foreach ($schemes as $scheme)
                                <li>
                                    <button type="button" class="scheme-list-item {{ $loop->first ? 'active' : '' }}"
                                        data-target="scheme-detail-{{ $scheme->id }}">
                                        <div class="name">
                                            @if ($scheme->is_default)
                                                <i class="fas fa-star"></i>
                                            @endif
                                            {{ $scheme->name }}
                                        </div>
                                        <div class="sub">
                                            <span class="mini-dot {{ $scheme->is_active ? 'on' : 'off' }}"></span>
                                            {{ $scheme->is_active ? 'Active' : 'Inactive' }} &bull;
                                            {{ $scheme->examinations_count }} exam(s)
                                        </div>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="gs-detail-panel">
                        @foreach ($schemes as $scheme)
                            @php
                                $passPct = round(($scheme->pass_mark / max($scheme->total_marks, 1)) * 100);
                            @endphp
                            <div class="gs-detail {{ $loop->first ? 'active' : '' }}" id="scheme-detail-{{ $scheme->id }}">
                                <div class="detail-top">
                                    <div>
                                        <div class="detail-title-row">
                                            <h4>{{ $scheme->name }}</h4>
                                            @if ($scheme->is_default)
                                                <span class="badge-pill badge-default">Default</span>
                                            @endif
                                            @if (!$scheme->is_active)
                                                <span class="badge-pill badge-inactive">Inactive</span>
                                            @endif
                                        </div>
                                        @if ($scheme->description)
                                            <p class="detail-desc">{{ $scheme->description }}</p>
                                        @endif
                                    </div>
                                    <div class="detail-actions">
                                        <button type="button" class="icon-btn edit-scheme" data-id="{{ $scheme->id }}"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                       <button type="button" class="icon-btn toggle-scheme" data-id="{{ $scheme->id }}"
    data-active="{{ $scheme->is_active ? 1 : 0 }}"
    title="{{ $scheme->is_active ? 'Deactivate' : 'Activate' }}">
    <i class="fas fa-power-off"></i>
</button>
                                        <button type="button" class="icon-btn danger delete-scheme"
                                            data-id="{{ $scheme->id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="detail-stats">
                                    <div class="detail-stat">
                                        <div class="label">Total Marks</div>
                                        <div class="value">{{ $scheme->total_marks }}</div>
                                    </div>
                                    <div class="detail-stat">
                                        <div class="label">Pass Mark</div>
                                        <div class="value">{{ $scheme->pass_mark }}</div>
                                    </div>
                                    <div class="detail-stat">
                                        <div class="label">Pass %</div>
                                        <div class="value">{{ $passPct }}%</div>
                                    </div>
                                    <div class="detail-stat">
                                        <div class="label">Exams Using</div>
                                        <div class="value">{{ $scheme->examinations_count }}</div>
                                    </div>
                                </div>

                                <div class="section-label">Grade Bands</div>
                                <table class="band-table">
                                    <thead>
                                        <tr>
                                            <th>Grade</th>
                                            <th>Range</th>
                                            <th>Remark</th>
                                            <th>Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($scheme->bands as $band)
                                            <tr>
                                                <td><span class="grade-chip">{{ $band->grade }}</span></td>
                                                <td>
                                                    {{ rtrim(rtrim(number_format($band->min_mark, 1), '0'), '.') }}–{{ rtrim(rtrim(number_format($band->max_mark, 1), '0'), '.') }}%
                                                </td>
                                                <td>{{ $band->remark ?? '—' }}</td>
                                                <td>{{ $band->points ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="gs-list-panel">
                    <div class="gs-empty-detail">
                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                        No grading schemes yet. Click "New Grading Scheme" to create your first one.
                    </div>
                </div>
            @endif

        </div>
    </div>
     </div>
    </div>

    {{-- ── Create / Edit Modal (hidden template, injected via SweetAlert) ── --}}
    <template id="schemeFormTemplate">
        <form id="schemeForm" style="text-align:left;">
            <div class="row g-2 mb-2">
                <div class="col-md-8">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">Scheme Name *</label>
                    <input type="text" name="name" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">Set as Default</label><br>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1">
                        <label class="form-check-label" style="font-size:.8rem;">Pre-select on new exams</label>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label fw-semibold" style="font-size:.82rem;">Description</label>
                <input type="text" name="description" class="form-control form-control-sm">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">Total Marks (out of) *</label>
                    <input type="number" name="total_marks" class="form-control form-control-sm" min="1" max="1000"
                        value="100" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">Pass Mark *</label>
                    <input type="number" name="pass_mark" class="form-control form-control-sm" min="1" value="50" required>
                </div>
            </div>

            <label class="form-label fw-semibold" style="font-size:.82rem;">
                Grade Bands (percentage of total marks) *
            </label>
            <div class="band-row" style="font-size:.72rem; font-weight:700; color:#6c757d;">
                <div>Grade</div>
                <div>Min %</div>
                <div>Max %</div>
                <div>Remark</div>
                <div>Points</div>
                <div></div>
            </div>
            <div id="bandRows"></div>
            <button type="button" id="addBandRow" class="btn btn-sm btn-outline-primary mt-1"
                style="border-radius:.5rem; font-size:.78rem;">
                <i class="fas fa-plus me-1"></i> Add Band
            </button>
        </form>
    </template>

    {{-- Row template for a single band --}}
    <template id="bandRowTemplate">
        <div class="band-row">
            <input type="text" class="form-control form-control-sm band-grade" placeholder="D1">
            <input type="number" step="0.1" class="form-control form-control-sm band-min" placeholder="80">
            <input type="number" step="0.1" class="form-control form-control-sm band-max" placeholder="100">
            <input type="text" class="form-control form-control-sm band-remark" placeholder="Distinction">
            <input type="number" step="0.1" class="form-control form-control-sm band-points" placeholder="1">
            <button type="button" class="btn btn-sm btn-outline-danger remove-band-row"><i
                    class="fas fa-times"></i></button>
        </div>
    </template>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';

        // ── List ↔ detail switching ──────────────────────────────────────────
        document.querySelectorAll('.scheme-list-item').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.scheme-list-item').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.gs-detail').forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.dataset.target)?.classList.add('active');
            });
        });

        function addBandRow(container, band = null) {
            const tpl = document.getElementById('bandRowTemplate').content.cloneNode(true);
            const row = tpl.querySelector('.band-row');
            if (band) {
                row.querySelector('.band-grade').value = band.grade ?? '';
                row.querySelector('.band-min').value = band.min_mark ?? '';
                row.querySelector('.band-max').value = band.max_mark ?? '';
                row.querySelector('.band-remark').value = band.remark ?? '';
                row.querySelector('.band-points').value = band.points ?? '';
            }
            row.querySelector('.remove-band-row').addEventListener('click', () => row.remove());
            container.appendChild(row);
        }

        function collectBands(container) {
            const bands = [];
            container.querySelectorAll('.band-row').forEach(row => {
                const grade = row.querySelector('.band-grade').value.trim();
                const min = row.querySelector('.band-min').value;
                const max = row.querySelector('.band-max').value;
                if (!grade || min === '' || max === '') return;
                bands.push({
                    grade,
                    min_mark: parseFloat(min),
                    max_mark: parseFloat(max),
                    remark: row.querySelector('.band-remark').value.trim() || null,
                    points: row.querySelector('.band-points').value || null,
                });
            });
            return bands;
        }

        function openSchemeModal(existing = null) {
            const isEdit = !!existing;

            Swal.fire({
                title: isEdit ? 'Edit Grading Scheme' : 'New Grading Scheme',
                html: document.getElementById('schemeFormTemplate').innerHTML,
                width: 700,
                showCancelButton: true,
                confirmButtonText: isEdit ? 'Save Changes' : 'Create Scheme',
                confirmButtonColor: '#2C29CA',
                customClass: { confirmButton: 'gs-swal-confirm' },
                focusConfirm: false,
                didOpen: () => {
                    const popup = Swal.getPopup();
                    const container = document.getElementById('bandRows');

                    if (isEdit) {
                        popup.querySelector('[name="name"]').value = existing.name;
                        popup.querySelector('[name="description"]').value = existing.description ?? '';
                        popup.querySelector('[name="total_marks"]').value = existing.total_marks;
                        popup.querySelector('[name="pass_mark"]').value = existing.pass_mark;
                        popup.querySelector('[name="is_default"]').checked = !!existing.is_default;
                        (existing.bands || []).forEach(b => addBandRow(container, b));
                    } else {
                        // Sensible starting point: standard 9-point scale
                        [
                            { grade: 'D1', min_mark: 80, max_mark: 100, remark: 'Distinction', points: 1 },
                            { grade: 'D2', min_mark: 75, max_mark: 79, remark: 'Distinction', points: 2 },
                            { grade: 'C3', min_mark: 70, max_mark: 74, remark: 'Credit', points: 3 },
                            { grade: 'C4', min_mark: 65, max_mark: 69, remark: 'Credit', points: 4 },
                            { grade: 'C5', min_mark: 60, max_mark: 64, remark: 'Credit', points: 5 },
                            { grade: 'C6', min_mark: 55, max_mark: 59, remark: 'Credit', points: 6 },
                            { grade: 'P7', min_mark: 45, max_mark: 54, remark: 'Pass', points: 7 },
                            { grade: 'P8', min_mark: 40, max_mark: 44, remark: 'Pass', points: 8 },
                            { grade: 'F9', min_mark: 0, max_mark: 39, remark: 'Fail', points: 9 },
                        ].forEach(b => addBandRow(container, b));
                    }

                    document.getElementById('addBandRow').addEventListener('click', () => addBandRow(container));
                },
                preConfirm: () => {
                    const popup = Swal.getPopup();
                    const name = popup.querySelector('[name="name"]').value.trim();
                    const totalMarks = popup.querySelector('[name="total_marks"]').value;
                    const passMark = popup.querySelector('[name="pass_mark"]').value;
                    const bands = collectBands(document.getElementById('bandRows'));

                    if (!name) {
                        Swal.showValidationMessage('Please enter a scheme name.');
                        return false;
                    }
                    if (!totalMarks || !passMark) {
                        Swal.showValidationMessage('Please enter total marks and pass mark.');
                        return false;
                    }
                    if (parseInt(passMark) > parseInt(totalMarks)) {
                        Swal.showValidationMessage('Pass mark cannot exceed total marks.');
                        return false;
                    }
                    if (bands.length < 1) {
                        Swal.showValidationMessage('Add at least one grade band.');
                        return false;
                    }

                    return {
                        name,
                        description: popup.querySelector('[name="description"]').value.trim() || null,
                        total_marks: totalMarks,
                        pass_mark: passMark,
                        is_default: popup.querySelector('[name="is_default"]').checked ? 1 : 0,
                        bands,
                    };
                },
            }).then(result => {
                if (!result.isConfirmed) return;

                const url = isEdit
                    ? `{{ url('examinations/grading-schemes') }}/${existing.id}/update`
                    : `{{ route('examination.grading-schemes.store') }}`;

                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(result.value),
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, confirmButtonColor: '#2C29CA' })
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message
                            || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).map(e => e[0]).join('\n') : 'Something went wrong.');
                        Swal.fire('Error', message, 'error');
                    },
                });
            });
        }

        document.getElementById('btnNewScheme').addEventListener('click', () => openSchemeModal());

        // ── Edit ──────────────────────────────────────────────────────────────
        document.querySelectorAll('.edit-scheme').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.dataset.id;
                // Scheme + band data is embedded server-side below, so no extra
                // request is needed to populate the edit modal.
                const scheme = window.__schemesById[id];
                openSchemeModal(scheme);
            });
        });

        // ── Toggle Active ─────────────────────────────────────────────────────
// ── Toggle Active with SweetAlert Confirmation ────────────────────────────
document.querySelectorAll('.toggle-scheme').forEach(el => {
    el.addEventListener('click', function (e) {
        e.preventDefault();
        const id = this.dataset.id;
        const isActive = this.dataset.active === '1';
        const action = isActive ? 'deactivate' : 'activate';
        const actionText = isActive ? 'Deactivate' : 'Activate';
        const iconColor = isActive ? '#C4293A' : '#12875A';
        
        Swal.fire({
            title: `${actionText} Grading Scheme?`,
            text: `Are you sure you want to ${action} this grading scheme? ${isActive ? 'Inactive schemes cannot be used for new exams.' : 'Active schemes can be used for new exams.'}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionText} it`,
            cancelButtonText: 'Cancel',
            confirmButtonColor: iconColor,
            cancelButtonColor: '#6B7280',
        }).then(result => {
            if (!result.isConfirmed) return;
            
            const nextActive = isActive ? 0 : 1;
            $.ajax({
                url: `{{ url('examinations/grading-schemes') }}/${id}/toggle-active`,
                method: 'POST',
                data: { is_active: nextActive },
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: `Scheme has been ${action}ed successfully.`,
                        confirmButtonColor: '#2C29CA',
                        timer: 2000,
                        timerProgressBar: true,
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Could not update scheme status.',
                        confirmButtonColor: '#C4293A',
                    });
                },
            });
        });
    });
});

        // ── Delete ────────────────────────────────────────────────────────────
        document.querySelectorAll('.delete-scheme').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Delete this grading scheme?',
                    text: 'This cannot be undone. Schemes already used by an exam cannot be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    confirmButtonColor: '#C4293A',
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: `{{ url('examinations/grading-schemes') }}/${id}`,
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({ icon: 'success', title: 'Deleted', confirmButtonColor: '#2C29CA' })
                                    .then(() => window.location.reload());
                            } else {
                                Swal.fire('Cannot Delete', res.message, 'warning');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Cannot Delete', xhr.responseJSON?.message || 'Could not delete scheme.', 'warning');
                        },
                    });
                });
            });
        });
    </script>

    {{-- Build schemes data in PHP for JavaScript --}}
    @php
        $schemesData = [];
        foreach ($schemes as $scheme) {
            $schemesData[$scheme->id] = [
                'id' => $scheme->id,
                'name' => $scheme->name,
                'description' => $scheme->description,
                'total_marks' => $scheme->total_marks,
                'pass_mark' => $scheme->pass_mark,
                'is_default' => $scheme->is_default,
                'bands' => $scheme->bands->map(fn($b) => [
                    'grade' => $b->grade,
                    'min_mark' => $b->min_mark,
                    'max_mark' => $b->max_mark,
                    'remark' => $b->remark,
                    'points' => $b->points,
                ])->toArray()
            ];
        }
    @endphp

    <script>
        // Embed the full scheme+bands payload so "Edit" can populate the
        // modal without another round trip.
        window.__schemesById = {!! json_encode($schemesData) !!};
    </script>

@endsection