{{--
    ═══════════════════════════════════════════════════════════════════════
    SCALE FIT — shared partial (Text & layout scale above 100%)
    ═══════════════════════════════════════════════════════════════════════
    Included by every slip / sheet view, right after its own <style>.
    Renders NOTHING at 100% (the default), so every existing slip prints
    exactly as before.

    Why this exists: <body> is zoomed by --page-scale, and the sheet is then
    given (paper width ÷ scale) layout pixels so that the ZOOMED result is
    exactly the paper width. That is correct — but it means the slip is
    narrower in layout pixels than the design was tuned for (e.g. 780px),
    while the table's fixed column widths / min-widths (Remarks 130px,
    Teacher 128px, Subject 110px …) stay the same number of pixels. So the
    marks table came out wider than the slip and was clipped on the right.

    Two layers, so it holds for any school's data:
      1. CSS  — relax those minimum widths, tighten cell padding and let
                text wrap, so most slips simply fit.
      2. JS   — safety net: any block that STILL overflows the sheet is
                shrunk (nested zoom) by exactly the amount it overflows.
--}}
@php
    $__sf = \App\Http\Controllers\Helper::passslipPageSizing();
    $__sfScale = $__sf['pageScale'] ?? 1;
@endphp
@if($__sfScale > 1)
    <style>
        /* Screen preview uses the same width the printed page will have. */
        .page-wrap {
            max-width: calc(({{ $__sf['pageW'] }} - 1.3cm) / var(--page-scale, 1));
        }

        /* Tables: drop fixed minimums, tighten padding. Header words are
           never broken mid-word (the header font steps down instead);
           only free-text cells (Remarks / Teacher) may break anywhere. */
        .slip .marks-tbl th,
        .slip .marks-tbl td,
        .sheet table th,
        .sheet table td {
            min-width: 0 !important;
            padding-left: .25rem !important;
            padding-right: .25rem !important;
        }
        .slip .marks-tbl th {
            letter-spacing: 0 !important;
            font-size: .58rem !important;
            overflow-wrap: normal;
            word-break: keep-all;
        }
        .slip .marks-tbl td.col-remarks,
        .slip .marks-tbl td.col-teacher {
            overflow-wrap: anywhere;
        }

        /* At a larger scale the school name wraps instead of being cut
           off with "…". */
        .slip .rc-lh-name,
        .slip .sch-name {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        /* Letterhead contact line may wrap onto a second line. */
        .slip .rc-lh-details > div,
        .slip .sch-details > div {
            white-space: normal !important;
        }
        .slip .rc-lh-details span,
        .slip .sch-details span {
            display: inline-block;
            margin-right: 12px !important;
        }
    </style>
    <script>
        (function () {
            var TARGETS = '.marks-tbl, .rc-lh-details, .sch-details, .rc-footer-grid, .rc-two-col, ' +
                '.bottom-section, .discipline-row, .stu-row, .rc-summary, .summary-row, .rc-stu-grid, ' +
                '.sheet table';
            var busy = false;

            function px(v) { return parseFloat(v) || 0; }

            // Right-most visible edge of an element or anything inside it.
            function rightmost(el) {
                var max = el.getBoundingClientRect().right;
                var all = el.getElementsByTagName('*');
                for (var i = 0; i < all.length; i++) {
                    var d = all[i];
                    if (getComputedStyle(d).position === 'absolute') continue;
                    var r = d.getBoundingClientRect();
                    if (r.width > 0 && r.right > max) max = r.right;
                }
                return max;
            }

            // Where this element is allowed to end (inside its parent's
            // padding, and clear of its own right margin).
            function limit(el) {
                var p = el.parentElement;
                var pr = p.getBoundingClientRect();
                var k = p.offsetWidth ? pr.width / p.offsetWidth : 1;
                var pcs = getComputedStyle(p), ecs = getComputedStyle(el);
                return pr.right
                    - (px(pcs.paddingRight) + px(pcs.borderRightWidth)) * k
                    - px(ecs.marginRight) * k
                    - pr.width * 0.01;
            }

            function fit(el) {
                for (var i = 0; i < 6; i++) {
                    var left = el.getBoundingClientRect().left;
                    var need = rightmost(el) - left;
                    var have = limit(el) - left;
                    if (need <= have + 0.5 || have <= 0) return;
                    var cur = parseFloat(el.style.zoom) || 1;
                    el.style.zoom = Math.max(0.3, cur * (have / need) * 0.995);
                }
            }

            function run() {
                if (busy) return;
                busy = true;
                try {
                    var els = document.querySelectorAll(TARGETS);
                    for (var i = 0; i < els.length; i++) els[i].style.zoom = '';
                    for (var j = 0; j < els.length; j++) fit(els[j]);
                } finally { busy = false; }
            }

            window.addEventListener('load', function () {
                run();
                if (document.fonts && document.fonts.ready) document.fonts.ready.then(run);
                setTimeout(run, 400);
            });
            window.addEventListener('resize', run);
            window.addEventListener('beforeprint', run);
            window.addEventListener('afterprint', run);
            if (window.matchMedia) {
                var mq = window.matchMedia('print');
                if (mq.addEventListener) mq.addEventListener('change', run);
                else if (mq.addListener) mq.addListener(run);
            }
        })();
    </script>
@endif
