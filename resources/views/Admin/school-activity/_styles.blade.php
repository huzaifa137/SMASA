<style>
    .sam { --sam-brand:#5351e4; --sam-ok:#16a34a; --sam-warn:#d97706; --sam-bad:#dc2626; --sam-mute:#64748b; --sam-line:#e5e7eb; padding:8px 4px 40px; }
    .sam h1 { font-size:1.35rem; font-weight:700; margin:0 0 4px; }
    .sam .sub { color:var(--sam-mute); font-size:.85rem; margin-bottom:18px; }
    .sam-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:18px; }
    .sam-card { background:#fff; border:1px solid var(--sam-line); border-radius:12px; padding:14px 16px; text-decoration:none; color:inherit; display:block; }
    .sam-card:hover { border-color:var(--sam-brand); }
    .sam-card.on { border-color:var(--sam-brand); box-shadow:0 0 0 2px rgba(83,81,228,.15); }
    .sam-card .n { font-size:1.6rem; font-weight:800; line-height:1.1; }
    .sam-card .l { font-size:.74rem; text-transform:uppercase; letter-spacing:.4px; color:var(--sam-mute); font-weight:600; }
    .sam-panel { background:#fff; border:1px solid var(--sam-line); border-radius:12px; margin-bottom:18px; }
    .sam-panel > .hd { padding:12px 16px; border-bottom:1px solid var(--sam-line); font-weight:700; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
    .sam-panel > .bd { padding:14px 16px; }
    .sam-filters { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .sam-filters input, .sam-filters select { border:1px solid var(--sam-line); border-radius:8px; padding:7px 10px; font-size:.85rem; background:#fff; }
    .sam-btn { border:1px solid var(--sam-brand); background:var(--sam-brand); color:#fff; border-radius:8px; padding:7px 14px; font-size:.85rem; text-decoration:none; cursor:pointer; display:inline-block; }
    .sam-btn.ghost { background:#fff; color:var(--sam-brand); }
    .sam-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .sam-table th { text-align:left; padding:10px 12px; font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; color:var(--sam-mute); border-bottom:1px solid var(--sam-line); white-space:nowrap; }
    .sam-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .sam-table tr:hover td { background:#fafbff; }
    .sam-wrap { overflow-x:auto; }
    .sam-badge { display:inline-block; padding:2px 9px; border-radius:99px; font-size:.72rem; font-weight:700; white-space:nowrap; }
    .sam-badge.active { background:#dcfce7; color:#166534; }
    .sam-badge.idle { background:#fef3c7; color:#92400e; }
    .sam-badge.dormant, .sam-badge.never { background:#fee2e2; color:#991b1b; }
    .sam-badge.new { background:#dbeafe; color:#1e40af; }
    .sam-badge.not_live { background:#e5e7eb; color:#374151; }
    .sam-dots { display:flex; gap:4px; }
    .sam-dot { width:22px; height:22px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:.68rem; background:#f1f5f9; color:#cbd5e1; }
    .sam-dot.on { background:#e0e7ff; color:var(--sam-brand); }
    .sam-muted { color:var(--sam-mute); font-size:.78rem; }
    .sam-spark { display:flex; align-items:flex-end; gap:3px; height:90px; }
    .sam-spark span { flex:1; background:#c7d2fe; border-radius:3px 3px 0 0; min-height:2px; }
    .sam-spark span.z { background:#e5e7eb; }
    .sam-grid2 { display:grid; grid-template-columns:2fr 1fr; gap:18px; }
    @media (max-width:991px){ .sam-grid2 { grid-template-columns:1fr; } }
    .sam-flash { background:#dcfce7; color:#166534; border-radius:8px; padding:9px 14px; margin-bottom:14px; font-size:.85rem; }
</style>
