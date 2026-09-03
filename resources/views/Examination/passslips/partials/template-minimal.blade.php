{{--
    ═══════════════════════════════════════════════════════════════════════
    MINIMAL report-card theme
    ═══════════════════════════════════════════════════════════════════════
    Included only by slip-minimal.blade.php. Styles the shared .slip /
    .sch-header / .stu-details / .marks-tbl / etc. markup as a plain,
    ruled exam-document ledger look.

    Split out of the old combined template-themes.blade.php (previously
    shared by classic/modern/minimal) so each design's CSS now lives
    next to the file that uses it.

    Expects: $accent, $accentDark, $accentA08, $accentA22, $accentA35
    to already be defined by the including view.
--}}
<style>
    /* ═══════════════════════════════════════════════════════════════
       MINIMAL — traditional ruled "ledger" exam-document look, modelled
       on classic CBSE-style report cards: a plain letterhead, a stacked
       label/value ledger for student info (no card panels), a solid
       black-gridded marks table, and boxed, banner-headed panels for
       Discipline / Remarks / Signature. Same markup/data/toggles as the
       other two templates — every override below only repositions or
       restyles elements that already exist; nothing is added or removed.
    ═══════════════════════════════════════════════════════════════ */
    body.tpl-minimal {
        font-family: Georgia, 'Times New Roman', serif;
    }

    body.tpl-minimal .tbtn,
    body.tpl-minimal .toolbar-info {
        font-family: 'Inter', sans-serif;
    }

    body.tpl-minimal .slip {
        box-shadow: none;
        border: 1px solid #111;
        border-radius: 0;
    }

    body.tpl-minimal .slip.has-border {
        border: 1px solid #111;
        outline: none;
    }

    body.tpl-minimal .slip.has-border::before,
    body.tpl-minimal .slip.has-border::after {
        content: none;
    }

    body.tpl-minimal .watermark,
    body.tpl-minimal .watermark-text {
        opacity: .035;
    }

    /* ── Letterhead: small square crests either side of a plain,
       tightly-set identity block — no colour fills, no circles ── */
    body.tpl-minimal .sch-header {
        border-bottom: 1px solid #111;
        padding: .7rem 1.1rem .55rem;
        align-items: flex-start;
    }

    body.tpl-minimal .sch-logo-area-right {
        display: flex;
    }

    body.tpl-minimal .sch-logo-box {
        width: 58px;
        height: 58px;
        border-radius: 0;
        border: 1px solid #111;
        background: #fff;
        margin-top: 2px;
    }

    body.tpl-minimal .sch-logo-box i {
        color: #111;
        font-size: 1.7rem;
    }

    body.tpl-minimal .sch-center {
        text-align: center;
        padding: 0 .6rem;
    }

    body.tpl-minimal .sch-name {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: .01em;
        color: #111;
        border-bottom: none;
        padding-bottom: 0;
        text-transform: none;
        font-family: Georgia, 'Times New Roman', serif;
    }

    body.tpl-minimal .sch-arabic-name {
        color: #333;
        font-size: 18px;
    }

    body.tpl-minimal .sch-details {
        font-weight: 400;
        color: #333;
        font-style: normal;
        font-size: 11px;
        margin-top: 2px;
    }

    body.tpl-minimal .sch-motto {
        font-weight: 400;
        font-style: italic;
        color: #444;
        font-size: 11px;
        margin-top: 2px;
    }

    /* ── Title band: styled as a real document heading — "REPORT CARD"
       set large above the class/term line, ruled top and bottom ── */
    body.tpl-minimal .title-band {
        background: #fff;
        text-align: center;
        padding: .3rem 1.2rem .45rem;
        border-top: 1px solid #111;
        border-bottom: 1px solid #111;
    }

    body.tpl-minimal .title-band::before {
        content: 'REPORT CARD';
        display: block;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: .06em;
        color: #111;
        margin-top: .25rem;
        margin-bottom: .1rem;
    }

    body.tpl-minimal .title-band span {
        color: #333;
        font-weight: 600;
        letter-spacing: .02em;
        font-size: .68rem;
        text-transform: none;
        font-family: 'Inter', sans-serif;
    }

    /* ── Student info: a stacked ledger — LABEL column fixed-width,
       value fills the rest, hairline rule under every row — instead
       of a card-style info panel. Photo / mini-chart / QR shrink into
       a compact strip underneath rather than sitting inline. ── */
    body.tpl-minimal .stu-row {
        border-bottom: 1px solid #111;
        align-items: flex-start;
        flex-wrap: wrap;
        padding: .5rem 1.2rem .6rem;
        gap: 0;
    }

    body.tpl-minimal .stu-details {
        order: 1;
        flex: 1 1 100%;
        border-right: none;
        padding-right: 0;
        gap: 0;
    }

    body.tpl-minimal .stu-field {
        display: flex;
        align-items: baseline;
        gap: .6rem;
        padding: .28rem 0;
        border-bottom: 1px dotted #bbb;
        font-size: .8rem;
        font-family: 'Inter', sans-serif;
    }

    body.tpl-minimal .stu-field:last-child {
        border-bottom: 1px dotted #bbb;
    }

    body.tpl-minimal .stu-field strong {
        flex: 0 0 108px;
        text-transform: uppercase;
        font-weight: 700;
        font-size: .66rem;
        letter-spacing: .03em;
        color: #444;
    }

    body.tpl-minimal .stu-photo {
        order: 2;
        width: 64px;
        height: 78px;
        border: 1px solid #111;
        border-radius: 0;
        margin-top: .55rem;
    }

    body.tpl-minimal .stu-chart-area {
        order: 3;
        flex: 1 1 auto;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
        padding: 0 .8rem;
        margin-top: .55rem;
    }

    body.tpl-minimal .stu-chart-title {
        color: #444;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
    }

    body.tpl-minimal .stu-qr-col {
        order: 4;
        border-left: none;
        padding-left: .8rem;
        margin-top: .55rem;
    }

    body.tpl-minimal .stu-qr-box {
        width: 84px;
        height: 84px;
        border: 1px solid #111;
        border-radius: 0;
        box-shadow: none;
        padding: 4px;
    }

    body.tpl-minimal .stu-qr-title,
    body.tpl-minimal .stu-qr-label {
        color: #444;
        font-family: 'Inter', sans-serif;
    }

    body.tpl-minimal .status-pill {
        border-radius: 0;
        border-width: 1px;
        font-family: 'Inter', sans-serif;
    }

    /* ── Summary bar (multi-exam mode) ── */
    body.tpl-minimal .sum-bar {
        background: #f7f7f7;
        border-bottom: 1px solid #111;
    }

    body.tpl-minimal .sum-cell {
        border-right: 1px solid #ccc;
    }

    body.tpl-minimal .sum-val {
        color: #111;
        font-weight: 700;
    }

    /* ── Marks table: full black exam-grid, shaded header row,
       shaded totals row — no zebra striping ── */
    body.tpl-minimal .marks-wrap {
        padding-left: 1.2rem;
        padding-right: 1.2rem;
    }

    body.tpl-minimal .marks-tbl {
        border: 1px solid #111;
        font-family: 'Inter', sans-serif;
    }

    body.tpl-minimal .marks-tbl th {
        background: #eaeaea;
        color: #111;
        border-right: 1px solid #111;
        border-bottom: 1px solid #111;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    body.tpl-minimal .marks-tbl th:last-child {
        border-right: none;
    }

    body.tpl-minimal .marks-tbl td {
        border: 1px solid #999 !important;
    }

    body.tpl-minimal .marks-tbl tbody tr:nth-child(even) {
        background: transparent;
    }

    body.tpl-minimal .g-pill {
        border-radius: 0;
        border: 1px solid rgba(0, 0, 0, .25);
    }

    body.tpl-minimal .totals-row td {
        background: #eaeaea;
        border-top: 1px solid #111 !important;
        font-weight: 700;
    }

    /* ── Bottom strip: banner-headed boxed panels — Discipline reads
       as its own ruled block, echoing a Co-Scholastic/Discipline
       grid on a traditional report card ── */
    body.tpl-minimal .bottom-section {
        border-top: 1px solid #111;
        font-family: 'Inter', sans-serif;
    }

    body.tpl-minimal .perf-chart-col,
    body.tpl-minimal .discipline-col {
        border-right: 1px solid #111;
        padding-top: 0;
    }

    body.tpl-minimal .remarks-col {
        padding-top: 0;
    }

    body.tpl-minimal .sig-col-right {
        border-left: 1px solid #111;
        padding-top: 0;
    }

    body.tpl-minimal .perf-chart-title,
    body.tpl-minimal .remarks-section-title,
    body.tpl-minimal .discipline-title,
    body.tpl-minimal .sig-col-title {
        color: #111;
        background: #eaeaea;
        border-bottom: 1px solid #111;
        text-align: left;
        font-weight: 700;
        letter-spacing: .03em;
    }

    body.tpl-minimal .perf-chart-title {
        padding: .3rem .9rem;
        margin: 0 -.9rem .5rem;
    }

    body.tpl-minimal .discipline-title {
        padding: .3rem .9rem;
        margin: 0 -.9rem .35rem;
    }

    body.tpl-minimal .remarks-section-title {
        padding: .3rem 1rem;
        margin: 0 -1rem .3rem;
        border-bottom: 1px solid #111 !important;
    }

    body.tpl-minimal .sig-col-title {
        padding: .3rem .8rem;
        margin: 0 -.8rem .5rem;
        text-align: center;
        width: auto;
    }

    body.tpl-minimal .discipline-row {
        padding: .22rem .9rem;
        margin: 0 -.9rem;
        border-bottom: 1px dotted #ccc;
    }

    body.tpl-minimal .discipline-rate {
        border-radius: 0;
        border: 1px solid #111;
        background: #fff;
        color: #111;
    }

    body.tpl-minimal .discipline-rate.dr-A,
    body.tpl-minimal .discipline-rate.dr-B,
    body.tpl-minimal .discipline-rate.dr-C {
        background: #fff;
        color: #111;
        border-color: #111;
    }

    body.tpl-minimal .discipline-rate.dr-empty {
        border-color: #ccc;
        color: #bbb;
    }

    body.tpl-minimal .sig-slot {
        border-bottom: 1px solid #111;
    }

    body.tpl-minimal .sig-dashes {
        border-top: 1px dashed #111;
    }

    /* ── Footer ── */
    body.tpl-minimal .slip-footer {
        background: #fff;
        border-top: 1px solid #111;
        font-family: 'Inter', sans-serif;
    }

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

    html[dir="rtl"] body.tpl-minimal .stu-field {
        flex-direction: row;
    }
</style>
