{{--
    ═══════════════════════════════════════════════════════════════════════
    FIT SCHOOL NAME — shared partial
    ═══════════════════════════════════════════════════════════════════════
    Included by every slip view right before </body>.

    Problem it fixes: the school name has a fixed font-size tuned for the
    wide on-screen preview. At the narrower width the print engine lays the
    page out at, a long name ("CORNERSTONE JUNIOR SCHOOL - MUKONO CAMPUS")
    no longer fits and used to be chopped with "…" (text-overflow: ellipsis).

    Behaviour now (the name is NEVER cut off):
      1. Keep it on ONE line and shrink the font until it fits.
      2. If it still doesn't fit at the minimum size, let it WRAP onto a
         second line at a readable size instead of clipping.
    Inline styles are used on purpose — they beat every stylesheet / media
    query rule, so print CSS can't silently override them.

    Re-measured on load, font load, resize, before/after print and when the
    browser switches to print media (the print layout is narrower).
--}}
<script>
    (function () {
        var MIN_PX = 10;     // smallest single-line size
        var WRAP_PX = 15;    // size used if it has to wrap onto 2 lines
var MIN_AR_PX = 10;  // smallest Arabic-name size
var AR_MAX_RATIO = 1.2; // Arabic may grow to at most this x the English name's font size
var AR_WIDTH_FRACTION = 0.75; // Arabic text spans this share of the English name's width (1 = full width)

        // Width of the rendered text itself (not the block it sits in).
        function textWidth(el) {
            var r = document.createRange();
            r.selectNodeContents(el);
            return r.getBoundingClientRect().width;
        }

        // Make the Arabic school name span the same width as the English one:
        // scale its font so its text is as wide as the English text, then give
        // it a block exactly that wide, aligned the same way as the English name.
        function fitArabicNames() {
            document.querySelectorAll('.rc-lh-arabic, .sch-arabic-name').forEach(function (ar) {
                var parent = ar.parentElement;
                if (!parent) return;
                var en = parent.querySelector('.rc-lh-name, .sch-name');
                if (!en) return;

                // reset, then measure the Arabic text on one line at its stylesheet size
                ar.style.fontSize = '';
                ar.style.width = '';
                ar.style.marginLeft = '';
                ar.style.marginRight = '';
                ar.style.display = 'inline-block';
                ar.style.whiteSpace = 'nowrap';

                var arW = ar.getBoundingClientRect().width;
                var enW = textWidth(en);
                if (!arW || !enW) return;

                var size = parseFloat(window.getComputedStyle(ar).fontSize);
                var enSize = parseFloat(window.getComputedStyle(en).fontSize);
                var target = Math.max(MIN_AR_PX, Math.min(size * (enW * AR_WIDTH_FRACTION) / arW, enSize * AR_MAX_RATIO));
                ar.style.fontSize = target + 'px';

                // convert the English text width to CSS px (page scaling / zoom safe)
                var k = en.offsetWidth ? en.getBoundingClientRect().width / en.offsetWidth : 1;
                var w = Math.min(enW / (k || 1), parent.clientWidth);

                ar.style.display = 'block';
                ar.style.width = w + 'px';
                ar.style.textAlign = 'center';
                if (window.getComputedStyle(parent).textAlign === 'center') {
                    ar.style.marginLeft = 'auto';
                    ar.style.marginRight = 'auto';
                }
            });
        }

        function fitSchoolNames() {
            document.querySelectorAll('.rc-lh-name, .sch-name').forEach(function (el) {
                if (!el.parentElement) return;

                // reset to the stylesheet size, measure on one line
                el.style.fontSize = '';
                el.style.whiteSpace = 'nowrap';
                el.style.overflow = 'visible';
                el.style.textOverflow = 'clip';
                el.style.overflowWrap = 'normal';

                var size = parseFloat(window.getComputedStyle(el).fontSize);
                while (size > MIN_PX && el.scrollWidth > el.clientWidth + 0.5) {
                    size -= 0.5;
                    el.style.fontSize = size + 'px';
                }

                // still too wide → wrap instead of clipping
                if (el.scrollWidth > el.clientWidth + 0.5) {
                    el.style.whiteSpace = 'normal';
                    el.style.overflowWrap = 'anywhere';
                    el.style.fontSize = WRAP_PX + 'px';
                }
            });

            // the Arabic name follows the English name's final size/width
            fitArabicNames();
        }
        window.fitSchoolNames = fitSchoolNames;

        function runFit() {
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(fitSchoolNames).catch(fitSchoolNames);
            } else {
                fitSchoolNames();
            }
        }

        window.addEventListener('load', runFit);
        window.addEventListener('resize', fitSchoolNames);
        window.addEventListener('beforeprint', fitSchoolNames);
        window.addEventListener('afterprint', fitSchoolNames);
        if (window.matchMedia) {
            var mq = window.matchMedia('print');
            if (mq.addEventListener) mq.addEventListener('change', fitSchoolNames);
            else if (mq.addListener) mq.addListener(fitSchoolNames);
        }
    })();
</script>
