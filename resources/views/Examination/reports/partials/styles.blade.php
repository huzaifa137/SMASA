{{-- Shared design tokens + components for the Reports & Summaries module. --}}
<style>
    :root {
        --rpt-brand: #2C29CA;
        --rpt-brand-mid: #5351e4;
        --rpt-brand-light: #7c7aec;
        --rpt-brand-pale: #ede9ff;
        --rpt-brand-ultra: #f8f7ff;
        --rpt-success: #10b981;
        --rpt-warning: #f59e0b;
        --rpt-danger: #ef4444;
        --rpt-radius-lg: 1.25rem;
        --rpt-radius-md: 0.875rem;
        --rpt-radius-sm: 0.5rem;
        --rpt-shadow: 0 4px 24px rgba(44, 41, 202, 0.10);
    }

    .rpt-hero {
        background: linear-gradient(135deg, #2C29CA 0%, #5351e4 55%, #7c7aec 100%);
        border-radius: 0 0 2rem 2rem;
        padding: 2rem 2rem 1.75rem;
        margin: -1.5rem -1.5rem 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .rpt-hero::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 260px;
        height: 260px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }

    .rpt-hero-back {
        border-radius: 1rem;
        padding: 0.6rem 1.25rem;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #fff;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: background 0.2s ease;
    }

    .rpt-hero-back:hover {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
    }

    .rpt-hero h3 {
        color: #fff;
        font-weight: 700;
        font-size: 1.6rem;
        margin: 0.75rem 0 0.25rem;
    }

    .rpt-hero p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        margin: 0;
    }

    .rpt-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 99px;
        margin: 0.25rem 0.4rem 0 0;
    }

    /* ── Filter bar ─────────────────────────────────────────────────────── */
    .rpt-filter-bar {
        background: #fff;
        border-radius: var(--rpt-radius-lg);
        box-shadow: var(--rpt-shadow);
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .rpt-filter-bar label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c7080;
        margin-bottom: 0.3rem;
    }

    .rpt-filter-bar select,
    .rpt-filter-bar input {
        border: 1px solid var(--rpt-brand-pale);
        border-radius: var(--rpt-radius-sm);
        font-size: 0.85rem;
        padding: 0.5rem 0.7rem;
    }

    .rpt-filter-bar select:focus,
    .rpt-filter-bar input:focus {
        border-color: var(--rpt-brand-mid);
        box-shadow: 0 0 0 3px rgba(83, 81, 228, 0.12);
    }

    .rpt-btn {
        border: none;
        border-radius: var(--rpt-radius-sm);
        padding: 0.55rem 1.1rem;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .rpt-btn-primary {
        background: linear-gradient(135deg, var(--rpt-brand), var(--rpt-brand-mid));
        color: #fff;
    }

    .rpt-btn-primary:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(44, 41, 202, 0.35);
    }

    .rpt-btn-outline {
        background: #fff;
        color: var(--rpt-brand);
        border: 1px solid var(--rpt-brand-pale);
    }

    .rpt-btn-outline:hover {
        background: var(--rpt-brand-pale);
        color: var(--rpt-brand);
    }

    /* ── Stat cards ─────────────────────────────────────────────────────── */
    .rpt-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .rpt-stat-card {
        background: #fff;
        border-radius: var(--rpt-radius-md);
        box-shadow: var(--rpt-shadow);
        padding: 1rem 1.15rem;
        border-left: 4px solid var(--rpt-brand);
    }

    .rpt-stat-card .rpt-stat-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8b8fa3;
        margin-bottom: 0.35rem;
    }

    .rpt-stat-card .rpt-stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a1a2e;
        line-height: 1;
    }

    .rpt-stat-card .rpt-stat-sub {
        font-size: 0.72rem;
        color: #8b8fa3;
        margin-top: 0.3rem;
    }

    .rpt-stat-card.success { border-left-color: var(--rpt-success); }
    .rpt-stat-card.warning { border-left-color: var(--rpt-warning); }
    .rpt-stat-card.danger { border-left-color: var(--rpt-danger); }

    /* ── Panel wrapper ──────────────────────────────────────────────────── */
    .rpt-panel {
        background: #fff;
        border-radius: var(--rpt-radius-lg);
        box-shadow: var(--rpt-shadow);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .rpt-panel-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .rpt-panel-title i { color: var(--rpt-brand); }

    /* ── Table ──────────────────────────────────────────────────────────── */
    .rpt-table-wrap {
        overflow-x: auto;
        border-radius: var(--rpt-radius-md);
        border: 1px solid var(--rpt-brand-pale);
    }

    table.rpt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
        min-width: 640px;
    }

    table.rpt-table thead th {
        background: var(--rpt-brand-ultra);
        color: #4b3fbf;
        font-weight: 700;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.65rem 0.6rem;
        border-bottom: 2px solid var(--rpt-brand-pale);
        white-space: nowrap;
        position: sticky;
        top: 0;
    }

    table.rpt-table td {
        padding: 0.55rem 0.6rem;
        border-bottom: 1px solid #f1f0ff;
        white-space: nowrap;
    }

    table.rpt-table tbody tr:hover {
        background: var(--rpt-brand-ultra);
    }

    table.rpt-table td.rpt-name-col,
    table.rpt-table th.rpt-name-col {
        position: sticky;
        left: 0;
        background: #fff;
        text-align: left;
        white-space: normal;
        min-width: 160px;
        z-index: 1;
    }

    table.rpt-table thead th.rpt-name-col {
        background: var(--rpt-brand-ultra);
        z-index: 2;
    }

    .rpt-cell-empty { color: #c4c6d4; }

    .rpt-badge {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.15rem 0.5rem;
        border-radius: 99px;
    }

    .rpt-badge-good { background: rgba(16, 185, 129, 0.12); color: #0d9668; }
    .rpt-badge-mid { background: rgba(245, 158, 11, 0.14); color: #b45309; }
    .rpt-badge-bad { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
    .rpt-badge-neutral { background: #f1f0ff; color: #6c7080; }

    /* ── Simple horizontal bar (grade distribution / subject averages) ──── */
    .rpt-bar-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.65rem;
    }

    .rpt-bar-label {
        width: 130px;
        flex-shrink: 0;
        font-size: 0.78rem;
        font-weight: 600;
        color: #1a1a2e;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rpt-bar-track {
        flex: 1;
        height: 10px;
        background: var(--rpt-brand-pale);
        border-radius: 99px;
        overflow: hidden;
    }

    .rpt-bar-fill {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--rpt-brand), var(--rpt-brand-mid));
    }

    .rpt-bar-value {
        width: 70px;
        flex-shrink: 0;
        text-align: right;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--rpt-brand);
    }

    .rpt-empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #8b8fa3;
    }

    .rpt-empty-state i {
        font-size: 2.5rem;
        color: var(--rpt-brand-pale);
        margin-bottom: 0.75rem;
    }

    @media print {
        .no-print { display: none !important; }
        .rpt-hero { background: #fff !important; border-radius: 0; margin: 0; padding: 0.5rem 0 1rem; }
        .rpt-hero h3, .rpt-hero p, .rpt-meta-pill { color: #1a1a2e !important; }
        .rpt-meta-pill { background: #f1f0ff !important; }
        .rpt-panel, .rpt-stat-card { box-shadow: none !important; border: 1px solid #e5e5f0; }
        table.rpt-table td.rpt-name-col, table.rpt-table th.rpt-name-col { position: static; }
    }

    /* Table header styling */
.rpt-table thead th {
    background: #2C29CA !important;
    color: #ffffff;
    font-weight: 600;
    padding: 0.75rem 1rem;
    border-bottom: 2px solid #2C29CA !important;
    white-space: nowrap;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* For the first column (Student) - keep it consistent */
.rpt-table thead th.rpt-name-col {
    background: #2C29CA !important;
    color: #ffffff;
}

/* Optional: Add a subtle gradient effect */
.rpt-table thead {
    background: #2C29CA !important;
}

/* If you want a slightly lighter shade for better readability on hover */
.rpt-table thead th:first-child {
    border-radius: 8px 0 0 0;
}

.rpt-table thead th:last-child {
    border-radius: 0 8px 0 0;
}

/* Alternative - if you want the header to have a gradient like your buttons */
.rpt-table thead th {
    background: linear-gradient(135deg, #2C29CA, #2C29CA) !important;
    color: #ffffff;
    font-weight: 600;
    padding: 0.75rem 1rem;
    border-bottom: 2px solid #2C29CA !important;
    white-space: nowrap;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* For the table footer (Subject Average row) - keep it visible */
.rpt-table tfoot td {
    background: #f8f7ff;
    font-weight: 700;
    padding: 0.75rem 1rem;
    border-top: 2px solid #2C29CA !important;
}

/* Make sure the table has proper borders */
.rpt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.rpt-table th,
.rpt-table td {
    padding: 0.6rem 0.75rem;
    text-align: center;
    border-bottom: 1px solid #f0eff5;
}

.rpt-table td.rpt-name-col {
    text-align: left;
    font-weight: 500;
}

/* Optional: Add hover effect on table rows */
.rpt-table tbody tr:hover {
    background: #f8f7ff;
    transition: background 0.2s ease;
}
</style>