{{-- Shared look for the Billing module (admin + school pages). --}}
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    /* Modal headers carry the platform's blue gradient: keep their text readable. */
    .modal.bl .modal-header .modal-title,
    .modal.bl .modal-header h5,
    .modal.bl .modal-header h6 { color: #fff; font-weight: 600; }
    .modal.bl .modal-header .close { color: #fff; opacity: .85; text-shadow: none; }
    .modal.bl .modal-header .close:hover { opacity: 1; }

    :root {
        --bl-blue: #2f2ccb; --bl-blue-d: #14136e; --bl-blue-l: rgba(47,44,203,.09);
        --bl-green: #059669; --bl-green-l: rgba(5,150,105,.11);
        --bl-red: #dc2626; --bl-red-l: rgba(220,38,38,.10);
        --bl-amber: #d97706; --bl-amber-l: rgba(217,119,6,.12);
        --bl-purple: #7c3aed; --bl-purple-l: rgba(124,58,237,.10);
        --bl-gray: #64748b; --bl-gray-l: rgba(100,116,139,.12);
        --bl-surface: #fff; --bl-bg: #f0f4f8; --bl-border: #e2e8f0;
        --bl-t1: #0f172a; --bl-t2: #475569; --bl-t3: #94a3b8;
        --bl-radius: 16px; --bl-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05);
    }
    .bl, .bl * { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }
    .bl i, .bl i::before { font-family: 'Font Awesome 6 Free'; }
    .bl { color: var(--bl-t1); }

    .bl-hero { background: linear-gradient(135deg,#464592 0%,#1613c9 60%,#050352 100%); border-radius: 22px; padding: 1.7rem 2.2rem; color: #fff; margin: 3rem 0 1.2rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; position: relative; overflow: hidden; }
    .bl-hero::after { content: ''; position: absolute; right: -60px; top: -60px; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,.07); }
    .bl-hero h1 { font-size: 1.65rem; font-weight: 700; margin: .3rem 0 .15rem; color: #fff; }
    .bl-hero p { margin: 0; opacity: .8; font-size: .9rem; }
    .bl-badge { display: inline-block; background: rgba(255,255,255,.16); border-radius: 99px; padding: .22rem .8rem; font-size: .72rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
    .bl-hero-actions { position: relative; z-index: 1; display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }

    .bl-tabs { display: flex; gap: .35rem; flex-wrap: wrap; background: var(--bl-surface); border: 1px solid var(--bl-border); border-radius: 14px; padding: .35rem; margin-bottom: 1.2rem; box-shadow: var(--bl-shadow); }
    .bl-tabs a { padding: .5rem .95rem; border-radius: 10px; font-size: .84rem; font-weight: 600; color: var(--bl-t2); text-decoration: none !important; white-space: nowrap; }
    .bl-tabs a:hover { background: var(--bl-bg); color: var(--bl-t1); }
    .bl-tabs a.active { background: var(--bl-blue); color: #fff; }
    .bl-tabs a i { margin-right: .4rem; }

    .bl-card { background: var(--bl-surface); border: 1px solid var(--bl-border); border-radius: var(--bl-radius); box-shadow: var(--bl-shadow); margin-bottom: 1.2rem; overflow: hidden; }
    .bl-card-h { padding: 1rem 1.3rem; border-bottom: 1px solid var(--bl-border); display: flex; align-items: center; justify-content: space-between; gap: .6rem; flex-wrap: wrap; }
    .bl-card-h h3 { font-size: 1rem; font-weight: 700; margin: 0; }
    .bl-card-h small { color: var(--bl-t3); font-weight: 500; }
    .bl-card-b { padding: 1.2rem 1.3rem; }

    .bl-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.2rem; }
    .bl-kpi { background: var(--bl-surface); border: 1px solid var(--bl-border); border-radius: var(--bl-radius); padding: 1.1rem 1.2rem; box-shadow: var(--bl-shadow); position: relative; }
    .bl-kpi .lbl { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--bl-t3); }
    .bl-kpi .val { font-size: 1.5rem; font-weight: 700; margin: .25rem 0 .1rem; line-height: 1.15; }
    .bl-kpi .sub { font-size: .78rem; color: var(--bl-t2); }
    .bl-kpi .ico { position: absolute; right: 1rem; top: 1rem; width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .bl-kpi.blue .ico { background: var(--bl-blue-l); color: var(--bl-blue); } .bl-kpi.blue .val { color: var(--bl-blue); }
    .bl-kpi.green .ico { background: var(--bl-green-l); color: var(--bl-green); } .bl-kpi.green .val { color: var(--bl-green); }
    .bl-kpi.red .ico { background: var(--bl-red-l); color: var(--bl-red); } .bl-kpi.red .val { color: var(--bl-red); }
    .bl-kpi.amber .ico { background: var(--bl-amber-l); color: var(--bl-amber); } .bl-kpi.amber .val { color: var(--bl-amber); }
    .bl-kpi.purple .ico { background: var(--bl-purple-l); color: var(--bl-purple); } .bl-kpi.purple .val { color: var(--bl-purple); }

    .bl-table-wrap { overflow-x: auto; }
    .bl-table { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .bl-table th { text-align: left; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--bl-t3); font-weight: 700; padding: .7rem .9rem; background: #f8fafc; border-bottom: 1px solid var(--bl-border); white-space: nowrap; }
    .bl-table td { padding: .75rem .9rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .bl-table tr:hover td { background: #fafbff; }
    .bl-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .bl-table tfoot td { font-weight: 700; background: #f8fafc; border-top: 2px solid var(--bl-border); }
    .bl-muted { color: var(--bl-t3); font-size: .78rem; }
    .bl-empty { text-align: center; padding: 2.4rem 1rem; color: var(--bl-t3); }
    .bl-empty i { font-size: 2rem; display: block; margin-bottom: .6rem; opacity: .5; }

    .bl-pill { display: inline-block; padding: .2rem .7rem; border-radius: 99px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .bl-pill.paid { background: var(--bl-green-l); color: var(--bl-green); }
    .bl-pill.issued { background: var(--bl-amber-l); color: var(--bl-amber); }
    .bl-pill.partial { background: var(--bl-blue-l); color: var(--bl-blue); }
    .bl-pill.overdue { background: var(--bl-red-l); color: var(--bl-red); }
    .bl-pill.draft, .bl-pill.cancelled, .bl-pill.reversed { background: var(--bl-gray-l); color: var(--bl-gray); }
    .bl-pill.waived { background: var(--bl-purple-l); color: var(--bl-purple); }
    .bl-pill.valid { background: var(--bl-green-l); color: var(--bl-green); }
    .bl-pill.open { background: var(--bl-green-l); color: var(--bl-green); }
    .bl-pill.closed { background: var(--bl-gray-l); color: var(--bl-gray); }
    .bl-pill.current { background: var(--bl-blue); color: #fff; }

    .bl-btn { display: inline-flex; align-items: center; gap: .4rem; border: 1px solid transparent; border-radius: 10px; padding: .5rem .95rem; font-size: .83rem; font-weight: 600; cursor: pointer; text-decoration: none !important; line-height: 1.2; transition: .15s; background: var(--bl-surface); }
    .bl-btn.primary { background: var(--bl-blue); color: #fff; } .bl-btn.primary:hover { background: var(--bl-blue-d); color: #fff; }
    .bl-btn.success { background: var(--bl-green); color: #fff; } .bl-btn.success:hover { filter: brightness(.92); color: #fff; }
    .bl-btn.danger { background: var(--bl-red); color: #fff; } .bl-btn.danger:hover { filter: brightness(.92); color: #fff; }
    .bl-btn.light { background: #fff; color: var(--bl-blue); border-color: rgba(255,255,255,.4); } .bl-btn.light:hover { background: #eef; color: var(--bl-blue); }
    .bl-btn.ghost { border-color: var(--bl-border); color: var(--bl-t1); } .bl-btn.ghost:hover { background: var(--bl-bg); color: var(--bl-t1); }
    .bl-btn.ghost-danger { border-color: rgba(220,38,38,.35); color: var(--bl-red); } .bl-btn.ghost-danger:hover { background: var(--bl-red-l); color: var(--bl-red); }
    .bl-btn.sm { padding: .32rem .65rem; font-size: .76rem; border-radius: 8px; }
    .bl-btn[disabled] { opacity: .5; pointer-events: none; }

    .bl-filters { display: flex; flex-wrap: wrap; gap: .6rem; align-items: flex-end; padding: 1rem 1.3rem; border-bottom: 1px solid var(--bl-border); background: #fcfdff; }
    .bl-filters label { display: block; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--bl-t3); margin-bottom: .25rem; }
    .bl-input, .bl .form-control { border: 1px solid var(--bl-border); border-radius: 10px; padding: .5rem .75rem; font-size: .86rem; background: #fff; min-width: 150px; height: auto; color: var(--bl-t1); }
    .bl-input:focus, .bl .form-control:focus { outline: none; border-color: var(--bl-blue); box-shadow: 0 0 0 3px var(--bl-blue-l); }
    .bl .form-group label { font-size: .78rem; font-weight: 700; color: var(--bl-t2); margin-bottom: .3rem; }
    .bl-hint { font-size: .76rem; color: var(--bl-t3); margin-top: .25rem; }

    .bl-progress { height: 10px; border-radius: 99px; background: var(--bl-gray-l); overflow: hidden; }
    .bl-progress > span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg,#10b981,#059669); }
    .bl-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; } .bl-grid3 { display: grid; grid-template-columns: 2fr 1fr; gap: 1.2rem; }
    @media (max-width: 992px) { .bl-grid2, .bl-grid3 { grid-template-columns: 1fr; } .bl-hero { padding: 1.3rem; } }

    .bl-alert { border-radius: 12px; padding: .8rem 1rem; margin-bottom: 1rem; font-size: .88rem; display: flex; gap: .6rem; align-items: flex-start; }
    .bl-alert.success { background: var(--bl-green-l); color: #047857; } .bl-alert.error { background: var(--bl-red-l); color: #b91c1c; } .bl-alert.warn { background: var(--bl-amber-l); color: #92400e; } .bl-alert.info { background: var(--bl-blue-l); color: var(--bl-blue-d); }
    .bl-list { list-style: none; margin: 0; padding: 0; } .bl-list li { padding: .65rem 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; gap: .8rem; font-size: .84rem; } .bl-list li:last-child { border: 0; }
    .bl-kv { display: grid; grid-template-columns: auto 1fr; gap: .35rem 1rem; font-size: .86rem; } .bl-kv dt { color: var(--bl-t3); font-weight: 600; } .bl-kv dd { margin: 0; text-align: right; font-weight: 600; }
</style>
