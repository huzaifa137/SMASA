<style>
    .au-hero{background:linear-gradient(135deg,#2C29CA 0%,#5351e4 100%);color:#fff;border-radius:14px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem}
    .au-hero h4{margin:0;font-weight:700}.au-hero small{opacity:.85}
    .au-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.9rem;margin-bottom:1.25rem}
    .au-stat{background:#fff;border-radius:12px;padding:.9rem 1.1rem;box-shadow:0 1px 4px rgba(20,20,60,.08);border-left:4px solid #5351e4}
    .au-stat .n{font-size:1.6rem;font-weight:700;line-height:1.1;color:#1f2354}.au-stat .l{font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280}
    .au-stat.warn{border-left-color:#f59e0b}.au-stat.bad{border-left-color:#dc2626}.au-stat.ok{border-left-color:#16a34a}
    .au-card{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(20,20,60,.08);padding:1rem 1.2rem;margin-bottom:1.25rem}
    .au-bars{display:flex;align-items:flex-end;gap:6px;height:70px}
    .au-bars div{flex:1;background:#c7c6f5;border-radius:4px 4px 0 0;min-height:3px;position:relative}
    .au-bars div:last-child{background:#5351e4}
    .au-bars-l{display:flex;gap:6px;font-size:.62rem;color:#9ca3af;margin-top:3px}.au-bars-l span{flex:1;text-align:center}
    .au-filters label{font-size:.72rem;font-weight:600;color:#6b7280;margin-bottom:2px;text-transform:uppercase;letter-spacing:.04em}
    .au-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap}
    .au-table td{vertical-align:middle;font-size:.85rem}
    .au-row{cursor:pointer}.au-row:hover{background:#f5f5ff}
    .au-av{width:32px;height:32px;border-radius:50%;background:#e0e7ff;color:#3730a3;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.72rem;flex:none}
    .au-av.guest{background:#f3f4f6;color:#6b7280}.au-av.system{background:#e5e7eb;color:#374151}
    .au-badge{display:inline-block;padding:.15rem .55rem;border-radius:999px;font-size:.7rem;font-weight:600;white-space:nowrap}
    .au-c-data{background:#e0f2fe;color:#075985}.au-c-auth{background:#ede9fe;color:#5b21b6}.au-c-request{background:#f3f4f6;color:#374151}
    .au-c-security{background:#fee2e2;color:#991b1b}.au-c-export{background:#fef3c7;color:#92400e}.au-c-system{background:#e5e7eb;color:#374151}
    .au-s-info{color:#6b7280}.au-s-notice{color:#2563eb}.au-s-warning{color:#d97706}.au-s-error,.au-s-critical{color:#dc2626}
    .au-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:4px;background:currentColor}
    .au-fail{color:#dc2626;font-weight:600}
    .au-diff{width:100%;border-collapse:collapse;font-size:.85rem}
    .au-diff th{background:#f9fafb;text-align:left;padding:.45rem .7rem;font-size:.72rem;text-transform:uppercase;color:#6b7280}
    .au-diff td{padding:.45rem .7rem;border-top:1px solid #eef0f4;vertical-align:top;word-break:break-word}
    .au-old{background:#fef2f2;color:#991b1b}.au-new{background:#f0fdf4;color:#166534}
    .au-kv{display:grid;grid-template-columns:150px 1fr;gap:.35rem 1rem;font-size:.88rem}.au-kv dt{color:#6b7280;font-weight:600}.au-kv dd{margin:0;word-break:break-word}
    .au-json{background:#0f172a;color:#e2e8f0;border-radius:8px;padding:.8rem 1rem;font-size:.78rem;max-height:340px;overflow:auto;white-space:pre-wrap}
    .au-ok{color:#16a34a}.au-bad{color:#dc2626}
    .au-tl{border-left:2px solid #e5e7eb;margin-left:.4rem;padding-left:1rem}.au-tl li{margin-bottom:.6rem;list-style:none;position:relative}
    .au-tl li:before{content:"";position:absolute;left:-1.35rem;top:.35rem;width:10px;height:10px;border-radius:50%;background:#5351e4}
</style>
