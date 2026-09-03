{{--
    ═══════════════════════════════════════════════════════════════════════
    CLASSIC report-card theme
    ═══════════════════════════════════════════════════════════════════════
    Included only by slip-classic.blade.php. Styles the shared .slip /
    .sch-header / .stu-details / .marks-tbl / etc. markup for the
    traditional bordered-ledger look.

    Split out of the old combined template-themes.blade.php (previously
    shared by classic/modern/minimal) so each design's CSS now lives
    next to the file that uses it.

    Expects: $accent, $accentDark, $accentA08, $accentA22, $accentA35
    to already be defined by the including view.
--}}
<style>
    /* ═══════════════════════════════════════════════════════════════
       CLASSIC — traditional CBSE-style bordered ledger report card:
       a plain boxed card, a bold colour-coded masthead with the
       school name in the accent colour, a stamp-style "REPORT CARD"
       badge, a fully black-gridded marks table, and banner-headed
       boxed panels for Discipline / Remarks / Signature — echoing a
       classic printed school report. Same markup/data/toggles as the
       other two templates — every override below only repositions or
       restyles elements that already exist; nothing is added/removed.
    ═══════════════════════════════════════════════════════════════ */
    body.tpl-classic .slip {
        box-shadow: 0 4px 18px rgba(0, 0, 0, .1);
        border-radius: 6px;
    }

    body.tpl-classic .slip.has-border {
        border: 2.5px solid var(--accent);
        outline: 1px solid #cfd3da;
        outline-offset: -7px;
        border-radius: 6px;
    }

    body.tpl-classic .slip.has-border::before {
        inset: 6px;
        border: 1px solid var(--accent-a35, rgba(0, 0, 0, .18));
        border-radius: 3px;
    }

    body.tpl-classic .slip.has-border::after {
        content: none;
    }

    /* ── Masthead: white ground, accent-coloured school name, thick
       accent rule beneath — a bold letterhead rather than a colour
       block banner ── */
    body.tpl-classic .sch-header {
        border-bottom: 3px solid var(--accent);
        background: #fff;
    }

    body.tpl-classic .sch-logo-box {
        border-width: 2.5px;
        background: #fff;
    }

    body.tpl-classic .sch-name {
        color: var(--accent-dark);
        text-shadow: .5px 0 0 var(--accent-dark);
    }

    body.tpl-classic .sch-details,
    body.tpl-classic .sch-motto {
        color: #333;
    }

    /* ── Title band: a rounded "REPORT CARD" stamp badge above the
       session line, instead of a full-width solid block ── */
    body.tpl-classic .title-band {
        background: #fff;
        padding: .55rem 1.1rem .5rem;
        border-bottom: 1px solid #e2e2e2;
    }

    body.tpl-classic .title-band::before {
        content: 'REPORT CARD';
        display: inline-block;
        background: var(--accent);
        color: #fff;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        padding: .28rem 1.1rem;
        border-radius: 999px;
        margin-bottom: .35rem;
    }

    body.tpl-classic .title-band span {
        display: block;
        color: #444;
        font-weight: 700;
        font-size: .7rem;
    }

    /* ── Student info: label left / value right ledger rows, with a
       coloured label instead of the plain stacked list ── */
    body.tpl-classic .stu-details {
        gap: .45rem;
    }

    body.tpl-classic .stu-field {
        border-bottom: 1px dotted #d8d8d8;
        padding-bottom: .25rem;
    }

    body.tpl-classic .stu-field strong {
        color: var(--accent-dark);
        margin-right: .3rem;
    }

    body.tpl-classic .stu-photo {
        border: 2px solid var(--accent);
        border-radius: 3px;
    }

    /* ── Marks table: full black-gridded ledger, solid accent header,
       no zebra striping, accent-tinted totals row ── */
    body.tpl-classic .marks-tbl {
        border: 1.5px solid #111;
    }

    body.tpl-classic .marks-tbl th {
        background: var(--accent);
        border-right: 1px solid rgba(255, 255, 255, .3);
        border-bottom: 1.5px solid #111;
    }

    body.tpl-classic .marks-tbl td {
        border: 1px solid #999;
    }

    body.tpl-classic .marks-tbl tbody tr:nth-child(even) {
        background: transparent;
    }

    body.tpl-classic .totals-row td {
        background: var(--accent-a08, rgba(0, 0, 0, .05));
        border-top: 1.5px solid #111 !important;
    }

    /* ── Bottom strip: banner-headed boxed panels, each with a solid
       accent title bar — Discipline reads as its own ruled block,
       matching a printed Co-Scholastic/Discipline grid ── */
    body.tpl-classic .bottom-section {
        border-top: 2px solid var(--accent);
    }

    body.tpl-classic .perf-chart-col,
    body.tpl-classic .discipline-col {
        border-right: 1.5px solid #e2e2e2;
    }

    body.tpl-classic .sig-col-right {
        border-left: 1.5px solid #e2e2e2;
    }

    body.tpl-classic .perf-chart-title,
    body.tpl-classic .remarks-section-title,
    body.tpl-classic .discipline-title,
    body.tpl-classic .sig-col-title {
        background: var(--accent);
        color: #fff;
        border-bottom: none;
        border-radius: 3px;
        text-align: left;
    }

    body.tpl-classic .perf-chart-title {
        padding: .25rem .6rem;
        margin: -.1rem 0 .5rem;
    }

    body.tpl-classic .discipline-title {
        padding: .25rem .6rem;
        margin: -.1rem 0 .35rem;
    }

    body.tpl-classic .remarks-section-title {
        padding: .25rem .7rem;
        margin: -.1rem 0 .3rem;
    }

    body.tpl-classic .sig-col-title {
        padding: .25rem .6rem;
        margin: -.1rem 0 .5rem;
        text-align: center;
    }

    body.tpl-classic .discipline-row {
        padding: .2rem .1rem;
    }

    body.tpl-classic .discipline-rate {
        border-radius: 3px;
    }

    body.tpl-classic .discipline-rate.dr-empty {
        border-style: dashed;
    }

    body.tpl-classic .sig-slot {
        border-bottom: 1.5px solid var(--accent-dark);
    }

    /* ── Footer ── */
    body.tpl-classic .slip-footer {
        border-top: 2px solid var(--accent);
        background: #fff;
    }
</style>
