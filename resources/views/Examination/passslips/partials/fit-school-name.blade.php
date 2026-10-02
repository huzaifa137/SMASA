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
