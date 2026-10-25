@extends('layouts-side-bar.master')

@section('css')
    {{-- Font Awesome 6 is needed for icons such as fa-stairs / fa-arrow-up-right-dots. No web-font is loaded here: the
         module inherits the same font as the rest of the system (same as the Sales Team module). --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ═════════ Student Promotion: scoped look (matches Sales Team / School Activity / Billing) ═════════
           Everything lives under .pr-page (or inside SweetAlert popups), so nothing here can restyle the
           sidebar, header or footer any more. */
        .pr-page, .swal2-container {
            --b: #2C29CA; --bl: rgba(44, 41, 202, .10); --b2: #1f1db0;
            --r: #dc2626; --rl: rgba(220, 38, 38, .10);
            --g: #059669; --gl: rgba(5, 150, 105, .12);
            --a: #d97706; --al: rgba(217, 119, 6, .14);
            --surf: #fff; --brd: #e6e8f5;
            --t1: #1e1b4b; --t2: #475569; --t3: #94a3b8;
            --rad: 14px; --rads: 10px; --sh: 0 1px 3px rgba(16, 24, 40, .05);
        }
        .pr-page { padding: 4px 4px 40px; }
        .pr-page *, .pr-page *::before, .pr-page *::after { box-sizing: border-box; }
        /* the batch modal (z-index 9990) must never hide SweetAlert confirmations opened from inside it */
        .swal2-container { z-index: 10050 !important; }

        /* palettes: --pc solid, --pt tint, --pb border */
        .pc-indigo { --pc: #4f46e5; --pt: #eef2ff; --pb: #c7d2fe; } .pc-violet { --pc: #7c3aed; --pt: #f5f3ff; --pb: #ddd6fe; }
        .pc-emerald { --pc: #059669; --pt: #ecfdf5; --pb: #a7f3d0; } .pc-amber { --pc: #d97706; --pt: #fffbeb; --pb: #fde68a; }
        .pc-sky { --pc: #0284c7; --pt: #f0f9ff; --pb: #bae6fd; } .pc-rose { --pc: #e11d48; --pt: #fff1f2; --pb: #fecdd3; }
        .pc-teal { --pc: #0d9488; --pt: #f0fdfa; --pb: #99f6e4; } .pc-slate { --pc: #475569; --pt: #f8fafc; --pb: #e2e8f0; }

        /* ── animations (all switched off for people who prefer reduced motion) ── */
        @keyframes prUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes prPop { from { opacity: 0; transform: scale(.96) translateY(10px); } to { opacity: 1; transform: none; } }
        @keyframes prGlow { 0%, 100% { box-shadow: 0 4px 18px rgba(217, 119, 6, .16); } 50% { box-shadow: 0 8px 30px rgba(217, 119, 6, .42); } }
        @media (prefers-reduced-motion: reduce) {
            .pr-page *, .pr-page *::before, .pr-modal-bg, .pr-modal { animation: none !important; transition: none !important; }
        }

        /* ── hero ── */
        .pr-hero { position: relative; overflow: hidden; background: linear-gradient(135deg, #000 0%, #070189 100%); border-radius: 20px; padding: 1.5rem 2rem; margin-bottom: 1.5rem; border: 1px solid rgba(255, 255, 255, .08); box-shadow: 0 4px 24px rgba(7, 1, 137, .3); color: #fff; animation: prUp .5s both; }
        .pr-hero::before { content: ''; position: absolute; top: -70px; right: -70px; width: 260px; height: 260px; border-radius: 50%; background: radial-gradient(circle, rgba(165, 180, 252, .22) 0%, transparent 70%); pointer-events: none; }
        .pr-hero > * { position: relative; }
        .pr-hero-left { display: flex; align-items: center; gap: 1.1rem; min-width: 0; }
        .pr-hero-icon { width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(102, 126, 234, .35); }
        .pr-hero h4 { font-size: 1.35rem; font-weight: 700; color: #fff !important; margin: 0 0 .15rem; display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .pr-hero p { color: rgba(255, 255, 255, .75) !important; font-size: .9rem; margin: 0; word-break: break-word; }
        .pr-hero-chip { font-size: .7rem; font-weight: 700; padding: .2rem .65rem; border-radius: 99px; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .25); color: #fff; display: inline-flex; align-items: center; gap: .35rem; }
        .pr-hero-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid rgba(255, 255, 255, .08); }
        .pr-hero-feature { display: flex; align-items: center; gap: .7rem; animation: prUp .6s both; }
        .pr-hero-feature:nth-child(2) { animation-delay: .08s; } .pr-hero-feature:nth-child(3) { animation-delay: .16s; } .pr-hero-feature:nth-child(4) { animation-delay: .24s; }
        .pr-hero-feature-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .pr-hero-feature-title { font-size: .82rem; font-weight: 700; color: #fff; } .pr-hero-feature-desc { font-size: .74rem; color: rgba(255, 255, 255, .62); }

        /* ── KPI cards (coloured, lift on hover, stagger in) ── */
        .pr-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 1.4rem; }
        .pr-kpi { position: relative; overflow: hidden; background: linear-gradient(160deg, var(--pt, #fff) 0%, #fff 75%); border: 1px solid var(--pb, var(--brd)); border-radius: 14px; padding: 16px 18px 14px 22px; box-shadow: 0 1px 2px rgba(16, 24, 40, .04); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; animation: prUp .55s cubic-bezier(.2, .8, .2, 1) both; }
        .pr-kpi::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: var(--pc, #4f46e5); }
        .pr-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15, 23, 42, .12); border-color: var(--pc, #4f46e5); }
        .pr-kpi-ic { position: absolute; top: 14px; right: 14px; width: 40px; height: 40px; border-radius: 12px; background: var(--pc, #4f46e5); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: 0 6px 14px rgba(15, 23, 42, .18); transition: transform .25s ease; }
        .pr-kpi:hover .pr-kpi-ic { transform: rotate(-8deg) scale(1.08); }
        .pr-kpi small { display: block; color: var(--pc, #6b6f99); font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; padding-right: 48px; }
        .pr-kpi b { display: block; font-size: 1.6rem; color: #111827; margin-top: .25rem; line-height: 1.15; font-weight: 800; }
        .pr-kpi b.sm { font-size: 1.15rem; padding: .3rem 0 .15rem; }
        .pr-kpi span { display: block; font-size: .78rem; color: #6b6f99; margin-top: .2rem; }
        .pr-kpis > :nth-child(2) { animation-delay: .06s; } .pr-kpis > :nth-child(3) { animation-delay: .12s; } .pr-kpis > :nth-child(4) { animation-delay: .18s; }

        /* ── tabs ── */
        .pr-tabs { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.1rem; }
        .pr-tab { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1.05rem; border-radius: 99px; font-weight: 600; font-size: .85rem; color: var(--pc, #5b5f8a); background: var(--pt, #f5f6ff); border: 1px solid var(--pb, var(--brd)); cursor: pointer; transition: all .18s ease; }
        .pr-tab:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(15, 23, 42, .1); }
        .pr-tab.active { background: var(--pc); border-color: var(--pc); color: #fff; box-shadow: 0 6px 16px rgba(15, 23, 42, .18); }
        .pr-tab .cnt { background: var(--r); color: #fff; border-radius: 99px; font-size: .68rem; font-weight: 700; padding: 1px 7px; }
        .pr-pane { display: none; }
        .pr-pane.active { display: block; animation: prUp .4s cubic-bezier(.2, .8, .2, 1) both; }

        /* ── steps strip ── */
        .pr-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 1.25rem; }
        .pr-step { display: flex; align-items: center; gap: .75rem; background: var(--pt); border: 1px solid var(--pb); border-radius: 14px; padding: .7rem .9rem; transition: transform .2s ease, box-shadow .2s ease; }
        .pr-step:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(15, 23, 42, .1); }
        .pr-step-n { width: 32px; height: 32px; border-radius: 50%; background: var(--pc); color: #fff; font-weight: 800; font-size: .85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(15, 23, 42, .18); }
        .pr-step b { display: block; font-size: .86rem; color: var(--t1); } .pr-step small { color: var(--t2); font-size: .76rem; }

        /* ── cards ── */
        .pr-card { background: #fff; border: 1px solid var(--brd); border-top: 4px solid var(--pc, #4f46e5); border-radius: var(--rad); box-shadow: var(--sh); margin-bottom: 1.25rem; transition: box-shadow .2s ease; animation: prUp .55s cubic-bezier(.2, .8, .2, 1) both; }
        .pr-card:hover { box-shadow: 0 8px 24px rgba(44, 41, 202, .10); }
        .pr-card--flat { border-top: 1px solid var(--brd); }
        .pr-card--pending { border-color: var(--pb); border-top-color: var(--pc); animation: prUp .55s both, prGlow 3s ease-in-out infinite 1s; }
        .pr-card-hd { padding: 1rem 1.4rem; border-bottom: 1px solid var(--brd); display: flex; align-items: center; justify-content: space-between; background: linear-gradient(180deg, var(--pt, #fafbff) 0%, #fff 100%); border-radius: 10px 10px 0 0; gap: 12px; flex-wrap: wrap; }
        .pr-card-hd h5 { margin: 0; font-size: .98rem; font-weight: 700; color: var(--t1); display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .pr-card-hd p { margin: 3px 0 0; font-size: .8rem; color: var(--t2); }
        .pr-h-ic { width: 30px; height: 30px; border-radius: 9px; background: var(--pt); color: var(--pc); border: 1px solid var(--pb); display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
        .pr-card-bd { padding: 1.25rem 1.4rem; }

        /* ── forms ── */
        .pr-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; align-items: start; }
        @media (max-width: 900px) { .pr-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .pr-grid { grid-template-columns: 1fr; } }
        .pr-field label { display: block; font-size: .76rem; font-weight: 700; color: #3b3f6b; margin-bottom: 5px; }
        .pr-field .hint { font-size: .72rem; color: var(--t3); margin-top: 4px; }
        .pr-in, .pr-sel { width: 100%; border: 1px solid #d5d8ee; border-radius: 10px; padding: .5rem .75rem; font-size: .85rem; background: #fff; color: var(--t1); transition: border-color .15s, box-shadow .15s; }
        .pr-in:focus, .pr-sel:focus { outline: none; border-color: #5351e4; box-shadow: 0 0 0 3px rgba(83, 81, 228, .15); }
        .pr-sel.sm { padding: .38rem .55rem; font-size: .8rem; }
        .pr-sel.invalid { border-color: var(--r); background: var(--rl); }
        .pr-sel:disabled { background: #f4f5fb; color: var(--t3); cursor: not-allowed; }

        /* ── buttons ── */
        .pr-page .btn-p, .pr-page .btn-g, .pr-page .btn-o, .pr-page .btn-d { display: inline-flex; align-items: center; gap: .5rem; border-radius: 99px; font-weight: 600; font-size: .85rem; line-height: 1.2; padding: .55rem 1.15rem; cursor: pointer; text-decoration: none !important; white-space: nowrap; border: 1px solid transparent; transition: transform .18s ease, box-shadow .18s ease, background .18s ease, filter .18s ease; }
        .pr-page .btn-p { background: linear-gradient(135deg, #2C29CA, #6d28d9); color: #fff !important; }
        .pr-page .btn-g { background: linear-gradient(135deg, #059669, #10b981); color: #fff !important; }
        .pr-page .btn-o { background: #fff; border-color: #c7c9f5; color: #2C29CA !important; }
        .pr-page .btn-d { background: #fff; border-color: #fecaca; color: #b42318 !important; }
        .pr-page .btn-p:hover, .pr-page .btn-g:hover { filter: brightness(1.1); transform: translateY(-2px); box-shadow: 0 8px 18px rgba(44, 41, 202, .25); color: #fff !important; }
        .pr-page .btn-g:hover { box-shadow: 0 8px 18px rgba(5, 150, 105, .3); }
        .pr-page .btn-o:hover { background: #eef0ff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(44, 41, 202, .18); }
        .pr-page .btn-d:hover { background: #fef2f2; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(220, 38, 38, .18); }
        .pr-page .btn-p:active, .pr-page .btn-g:active, .pr-page .btn-o:active, .pr-page .btn-d:active { transform: translateY(0); }
        .pr-page .btn-p:disabled, .pr-page .btn-g:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; filter: none; }
        .pr-page .btn-xs { padding: .3rem .75rem; font-size: .76rem; }

        /* ── alerts / chips (also used inside SweetAlert popups) ── */
        .pr-alert { border-radius: 12px; padding: .8rem 1rem; font-size: .84rem; margin-bottom: 1rem; display: flex; gap: .7rem; align-items: flex-start; border: 1px solid transparent; border-left-width: 4px; text-align: left; }
        .pr-alert i { margin-top: 2px; }
        .pr-alert > div { flex: 1 1 220px; min-width: 0; }
        .pr-alert { flex-wrap: wrap; }
        .pr-alert.info { background: linear-gradient(135deg, #eef2ff, #f5f3ff); border-color: #c7d2fe; border-left-color: #4f46e5; color: #312e81; }
        .pr-alert.warning { background: #fffbeb; border-color: #fde68a; border-left-color: #d97706; color: #92400e; }
        .pr-alert.danger { background: #fff1f2; border-color: #fecdd3; border-left-color: #e11d48; color: #991b1b; }
        .pr-alert.success { background: #ecfdf5; border-color: #a7f3d0; border-left-color: #059669; color: #065f46; }
        .pr-meta { font-size: .74rem; color: var(--t2); }
        /* the theme gives .chip a fixed 2rem height / line-height; reset it so chips stay compact */
        .pr-page .chip, .swal2-container .chip { display: inline-flex; align-items: center; gap: 5px; height: auto; line-height: 1.35; border-radius: 99px; padding: 3px 10px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
        .pr-page .chip.g, .swal2-container .chip.g { background: #dcfce7; color: #166534; } .pr-page .chip.r, .swal2-container .chip.r { background: #fee2e2; color: #991b1b; }
        .pr-page .chip.a, .swal2-container .chip.a { background: #fef3c7; color: #92400e; } .pr-page .chip.b, .swal2-container .chip.b { background: #e0e7ff; color: #3730a3; }
        .pr-page .chip.n, .swal2-container .chip.n { background: #e5e7eb; color: #374151; }

        /* ── tables ── */
        .pr-table-wrap { overflow-x: auto; border-radius: 12px; border: 1px solid var(--brd); }
        table.pr-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .84rem; margin: 0; }
        .pr-table thead th { background: #2C29CA !important; color: #fff !important; text-align: left; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: .75rem .85rem; white-space: nowrap; border: 0; }
        .pr-table td { padding: .7rem .85rem; border-bottom: 1px solid #eef0fa; vertical-align: middle; background: #fff; transition: background .15s ease; }
        .pr-table tbody tr:nth-child(even) td { background: #fafbff; }
        .pr-table tbody tr:hover td { background: #eef0ff; }
        .pr-table tbody tr:last-child td { border-bottom: 0; }
        .pr-table tr.locked td { opacity: .55; }
        .pr-table tr.pr-o-promote td:first-child { box-shadow: inset 4px 0 0 var(--g); }
        .pr-table tr.pr-o-repeat td:first-child { box-shadow: inset 4px 0 0 var(--a); }
        .pr-table tr.pr-o-graduate td:first-child { box-shadow: inset 4px 0 0 #4f46e5; }
        .pr-table tr.pr-o-skip td:first-child { box-shadow: inset 4px 0 0 #cbd5e1; }
        .pr-name { font-weight: 700; color: var(--t1); }
        .pr-who { display: flex; align-items: center; gap: .65rem; }
        .pr-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .76rem; flex-shrink: 0; box-shadow: 0 3px 8px rgba(15, 23, 42, .18); }
        .pr-table td.nw { white-space: nowrap; }
        .pr-acts { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }

        .pr-summary { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 1rem; }
        .pr-bulk { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 1rem; }
        .pr-bulk .pr-in { max-width: 260px; }
        .pr-footer { position: sticky; bottom: 0; background: #fff; border-top: 1px solid var(--brd); padding: .9rem 1.4rem; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; border-radius: 0 0 var(--rad) var(--rad); box-shadow: 0 -6px 16px rgba(15, 23, 42, .05); }

        .pr-page .empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--t2); }
        .pr-page .empty-state i { font-size: 2.2rem; color: #c7c9f5; margin-bottom: .6rem; display: block; }

        /* ── setup ── */
        .mode-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 760px) { .mode-grid { grid-template-columns: 1fr; } }
        .mode-card { position: relative; border: 2px solid var(--brd); border-radius: var(--rad); padding: 1.1rem 1.2rem; cursor: pointer; display: flex; gap: 14px; align-items: flex-start; background: #fff; transition: all .18s ease; }
        .mode-card:hover { border-color: var(--pb); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15, 23, 42, .08); }
        .mode-card.active { border-color: var(--pc); background: linear-gradient(160deg, var(--pt) 0%, #fff 80%); box-shadow: 0 8px 22px rgba(15, 23, 42, .1); }
        .mode-card:focus-within { box-shadow: 0 0 0 3px rgba(83, 81, 228, .2); }
        .mode-card input { position: absolute; opacity: 0; pointer-events: none; }
        .mode-ic { width: 44px; height: 44px; border-radius: 12px; background: var(--pt); color: var(--pc); border: 1px solid var(--pb); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; transition: all .18s ease; }
        .mode-card.active .mode-ic { background: var(--pc); color: #fff; border-color: var(--pc); box-shadow: 0 6px 14px rgba(15, 23, 42, .2); }
        .mode-card::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; top: 12px; right: 12px; width: 22px; height: 22px; border-radius: 50%; background: var(--pc); color: #fff; font-size: .62rem; display: flex; align-items: center; justify-content: center; opacity: 0; transform: scale(.5); transition: all .18s ease; }
        .mode-card.active::after { opacity: 1; transform: none; }
        .mode-card h6 { margin: 0 0 3px; font-size: .95rem; font-weight: 700; color: var(--t1); }
        .mode-card p { margin: 0; font-size: .8rem; color: var(--t2); }
        .sub-box { border: 1px dashed #c7d2fe; border-radius: var(--rads); padding: 1rem 1.1rem; margin-top: 1rem; background: linear-gradient(135deg, #f8f9ff, #fdfaff); }
        .sub-box .sb-title { font-weight: 700; font-size: .85rem; color: var(--t1); }
        .opt-row { display: flex; gap: 10px; align-items: flex-start; padding: .4rem 0; font-size: .85rem; color: var(--t1); cursor: pointer; }
        .opt-row input { margin-top: 3px; accent-color: var(--b); }
        .opt-row small { display: block; color: var(--t2); font-size: .76rem; }
        .ladder-row.suggested td:first-child { box-shadow: inset 4px 0 0 var(--a); }
        .sw { position: relative; display: inline-block; width: 42px; height: 24px; }
        .sw input { opacity: 0; width: 0; height: 0; }
        .sw span { position: absolute; inset: 0; background: #cbd5e1; border-radius: 24px; cursor: pointer; transition: .2s; }
        .sw span::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); }
        .sw input:checked + span { background: linear-gradient(135deg, #059669, #10b981); }
        .sw input:checked + span::before { transform: translateX(18px); }
        .sw input:focus-visible + span { box-shadow: 0 0 0 3px rgba(83, 81, 228, .25); }

        /* ── batch details modal ── */
        .pr-modal-bg { position: fixed; inset: 0; background: rgba(10, 8, 60, .6); backdrop-filter: blur(3px); z-index: 9990; display: none; align-items: center; justify-content: center; padding: 1rem; }
        .pr-modal-bg.show { display: flex; }
        .pr-modal { background: #fff; border-radius: 20px; width: 100%; max-width: 1050px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 24px 70px rgba(7, 1, 137, .4); overflow: hidden; }
        .pr-modal-bg.show .pr-modal { animation: prPop .25s cubic-bezier(.2, .8, .2, 1); }
        .pr-modal-hd { background: linear-gradient(135deg, #000 0%, #070189 100%); color: #fff; padding: 1rem 1.4rem; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .pr-modal-hd h5 { margin: 0; font-size: 1rem; font-weight: 700; color: #fff; }
        .pr-modal-bd { padding: 1.1rem 1.4rem 1.4rem; overflow: auto; }
        .pr-x { width: 32px; height: 32px; border-radius: 50%; background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25); font-size: 1.2rem; line-height: 1; color: #fff; cursor: pointer; transition: all .18s ease; }
        .pr-x:hover { background: rgba(255, 255, 255, .28); transform: rotate(90deg); }

        /* ── pagination ── */
        .pr-page .pagination { display: flex; gap: .25rem; align-items: center; flex-wrap: wrap; }
        .pr-page .pagination .page-link { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 .55rem; border-radius: 10px; font-size: .8rem; font-weight: 600; color: var(--t2); background: var(--surf); border: 1px solid var(--brd); text-decoration: none; transition: all .15s ease; }
        .pr-page .pagination .page-link:hover { background: #eef0ff; color: #2C29CA; transform: translateY(-1px); }
        .pr-page .pagination .page-item.active .page-link { background: linear-gradient(135deg, #2C29CA, #6d28d9); color: #fff; border-color: transparent; }
        .pr-page .pagination .page-item.disabled .page-link { opacity: .4; pointer-events: none; }

        @media (max-width: 640px) { .pr-hero { padding: 1.2rem; } .pr-card-bd { padding: 1rem; } .pr-card-hd { padding: .9rem 1rem; } .pr-footer { padding: .8rem 1rem; } }
    </style>
@endsection

@section('content')
@php
    $classList = $classes->map(fn ($c) => [
        'id' => (string) $c['id'], 'name' => $c['name'], 'type' => $c['type'], 'students' => $c['students'], 'streams' => $c['streams'],
    ])->values();

    $ladderInit = $classes->map(function ($c) use ($paths, $suggested) {
        $p = $paths->get($c['id']);
        if ($p) {
            return ['from' => (string) $c['id'], 'to' => $p->to_class_id ? (string) $p->to_class_id : '', 'final' => (bool) $p->is_final,
                'stream_mode' => $p->stream_mode, 'fallback' => $p->fallback_stream ?? '', 'auto' => (bool) $p->auto_eligible, 'suggested' => false, 'note' => null];
        }
        $s = $suggested[$c['id']] ?? ['to' => null, 'final' => false, 'auto' => true, 'stream_mode' => 'same_name', 'note' => null];
        return ['from' => (string) $c['id'], 'to' => $s['to'] ?? '', 'final' => (bool) $s['final'], 'stream_mode' => $s['stream_mode'],
            'fallback' => '', 'auto' => (bool) $s['auto'], 'suggested' => true, 'note' => $s['note']];
    })->values();

    $yearNames = $years->pluck('name')->all();
    if ($defaultYear && !in_array($defaultYear, $yearNames, true)) { array_unshift($yearNames, $defaultYear); }

    $outcomeChip = ['promoted' => ['g', 'Promoted'], 'repeated' => ['a', 'Repeating'], 'graduated' => ['b', 'Graduated']];
    $statusChip = ['completed' => ['g', 'Completed'], 'partially_reverted' => ['a', 'Partly reverted'], 'reverted' => ['n', 'Reverted'], 'discarded' => ['n', 'Discarded'], 'pending' => ['a', 'Awaiting approval']];

    // [icon, colour, title, description]
    $heroFeatures = [
        ['fa-arrow-up-right-dots', '#fbbf24', 'Manual or automatic', 'Promote by hand or on a schedule'],
        ['fa-scale-balanced', '#818cf8', 'Smart rules', 'Fees, averages and final classes'],
        ['fa-clock-rotate-left', '#34d399', 'Full history', 'Every run and every student recorded'],
        ['fa-rotate-left', '#f472b6', 'Undo anytime', 'Revert a whole run or one student'],
    ];
@endphp

    <div class="pr-page">

    {{-- ════════════════════════ HERO ════════════════════════ --}}
    <div class="pr-hero mt-5">
        <div class="pr-hero-left">
            <div class="pr-hero-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
                <h4>Promote Students
                    @if ($defaultYear)<span class="pr-hero-chip"><i class="fas fa-calendar-days"></i> {{ $defaultYear }}</span>@endif
                    <span class="pr-hero-chip"><i class="fas {{ $setting->isAuto() ? 'fa-robot' : 'fa-hand-pointer' }}"></i> {{ $setting->isAuto() ? 'Automatic' : 'Manual' }}</span>
                </h4>
                <p>Move students up to the next class at the end of the year — by hand or automatically — with a full history you can review and undo.</p>
            </div>
        </div>
        <div class="pr-hero-features">
            @foreach ($heroFeatures as $f)
                <div class="pr-hero-feature">
                    <div class="pr-hero-feature-icon" style="background:{{ $f[1] }}26;"><i class="fas {{ $f[0] }}" style="color:{{ $f[1] }};"></i></div>
                    <div><div class="pr-hero-feature-title">{{ $f[2] }}</div><div class="pr-hero-feature-desc">{{ $f[3] }}</div></div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="pr-card pr-card--flat pc-slate">
        <div class="pr-card-bd">@include('layouts.subjects-buttons')</div>
    </div>

    <div class="pr-kpis">
        <div class="pr-kpi pc-indigo"><i class="fas fa-user-graduate pr-kpi-ic"></i><small>Enrolled students</small>
            <b>{{ number_format($stats['enrolled']) }}</b><span>Currently in class lists</span></div>
        <div class="pr-kpi pc-emerald"><i class="fas fa-graduation-cap pr-kpi-ic"></i><small>Alumni (graduated)</small>
            <b>{{ number_format($stats['alumni']) }}</b><span>Moved out of class lists</span></div>
        <div class="pr-kpi pc-amber"><i class="fas fa-hourglass-half pr-kpi-ic"></i><small>Awaiting approval</small>
            <b>{{ number_format($stats['pending']) }}</b><span>Proposals waiting for a decision</span></div>
        <div class="pr-kpi pc-sky"><i class="fas fa-clock-rotate-left pr-kpi-ic"></i><small>Last promotion</small>
            <b class="sm">{{ $stats['last_run'] ? ($stats['last_run']->executed_at?->format('d M Y') ?? '—') : 'No promotion yet' }}</b>
            <span>{{ $stats['last_run'] ? $stats['last_run']->reference : 'Run one from the Promote tab' }}</span></div>
    </div>

    <div class="pr-tabs" id="prTabs">
        <button type="button" class="pr-tab pc-indigo {{ $tab === 'promote' ? 'active' : '' }}" data-tab="promote"><i class="fas fa-arrow-up-right-dots"></i> Promote @if ($pending->isNotEmpty())<span class="cnt">{{ $pending->count() }}</span>@endif</button>
        <button type="button" class="pr-tab pc-violet {{ $tab === 'setup' ? 'active' : '' }}" data-tab="setup"><i class="fas fa-sliders"></i> Setup</button>
        <button type="button" class="pr-tab pc-amber {{ $tab === 'history' ? 'active' : '' }}" data-tab="history"><i class="fas fa-clock-rotate-left"></i> History</button>
        <button type="button" class="pr-tab pc-emerald {{ $tab === 'alumni' ? 'active' : '' }}" data-tab="alumni"><i class="fas fa-graduation-cap"></i> Alumni</button>
    </div>

    {{-- ════════════════════════ PROMOTE ════════════════════════ --}}
    <div class="pr-pane {{ $tab === 'promote' ? 'active' : '' }}" id="pane-promote">

        <div class="pr-steps">
            <div class="pr-step pc-indigo"><span class="pr-step-n">1</span><div><b>Choose</b><small>Year, class and stream</small></div></div>
            <div class="pr-step pc-violet"><span class="pr-step-n">2</span><div><b>Review</b><small>Decide for every student</small></div></div>
            <div class="pr-step pc-emerald"><span class="pr-step-n">3</span><div><b>Confirm</b><small>Promote and keep a full history</small></div></div>
        </div>

        @if (!$setting->isConfigured())
            <div class="pr-alert warning"><i class="fas fa-triangle-exclamation"></i>
                <div><strong>Set up promotion first.</strong> Tell SMASA which class follows which, and whether promotion is manual or automatic.
                    <a href="#" data-goto="setup" style="font-weight:700;">Open Setup →</a></div></div>
        @endif

        @if ($setting->isAuto() && $setting->isConfigured())
            <div class="pr-alert info"><i class="fas fa-robot"></i>
                <div>
                    <strong>Automatic promotion is on.</strong>
                    @if ($setting->auto_trigger === 'date')
                        It runs on <strong>{{ $setting->auto_run_date?->format('d M Y') }}</strong>
                    @else
                        It runs when the school's active academic year changes
                    @endif
                    and {{ $setting->auto_apply ? 'applies the promotion straight away' : 'prepares a proposal for you to approve' }}.
                    Classes that are not allowed to run automatically (and students who need a decision) stay for the manual list below.
                    @if ($setting->last_auto_run_at) <span class="pr-meta">Last run {{ $setting->last_auto_run_at->diffForHumans() }}.</span> @endif
                </div>
                @if ($can['promote'])
                    <button class="btn-o btn-xs" id="btnRunAuto"><i class="fas fa-play"></i> Run now for selected year</button>
                @endif
            </div>
        @endif

        @if ($pending->isNotEmpty())
            <div class="pr-card pr-card--pending pc-amber">
                <div class="pr-card-hd">
                    <div><h5><span class="pr-h-ic"><i class="fas fa-hourglass-half"></i></span> Proposals awaiting your approval</h5>
                        <p>Prepared automatically. Nothing has moved yet — review, then approve or discard.</p></div>
                </div>
                <div class="pr-card-bd">
                    @foreach ($pending as $b)
                        <div style="display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;padding:.7rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--brd);' : '' }}">
                            <div>
                                <div class="pr-name">{{ $b->reference }} <span class="chip a">{{ $b->from_academic_year }}{{ $b->to_academic_year ? ' → ' . $b->to_academic_year : '' }}</span></div>
                                <div class="pr-meta">{{ $b->promoted }} to promote · {{ $b->repeated }} repeating · {{ $b->graduated }} to graduate · prepared {{ $b->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="pr-acts">
                                <button class="btn-o btn-xs" data-view-batch="{{ $b->id }}"><i class="fas fa-eye"></i> Review</button>
                                @if ($can['promote'])
                                    <button class="btn-g btn-xs" data-approve="{{ $b->id }}" data-ref="{{ $b->reference }}"><i class="fas fa-check"></i> Approve & apply</button>
                                    <button class="btn-d btn-xs" data-discard="{{ $b->id }}" data-ref="{{ $b->reference }}"><i class="fas fa-xmark"></i> Discard</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pr-card pc-indigo">
            <div class="pr-card-hd">
                <div><h5><span class="pr-h-ic"><i class="fas fa-filter"></i></span> Choose who to promote</h5>
                    <p>Work one class at a time. Pick the academic year that is ending, the class, and (optionally) a single stream.</p></div>
            </div>
            <div class="pr-card-bd">
                <div class="pr-grid">
                    <div class="pr-field"><label>Academic year being closed</label>
                        <select class="pr-sel" id="fYear">
                            @foreach ($yearNames as $yn) <option value="{{ $yn }}" {{ $yn === $defaultYear ? 'selected' : '' }}>{{ $yn }}</option> @endforeach
                        </select>
                        <div class="hint">A student can only be promoted once per year.</div></div>
                    <div class="pr-field"><label>Class</label>
                        <select class="pr-sel" id="fClass">
                            <option value="">— Select class —</option>
                            @foreach ($classList as $c) <option value="{{ $c['id'] }}">{{ $c['name'] }} ({{ $c['students'] }})</option> @endforeach
                        </select></div>
                    <div class="pr-field"><label>Stream</label>
                        <select class="pr-sel" id="fStream"><option value="">All streams</option></select></div>
                    <div class="pr-field"><label>New academic year (label, optional)</label>
                        <input class="pr-in" id="fToYear" placeholder="e.g. 2027" maxlength="50"></div>
                </div>
                <div style="margin-top:1.1rem;display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn-p" id="btnLoad"><i class="fas fa-users"></i> Load students</button>
                </div>
            </div>
        </div>

        <div id="readinessBox"></div>
        <div id="planBox" style="display:none;">
            <div class="pr-card pc-violet">
                <div class="pr-card-hd">
                    <div><h5><span class="pr-h-ic"><i class="fas fa-list-check"></i></span> <span id="planTitle">Students</span></h5><p id="planPath"></p></div>
                    <div class="pr-summary" id="planSummary" style="margin:0;"></div>
                </div>
                <div class="pr-card-bd">
                    <div class="pr-bulk">
                        <span class="pr-meta" style="font-weight:700;">Set everyone to:</span>
                        <button class="btn-o btn-xs" data-bulk="promote" id="bulkPromote"><i class="fas fa-arrow-up"></i> Promote</button>
                        <button class="btn-o btn-xs" data-bulk="repeat"><i class="fas fa-rotate-left"></i> Repeat</button>
                        <button class="btn-o btn-xs" data-bulk="graduate" id="bulkGraduate"><i class="fas fa-graduation-cap"></i> Graduate</button>
                        <button class="btn-o btn-xs" data-bulk="skip"><i class="fas fa-ban"></i> Leave out</button>
                        <button class="btn-o btn-xs" data-bulk="suggested"><i class="fas fa-wand-magic-sparkles"></i> Reset to suggestion</button>
                        <input class="pr-in" id="planSearch" placeholder="Filter by name or admission no…" style="margin-left:auto;">
                    </div>
                    <div class="pr-table-wrap">
                        <table class="pr-table">
                            <thead><tr>
                                <th>#</th><th>Student</th><th>Stream</th><th>Avg %</th><th>Fees</th><th>Notes</th><th>Result</th><th style="min-width:210px;">Destination</th>
                            </tr></thead>
                            <tbody id="planRows"></tbody>
                        </table>
                    </div>
                    <div class="empty-state" id="planEmpty" style="display:none;"><i class="fas fa-user-slash"></i><div style="font-weight:700;color:var(--t1);">No students in this selection</div></div>
                </div>
                <div class="pr-footer" id="planFooter">
                    <div style="flex:1;min-width:240px;"><input class="pr-in" id="fNote" maxlength="1000" placeholder="Note for the history (optional) — e.g. “End of year 2026 promotion”"></div>
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <span class="pr-meta" id="footCount"></span>
                        @if ($can['promote'])
                            <button class="btn-g" id="btnReview"><i class="fas fa-check-double"></i> Review & promote</button>
                        @else
                            <span class="chip n"><i class="fas fa-lock"></i> You can view but not run promotions</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════ SETUP ════════════════════════ --}}
    <div class="pr-pane {{ $tab === 'setup' ? 'active' : '' }}" id="pane-setup">
        @if (!$can['settings'])
            <div class="pr-alert warning"><i class="fas fa-lock"></i><div>You can view these settings but not change them.</div></div>
        @endif

        <div class="pr-card pc-indigo">
            <div class="pr-card-hd"><div><h5><span class="pr-h-ic"><i class="fas fa-route"></i></span> 1 · How should promotion work?</h5>
                <p>You can always run a manual promotion for exceptions, even in automatic mode.</p></div></div>
            <div class="pr-card-bd">
                <div class="mode-grid">
                    <label class="mode-card pc-indigo {{ !$setting->isAuto() ? 'active' : '' }}" id="modeManual">
                        <input type="radio" name="mode" value="manual" {{ !$setting->isAuto() ? 'checked' : '' }}>
                        <span class="mode-ic"><i class="fas fa-hand-pointer"></i></span>
                        <div><h6>Manual</h6>
                            <p>You choose a class, review every student and promote when you are ready.</p></div>
                    </label>
                    <label class="mode-card pc-violet {{ $setting->isAuto() ? 'active' : '' }}" id="modeAuto">
                        <input type="radio" name="mode" value="auto" {{ $setting->isAuto() ? 'checked' : '' }}>
                        <span class="mode-ic"><i class="fas fa-robot"></i></span>
                        <div><h6>Automatic</h6>
                            <p>SMASA prepares (or applies) the promotion by itself on a schedule, using the rules below.</p></div>
                    </label>
                </div>

                <div class="sub-box" id="autoBox" style="{{ $setting->isAuto() ? '' : 'display:none;' }}">
                    <div class="sb-title" style="margin-bottom:.4rem;"><i class="fas fa-calendar-check me-1" style="color:#7c3aed;"></i> When should it run?</div>
                    <label class="opt-row"><input type="radio" name="trigger" value="new_year" {{ $setting->auto_trigger !== 'date' ? 'checked' : '' }}>
                        <span>When a new academic year becomes active for the school<small>Recommended. Fires the first day after the active year in Term Dates / Academic Year changes.</small></span></label>
                    <label class="opt-row"><input type="radio" name="trigger" value="date" {{ $setting->auto_trigger === 'date' ? 'checked' : '' }}>
                        <span>On a fixed date <input type="date" class="pr-in" id="sRunDate" style="width:auto;display:inline-block;margin-left:6px;padding:4px 8px;" value="{{ $setting->auto_run_date?->format('Y-m-d') }}"></span></label>

                    <div class="sb-title" style="margin:1rem 0 .4rem;"><i class="fas fa-bolt me-1" style="color:#d97706;"></i> What should it do when it runs?</div>
                    <label class="opt-row"><input type="radio" name="apply" value="0" {{ !$setting->auto_apply ? 'checked' : '' }}>
                        <span>Prepare a proposal and wait for my approval<small>Recommended. Nothing moves until an authorised person approves it on the Promote tab.</small></span></label>
                    <label class="opt-row"><input type="radio" name="apply" value="1" {{ $setting->auto_apply ? 'checked' : '' }}>
                        <span>Apply it immediately<small>Students move as soon as the schedule fires. You can still revert the whole run from History.</small></span></label>
                </div>
            </div>
        </div>

        <div class="pr-card pc-violet">
            <div class="pr-card-hd"><div><h5><span class="pr-h-ic"><i class="fas fa-scale-balanced"></i></span> 2 · Promotion rules</h5>
                <p>These shape the suggested decision for every student. In the wizard you can still override soft rules; the fee “block” rule cannot be overridden.</p></div></div>
            <div class="pr-card-bd">
                <div class="pr-grid">
                    <div class="pr-field"><label>Outstanding fees</label>
                        <select class="pr-sel" id="sFeePolicy">
                            <option value="off" {{ $setting->fee_policy === 'off' ? 'selected' : '' }}>Ignore fees</option>
                            <option value="warn" {{ $setting->fee_policy === 'warn' ? 'selected' : '' }}>Warn only</option>
                            <option value="block" {{ $setting->fee_policy === 'block' ? 'selected' : '' }}>Block promotion</option>
                        </select></div>
                    <div class="pr-field"><label>Balance allowed before the rule applies</label>
                        <input type="number" min="0" step="1" class="pr-in" id="sFeeMax" value="{{ (int) $setting->fee_max_balance }}">
                        <div class="hint">0 = any unpaid balance counts.</div></div>
                    <div class="pr-field"><label>Minimum year average % (optional)</label>
                        <input type="number" min="0" max="100" step="0.1" class="pr-in" id="sMinAvg" value="{{ $setting->min_average }}" placeholder="No minimum">
                        <div class="hint">Uses the average of all examination marks in the year.</div></div>
                    <div class="pr-field"><label>Student with no results</label>
                        <select class="pr-sel" id="sMissing">
                            <option value="hold" {{ $setting->missing_results_policy === 'hold' ? 'selected' : '' }}>Hold back (repeat)</option>
                            <option value="promote" {{ $setting->missing_results_policy === 'promote' ? 'selected' : '' }}>Promote anyway</option>
                        </select>
                        <div class="hint">Only matters when a minimum average is set.</div></div>
                    <div class="pr-field"><label>Students in a final class</label>
                        <select class="pr-sel" id="sFinal">
                            <option value="graduate" {{ $setting->final_class_action === 'graduate' ? 'selected' : '' }}>Graduate (move to Alumni)</option>
                            <option value="stay" {{ $setting->final_class_action === 'stay' ? 'selected' : '' }}>Leave in class (decide later)</option>
                        </select></div>
                </div>
            </div>
        </div>

        <div class="pr-card pc-emerald">
            <div class="pr-card-hd">
                <div><h5><span class="pr-h-ic"><i class="fas fa-stairs"></i></span> 3 · Class ladder</h5>
                    <p>For each class: which class it promotes into, or mark it as a <strong>final class</strong> (no class after it). Rows marked <span class="chip a">suggested</span> have not been confirmed yet.</p></div>
                @if ($can['settings'])
                    <button class="btn-o" id="btnSuggest"><i class="fas fa-wand-magic-sparkles"></i> Fill from suggestion</button>
                @endif
            </div>
            <div class="pr-card-bd">
                @if ($classes->isEmpty())
                    <div class="empty-state"><i class="fas fa-chalkboard"></i><div style="font-weight:700;color:var(--t1);">No classes yet</div>
                        <div>Create the school's classes first, then come back to set the promotion ladder.</div></div>
                @else
                    <div class="pr-table-wrap">
                        <table class="pr-table">
                            <thead><tr><th>Class</th><th style="min-width:220px;">Promotes into</th><th style="min-width:250px;">Stream placement</th><th>Automatic</th></tr></thead>
                            <tbody id="ladderRows"></tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if ($can['settings'] && $classes->isNotEmpty())
                <div class="pr-footer"><span class="pr-meta">Saving also stores the rules above.</span>
                    <button class="btn-p" id="btnSave"><i class="fas fa-floppy-disk"></i> Save promotion setup</button></div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════ HISTORY ════════════════════════ --}}
    <div class="pr-pane {{ $tab === 'history' ? 'active' : '' }}" id="pane-history">
        <div class="pr-card pc-amber">
            <div class="pr-card-hd"><div><h5><span class="pr-h-ic"><i class="fas fa-clock-rotate-left"></i></span> Promotion history</h5>
                <p>Every run, who did it and what happened to each student. Open a run to see the full list or undo it.</p></div></div>
            <div class="pr-card-bd">
                @if ($history->isEmpty())
                    <div class="empty-state"><i class="fas fa-inbox"></i><div style="font-weight:700;color:var(--t1);">No promotions yet</div></div>
                @else
                    <div class="pr-table-wrap">
                        <table class="pr-table">
                            <thead><tr><th>Reference</th><th>Date</th><th>Year</th><th>Type</th><th>Result</th><th>By</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            @foreach ($history as $b)
                                @php [$sc, $sl] = $statusChip[$b->status] ?? ['n', ucfirst($b->status)]; @endphp
                                <tr>
                                    <td class="pr-name nw">{{ $b->reference }}</td>
                                    <td class="nw">{{ ($b->executed_at ?? $b->created_at)->format('d M Y H:i') }}</td>
                                    <td class="nw">{{ $b->from_academic_year }}{{ $b->to_academic_year ? ' → ' . $b->to_academic_year : '' }}</td>
                                    <td><span class="chip {{ $b->source === 'auto' ? 'b' : 'n' }}"><i class="fas {{ $b->source === 'auto' ? 'fa-robot' : 'fa-hand-pointer' }}"></i> {{ $b->source === 'auto' ? 'Automatic' : 'Manual' }}</span></td>
                                    <td><span class="chip g">{{ $b->promoted }} promoted</span> <span class="chip a">{{ $b->repeated }} repeat</span> <span class="chip b">{{ $b->graduated }} graduated</span></td>
                                    <td class="nw">{{ $b->created_by_name }}</td>
                                    <td><span class="chip {{ $sc }}">{{ $sl }}</span></td>
                                    <td>
                                        <div class="pr-acts">
                                            <button class="btn-o btn-xs" data-view-batch="{{ $b->id }}"><i class="fas fa-eye"></i> Open</button>
                                            @if ($can['export'])<a class="btn-o btn-xs" href="{{ route('students.promotion.export', $b->id) }}"><i class="fas fa-file-csv"></i> CSV</a>@endif
                                            @if ($can['revert'] && $b->canBeReverted())<button class="btn-d btn-xs" data-revert-batch="{{ $b->id }}" data-ref="{{ $b->reference }}"><i class="fas fa-rotate-left"></i> Revert</button>@endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:1rem;">{{ $history->appends(['tab' => 'history'])->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════ ALUMNI ════════════════════════ --}}
    <div class="pr-pane {{ $tab === 'alumni' ? 'active' : '' }}" id="pane-alumni">
        <div class="pr-card pc-emerald">
            <div class="pr-card-hd">
                <div><h5><span class="pr-h-ic"><i class="fas fa-graduation-cap"></i></span> Alumni</h5>
                    <p>Students who graduated from a final class. They no longer appear in class lists, attendance, marks entry or the student count used for billing.</p></div>
                <form method="GET" action="{{ route('students.promotion') }}" style="display:flex;gap:8px;margin:0;">
                    <input type="hidden" name="tab" value="alumni">
                    <input class="pr-in" name="alumni_q" value="{{ $alumniQ }}" placeholder="Search name or admission no…" style="min-width:230px;">
                    <button class="btn-p" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="pr-card-bd">
                @if ($alumni->isEmpty())
                    <div class="empty-state"><i class="fas fa-user-graduate"></i><div style="font-weight:700;color:var(--t1);">No alumni{{ $alumniQ !== '' ? ' match your search' : ' yet' }}</div></div>
                @else
                    <div class="pr-table-wrap">
                        <table class="pr-table">
                            <thead><tr><th>Student</th><th>Admission no.</th><th>Graduated from</th><th>Year</th><th></th></tr></thead>
                            <tbody>
                            @foreach ($alumni as $s)
                                @php
                                    $g = $graduations->get($s->id);
                                    $__ini = strtoupper(mb_substr(trim((string) $s->firstname), 0, 1) . mb_substr(trim((string) $s->lastname), 0, 1)) ?: '?';
                                @endphp
                                <tr>
                                    <td><div class="pr-who"><span class="pr-avatar">{{ $__ini }}</span><span class="pr-name">{{ $s->firstname }} {{ $s->lastname }}</span></div></td>
                                    <td>{{ $s->admission_number ?: '—' }}</td>
                                    <td>{{ $g ? $svc->classLabel($g->from_class_id) . ' · ' . $svc->streamLabel($g->from_stream) : '—' }}</td>
                                    <td>{{ $g?->from_academic_year ?: '—' }}</td>
                                    <td>@if ($g && $can['revert'])<button class="btn-d btn-xs" data-revert-record="{{ $g->id }}" data-name="{{ $s->firstname }} {{ $s->lastname }}"><i class="fas fa-rotate-left"></i> Restore to class</button>@endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:1rem;">{{ $alumni->appends(['tab' => 'alumni', 'alumni_q' => $alumniQ])->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- batch details modal --}}
    <div class="pr-modal-bg" id="batchModal">
        <div class="pr-modal">
            <div class="pr-modal-hd"><h5 id="bmTitle">Promotion</h5><button type="button" class="pr-x" id="bmClose" aria-label="Close">&times;</button></div>
            <div class="pr-modal-bd" id="bmBody"></div>
        </div>
    </div>

    </div>{{-- /.pr-page --}}
    {{-- 3 extra closing divs: push the footer down below the content (same as the Sales Team module) --}}
    </div>
    </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';
    const URLS = {
        plan: '{{ route('students.promotion.plan') }}',
        execute: '{{ route('students.promotion.execute') }}',
        settings: '{{ route('students.promotion.settings') }}',
        suggest: '{{ route('students.promotion.suggest') }}',
        runAuto: '{{ route('students.promotion.run-auto') }}',
        batch: '{{ url('students/promotion/batches') }}',
        records: '{{ url('students/promotion/records') }}',
        page: '{{ route('students.promotion') }}',
    };
    const CAN = @json($can);
    const CLASSES = @json($classList);
    const CLASS_BY_ID = Object.fromEntries(CLASSES.map(c => [c.id, c]));
    let LADDER = @json($ladderInit);

    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const esc = v => String(v ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    const fmt = n => Number(n || 0).toLocaleString();
    const initials = n => String(n || '').trim().split(/\s+/).filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '?';
    const avatar = n => `<span class="pr-avatar">${esc(initials(n))}</span>`;

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3200, timerProgressBar: true, background: '#0f172a', color: '#fff' });
    const toast = (m, ok = true) => Toast.fire({ iconHtml: ok ? '✓' : '!', iconColor: ok ? '#34d399' : '#f87171', title: m });

    async function api(url, method = 'GET', body = null) {
        const res = await fetch(url, {
            method,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: body ? JSON.stringify(body) : null,
        });
        let data = {};
        try { data = await res.json(); } catch (e) { data = { status: false, message: 'Unexpected server response (' + res.status + ').' }; }
        if (res.status === 422 && data.errors && !data.message) data.message = Object.values(data.errors).flat()[0];
        if (!res.ok && data.status !== false) data.status = false;
        return data;
    }

    // ───────────── tabs ─────────────
    function showTab(t) {
        $$('.pr-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === t));
        $$('.pr-pane').forEach(p => p.classList.toggle('active', p.id === 'pane-' + t));
        const u = new URL(location.href); u.searchParams.set('tab', t); history.replaceState(null, '', u);
    }
    $$('.pr-tab').forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
    $$('[data-goto]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); showTab(a.dataset.goto); }));

    // ═════════════ PROMOTE WIZARD ═════════════
    let PLAN = null;      // last plan response
    let ROWS = [];        // working copy with user's choices

    const fClass = $('#fClass'), fStream = $('#fStream');
    fClass.addEventListener('change', () => {
        const c = CLASS_BY_ID[fClass.value];
        fStream.innerHTML = '<option value="">All streams</option>' + (c ? c.streams.map(s => `<option value="${esc(s.id)}">${esc(s.name)}</option>`).join('') : '');
    });

    $('#btnLoad').addEventListener('click', loadPlan);

    async function loadPlan() {
        if (!fClass.value) { toast('Select a class first.', false); return; }
        const btn = $('#btnLoad'); btn.disabled = true;
        const q = new URLSearchParams({ class_id: fClass.value, stream: fStream.value, from_year: $('#fYear').value });
        const data = await api(URLS.plan + '?' + q.toString());
        btn.disabled = false;
        if (!data.status) { toast(data.message || 'Could not load students.', false); return; }

        PLAN = data;
        ROWS = data.rows.map(r => ({ ...r, suggested: { outcome: r.outcome, to_class: r.to_class, to_stream: r.to_stream } }));

        $('#readinessBox').innerHTML = (data.readiness || []).map(w => `<div class="pr-alert ${w.level}"><i class="fas fa-triangle-exclamation"></i><div>${esc(w.text)}</div></div>`).join('');

        const isFinal = data.path && data.path.is_final;
        $('#planBox').style.display = '';
        $('#planTitle').textContent = CLASS_BY_ID[fClass.value].name + (fStream.value ? ' · ' + (fStream.options[fStream.selectedIndex].text) : '');
        $('#bulkPromote').style.display = isFinal ? 'none' : '';
        $('#bulkGraduate').style.display = isFinal ? '' : 'none';

        if (data.blocking) {
            $('#planPath').innerHTML = `<span style="color:var(--r);font-weight:600;">${esc(data.blocking)}</span>`;
            ROWS = [];
        } else if (isFinal) {
            $('#planPath').innerHTML = '<span class="chip b"><i class="fas fa-flag-checkered"></i> Final class</span> There is no class after this one — students graduate to Alumni.';
        } else if (data.path) {
            const modes = { same_name: 'same-name streams', fixed: 'one fixed stream', manual: 'streams chosen by you' };
            $('#planPath').innerHTML = `Promotes into <strong>${esc(data.path.to_name || '—')}</strong> · ${modes[data.path.stream_mode] || ''}`;
        }
        renderRows();
    }

    function destOptions(row, selected) {
        const out = ['<option value="">— choose class & stream —</option>'];
        CLASSES.filter(c => c.id !== row.class_id && c.streams.length).forEach(c => {
            out.push(`<optgroup label="${esc(c.name)}">` + c.streams.map(s => {
                const v = c.id + '|' + s.id;
                return `<option value="${esc(v)}" ${v === selected ? 'selected' : ''}>${esc(c.name)} · ${esc(s.name)}</option>`;
            }).join('') + '</optgroup>');
        });
        return out.join('');
    }

    function resultOptions(row) {
        const isFinal = PLAN.path && PLAN.path.is_final;
        const opts = [];
        if (!isFinal) opts.push(['promote', 'Promote']);
        if (isFinal) opts.push(['graduate', 'Graduate']);
        opts.push(['repeat', 'Repeat class'], ['skip', 'Leave out']);
        return opts.map(([v, l]) => `<option value="${v}" ${row.outcome === v ? 'selected' : ''}>${l}</option>`).join('');
    }

    const FLAGS = {
        fees_blocked: ['r', 'Fees block'], fees_warn: ['a', 'Owes fees'], below_average: ['r', 'Below average'],
        no_results: ['n', 'No results'], needs_stream: ['r', 'Pick stream'], processed: ['g', 'Done'], bad_path: ['r', 'Bad path'], no_path: ['r', 'No path'],
    };

    function renderRows() {
        const body = $('#planRows');
        const term = ($('#planSearch').value || '').toLowerCase();
        $('#planEmpty').style.display = ROWS.length ? 'none' : '';
        let i = 0;
        body.innerHTML = ROWS.map((r, idx) => {
            if (term && !(r.name.toLowerCase().includes(term) || String(r.admission_number || '').toLowerCase().includes(term))) return '';
            i++;
            const chips = r.flags.map(f => FLAGS[f] ? `<span class="chip ${FLAGS[f][0]}">${FLAGS[f][1]}</span>` : '').join(' ');
            const dest = r.to_class && r.to_stream ? r.to_class + '|' + r.to_stream : '';
            const needs = r.outcome === 'promote' && !dest;
            return `<tr class="${r.locked ? 'locked' : 'pr-o-' + esc(r.outcome)}" data-idx="${idx}">
                <td>${i}</td>
                <td><div class="pr-who">${avatar(r.name)}<div><div class="pr-name">${esc(r.name)}</div><div class="pr-meta">${esc(r.admission_number || '')}${r.gender ? ' · ' + esc(r.gender) : ''}</div></div></div></td>
                <td>${esc(r.stream_label)}</td>
                <td>${r.average !== null ? r.average + '%' : '<span class="pr-meta">—</span>'}</td>
                <td>${r.fee_balance > 0 ? '<span style="color:var(--a);font-weight:600;">' + fmt(r.fee_balance) + '</span>' : '<span class="pr-meta">Clear</span>'}</td>
                <td style="max-width:280px;">${chips}<div class="pr-meta">${esc(r.notes.join(' '))}</div></td>
                <td>${r.locked ? '<span class="chip g">Already processed</span>' : `<select class="pr-sel sm js-out" style="min-width:130px;">${resultOptions(r)}</select>`}</td>
                <td>${r.locked ? '' : `<select class="pr-sel sm js-dest ${needs ? 'invalid' : ''}" ${r.outcome === 'promote' ? '' : 'disabled'}>${destOptions(r, dest)}</select>`}</td>
            </tr>`;
        }).join('');
        updateSummary();
    }

    $('#planRows').addEventListener('change', e => {
        const tr = e.target.closest('tr'); if (!tr) return;
        const r = ROWS[+tr.dataset.idx];
        if (e.target.classList.contains('js-out')) {
            r.outcome = e.target.value;
            if (r.outcome === 'promote' && !r.to_class) {
                const s = r.suggested; if (s.to_class && s.to_stream) { r.to_class = s.to_class; r.to_stream = s.to_stream; }
            }
            renderRowsKeepScroll();
        } else if (e.target.classList.contains('js-dest')) {
            const [c, s] = e.target.value.split('|');
            r.to_class = c || null; r.to_stream = s || null;
            r.flags = r.flags.filter(f => f !== 'needs_stream');
            renderRowsKeepScroll();
        }
    });
    $('#planSearch').addEventListener('input', renderRows);
    function renderRowsKeepScroll() { const y = window.scrollY; renderRows(); window.scrollTo(0, y); }

    $$('[data-bulk]').forEach(b => b.addEventListener('click', () => {
        const mode = b.dataset.bulk;
        ROWS.forEach(r => {
            if (r.locked) return;
            if (mode === 'suggested') { r.outcome = r.suggested.outcome; r.to_class = r.suggested.to_class; r.to_stream = r.suggested.to_stream; return; }
            r.outcome = mode;
            if (mode === 'promote' && !r.to_class && r.suggested.to_class) { r.to_class = r.suggested.to_class; r.to_stream = r.suggested.to_stream; }
        });
        renderRows();
    }));

    function counts() {
        const c = { promote: 0, repeat: 0, graduate: 0, skip: 0, locked: 0, needs: 0 };
        ROWS.forEach(r => {
            if (r.locked) { c.locked++; return; }
            c[r.outcome]++;
            if (r.outcome === 'promote' && !(r.to_class && r.to_stream)) c.needs++;
        });
        return c;
    }
    function updateSummary() {
        const c = counts();
        $('#planSummary').innerHTML =
            `<span class="chip g">${c.promote} promote</span><span class="chip a">${c.repeat} repeat</span>` +
            (PLAN && PLAN.path && PLAN.path.is_final ? `<span class="chip b">${c.graduate} graduate</span>` : '') +
            `<span class="chip n">${c.skip} left out</span>` + (c.locked ? `<span class="chip g">${c.locked} already done</span>` : '') +
            (c.needs ? `<span class="chip r">${c.needs} need a stream</span>` : '');
        const act = c.promote + c.repeat + c.graduate;
        $('#footCount').textContent = act + ' student(s) will be recorded';
        const b = $('#btnReview'); if (b) b.disabled = act === 0;
    }

    const btnReview = $('#btnReview');
    if (btnReview) btnReview.addEventListener('click', async () => {
        const c = counts();
        if (c.needs) { toast(c.needs + ' student(s) marked "Promote" still need a destination.', false); return; }

        const byDest = {};
        ROWS.filter(r => !r.locked && r.outcome === 'promote').forEach(r => {
            const cls = CLASS_BY_ID[r.to_class]; const st = cls.streams.find(s => s.id === r.to_stream);
            const k = cls.name + ' · ' + (st ? st.name : r.to_stream); byDest[k] = (byDest[k] || 0) + 1;
        });
        const feeWarn = ROWS.filter(r => !r.locked && r.outcome !== 'repeat' && r.outcome !== 'skip' && r.fee_balance > 0).length;
        const offPath = ROWS.filter(r => !r.locked && r.outcome === 'promote' && PLAN.path && PLAN.path.to_class && r.to_class !== PLAN.path.to_class).length;

        const html = `<div style="text-align:left;font-size:.9rem;">
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:.8rem;">
                <span class="chip g">${c.promote} promoted</span><span class="chip a">${c.repeat} repeating</span>
                ${c.graduate ? `<span class="chip b">${c.graduate} graduating</span>` : ''}<span class="chip n">${c.skip} left out</span></div>
            ${Object.keys(byDest).length ? '<div style="font-weight:700;margin-bottom:4px;">Destinations</div>' + Object.entries(byDest).map(([k, v]) => `<div>${esc(k)} <strong>× ${v}</strong></div>`).join('') : ''}
            ${feeWarn ? `<div class="pr-alert warning" style="margin-top:.8rem;"><i class="fas fa-coins"></i><div>${feeWarn} student(s) still owe fees.</div></div>` : ''}
            ${offPath ? `<div class="pr-alert warning" style="margin-top:.5rem;"><i class="fas fa-route"></i><div>${offPath} student(s) are placed outside the usual path.</div></div>` : ''}
            ${c.graduate ? `<div class="pr-alert info" style="margin-top:.5rem;"><i class="fas fa-graduation-cap"></i><div>Graduates leave class lists and become Alumni.</div></div>` : ''}
            <div class="pr-meta" style="margin-top:.8rem;">You can undo this run from the History tab.</div></div>`;

        const ok = await Swal.fire({ title: 'Confirm promotion', html, icon: 'question', showCancelButton: true, confirmButtonText: 'Promote now', cancelButtonText: 'Back', confirmButtonColor: '#059669', reverseButtons: true, width: 560 });
        if (!ok.isConfirmed) return;

        const decisions = ROWS.filter(r => !r.locked && r.outcome !== 'skip').map(r => ({
            student_id: r.id, outcome: r.outcome, from_class: r.class_id, from_stream: r.stream,
            to_class: r.outcome === 'promote' ? r.to_class : null, to_stream: r.outcome === 'promote' ? r.to_stream : null,
        }));

        Swal.fire({ title: 'Promoting…', text: 'Please wait.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const data = await api(URLS.execute, 'POST', {
            from_year: $('#fYear').value, to_year: $('#fToYear').value || null, note: $('#fNote').value || null,
            scope_label: $('#planTitle').textContent, decisions,
        });
        Swal.close();

        if (!data.status) { Swal.fire({ icon: 'error', title: 'Promotion failed', text: data.message || 'Something went wrong.' }); return; }

        const k = data.counts;
        const skipped = (data.skipped || []);
        const res = await Swal.fire({
            icon: skipped.length ? 'warning' : 'success', title: 'Promotion ' + esc(data.batch.reference),
            html: `<div style="text-align:left;font-size:.9rem;"><div><strong>${k.promoted}</strong> promoted · <strong>${k.repeated}</strong> repeating · <strong>${k.graduated}</strong> graduated</div>
                ${skipped.length ? `<div class="pr-alert warning" style="margin-top:.8rem;"><i class="fas fa-triangle-exclamation"></i><div><strong>${skipped.length} left untouched:</strong><br>${skipped.slice(0, 12).map(s => esc(s.name) + ' — ' + esc(s.reason)).join('<br>')}${skipped.length > 12 ? '<br>…' : ''}</div></div>` : ''}</div>`,
            showCancelButton: true, confirmButtonText: 'View history', cancelButtonText: 'Close', confirmButtonColor: '#2f2ccb', reverseButtons: true,
        });
        if (res.isConfirmed) { location.href = URLS.page + '?tab=history'; } else { location.href = URLS.page + '?tab=promote'; }
    });

    const runAuto = $('#btnRunAuto');
    if (runAuto) runAuto.addEventListener('click', async () => {
        const year = $('#fYear').value;
        const ok = await Swal.fire({ icon: 'question', title: 'Run automatic promotion now?', text: `This builds the promotion for ${year} from your ladder and rules and ${@json($setting->auto_apply) ? 'applies it immediately' : 'saves it as a proposal for approval'}.`, showCancelButton: true, confirmButtonText: 'Run now', reverseButtons: true, confirmButtonColor: '#2f2ccb' });
        if (!ok.isConfirmed) return;
        Swal.fire({ title: 'Working…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const d = await api(URLS.runAuto, 'POST', { from_year: year });
        Swal.close();
        if (!d.status) { toast(d.message || 'Failed.', false); return; }
        await Swal.fire({ icon: 'success', title: 'Done', text: d.message });
        location.href = URLS.page + '?tab=promote';
    });

    // ═════════════ PROPOSALS / HISTORY ACTIONS ═════════════
    document.addEventListener('click', async e => {
        const t = e.target.closest('[data-approve],[data-discard],[data-view-batch],[data-revert-batch],[data-revert-record]');
        if (!t) return;

        if (t.dataset.viewBatch) { openBatch(t.dataset.viewBatch); return; }

        if (t.dataset.approve) {
            const ok = await Swal.fire({ icon: 'question', title: 'Approve ' + t.dataset.ref + '?', text: 'Every student is re-checked first. Anyone who was moved or changed since the proposal was prepared is left alone.', showCancelButton: true, confirmButtonText: 'Approve & apply', confirmButtonColor: '#059669', reverseButtons: true });
            if (!ok.isConfirmed) return;
            Swal.fire({ title: 'Applying…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const d = await api(URLS.batch + '/' + t.dataset.approve + '/approve', 'POST');
            Swal.close();
            if (!d.status) { toast(d.message || 'Failed.', false); return; }
            const sk = d.skipped || [];
            await Swal.fire({ icon: sk.length ? 'warning' : 'success', title: 'Applied', html: `<strong>${d.counts.promoted}</strong> promoted · <strong>${d.counts.repeated}</strong> repeating · <strong>${d.counts.graduated}</strong> graduated` + (sk.length ? `<br><br>${sk.length} left untouched:<br>` + sk.slice(0, 10).map(s => esc(s.name) + ' — ' + esc(s.reason)).join('<br>') : '') });
            location.href = URLS.page + '?tab=history';
        }

        if (t.dataset.discard) {
            const ok = await Swal.fire({ icon: 'warning', title: 'Discard ' + t.dataset.ref + '?', text: 'No student will be moved. You can still promote manually.', showCancelButton: true, confirmButtonText: 'Discard', confirmButtonColor: '#dc2626', reverseButtons: true });
            if (!ok.isConfirmed) return;
            const d = await api(URLS.batch + '/' + t.dataset.discard + '/discard', 'POST');
            if (!d.status) { toast(d.message || 'Failed.', false); return; }
            toast(d.message); setTimeout(() => location.reload(), 700);
        }

        if (t.dataset.revertBatch) {
            const ok = await Swal.fire({ icon: 'warning', title: 'Revert ' + t.dataset.ref + '?', text: 'Every student goes back to the class and stream they were in. Anyone moved again since then is left where they are.', showCancelButton: true, confirmButtonText: 'Revert promotion', confirmButtonColor: '#dc2626', reverseButtons: true });
            if (!ok.isConfirmed) return;
            Swal.fire({ title: 'Reverting…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const d = await api(URLS.batch + '/' + t.dataset.revertBatch + '/revert', 'POST');
            Swal.close();
            if (!d.status) { toast(d.message || 'Failed.', false); return; }
            const sk = d.skipped || [];
            await Swal.fire({ icon: sk.length ? 'warning' : 'success', title: 'Reverted', html: `${d.reverted} student(s) restored.` + (sk.length ? `<br><br>Not restored:<br>` + sk.slice(0, 12).map(s => esc(s.name) + ' — ' + esc(s.reason)).join('<br>') : '') });
            location.reload();
        }

        if (t.dataset.revertRecord) {
            const ok = await Swal.fire({ icon: 'warning', title: 'Restore ' + (t.dataset.name || 'student') + '?', text: 'They go back to the class and stream they were in before this promotion.', showCancelButton: true, confirmButtonText: 'Restore', confirmButtonColor: '#dc2626', reverseButtons: true });
            if (!ok.isConfirmed) return;
            const d = await api(URLS.records + '/' + t.dataset.revertRecord + '/revert', 'POST');
            if (!d.status) { toast(d.message || 'Failed.', false); return; }
            toast(d.message); setTimeout(() => location.reload(), 700);
        }
    });

    const modal = $('#batchModal');
    $('#bmClose').addEventListener('click', () => modal.classList.remove('show'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('show'); });

    const OUT_CHIP = { promoted: ['g', 'Promoted'], repeated: ['a', 'Repeating'], graduated: ['b', 'Graduated'] };

    async function openBatch(id) {
        $('#bmTitle').textContent = 'Loading…'; $('#bmBody').innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i></div>';
        modal.classList.add('show');
        const d = await api(URLS.batch + '/' + id);
        if (!d.status) { $('#bmBody').innerHTML = `<div class="pr-alert danger">${esc(d.message || 'Could not load.')}</div>`; return; }
        const b = d.batch;
        $('#bmTitle').textContent = 'Promotion ' + b.reference;
        $('#bmBody').innerHTML = `
            <div class="pr-summary">
                <span class="chip b">${esc(b.source === 'auto' ? 'Automatic' : 'Manual')}</span>
                <span class="chip n">${esc(b.from_year || '')}${b.to_year ? ' → ' + esc(b.to_year) : ''}</span>
                <span class="chip g">${b.counts.promoted} promoted</span><span class="chip a">${b.counts.repeated} repeating</span><span class="chip b">${b.counts.graduated} graduated</span>
                <span class="pr-meta">By ${esc(b.by || '—')}${b.executed_at ? ' · ' + esc(b.executed_at) : ''}</span>
            </div>
            ${b.scope ? `<div class="pr-meta" style="margin-bottom:.4rem;">Scope: ${esc(b.scope)}</div>` : ''}
            ${b.note ? `<div class="pr-alert info"><i class="fas fa-note-sticky"></i><div>${esc(b.note)}</div></div>` : ''}
            <div class="pr-bulk"><input class="pr-in" id="bmSearch" placeholder="Filter students…"></div>
            <div class="pr-table-wrap"><table class="pr-table"><thead><tr><th>Student</th><th>Result</th><th>From</th><th>To</th><th>Avg %</th><th>Notes</th><th></th></tr></thead>
            <tbody id="bmRows"></tbody></table></div>`;
        const paint = () => {
            const term = ($('#bmSearch').value || '').toLowerCase();
            $('#bmRows').innerHTML = d.records.filter(r => !term || r.name.toLowerCase().includes(term) || String(r.admission_number || '').toLowerCase().includes(term)).map(r => {
                const [c, l] = OUT_CHIP[r.outcome] || ['n', r.outcome];
                const reverted = r.status === 'reverted';
                return `<tr style="${reverted ? 'opacity:.55' : ''}">
                    <td><div class="pr-who">${avatar(r.name)}<div><div class="pr-name">${esc(r.name)}</div><div class="pr-meta">${esc(r.admission_number || '')}</div></div></div></td>
                    <td><span class="chip ${c}">${l}</span>${reverted ? ' <span class="chip n">Reverted</span>' : (r.status === 'planned' ? ' <span class="chip a">Planned</span>' : '')}</td>
                    <td>${esc(r.from)}</td><td>${esc(r.to)}</td>
                    <td>${r.average !== null ? r.average + '%' : '—'}</td>
                    <td class="pr-meta" style="max-width:240px;">${esc(r.notes || '')}</td>
                    <td>${CAN.revert && r.status === 'applied' && r.outcome !== 'repeated' ? `<button class="btn-d btn-xs" data-revert-record="${r.id}" data-name="${esc(r.name)}"><i class="fas fa-rotate-left"></i></button>` : ''}</td>
                </tr>`;
            }).join('') || '<tr><td colspan="7" class="pr-meta" style="text-align:center;padding:1.5rem;">No students</td></tr>';
        };
        $('#bmSearch').addEventListener('input', paint); paint();
    }

    // ═════════════ SETUP ═════════════
    const ladderBody = $('#ladderRows');

    function renderLadder() {
        if (!ladderBody) return;
        ladderBody.innerHTML = LADDER.map((row, i) => {
            const c = CLASS_BY_ID[row.from]; if (!c) return '';
            const target = CLASS_BY_ID[row.to];
            const toOpts = ['<option value="">— choose —</option>', `<option value="__final" ${row.final ? 'selected' : ''}>★ Final class — students graduate</option>`]
                .concat(CLASSES.filter(x => x.id !== row.from).map(x => `<option value="${esc(x.id)}" ${!row.final && row.to === x.id ? 'selected' : ''}>${esc(x.name)}</option>`)).join('');
            const modeOpts = [['same_name', 'Same stream name (fallback below)'], ['fixed', 'Always the same stream…'], ['manual', 'I will choose each time']]
                .map(([v, l]) => `<option value="${v}" ${row.stream_mode === v ? 'selected' : ''}>${l}</option>`).join('');
            const fbOpts = ['<option value="">— none —</option>'].concat(target ? target.streams.map(s => `<option value="${esc(s.id)}" ${row.fallback === s.id ? 'selected' : ''}>${esc(s.name)}</option>`) : []).join('');
            const showFb = !row.final && row.stream_mode !== 'manual' && target;
            return `<tr class="ladder-row ${row.suggested ? 'suggested' : ''}" data-i="${i}">
                <td><div class="pr-name">${esc(c.name)}</div><div class="pr-meta">${esc(c.type)} · ${c.students} student(s)</div>${row.suggested ? '<span class="chip a" style="margin-top:3px;">suggested</span>' : ''}</td>
                <td><select class="pr-sel sm l-to" ${CAN.settings ? '' : 'disabled'}>${toOpts}</select>${row.note ? `<div class="pr-meta" style="margin-top:3px;color:var(--a);">${esc(row.note)}</div>` : ''}</td>
                <td>${row.final ? '<span class="pr-meta">Not needed for a final class</span>' : `<select class="pr-sel sm l-mode" ${CAN.settings ? '' : 'disabled'}>${modeOpts}</select>
                    ${showFb ? `<select class="pr-sel sm l-fb" style="margin-top:6px;" ${CAN.settings ? '' : 'disabled'}>${fbOpts}</select>` : ''}`}</td>
                <td>${row.final && false ? '' : `<label class="sw"><input type="checkbox" class="l-auto" ${row.auto ? 'checked' : ''} ${CAN.settings ? '' : 'disabled'}><span></span></label>`}</td>
            </tr>`;
        }).join('');
    }
    if (ladderBody) {
        renderLadder();
        ladderBody.addEventListener('change', e => {
            const tr = e.target.closest('tr'); const row = LADDER[+tr.dataset.i]; row.suggested = false; row.note = null;
            if (e.target.classList.contains('l-to')) {
                if (e.target.value === '__final') { row.final = true; row.to = ''; } else { row.final = false; row.to = e.target.value; row.fallback = ''; }
                renderLadder();
            } else if (e.target.classList.contains('l-mode')) { row.stream_mode = e.target.value; renderLadder(); }
            else if (e.target.classList.contains('l-fb')) { row.fallback = e.target.value; }
            else if (e.target.classList.contains('l-auto')) { row.auto = e.target.checked; }
        });
    }

    $$('input[name=mode]').forEach(r => r.addEventListener('change', () => {
        const auto = $('input[name=mode]:checked').value === 'auto';
        $('#modeAuto').classList.toggle('active', auto); $('#modeManual').classList.toggle('active', !auto);
        $('#autoBox').style.display = auto ? '' : 'none';
    }));

    const btnSuggest = $('#btnSuggest');
    if (btnSuggest) btnSuggest.addEventListener('click', async () => {
        const ok = await Swal.fire({ icon: 'question', title: 'Fill from suggestion?', text: 'This replaces the ladder below with the suggested sequence. Nothing is saved until you press Save.', showCancelButton: true, confirmButtonText: 'Fill', reverseButtons: true, confirmButtonColor: '#2f2ccb' });
        if (!ok.isConfirmed) return;
        const d = await api(URLS.suggest);
        if (!d.status) { toast(d.message || 'Failed.', false); return; }
        LADDER = LADDER.map(r => {
            const s = d.ladder[r.from]; if (!s) return r;
            return { ...r, to: s.to || '', final: !!s.final, stream_mode: s.stream_mode, fallback: '', auto: !!s.auto, suggested: true, note: s.note };
        });
        renderLadder(); toast('Suggestion applied — review it, then save.');
    });

    const btnSave = $('#btnSave');
    if (btnSave) btnSave.addEventListener('click', async () => {
        const mode = $('input[name=mode]:checked').value;
        const unresolved = LADDER.filter(r => !r.final && !r.to);
        if (unresolved.length) {
            const names = unresolved.map(r => CLASS_BY_ID[r.from].name).join(', ');
            const ok = await Swal.fire({ icon: 'warning', title: 'Some classes have no destination', text: names + ' will be left out of the ladder (and cannot be promoted) until you set them.', showCancelButton: true, confirmButtonText: 'Save anyway', reverseButtons: true });
            if (!ok.isConfirmed) return;
        }
        const body = {
            mode,
            auto_trigger: $('input[name=trigger]:checked')?.value || 'new_year',
            auto_run_date: $('#sRunDate').value || null,
            auto_apply: $('input[name=apply]:checked')?.value === '1',
            fee_policy: $('#sFeePolicy').value, fee_max_balance: $('#sFeeMax').value || 0,
            min_average: $('#sMinAvg').value === '' ? null : $('#sMinAvg').value,
            missing_results_policy: $('#sMissing').value, final_class_action: $('#sFinal').value,
            paths: LADDER.filter(r => r.final || r.to).map(r => ({ from: r.from, to: r.final ? null : r.to, final: r.final, stream_mode: r.stream_mode, fallback_stream: r.fallback || null, auto: r.auto })),
        };
        if (!body.paths.length) { toast('Set at least one class in the ladder.', false); return; }
        btnSave.disabled = true;
        const d = await api(URLS.settings, 'POST', body);
        btnSave.disabled = false;
        if (!d.status) { Swal.fire({ icon: 'error', title: 'Could not save', html: (d.errors || [d.message]).map(esc).join('<br>') }); return; }
        await Swal.fire({ icon: 'success', title: 'Saved', text: 'Promotion setup saved.', timer: 1400, showConfirmButton: false });
        location.href = URLS.page + '?tab=promote';
    });

    // pending-proposal convenience: open the Promote tab when something waits for approval
    @if ($pending->isNotEmpty() && request('tab') === null)
        showTab('promote');
    @endif
})();
</script>
@endsection
