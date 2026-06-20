<style>
    /* ════════════════════════════════════════════════════════════
       MASTER DATA — shared design system
       Palette matches the rest of the admin (see user-rights pages)
       so these screens finally feel like part of the same product.
    ════════════════════════════════════════════════════════════ */
    :root {
        --mdx-navy: #0f172a;
        --mdx-indigo: #2c29ca;
        --mdx-indigo2: #4338ca;
        --mdx-indigo3: #6366f1;
        --mdx-teal: #0d9488;
        --mdx-amber: #2c29ca;
        --mdx-sky: #0284c7;
        --mdx-rose: #e11d48;
        --mdx-slate: #64748b;
        --mdx-border: #dde3f7;
        --mdx-bg2: #f4f6fd;
        --mdx-white: #ffffff;
        --mdx-r: 12px;
    }

    .mdx-page {
        background: linear-gradient(160deg, #e8ecf8 0%, #eef1fb 40%, #f0f4ff 100%);
        min-height: 100vh;
        padding: 1.5rem;
        border-radius: var(--mdx-r);
    }

    .mdx-topbar {
        height: 4px;
        background: linear-gradient(90deg, var(--mdx-indigo) 0%, #5b5fef 50%, var(--mdx-teal) 100%);
        border-radius: var(--mdx-r) var(--mdx-r) 0 0;
    }

    /* ── Header ── */
    .mdx-header {
        background: var(--mdx-white);
        border: 1px solid #2c29ca;
        border-top: none;
        border-radius: 0 0 var(--mdx-r) var(--mdx-r);
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 2px 12px rgba(44, 41, 202, .06);
    }

    .mdx-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .mdx-header-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--mdx-indigo), var(--mdx-indigo3));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(44, 41, 202, .25);
    }

    .mdx-header-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--mdx-navy);
        letter-spacing: -.025em;
        margin: 0;
    }

    .mdx-header-sub {
        font-size: .8rem;
        color: var(--mdx-slate);
        margin-top: .2rem;
    }

    .mdx-header-actions {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
    }

    /* ── Sub nav (switch between Master Data / Master Codes) ── */
    .mdx-subnav {
        display: flex;
        gap: .4rem;
        background: var(--mdx-bg2);
        border: 1px solid #2c29ca;
        padding: .3rem;
        border-radius: 999px;
    }

    .mdx-subnav a {
        font-size: .76rem;
        font-weight: 600;
        color: var(--mdx-slate);
        padding: .45rem 1rem;
        border-radius: 999px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        transition: all .15s;
        white-space: nowrap;
    }

    .mdx-subnav a:hover {
        color: var(--mdx-indigo);
        text-decoration: none;
    }

    .mdx-subnav a.active {
        background: var(--mdx-white);
        color: var(--mdx-indigo);
        box-shadow: 0 2px 8px rgba(44, 41, 202, .12);
    }

    /* ── Buttons ── */
    .mdx-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .78rem;
        font-weight: 600;
        padding: .55rem 1.1rem;
        border-radius: 9px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all .15s;
        text-decoration: none;
        line-height: 1.2;
    }

    .mdx-btn:hover {
        text-decoration: none;
    }

    .mdx-btn-primary {
        background: linear-gradient(135deg, var(--mdx-indigo), var(--mdx-indigo3));
        color: #fff;
        box-shadow: 0 3px 10px rgba(44, 41, 202, .25);
    }

    .mdx-btn-primary:hover {
        box-shadow: 0 5px 16px rgba(44, 41, 202, .35);
        transform: translateY(-1px);
        color: #fff;
    }

    .mdx-btn-outline {
        background: var(--mdx-white);
        border-color: #2c29ca;
        color: var(--mdx-indigo2);
    }

    .mdx-btn-outline:hover {
        border-color: var(--mdx-indigo);
        background: #eef2ff;
        color: var(--mdx-indigo);
    }

    .mdx-btn-sm {
        padding: .4rem .8rem;
        font-size: .72rem;
    }

    .mdx-btn-danger-outline {
        background: var(--mdx-white);
        border-color: #fecdd3;
        color: var(--mdx-rose);
    }

    .mdx-btn-danger-outline:hover {
        background: #fff1f2;
        border-color: var(--mdx-rose);
        color: var(--mdx-rose);
    }

    .mdx-btn[disabled],
    .mdx-btn.disabled {
        opacity: .5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ── Stat strip ── */
    .mdx-stat-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .mdx-stat {
        background: var(--mdx-white);
        border: 1px solid #2c29ca;
        border-radius: var(--mdx-r);
        padding: 1.2rem 1.4rem 1.1rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(44, 41, 202, .06);
        transition: transform .2s, box-shadow .2s;
    }

    .mdx-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(44, 41, 202, .12);
    }

    .mdx-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: var(--mdx-r) 0 0 var(--mdx-r);
    }

    .m-indigo::before {
        background: var(--mdx-indigo);
    }

    .m-teal::before {
        background: var(--mdx-teal);
    }

    .m-sky::before {
        background: var(--mdx-sky);
    }

    .m-amber::before {
        background: var(--mdx-amber);
    }

    .mdx-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .8rem;
    }

    .mdx-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .92rem;
        flex-shrink: 0;
    }

    .m-indigo .mdx-stat-icon {
        background: #eef2ff;
        color: var(--mdx-indigo);
    }

    .m-teal .mdx-stat-icon {
        background: #f0fdfa;
        color: var(--mdx-teal);
    }

    .m-sky .mdx-stat-icon {
        background: #e0f2fe;
        color: var(--mdx-sky);
    }

    .m-amber .mdx-stat-icon {
        background: #fff7ed;
        color: var(--mdx-amber);
    }

    .mdx-stat-value {
        font-size: 2.1rem;
        font-weight: 800;
        color: var(--mdx-navy);
        line-height: 1;
        letter-spacing: -.04em;
    }

    .mdx-stat-label {
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--mdx-slate);
        margin-top: .3rem;
    }

    /* ── Generic panel ── */
    .mdx-panel {
        position: relative;
        background: var(--mdx-white);
        border: 1px solid #2c29ca;
        border-radius: var(--mdx-r);
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(44, 41, 202, .05);
        margin-bottom: 1.75rem;
    }

    .mdx-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--mdx-indigo) 0%, #5b5fef 50%, var(--mdx-teal) 100%);
    }

    .mdx-panel-head {
        padding: 1rem 1.4rem;
        border-bottom: 1px solid #2c29ca;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .6rem;
        background: linear-gradient(90deg, #f4f6fd, #f8faff);
    }

    .mdx-panel-label {
        font-size: .66rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: #2c29ca;
    }

    .mdx-panel-title {
        font-size: .92rem;
        font-weight: 700;
        color: var(--mdx-navy);
        margin-top: .1rem;
    }

    .mdx-panel-body {
        padding: 1.3rem 1.4rem;
    }

    /* ── Collapsible add-form ── */
    .mdx-collapse {
        max-height: 0;
        overflow: hidden;
        transition: max-height .35s ease;
    }

    .mdx-collapse.open {
        max-height: 1400px;
        transition: max-height .5s ease;
    }

    .mdx-form-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 1rem;
    }

    .mdx-col-3 {
        grid-column: span 3;
    }

    .mdx-col-4 {
        grid-column: span 4;
    }

    .mdx-col-6 {
        grid-column: span 6;
    }

    .mdx-col-8 {
        grid-column: span 8;
    }

    .mdx-col-9 {
        grid-column: span 9;
    }

    .mdx-col-12 {
        grid-column: span 12;
    }

    @media (max-width: 700px) {

        .mdx-col-3,
        .mdx-col-4,
        .mdx-col-6,
        .mdx-col-8,
        .mdx-col-9 {
            grid-column: span 12;
        }
    }

    .mdx-field {
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .mdx-field label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--mdx-slate);
        margin: 0;
    }

    .mdx-field .mdx-input,
    .mdx-field select.mdx-input,
    .mdx-field textarea.mdx-input,
    .mdx-input.form-control {
        border: 1px solid #2c29ca;
        border-radius: 9px;
        padding: .6rem .85rem;
        font-size: .85rem;
        color: var(--mdx-navy);
        background: var(--mdx-bg2);
        transition: border-color .15s, background .15s, box-shadow .15s;
        width: 100%;
        height: auto;
    }

    .mdx-field .mdx-input:focus,
    .mdx-input.form-control:focus {
        outline: none;
        border-color: var(--mdx-indigo3);
        background: var(--mdx-white);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
    }

    .mdx-field textarea.mdx-input {
        resize: vertical;
        min-height: 80px;
    }

    /* ── Toolbar / search ── */
    .mdx-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.1rem;
    }

    .mdx-search-wrap {
        position: relative;
        flex: 1;
        min-width: 220px;
        max-width: 360px;
    }

    .mdx-search-wrap i {
        position: absolute;
        left: .9rem;
        top: 50%;
        transform: translateY(-50%);
        color: #2c29ca;
        font-size: .8rem;
    }

    .mdx-search-wrap input {
        width: 100%;
        border: 1px solid #2c29ca;
        border-radius: 999px;
        padding: .55rem 1rem .55rem 2.3rem;
        font-size: .8rem;
        background: var(--mdx-white);
        transition: border-color .15s, box-shadow .15s;
    }

    .mdx-search-wrap input:focus {
        outline: none;
        border-color: var(--mdx-indigo3);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
    }

    /* ── Category cards grid (master-code-to-data) ── */
    .mdx-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 1.1rem;
    }

    .mdx-card {
        background: var(--mdx-white);
        border: 1.5px solid #2c29ca;
        border-radius: var(--mdx-r);
        padding: 1.25rem 1.3rem;
        box-shadow: 0 2px 10px rgba(44, 41, 202, .05);
        transition: transform .18s, box-shadow .18s, border-color .18s;
        display: flex;
        flex-direction: column;
        gap: .85rem;
    }

    .mdx-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(44, 41, 202, .14);
        border-color: #60a5fa;
    }

    .mdx-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .6rem;
    }

    .mdx-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #eef2ff;
        color: var(--mdx-indigo);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .85rem;
        flex-shrink: 0;
    }

    .mdx-card-count {
        font-size: .68rem;
        font-weight: 700;
        background: #eef2ff;
        color: var(--mdx-indigo2);
        border: 1px solid #c7d2fe;
        border-radius: 20px;
        padding: .25rem .65rem;
        white-space: nowrap;
    }

    .mdx-card-code {
        font-size: .66rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #2c29ca;
    }

    .mdx-card-title {
        font-size: .98rem;
        font-weight: 700;
        color: var(--mdx-navy);
        margin: .15rem 0 0;
        line-height: 1.3;
    }

    .mdx-card-desc {
        font-size: .78rem;
        color: var(--mdx-slate);
        line-height: 1.5;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .mdx-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding-top: .85rem;
        border-top: 1px solid #2c29ca;
    }

    .mdx-card-actions {
        display: flex;
        gap: .4rem;
    }

    .mdx-icon-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #2c29ca;
        background: var(--mdx-white);
        color: var(--mdx-slate);
        font-size: .76rem;
        transition: all .15s;
        text-decoration: none;
    }

    .mdx-icon-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .mdx-icon-btn.i-edit:hover {
        border-color: var(--mdx-indigo);
        color: var(--mdx-indigo);
        background: #eef2ff;
    }

    .mdx-icon-btn.i-delete:hover {
        border-color: var(--mdx-rose);
        color: var(--mdx-rose);
        background: #fff1f2;
    }

    /* ── Empty state ── */
    .mdx-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--mdx-slate);
    }

    .mdx-empty i {
        font-size: 2.2rem;
        color: #c7d2fe;
        display: block;
        margin-bottom: .9rem;
    }

    .mdx-empty h5 {
        color: var(--mdx-navy);
        font-weight: 700;
        font-size: .98rem;
        margin-bottom: .4rem;
    }

    .mdx-empty p {
        font-size: .82rem;
        max-width: 360px;
        margin: 0 auto;
    }

    /* ── Category sidebar (used by list-select + list pages) ── */
    .mdx-sidebar {
        position: relative;
        background: var(--mdx-white);
        border: 1px solid #2c29ca;
        border-radius: var(--mdx-r);
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(44, 41, 202, .05);
    }

    .mdx-sidebar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--mdx-indigo) 0%, #5b5fef 50%, var(--mdx-teal) 100%);
    }

    .mdx-sidebar-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid #2c29ca;
        background: linear-gradient(90deg, #f4f6fd, #f8faff);
    }

    .mdx-sidebar-list {
        max-height: 640px;
        overflow-y: auto;
    }

    .mdx-sidebar-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .6rem;
        padding: .7rem 1.2rem;
        text-decoration: none;
        color: var(--mdx-navy);
        font-size: .82rem;
        font-weight: 500;
        border-left: 3px solid transparent;
        border-bottom: 1px solid #f1f3fb;
        transition: all .15s;
    }

    .mdx-sidebar-item:last-child {
        border-bottom: none;
    }

    .mdx-sidebar-item:hover {
        background: #f4f6fd;
        color: var(--mdx-indigo);
        text-decoration: none;
    }

    .mdx-sidebar-item.active {
        background: #eef2ff;
        border-left-color: var(--mdx-indigo);
        color: var(--mdx-indigo2);
        font-weight: 700;
    }

    .mdx-sidebar-item .name {
        display: flex;
        align-items: center;
        gap: .55rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mdx-sidebar-item .name i {
        font-size: .55rem;
        color: #c7d2fe;
        flex-shrink: 0;
    }

    .mdx-sidebar-item.active .name i {
        color: var(--mdx-indigo);
    }

    .mdx-sidebar-badge {
        font-size: .65rem;
        font-weight: 700;
        background: #eef2ff;
        color: var(--mdx-indigo2);
        border-radius: 20px;
        padding: .15rem .55rem;
        flex-shrink: 0;
    }

    .mdx-sidebar-item.active .mdx-sidebar-badge {
        background: var(--mdx-indigo);
        color: #fff;
    }

    /* ── Table ── */
    .mdx-table-wrap {
        border: 1px solid #2c29ca;
        border-radius: var(--mdx-r);
        overflow: hidden;
    }

    table.mdx-table {
        width: 100%;
        margin-bottom: 0;
        font-size: .82rem;
    }

    table.mdx-table thead th {
        background: #f4f6fd;
        color: var(--mdx-slate);
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        border-bottom: 1px solid #2c29ca;
        border-top: none;
        padding: .85rem 1rem;
        white-space: nowrap;
    }

    table.mdx-table tbody td {
        padding: .8rem 1rem;
        vertical-align: middle;
        color: var(--mdx-navy);
        border-top: 1px solid #f1f3fb;
    }

    table.mdx-table tbody tr:hover {
        background: #f8faff;
    }

    .mdx-code-pill {
        display: inline-block;
        font-size: .7rem;
        font-weight: 700;
        background: #eef2ff;
        color: var(--mdx-indigo2);
        border-radius: 6px;
        padding: .2rem .55rem;
        letter-spacing: .03em;
    }

    table.mdx-table .dropdown-toggle {
        font-size: .73rem;
        padding: .35rem .8rem;
        border-radius: 7px;
        background: var(--mdx-white);
        border: 1px solid #2c29ca;
        color: var(--mdx-indigo2);
        font-weight: 600;
    }

    table.mdx-table .dropdown-toggle:hover,
    table.mdx-table .dropdown-toggle:focus {
        background: #eef2ff;
        border-color: var(--mdx-indigo);
    }

    table.mdx-table .dropdown-toggle::after {
        display: none;
    }

    .mdx-table-wrap .dropdown-menu {
        border-radius: 9px;
        border: 1px solid #2c29ca;
        box-shadow: 0 8px 24px rgba(44, 41, 202, .14);
        font-size: .78rem;
        padding: .4rem;
    }

    .mdx-table-wrap .dropdown-item {
        border-radius: 6px;
        padding: .5rem .7rem;
    }

    .mdx-table-wrap .dropdown-item:hover {
        background: #eef2ff;
        color: var(--mdx-indigo);
    }

    /* ── Form builder (dynamic fields) — keeps original selectors,
       just restyled to match the palette ── */
    .mdx-builder-wrap {
        margin-top: 1.4rem;
        padding-top: 1.2rem;
        border-top: 1px dashed #2c29ca;
    }

    .mdx-builder-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--mdx-slate);
        margin-bottom: .6rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .form-builder-section {
        position: relative;
        margin-bottom: .9rem;
        padding: 1rem 1.1rem;
        border: 1px solid #2c29ca;
        border-radius: 10px;
        background: var(--mdx-bg2);
    }

    .form-builder-section label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--mdx-slate);
        display: block;
        margin-bottom: .4rem;
    }

    .delete-btn {
        position: absolute;
        top: .6rem;
        right: .6rem;
        color: var(--mdx-rose);
        background: none;
        border: none;
        font-size: .85rem;
    }

    .delete-btn:hover {
        color: #be123c;
    }

    .option-wrapper {
        position: relative;
        margin-bottom: .55rem;
    }

    .option-wrapper input {
        padding-right: 2.2rem;
    }

    .delete-option-btn {
        position: absolute;
        right: .7rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--mdx-rose);
        font-size: 1.05rem;
        cursor: pointer;
    }

    .dropdown-options-list li {
        font-size: .82rem;
        border-color: #2c29ca;
    }

    .dropdown-options-list .btn {
        font-size: .68rem;
        padding: .15rem .5rem;
    }

    /* ── DataTables chrome (search/length/pagination/export buttons) ── */
    .mdx-panel .dataTables_wrapper {
        padding: 0 1.4rem 1.4rem;
    }

    .mdx-panel .dataTables_wrapper .dt-buttons {
        margin-bottom: .9rem;
    }

    .mdx-panel .dataTables_wrapper .dt-buttons .btn {
        font-size: .72rem;
        font-weight: 600;
        padding: .4rem .85rem;
        border-radius: 7px;
        border: 1px solid #2c29ca;
        background: var(--mdx-white);
        color: var(--mdx-indigo2);
        margin-right: .4rem;
        box-shadow: none;
    }

    .mdx-panel .dataTables_wrapper .dt-buttons .btn:hover {
        background: #eef2ff;
        border-color: var(--mdx-indigo);
        color: var(--mdx-indigo);
    }

    .mdx-panel .dataTables_filter {
        margin-bottom: .9rem;
    }

    .mdx-panel .dataTables_filter label {
        font-size: .8rem;
        color: var(--mdx-slate);
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .mdx-panel .dataTables_filter input {
        border: 1px solid #2c29ca;
        border-radius: 999px;
        padding: .45rem 1rem;
        font-size: .8rem;
        min-width: 220px;
    }

    .mdx-panel .dataTables_filter input:focus {
        outline: none;
        border-color: var(--mdx-indigo3);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
    }

    .mdx-panel .dataTables_info {
        font-size: .76rem;
        color: var(--mdx-slate);
        padding-top: .9rem;
    }

    .mdx-panel .dataTables_paginate {
        padding-top: .9rem;
    }

    .mdx-panel .dataTables_paginate .paginate_button {
        font-size: .76rem;
        padding: .35rem .7rem;
        border-radius: 7px;
        margin-left: .25rem;
        border: 1px solid transparent !important;
        background: transparent !important;
        color: var(--mdx-slate) !important;
    }

    .mdx-panel .dataTables_paginate .paginate_button:hover {
        background: #eef2ff !important;
        color: var(--mdx-indigo) !important;
        border-color: #eef2ff !important;
    }

    .mdx-panel .dataTables_paginate .paginate_button.current {
        background: var(--mdx-indigo) !important;
        color: #fff !important;
        border-color: var(--mdx-indigo) !important;
    }

    .mdx-panel .dataTables_length select {
        border: 1px solid #2c29ca;
        border-radius: 7px;
        padding: .2rem .5rem;
        font-size: .78rem;
    }

    /* ── Misc ── */
    .mdx-alert {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .8rem 1.1rem;
        border-radius: var(--mdx-r);
        font-size: .82rem;
        margin-bottom: 1.25rem;
        border: 1px solid;
    }

    .mdx-alert-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    @media (max-width: 992px) {
        .mdx-stat-strip {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 560px) {
        .mdx-page {
            padding: 1rem;
        }

        .mdx-stat-strip {
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        .mdx-form-grid {
            grid-template-columns: 1fr;
        }
    }

    /* DataTable Header */
    .mdx-table thead th,
    table.dataTable thead th {
        background-color: #2c29ca !important;
        color: #FFF !important;
        font-weight: 600;
        border-color: #2c29ca !important;
    }
</style>