{{--
    ═══════════════════════════════════════════════════════════════════════
    REPORT CARD DESIGN TEMPLATES — theme overlay
    ═══════════════════════════════════════════════════════════════════════
    Included by every pass-slip view (standard, nursery, Arabic). Restyles
    the SAME markup/classes the base stylesheet already defines, scoped
    under body.tpl-modern / body.tpl-minimal, so switching templates never
    touches PHP data, toggle logic, or structure — only presentation.

    "classic" needs no overrides here — it IS the base stylesheet.

    Expects: $accent, $accentDark, $accentA08, $accentA22, $accentA35
    to already be defined by the including view.
--}}
<style>
    /* ═══════════════════════════════════════════════════════════════
       MODERN — bold colour-blocked banner, squared logo, flat rule
       instead of ornate border, confident sans-serif spacing.
    ═══════════════════════════════════════════════════════════════ */
    body.tpl-modern .slip {
        box-shadow: 0 10px 32px rgba(0, 0, 0, .16);
        border-radius: 10px;
    }

    body.tpl-modern .slip.has-border {
        border: none;
        outline: none;
        border-top: 8px solid var(--accent);
        border-radius: 10px;
    }

    body.tpl-modern .slip.has-border::before,
    body.tpl-modern .slip.has-border::after {
        content: none;
    }

    body.tpl-modern .sch-header {
        background: linear-gradient(120deg, var(--accent) 0%, var(--accent-dark) 100%);
        border-bottom: none;
        padding: 1.3rem 1.4rem 1.15rem;
        border-radius: 10px 10px 0 0;
    }

    body.tpl-modern .sch-logo-box {
        border-radius: 16px;
        border: 3px solid rgba(255, 255, 255, .85);
        background: #fff;
    }

    body.tpl-modern .sch-logo-box i {
        color: var(--accent);
    }

    body.tpl-modern .sch-name {
        color: #fff;
        letter-spacing: .01em;
    }

    body.tpl-modern .sch-arabic-name {
        color: rgba(255, 255, 255, .92);
    }

    body.tpl-modern .sch-details,
    body.tpl-modern .sch-motto {
        color: rgba(255, 255, 255, .88);
        font-weight: 600;
    }

    body.tpl-modern .title-band {
        background: #1a1a1a;
        padding: .55rem 1.1rem;
    }

    body.tpl-modern .title-band span {
        letter-spacing: .1em;
    }

    body.tpl-modern .sum-bar {
        background: var(--accent-a08, rgba(0,0,0,.04));
        border-bottom: 2px solid var(--accent);
    }

    body.tpl-modern .sum-cell {
        border-right-color: var(--accent-a22, rgba(0,0,0,.12));
    }

    body.tpl-modern .sum-val {
        color: var(--accent-dark);
    }

    body.tpl-modern .marks-tbl {
        border: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 0 1.5px #e2e2e2;
    }

    body.tpl-modern .marks-tbl th {
        background: var(--accent-dark);
        border-right-color: rgba(255, 255, 255, .18);
    }

    body.tpl-modern .watermark-text {
        font-weight: 900;
        color: var(--accent-a08, rgba(0,0,0,.04));
    }

    body.tpl-modern .status-promoted {
        background: var(--accent-a08, rgba(0,0,0,.04));
        color: var(--accent-dark);
        border-color: var(--accent-a35, rgba(0,0,0,.18));
    }

    body.tpl-modern .discipline-col {
        border-right-color: var(--accent-a22, rgba(0,0,0,.12));
    }

    body.tpl-modern .discipline-title {
        color: var(--accent-dark);
    }


    /* ═══════════════════════════════════════════════════════════════
       MINIMAL — formal, ruled "examination document" look: sharp
       corners, a boxed two-logo header, hairline grid rules on every
       table and panel, no soft shadows/gradients/colour fills. Same
       markup/toggles as the other two templates — this is a restyle,
       not a rebuild. Modelled after traditional CBSE-style report
       cards (double logo header, gridded marks table, boxed
       Co-Scholastic/Discipline + Remarks + Signature strip).
    ═══════════════════════════════════════════════════════════════ */
    body.tpl-minimal .slip {
        box-shadow: none;
        border: 1.5px solid #111;
        border-radius: 0;
    }

    body.tpl-minimal .slip.has-border {
        border: 1.5px solid #111;
        outline: none;
    }

    body.tpl-minimal .slip.has-border::before,
    body.tpl-minimal .slip.has-border::after {
        content: none;
    }

    body.tpl-minimal .watermark,
    body.tpl-minimal .watermark-text {
        opacity: .04;
    }

    /* ── Header: logo · centred identity block · logo, like a
       traditional two-crest school letterhead ── */
    body.tpl-minimal .sch-header {
        border-bottom: 2px solid #111;
        padding: 1rem 1.2rem .9rem;
    }

    body.tpl-minimal .sch-logo-area-right {
        display: flex;
    }

    body.tpl-minimal .sch-logo-box {
        width: 72px;
        height: 72px;
        border-radius: 4px;
        border: 1.5px solid #111;
        background: #fff;
    }

    body.tpl-minimal .sch-logo-box i {
        color: #111;
    }

    body.tpl-minimal .sch-center {
        text-align: center;
        padding: 0 .5rem;
    }

    body.tpl-minimal .sch-name {
        font-size: 25px;
        font-weight: 800;
        letter-spacing: .02em;
        color: #111;
        border-bottom: none;
        padding-bottom: 0;
    }

    body.tpl-minimal .sch-arabic-name {
        color: #333;
    }

    body.tpl-minimal .sch-details,
    body.tpl-minimal .sch-motto {
        font-weight: 500;
        color: #444;
        font-style: normal;
    }

    /* ── Title band: a ruled document title instead of a coloured
       banner — "ACADEMIC REPORT FORM — ..." reads like a form header ── */
    body.tpl-minimal .title-band {
        background: #fff;
        text-align: center;
        padding: .55rem 1.2rem;
        border-bottom: 1.5px solid #111;
    }

    body.tpl-minimal .title-band span {
        color: #111;
        font-weight: 800;
        letter-spacing: .06em;
        font-size: .74rem;
    }

    /* ── Student info: boxed ledger rows instead of a soft card ── */
    body.tpl-minimal .stu-row {
        border-bottom: 2px solid #111;
        align-items: stretch;
    }

    body.tpl-minimal .stu-photo {
        border: 1.5px solid #111;
        border-radius: 0;
    }

    body.tpl-minimal .stu-details {
        border-right: 1px solid #ccc;
        gap: 0;
    }

    body.tpl-minimal .stu-field {
        padding: .18rem 0;
        border-bottom: 1px dotted #ccc;
    }

    body.tpl-minimal .stu-field:last-child {
        border-bottom: none;
    }

    body.tpl-minimal .stu-chart-area {
        border-right: 1px solid #ccc;
        padding-right: .8rem;
    }

    body.tpl-minimal .stu-chart-title {
        color: #111;
        font-weight: 800;
    }

    body.tpl-minimal .stu-qr-col {
        border-left: none;
    }

    body.tpl-minimal .stu-qr-box {
        border: 1.5px solid #111;
        border-radius: 2px;
        box-shadow: none;
    }

    body.tpl-minimal .stu-qr-label {
        color: #111;
    }

    body.tpl-minimal .status-pill {
        border-radius: 2px;
    }

    /* ── Summary bar (if used) ── */
    body.tpl-minimal .sum-bar {
        background: #fafafa;
        border-bottom: 2px solid #111;
    }

    body.tpl-minimal .sum-cell {
        border-right: 1px solid #ccc;
    }

    body.tpl-minimal .sum-val {
        color: #111;
        font-weight: 800;
    }

    /* ── Marks table: full black exam-paper grid ── */
    body.tpl-minimal .marks-tbl {
        border: 1.5px solid #111;
    }

    body.tpl-minimal .marks-tbl th {
        background: #f2f2f2;
        color: #111;
        border-right: 1px solid #111;
        border-bottom: 1.5px solid #111;
        font-weight: 800;
    }

    body.tpl-minimal .marks-tbl th:last-child {
        border-right: none;
    }

    body.tpl-minimal .marks-tbl td {
        border: 1px solid #ccc !important;
    }

    body.tpl-minimal .marks-tbl tbody tr:nth-child(even) {
        background: #fafafa;
    }

    body.tpl-minimal .g-pill {
        border-radius: 2px;
        border: 1px solid rgba(0, 0, 0, .12);
    }

    body.tpl-minimal .totals-row td {
        background: #eee;
        border-top: 2px solid #111 !important;
    }

    /* ── Bottom strip: boxed Discipline / Remarks / Signature panels,
       ruled like the reference report's Co-Scholastic/Discipline grid ── */
    body.tpl-minimal .bottom-section {
        border-top: 2px solid #111;
    }

    body.tpl-minimal .perf-chart-col,
    body.tpl-minimal .discipline-col {
        border-right: 1px solid #111;
    }

    body.tpl-minimal .sig-col-right {
        border-left: 1px solid #111;
    }

    body.tpl-minimal .perf-chart-title,
    body.tpl-minimal .remarks-section-title,
    body.tpl-minimal .discipline-title,
    body.tpl-minimal .sig-col-title {
        color: #111;
        border-bottom: 1px solid #111;
    }

    body.tpl-minimal .discipline-row {
        border-bottom: 1px dotted #ccc;
    }

    body.tpl-minimal .discipline-rate {
        border-radius: 2px;
    }

    body.tpl-minimal .sig-slot {
        border-bottom: 1.5px solid #111;
    }

    body.tpl-minimal .sig-dashes {
        border-top: 1.5px dashed #111;
    }

    /* ── Footer ── */
    body.tpl-minimal .slip-footer {
        background: #fff;
        border-top: 2px solid #111;
    }

    body.tpl-minimal .title-band + * .marks-tbl,
    body.tpl-minimal .tbtn-print {
        border-radius: 4px;
    }

    /* RTL (Arabic slip): keep the centred minimal header centred */
    html[dir="rtl"] body.tpl-minimal .sch-center {
        text-align: center;
        padding-left: .5rem;
        padding-right: .5rem;
    }

    html[dir="rtl"] body.tpl-minimal .title-band {
        text-align: center;
    }
</style>