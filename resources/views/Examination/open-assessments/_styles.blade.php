<style>
    .oa-hero { background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%); border-radius: 1.75rem; padding: 1.5rem 2rem 1.75rem; margin-bottom: 1.5rem; }
    .oa-hero .hero-badge { background: rgba(44,41,202,.25); border: 1px solid rgba(107,105,232,.5); color: #c7c5ff; padding: .3rem .9rem; border-radius: 999px; font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .oa-hero .hero-title { font-size: 1.4rem; font-weight: 800; color: #fff; margin: .35rem 0 .15rem; }
    .oa-hero .hero-subtitle { color: rgba(255,255,255,.7); font-size: .85rem; max-width: 780px; }
    .oa-hero .hero-links a { color: #c7c5ff; font-size: .78rem; font-weight: 700; text-decoration: none; margin-right: 1rem; }
    .oa-hero .hero-links a:hover { color: #fff; }

    .oa-card { background: #fff; border-radius: 1.25rem; box-shadow: 0 4px 28px rgba(44,41,202,.08); overflow: hidden; margin-bottom: 1.5rem; }
    .oa-card .oa-card-head { padding: 1rem 1.5rem; border-bottom: 2px solid #f0eeff; font-weight: 700; font-size: .95rem; color: #1e1b4b; display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; }
    .oa-card .oa-card-body { padding: 1.25rem 1.5rem; }
    .oa-sub { font-size: .75rem; font-weight: 600; color: #7c79b3; }

    .oa-table { margin-bottom: 0; font-size: .87rem; }
    .oa-table thead th { background: #2C29CA; color: #fff; font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; padding: .8rem .9rem; border: none; white-space: nowrap; }
    .oa-table tbody td { vertical-align: middle; padding: .8rem .9rem; border-bottom: 1px solid #f0eeff; }
    .oa-table tbody tr:hover { background: #fafaff; }

    .oa-btn { display: inline-flex; align-items: center; gap: .4rem; border: none; border-radius: .65rem; padding: .5rem 1rem; font-weight: 700; font-size: .8rem; text-decoration: none; cursor: pointer; background: #2C29CA; color: #fff; }
    .oa-btn:hover { background: #211ea3; color: #fff; }
    .oa-btn.light { background: #eef0ff; color: #3a37b8; }
    .oa-btn.light:hover { background: #dfe2ff; color: #2a2790; }
    .oa-btn.danger { background: #dc3545; }
    .oa-btn.danger:hover { background: #b3212f; }
    .oa-btn.sm { padding: .35rem .75rem; font-size: .74rem; border-radius: .55rem; }
    .oa-btn:disabled { background: #b9b6f0; cursor: not-allowed; }

    .oa-pill { display: inline-flex; align-items: center; font-size: .7rem; font-weight: 700; padding: .25rem .65rem; border-radius: .6rem; background: #eef0ff; color: #3a37b8; white-space: nowrap; }
    .oa-pill.green { background: #e3f8ec; color: #157347; }
    .oa-pill.amber { background: #fff4db; color: #a06a00; }
    .oa-pill.grey { background: #f1f0f6; color: #7c79b3; }

    .oa-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .oa-stat { background: #fff; border-radius: 1.1rem; box-shadow: 0 4px 22px rgba(44,41,202,.07); padding: 1rem 1.2rem; }
    .oa-stat .v { font-size: 1.55rem; font-weight: 800; color: #2C29CA; line-height: 1.1; }
    .oa-stat .l { font-size: .72rem; font-weight: 700; color: #7c79b3; text-transform: uppercase; letter-spacing: .05em; margin-top: .25rem; }

    .oa-bar { height: 7px; background: #eceaff; border-radius: 99px; overflow: hidden; min-width: 90px; }
    .oa-bar > span { display: block; height: 100%; background: linear-gradient(90deg, #2C29CA, #6b69e8); border-radius: 99px; }

    .oa-empty { text-align: center; padding: 2.5rem 1rem; color: #a3a0c9; }
    .oa-empty i { font-size: 1.8rem; display: block; margin-bottom: .6rem; }

    .oa-label { font-size: .74rem; font-weight: 700; color: #3a37b8; margin-bottom: .25rem; display: block; }
    .oa-input { width: 100%; border: 1.5px solid #dcdaf5; border-radius: .65rem; padding: .5rem .75rem; font-size: .86rem; background: #fff; }
    .oa-input:focus { outline: none; border-color: #2C29CA; box-shadow: 0 0 0 3px rgba(44,41,202,.13); }
    .oa-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; }
    .oa-filters { display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end; }
    .oa-filters > div { min-width: 190px; }
    .oa-note { background: #fff8e6; border: 1px solid #ffe2a0; color: #8a5a00; border-radius: .8rem; padding: .7rem 1rem; font-size: .82rem; margin-bottom: 1.25rem; }

    .oa-avatar { width: 32px; height: 32px; border-radius: 50%; background: #eef0ff; color: #2C29CA; font-size: .7rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; margin-right: .6rem; }
    .oa-marks { width: 100px; border: 1.5px solid #dcdaf5; border-radius: .55rem; padding: .35rem .5rem; text-align: center; font-weight: 700; }
    .oa-marks:focus { outline: none; border-color: #2C29CA; box-shadow: 0 0 0 3px rgba(44,41,202,.14); }
    .oa-marks.invalid { border-color: #dc3545; background: #fff5f5; }
    .oa-marks:disabled { background: #f1f0f6; color: #a3a0c9; cursor: not-allowed; }
    .oa-fab { position: fixed; right: 2rem; bottom: 2rem; z-index: 50; background: #2C29CA; color: #fff; border: none; border-radius: 999px; padding: .85rem 1.6rem; font-weight: 800; font-size: .88rem; box-shadow: 0 8px 24px rgba(44,41,202,.38); cursor: pointer; }
    .oa-fab:hover { background: #211ea3; }
    .oa-fab:disabled { background: #b9b6f0; box-shadow: none; }

    .oa-teacher { border-radius: 1.25rem; background: #fff; box-shadow: 0 4px 28px rgba(44,41,202,.08); margin-bottom: 1.25rem; overflow: hidden; }
    .oa-teacher > summary { list-style: none; cursor: pointer; padding: 1rem 1.5rem; display: flex; align-items: center; gap: .85rem; background: linear-gradient(90deg, #f6f5ff, #fff); }
    .oa-teacher > summary::-webkit-details-marker { display: none; }
    .oa-teacher .t-avatar { width: 40px; height: 40px; border-radius: 12px; background: #2C29CA; color: #fff; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
    .oa-teacher .t-name { font-weight: 800; color: #1e1b4b; }
    .oa-teacher .chev { margin-left: auto; color: #7c79b3; transition: transform .2s; }
    .oa-teacher[open] .chev { transform: rotate(90deg); }
    .oa-subject { padding: 0 1.5rem 1.1rem; }
    .oa-subject h6 { font-size: .86rem; font-weight: 800; color: #1e1b4b; margin: 1rem 0 .5rem; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    @media (max-width: 767px) { .oa-hero { padding: 1.25rem; } .oa-fab { right: 1rem; bottom: 1rem; } }
</style>
