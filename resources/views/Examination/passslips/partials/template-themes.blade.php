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


    /* ═══════════════════════════════════════════════════════════════
       MINIMAL — quiet, editorial, generous whitespace. No border,
       no watermark noise, one accent hairline, single-logo header.
    ═══════════════════════════════════════════════════════════════ */
    body.tpl-minimal .slip {
        box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        border: 1px solid #e6e6e6;
    }

    body.tpl-minimal .slip.has-border {
        border: 1px solid #e6e6e6;
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

    body.tpl-minimal .sch-header {
        border-bottom: 1px solid #e2e2e2;
        padding: 1.5rem 1.4rem 1.1rem;
    }

    body.tpl-minimal .sch-logo-area-right {
        display: none;
    }

    body.tpl-minimal .sch-logo-box {
        width: 68px;
        height: 68px;
        border-radius: 8px;
        border: 1px solid #e2e2e2;
        background: #fafafa;
    }

    body.tpl-minimal .sch-center {
        text-align: left;
        padding-left: .3rem;
    }

    body.tpl-minimal .sch-name {
        font-size: 26px;
        font-weight: 700;
        letter-spacing: .01em;
        border-bottom: 2px solid var(--accent);
        display: inline-block;
        padding-bottom: 4px;
    }

    body.tpl-minimal .sch-details,
    body.tpl-minimal .sch-motto {
        font-weight: 400;
        color: #666;
        font-style: normal;
    }

    body.tpl-minimal .title-band {
        background: transparent;
        text-align: left;
        padding: .8rem 1.4rem 0;
    }

    body.tpl-minimal .title-band span {
        color: #888;
        font-weight: 600;
        letter-spacing: .12em;
        font-size: .68rem;
    }

    body.tpl-minimal .stu-row {
        border-bottom: 1px solid #eee;
    }

    body.tpl-minimal .stu-details {
        border-right: none;
    }

    body.tpl-minimal .sum-bar {
        background: transparent;
        border-bottom: 1px solid #eee;
    }

    body.tpl-minimal .sum-cell {
        border-right: 1px solid #f0f0f0;
    }

    body.tpl-minimal .sum-val {
        color: #111;
        font-weight: 700;
    }

    body.tpl-minimal .marks-tbl {
        border: none;
        border-top: 2px solid #111;
    }

    body.tpl-minimal .marks-tbl th {
        background: #fff;
        color: #111;
        border-bottom: 1px solid #111;
        border-right: none;
        font-weight: 700;
    }

    body.tpl-minimal .marks-tbl td {
        border-color: #f0f0f0 !important;
    }

    body.tpl-minimal .status-pill {
        border-radius: 3px;
    }

    body.tpl-minimal .title-band + * .marks-tbl,
    body.tpl-minimal .tbtn-print {
        border-radius: 4px;
    }

    /* RTL (Arabic slip): mirror the left-aligned minimal header */
    html[dir="rtl"] body.tpl-minimal .sch-center {
        text-align: right;
        padding-left: .3rem;
        padding-right: 0;
    }

    html[dir="rtl"] body.tpl-minimal .title-band {
        text-align: right;
    }
</style>