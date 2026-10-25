<style>
    table thead th{background-color:#2C29CA !important;color:#fff !important}
    .st-hero{background:linear-gradient(135deg,#000 0%,#070189 100%);border-radius:20px;padding:1.4rem 2rem;margin-bottom:1.25rem;border:1px solid rgba(255,255,255,.08);box-shadow:0 4px 24px rgba(7,1,137,.3);color:#fff;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
    .st-hero-left{display:flex;align-items:center;gap:1.1rem;min-width:0}
    .st-hero-icon{width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0}
    .st-hero h4{font-size:1.35rem;font-weight:700;color:#fff !important;margin:0 0 .15rem}
    .st-hero p{color:rgba(255,255,255,.75) !important;font-size:.9rem;margin:0}
    .st-hero-actions{display:flex;flex-wrap:wrap;gap:.5rem}
    .st-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;border-radius:99px;font-size:.85rem;font-weight:600;line-height:1.2;cursor:pointer;text-decoration:none !important;border:1px solid #c7c9f5;background:#fff;color:#2C29CA !important;transition:all .15s}
    .st-btn:hover{background:#eef0ff;transform:translateY(-1px)}
    .st-btn--primary{background:#2C29CA;border-color:#2C29CA;color:#fff !important}.st-btn--primary:hover{background:#1f1db0}
    .st-btn--danger{border-color:#f5c2c7;color:#b42318 !important}.st-btn--danger:hover{background:#fef3f2}
    .st-hero .st-btn{background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.28);color:#fff !important}
    .st-hero .st-btn--primary{background:#fff;color:#1e1b4b !important}
    .st-btn--sm{padding:.3rem .75rem;font-size:.78rem}
    .st-tabs{display:flex;gap:.25rem;flex-wrap:wrap;margin-bottom:1rem;border-bottom:2px solid #e6e8f5}
    .st-tabs a{padding:.6rem 1rem;font-weight:600;font-size:.88rem;color:#5b5f8a !important;text-decoration:none !important;border-bottom:3px solid transparent;margin-bottom:-2px}
    .st-tabs a.on,.st-tabs a:hover{color:#2C29CA !important;border-bottom-color:#2C29CA}
    .st-bar{display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem}
    .st-bar form{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:0}
    .st-in,.st-bar select,.st-bar input[type=text],.st-bar input[type=date]{border:1px solid #d5d8ee;border-radius:10px;padding:.45rem .7rem;font-size:.85rem;background:#fff;color:#1e1b4b}
    .st-grow{flex:1}
    .st-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:.9rem;margin-bottom:1.25rem}
    .st-kpi{background:#fff;border:1px solid #e6e8f5;border-radius:16px;padding:1rem 1.1rem;box-shadow:0 2px 10px rgba(44,41,202,.05)}
    .st-kpi small{display:block;color:#6b6f99;font-weight:600;font-size:.74rem;text-transform:uppercase;letter-spacing:.04em}
    .st-kpi b{display:block;font-size:1.5rem;color:#1e1b4b;margin-top:.2rem;line-height:1.15}
    .st-kpi span{font-size:.78rem;color:#6b6f99}
    .st-kpi--warn{border-color:#f9c97a;background:#fffaf0}.st-kpi--bad{border-color:#f5c2c7;background:#fff5f5}
    .st-card{background:#fff;border:1px solid #e6e8f5;border-radius:16px;padding:1.1rem 1.25rem;margin-bottom:1.25rem;box-shadow:0 2px 10px rgba(44,41,202,.05)}
    .st-card h5{font-weight:700;color:#1e1b4b;margin:0 0 .8rem;font-size:1.02rem}
    .st-card h5 small{font-weight:500;color:#6b6f99;margin-left:.4rem}
    .st-scroll{overflow-x:auto}
    .st-table{width:100%;border-collapse:collapse;font-size:.86rem}
    .st-table th,.st-table td{padding:.6rem .7rem;white-space:nowrap;vertical-align:middle}
    .st-table tbody tr{border-bottom:1px solid #eef0fa}.st-table tbody tr:hover{background:#f7f8ff}
    .st-table th a{color:#fff !important;text-decoration:none !important}
    .st-table .num{text-align:right;font-variant-numeric:tabular-nums}
    .st-mute{color:#8a8eb0}
    .st-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0}
    .st-avatar--lg{width:64px;height:64px;font-size:1.3rem}
    .st-who{display:flex;align-items:center;gap:.65rem}.st-who a{font-weight:700;color:#1e1b4b !important;text-decoration:none !important}.st-who small{display:block;color:#8a8eb0}
    .st-pill{display:inline-block;padding:.15rem .6rem;border-radius:99px;font-size:.72rem;font-weight:700}
    .st-pill.ok,.st-pill.achieved,.st-pill.on_track{background:#e7f8ee;color:#157347}
    .st-pill.warn,.st-pill.at_risk{background:#fff4dc;color:#a15c00}
    .st-pill.bad,.st-pill.behind,.st-pill.missed{background:#fde8e8;color:#b42318}
    .st-pill.mute,.st-pill.upcoming,.st-pill.no_target{background:#eef0fa;color:#5b5f8a}
    .st-prog{height:8px;border-radius:99px;background:#eceefb;overflow:hidden;min-width:90px}
    .st-prog i{display:block;height:100%;border-radius:99px;background:#2C29CA}
    .st-prog.achieved i,.st-prog.on_track i{background:#1fa463}.st-prog.at_risk i{background:#e8a317}.st-prog.behind i,.st-prog.missed i{background:#d92d20}
    .st-rank{display:inline-flex;width:28px;height:28px;border-radius:50%;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;background:#eef0fa;color:#5b5f8a}
    .st-rank.r1{background:#ffd54a;color:#6b4e00}.st-rank.r2{background:#d9dde6;color:#3b4252}.st-rank.r3{background:#e7b17c;color:#5b3510}
    .st-podium{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1rem;margin-bottom:1.25rem}
    .st-pod{background:linear-gradient(160deg,#fff,#f3f4ff);border:1px solid #dfe2f7;border-radius:18px;padding:1.1rem;text-align:center;position:relative}
    .st-pod.r1{border-color:#f2cf55;box-shadow:0 6px 22px rgba(242,207,85,.35)}
    .st-pod .st-rank{position:absolute;top:.7rem;left:.7rem}
    .st-pod h6{margin:.6rem 0 0;font-weight:700;color:#1e1b4b}.st-pod h6 a{color:inherit !important;text-decoration:none !important}
    .st-pod .big{font-size:1.35rem;font-weight:800;color:#2C29CA;margin-top:.3rem}
    .st-pod .sub{font-size:.78rem;color:#6b6f99}
    .st-flash{padding:.75rem 1rem;border-radius:12px;margin-bottom:1rem;font-weight:600;font-size:.88rem}
    .st-flash.ok{background:#e7f8ee;color:#157347}.st-flash.err{background:#fde8e8;color:#b42318}
    .st-empty{text-align:center;padding:2.2rem 1rem;color:#6b6f99}.st-empty i{font-size:2rem;color:#c7c9f5;display:block;margin-bottom:.5rem}
    .st-grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:1.25rem}
    .st-tg{display:grid;grid-template-columns:1fr auto;gap:.35rem .8rem;align-items:center;padding:.75rem 0;border-bottom:1px solid #eef0fa}
    .st-tg:last-child{border:0}.st-tg .full{grid-column:1/-1}
    .st-note{font-size:.8rem;color:#6b6f99;background:#f5f6ff;border-radius:12px;padding:.7rem .9rem}
    dialog.st-dlg{border:0;border-radius:18px;padding:0;width:min(520px,94vw);box-shadow:0 20px 60px rgba(7,1,137,.35)}
    dialog.st-dlg::backdrop{background:rgba(10,8,60,.55)}
    .st-dlg form{padding:1.4rem 1.5rem}.st-dlg h5{font-weight:700;color:#1e1b4b;margin-bottom:1rem}
    .st-dlg label{display:block;font-size:.8rem;font-weight:600;color:#3b3f6b;margin:.7rem 0 .25rem}
    .st-dlg input,.st-dlg select,.st-dlg textarea{width:100%;border:1px solid #d5d8ee;border-radius:10px;padding:.5rem .7rem;font-size:.88rem}
    .st-dlg .row2{display:grid;grid-template-columns:1fr 1fr;gap:.7rem}
    .st-dlg .foot{display:flex;justify-content:flex-end;gap:.5rem;margin-top:1.2rem}
    .st-chart text{font-size:11px;fill:#6b6f99}
    @media(max-width:640px){.st-hero{padding:1.1rem}.st-dlg .row2{grid-template-columns:1fr}}
</style>
<script>
    function stOpen(id){var d=document.getElementById(id);if(d&&d.showModal)d.showModal();}
    function stClose(el){el.closest('dialog').close();}
    document.addEventListener('click',function(e){var d=e.target;if(d.tagName==='DIALOG')d.close();});
</script>
