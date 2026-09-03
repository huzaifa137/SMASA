{{--
    ═══════════════════════════════════════════════════════════════════════
    MODERN report-card theme
    ═══════════════════════════════════════════════════════════════════════
    Included only by slip-modern.blade.php. Styles the shared .slip /
    .sch-header / .stu-details / .marks-tbl / etc. markup with a bold
    colour-blocked banner, squared logo, and confident sans-serif spacing.

    Split out of the old combined template-themes.blade.php (previously
    shared by classic/modern/minimal) so each design's CSS now lives
    next to the file that uses it.

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

</style>
