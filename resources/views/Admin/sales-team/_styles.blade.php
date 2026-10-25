<style>
    /* ═════════ Sales Team: shared look (matches School Activity / Billing / Audit Trail) ═════════ */
    .st-page{--st-brand:#2C29CA;--st-ink:#1e1b4b;--st-mute:#6b6f99;--st-line:#e6e8f5;padding:4px 4px 40px}
    table thead th{background-color:#2C29CA !important;color:#fff !important}

    /* palettes: --c solid, --t tint, --b border */
    .c-indigo{--c:#4f46e5;--t:#eef2ff;--b:#c7d2fe}.c-violet{--c:#7c3aed;--t:#f5f3ff;--b:#ddd6fe}
    .c-emerald{--c:#059669;--t:#ecfdf5;--b:#a7f3d0}.c-amber{--c:#d97706;--t:#fffbeb;--b:#fde68a}
    .c-sky{--c:#0284c7;--t:#f0f9ff;--b:#bae6fd}.c-rose{--c:#e11d48;--t:#fff1f2;--b:#fecdd3}
    .c-teal{--c:#0d9488;--t:#f0fdfa;--b:#99f6e4}.c-slate{--c:#475569;--t:#f8fafc;--b:#e2e8f0}

    /* ── animations (all switched off for people who prefer reduced motion) ── */
    @keyframes stUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
    @keyframes stGrow{from{transform:scaleX(0)}to{transform:scaleX(1)}}
    @keyframes stPop{from{opacity:0;transform:scale(.94) translateY(8px)}to{opacity:1;transform:none}}
    @keyframes stGlow{0%,100%{box-shadow:0 6px 22px rgba(245,158,11,.30)}50%{box-shadow:0 10px 34px rgba(245,158,11,.55)}}
    @keyframes stShine{from{background-position:-200% 0}to{background-position:200% 0}}
    .st-in-up{animation:stUp .55s cubic-bezier(.2,.8,.2,1) both}
    @media (prefers-reduced-motion:reduce){.st-page *,.st-page *::before,dialog.st-dlg{animation:none !important;transition:none !important}}

    /* ── hero ── */
    .st-hero{background:linear-gradient(135deg,#000 0%,#070189 100%);border-radius:20px;padding:1.5rem 2rem;margin-bottom:1.5rem;border:1px solid rgba(255,255,255,.08);box-shadow:0 4px 24px rgba(7,1,137,.3);color:#fff;animation:stUp .5s both}
    .st-hero-main{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
    .st-hero-left{display:flex;align-items:center;gap:1.1rem;min-width:0}
    .st-hero-icon{width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;flex-shrink:0;box-shadow:0 4px 12px rgba(102,126,234,.35)}
    .st-hero h4{font-size:1.35rem;font-weight:700;color:#fff !important;margin:0 0 .15rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
    .st-hero p{color:rgba(255,255,255,.75) !important;font-size:.9rem;margin:0;word-break:break-word}
    .st-hero-chip{font-size:.7rem;font-weight:700;padding:.2rem .65rem;border-radius:99px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);color:#fff}
    .st-hero-actions{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
    .st-hero-actions form{margin:0}
    .st-hero .st-btn{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);color:#fff !important;box-shadow:none}
    .st-hero .st-btn:hover,.st-hero .st-btn:focus{background:rgba(255,255,255,.24);border-color:rgba(255,255,255,.5);color:#fff !important}
    .st-hero .st-btn--primary{background:#fff;border-color:#fff;color:#1e1b4b !important}
    .st-hero .st-btn--primary:hover{background:#eef0ff;color:#1e1b4b !important}
    .st-hero-features{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.08)}
    .st-hero-feature{display:flex;align-items:center;gap:.7rem;animation:stUp .6s both}
    .st-hero-feature:nth-child(2){animation-delay:.08s}.st-hero-feature:nth-child(3){animation-delay:.16s}.st-hero-feature:nth-child(4){animation-delay:.24s}
    .st-hero-feature-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .st-hero-feature-title{font-size:.82rem;font-weight:700;color:#fff}.st-hero-feature-desc{font-size:.74rem;color:rgba(255,255,255,.62)}

    /* ── buttons ── */
    .st-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:99px;font-size:.85rem;font-weight:600;line-height:1.2;cursor:pointer;text-decoration:none !important;white-space:nowrap;border:1px solid #c7c9f5;background:#fff;color:#2C29CA !important;transition:transform .18s ease,box-shadow .18s ease,background .18s ease,filter .18s ease}
    .st-btn:hover{background:#eef0ff;transform:translateY(-2px);box-shadow:0 6px 16px rgba(44,41,202,.18)}
    .st-btn:active{transform:translateY(0)}
    .st-btn--primary,.st-btn--success,.st-btn--warn,.st-btn--info,.st-btn--violet,.st-btn--danger-solid{border-color:transparent;color:#fff !important}
    .st-btn--primary{background:linear-gradient(135deg,#2C29CA,#6d28d9)}
    .st-btn--success{background:linear-gradient(135deg,#059669,#10b981)}
    .st-btn--warn{background:linear-gradient(135deg,#d97706,#f59e0b)}
    .st-btn--info{background:linear-gradient(135deg,#0284c7,#0d9488)}
    .st-btn--violet{background:linear-gradient(135deg,#7c3aed,#c026d3)}
    .st-btn--danger-solid{background:linear-gradient(135deg,#dc2626,#e11d48)}
    .st-btn--primary:hover,.st-btn--success:hover,.st-btn--warn:hover,.st-btn--info:hover,.st-btn--violet:hover,.st-btn--danger-solid:hover{filter:brightness(1.1);color:#fff !important}
    .st-btn--danger{border-color:#fecaca;color:#b42318 !important;background:#fff}.st-btn--danger:hover{background:#fef2f2;box-shadow:0 6px 16px rgba(220,38,38,.18)}
    .st-btn--sm{padding:.32rem .8rem;font-size:.78rem}

    /* ── tabs ── */
    .st-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.1rem}
    .st-tabs a{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:99px;font-weight:600;font-size:.85rem;color:var(--c,#5b5f8a) !important;background:var(--t,#f5f6ff);border:1px solid var(--b,#e6e8f5);text-decoration:none !important;transition:all .18s ease}
    .st-tabs a:hover{transform:translateY(-2px);box-shadow:0 6px 14px rgba(15,23,42,.1)}
    .st-tabs a.on{background:var(--c);border-color:var(--c);color:#fff !important;box-shadow:0 6px 16px rgba(15,23,42,.18)}

    /* ── toolbar ── */
    .st-bar{display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem}
    .st-bar form{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:0}
    .st-in,.st-bar select,.st-bar input[type=text],.st-bar input[type=date]{border:1px solid #d5d8ee;border-radius:10px;padding:.45rem .75rem;font-size:.85rem;background:#fff;color:#1e1b4b;transition:border-color .15s,box-shadow .15s}
    .st-in:focus,.st-bar select:focus,.st-bar input:focus{outline:none;border-color:#5351e4;box-shadow:0 0 0 3px rgba(83,81,228,.15)}
    .st-grow{flex:1}

    /* ── KPI cards (coloured, lift on hover, stagger in) ── */
    .st-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:1.4rem}
    .st-kpi{position:relative;overflow:hidden;background:linear-gradient(160deg,var(--t,#fff) 0%,#fff 75%);border:1px solid var(--b,#e6e8f5);border-radius:14px;padding:16px 18px 14px 22px;box-shadow:0 1px 2px rgba(16,24,40,.04);transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;animation:stUp .55s cubic-bezier(.2,.8,.2,1) both}
    .st-kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--c,#4f46e5)}
    .st-kpi:hover{transform:translateY(-3px);box-shadow:0 10px 24px rgba(15,23,42,.12);border-color:var(--c,#4f46e5)}
    .st-kpi-ic{position:absolute;top:14px;right:14px;width:40px;height:40px;border-radius:12px;background:var(--c,#4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;box-shadow:0 6px 14px rgba(15,23,42,.18);transition:transform .25s ease}
    .st-kpi:hover .st-kpi-ic{transform:rotate(-8deg) scale(1.08)}
    .st-kpi small{display:block;color:var(--c,#6b6f99);font-weight:700;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;padding-right:48px}
    .st-kpi b{display:block;font-size:1.6rem;color:#111827;margin-top:.25rem;line-height:1.15;font-weight:800}
    .st-kpi span{display:block;font-size:.78rem;color:var(--st-mute);margin-top:.2rem}
    .st-kpis > :nth-child(2){animation-delay:.06s}.st-kpis > :nth-child(3){animation-delay:.12s}.st-kpis > :nth-child(4){animation-delay:.18s}.st-kpis > :nth-child(5){animation-delay:.24s}.st-kpis > :nth-child(6){animation-delay:.3s}

    /* ── section cards ── */
    .st-card{background:#fff;border:1px solid var(--st-line);border-top:4px solid var(--c,#4f46e5);border-radius:14px;padding:1.1rem 1.25rem;margin-bottom:1.25rem;box-shadow:0 1px 3px rgba(16,24,40,.05);transition:box-shadow .2s ease;animation:stUp .6s cubic-bezier(.2,.8,.2,1) both}
    .st-card:hover{box-shadow:0 8px 24px rgba(44,41,202,.10)}
    .st-card h5{font-weight:700;color:var(--st-ink);margin:0 0 .9rem;font-size:1.02rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
    .st-card h5 .st-h-ic{width:30px;height:30px;border-radius:9px;background:var(--t);color:var(--c);display:inline-flex;align-items:center;justify-content:center;font-size:.85rem}
    .st-card h5 small{font-weight:500;color:var(--st-mute)}
    .st-scroll{overflow-x:auto;border-radius:10px}
    .st-table{width:100%;border-collapse:separate;border-spacing:0;font-size:.86rem}
    .st-table thead th:first-child{border-top-left-radius:10px}.st-table thead th:last-child{border-top-right-radius:10px}
    .st-table th,.st-table td{padding:.7rem .8rem;white-space:nowrap;vertical-align:middle}
    .st-table tbody tr{transition:background .15s ease,box-shadow .15s ease}
    .st-table tbody td{border-bottom:1px solid #eef0fa}
    .st-table tbody tr:nth-child(even) td{background:#fafbff}
    .st-table tbody tr:hover td{background:#eef0ff}
    .st-table th a{color:#fff !important;text-decoration:none !important}.st-table th a:hover{text-decoration:underline !important}
    .st-table .num{text-align:right;font-variant-numeric:tabular-nums}
    .st-mute{color:#8a8eb0}

    /* ── people ── */
    .st-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--c,#667eea),#764ba2);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;flex-shrink:0;box-shadow:0 3px 8px rgba(15,23,42,.18)}
    .st-avatar--lg{width:68px;height:68px;font-size:1.35rem;border:3px solid #fff}
    .st-who{display:flex;align-items:center;gap:.7rem}.st-who a{font-weight:700;color:#1e1b4b !important;text-decoration:none !important}.st-who a:hover{color:#2C29CA !important}.st-who small{display:block;color:#8a8eb0}

    /* ── pills / progress / rank ── */
    .st-pill{display:inline-flex;align-items:center;gap:.35rem;padding:.18rem .65rem;border-radius:99px;font-size:.72rem;font-weight:700}
    .st-pill::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
    .st-pill.ok,.st-pill.achieved,.st-pill.on_track{background:#dcfce7;color:#166534}
    .st-pill.warn,.st-pill.at_risk{background:#fef3c7;color:#92400e}
    .st-pill.bad,.st-pill.behind,.st-pill.missed{background:#fee2e2;color:#991b1b}
    .st-pill.mute,.st-pill.upcoming,.st-pill.no_target{background:#e5e7eb;color:#374151}
    .st-prog{height:9px;border-radius:99px;background:#e8eafb;overflow:hidden;min-width:90px}
    .st-prog i{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#4f46e5,#7c3aed);transform-origin:left;animation:stGrow 1s cubic-bezier(.2,.8,.2,1) both .2s}
    .st-prog.achieved i,.st-prog.on_track i{background:linear-gradient(90deg,#059669,#34d399)}
    .st-prog.at_risk i{background:linear-gradient(90deg,#d97706,#fbbf24)}
    .st-prog.behind i,.st-prog.missed i{background:linear-gradient(90deg,#dc2626,#fb7185)}
    .st-rank{display:inline-flex;width:30px;height:30px;border-radius:50%;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;background:#eef0fa;color:#5b5f8a}
    .st-rank.r1{background:linear-gradient(135deg,#fde047,#f59e0b);color:#5c3b00}.st-rank.r2{background:linear-gradient(135deg,#e5e7eb,#9ca3af);color:#1f2937}.st-rank.r3{background:linear-gradient(135deg,#fdba74,#c2410c);color:#fff}

    /* ── podium ── */
    .st-podium{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:1.4rem;align-items:end}
    .st-pod{position:relative;background:linear-gradient(170deg,var(--t) 0%,#fff 70%);border:1px solid var(--b);border-radius:18px;padding:1.3rem 1rem 1.1rem;text-align:center;transition:transform .22s ease,box-shadow .22s ease;animation:stUp .7s cubic-bezier(.2,.8,.2,1) both}
    .st-pod:hover{transform:translateY(-5px);box-shadow:0 14px 30px rgba(15,23,42,.14)}
    .st-pod.r1{--c:#d97706;--t:#fffbeb;--b:#fcd34d;padding-top:1.7rem;animation:stUp .7s both,stGlow 3s ease-in-out infinite 1s}
    .st-pod.r2{--c:#64748b;--t:#f1f5f9;--b:#cbd5e1;animation-delay:.1s}.st-pod.r3{--c:#c2410c;--t:#fff7ed;--b:#fdba74;animation-delay:.2s}
    .st-pod .st-rank{position:absolute;top:.8rem;left:.8rem}
    .st-pod .crown{position:absolute;top:.7rem;right:.9rem;color:#f59e0b;font-size:1.2rem}
    .st-pod h6{margin:.7rem 0 0;font-weight:700;color:var(--st-ink)}.st-pod h6 a{color:inherit !important;text-decoration:none !important}
    .st-pod .big{font-size:1.5rem;font-weight:800;color:var(--c);margin-top:.35rem}
    .st-pod .sub{font-size:.78rem;color:var(--st-mute)}

    /* ── flash / empty / notes ── */
    .st-flash{display:flex;align-items:center;gap:.6rem;padding:.8rem 1rem;border-radius:12px;margin-bottom:1rem;font-weight:600;font-size:.88rem;animation:stUp .4s both}
    .st-flash.ok{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}.st-flash.err{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}.st-flash.warn{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
    .st-flash a{color:inherit;text-decoration:underline}
    .st-empty{text-align:center;padding:2.4rem 1rem;color:var(--st-mute)}.st-empty i{font-size:2.2rem;color:#c7c9f5;display:block;margin-bottom:.6rem}
    .st-grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:1.25rem}
    .st-tg{display:grid;grid-template-columns:1fr auto;gap:.35rem .8rem;align-items:center;padding:.8rem 0;border-bottom:1px solid #eef0fa}
    .st-tg:last-child{border:0}.st-tg .full{grid-column:1/-1}
    .st-note{font-size:.82rem;color:#3b3f6b;background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:1px solid #ddd6fe;border-left:4px solid #7c3aed;border-radius:12px;padding:.8rem 1rem}

    /* ── dialogs ── */
    dialog.st-dlg:not([open]){display:none !important}
    dialog.st-dlg[open]{display:block;position:fixed;inset:0;margin:auto;max-height:92vh;overflow:auto}
    dialog.st-dlg{border:0;border-radius:20px;padding:0;width:min(540px,94vw);box-shadow:0 24px 70px rgba(7,1,137,.4);animation:stPop .25s cubic-bezier(.2,.8,.2,1)}
    dialog.st-dlg::backdrop{background:rgba(10,8,60,.6);backdrop-filter:blur(3px)}
    .st-dlg form{padding:1.5rem 1.6rem}.st-dlg h5{font-weight:700;color:#1e1b4b;margin-bottom:1rem;display:flex;align-items:center;gap:.6rem}
    .st-dlg label{display:block;font-size:.8rem;font-weight:600;color:#3b3f6b;margin:.75rem 0 .25rem}
    .st-dlg input,.st-dlg select,.st-dlg textarea{width:100%;border:1px solid #d5d8ee;border-radius:10px;padding:.5rem .75rem;font-size:.88rem;transition:border-color .15s,box-shadow .15s}
    .st-dlg input:focus,.st-dlg select:focus,.st-dlg textarea:focus{outline:none;border-color:#5351e4;box-shadow:0 0 0 3px rgba(83,81,228,.15)}
    .st-dlg .row2{display:grid;grid-template-columns:1fr 1fr;gap:.7rem}
    .st-dlg .foot{display:flex;justify-content:flex-end;gap:.5rem;margin-top:1.3rem}
    .st-chart text{font-size:11px;fill:#6b6f99}.st-chart rect{transition:opacity .2s}.st-chart rect:hover{opacity:1 !important}
    .st-chart .bar{transform-origin:bottom;transform-box:fill-box;animation:stUp .8s cubic-bezier(.2,.8,.2,1) both}
    @media(max-width:640px){.st-hero{padding:1.2rem}.st-dlg .row2{grid-template-columns:1fr}}
</style>
<script>
    function stOpen(id){var d=document.getElementById(id);if(d&&d.showModal)d.showModal();}
    function stClose(el){el.closest('dialog').close();}
    document.addEventListener('click',function(e){if(e.target.tagName==='DIALOG')e.target.close();});
</script>
