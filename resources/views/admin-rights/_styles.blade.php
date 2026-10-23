<style>
:root { --arp-primary:#4f46e5; --arp-dark:#1e1b4b; --arp-accent:#7c3aed; --arp-radius:14px; --arp-ok:#059669; --arp-warn:#d97706; --arp-bad:#dc2626; }
.arp-hero { background:linear-gradient(135deg,var(--arp-dark),var(--arp-primary) 60%,var(--arp-accent)); color:#fff; border-radius:var(--arp-radius); padding:22px 26px; margin-bottom:18px; }
.arp-hero h3 { margin:0 0 4px; font-weight:700; color:#fff; }
.arp-hero p { margin:0; opacity:.85; }
.arp-me { display:inline-block; margin-top:10px; background:rgba(255,255,255,.15); border-radius:20px; padding:3px 12px; font-size:12px; }
.arp-tabs { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:18px; }
.arp-tabs a { padding:8px 16px; border-radius:10px; background:#fff; border:1px solid #e5e7eb; color:#374151; font-weight:600; font-size:13px; text-decoration:none; }
.arp-tabs a:hover { border-color:var(--arp-primary); color:var(--arp-primary); }
.arp-tabs a.active { background:var(--arp-primary); border-color:var(--arp-primary); color:#fff; }
.arp-card { background:#fff; border:1px solid #e5e7eb; border-radius:var(--arp-radius); padding:18px 20px; margin-bottom:18px; }
.arp-card h5 { font-weight:700; margin-bottom:12px; }
.arp-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:18px; }
.arp-stat { background:#fff; border:1px solid #e5e7eb; border-radius:var(--arp-radius); padding:14px 16px; }
.arp-stat b { display:block; font-size:26px; color:var(--arp-dark); line-height:1.1; }
.arp-stat span { font-size:12px; color:#6b7280; }
.arp-stat.warn b { color:var(--arp-warn); }
.arp-table { width:100%; border-collapse:collapse; }
.arp-table th { text-align:left; font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; padding:8px 10px; border-bottom:2px solid #e5e7eb; white-space:nowrap; }
.arp-table td { padding:10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; font-size:14px; }
.arp-wrap { overflow-x:auto; }
.arp-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.arp-badge.super { background:#fef3c7; color:#92400e; }
.arp-badge.role { background:#e0e7ff; color:#3730a3; }
.arp-badge.none { background:#f3f4f6; color:#6b7280; }
.arp-badge.on { background:#d1fae5; color:#065f46; }
.arp-badge.off { background:#fee2e2; color:#991b1b; }
.arp-btn { border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:5px 10px; font-size:12px; font-weight:600; cursor:pointer; color:#374151; margin:1px; }
.arp-btn:hover { border-color:var(--arp-primary); color:var(--arp-primary); }
.arp-btn.primary { background:var(--arp-primary); border-color:var(--arp-primary); color:#fff; }
.arp-btn.primary:hover { color:#fff; opacity:.92; }
.arp-btn.danger:hover { border-color:var(--arp-bad); color:var(--arp-bad); }
.arp-muted { color:#6b7280; font-size:13px; }
.arp-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:14px; }
.arp-input { border:1px solid #d1d5db; border-radius:8px; padding:7px 12px; font-size:14px; min-width:220px; }
.arp-mod-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(310px,1fr)); gap:14px; }
.arp-mod { border:1px solid #e5e7eb; border-radius:var(--arp-radius); padding:14px 16px; background:#fff; }
.arp-mod header { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px; }
.arp-mod header strong { font-size:15px; }
.arp-mod .desc { font-size:12px; color:#6b7280; margin-bottom:8px; }
.arp-perm { display:flex; gap:8px; align-items:flex-start; padding:5px 0; font-size:13px; cursor:pointer; }
.arp-perm input { margin-top:3px; }
.arp-perm.locked { opacity:.45; cursor:not-allowed; }
.arp-savebar { position:sticky; bottom:0; background:#fff; border-top:1px solid #e5e7eb; padding:12px 0; display:flex; gap:10px; align-items:center; justify-content:space-between; z-index:2; }
.arp-dirty { color:var(--arp-warn); font-weight:600; font-size:13px; }
.swal2-html-container .arp-field { text-align:left; margin-bottom:10px; }
.swal2-html-container .arp-field label { display:block; font-size:12px; font-weight:700; margin-bottom:3px; color:#374151; }
.swal2-html-container .arp-field input, .swal2-html-container .arp-field select, .swal2-html-container .arp-field textarea { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; font-size:14px; }
</style>
