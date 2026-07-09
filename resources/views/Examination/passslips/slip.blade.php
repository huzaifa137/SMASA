<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — {{ $exam->exam_name }}</title>
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Roboto+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>

    @php
        /*
        |─────────────────────────────────────────────────────────────
        | CUSTOMISATION — read query-string params sent from index.blade
        | Every param defaults to ON (1) so the slip is fully featured
        | when visited without params (e.g. direct URL).
        |─────────────────────────────────────────────────────────────
        */
        $accent = request('accent', '#f0a500');
        // Sanitise: must be a valid 6-digit hex colour, else fall back.
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $accent)) {
            $accent = '#f0a500';
        }

        // Helper: treat '1' / 'true' / missing (falls back to saved
        // per-class settings, then to $default) as ON. Query-string always
        // wins so the live customisation preview keeps working instantly.
        $on = fn(string $key, bool $default = true, array $saved = []): bool =>
            request()->has($key)
            ? in_array(request($key), ['1', 'true', 1, true], true)
            : ($saved[$key] ?? $default);

        // Derive a slightly darker shade of accent for outlines / borders
        // We do this purely in PHP by parsing the hex and darkening by ~15 %.
        $hexToDark = function (string $hex): string {
            $hex = ltrim($hex, '#');
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            $r = max(0, (int) ($r * 0.82));
            $g = max(0, (int) ($g * 0.82));
            $b = max(0, (int) ($b * 0.82));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };
        $accentDark = $hexToDark($accent);

        // Transparent version (rgba) for subtle fills — generated as CSS string
        $accentAlpha = function (string $hex, float $a): string {
            $hex = ltrim($hex, '#');
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            return "rgba({$r},{$g},{$b},{$a})";
        };
        $accentA08 = $accentAlpha($accent, 0.08);
        $accentA22 = $accentAlpha($accent, 0.22);
        $accentA35 = $accentAlpha($accent, 0.35);
    @endphp

    <style>
        /* ════════════════════════════════════════════════════════════════
   DESIGN TOKENS  (driven by customisation)
════════════════════════════════════════════════════════════════ */
        :root {
            --accent:
                {{ $accent }}
            ;
            --accent-dark:
                {{ $accentDark }}
            ;
            --accent-a08:
                {{ $accentA08 }}
            ;
            --accent-a22:
                {{ $accentA22 }}
            ;
            --accent-a35:
                {{ $accentA35 }}
            ;
        }

        /* ════════════════════════════════════════════════════════════════
   RESET & BASE
════════════════════════════════════════════════════════════════ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #dde1e7;
            color: #111;
            font-size: 12px;
            line-height: 1.4;
        }

        /* ════════════════════════════════════════════════════════════════
   SCREEN TOOLBAR
════════════════════════════════════════════════════════════════ */
        .toolbar {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 60%, var(--accent-dark) 100%);
            padding: .75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .35);
        }

        .toolbar-info strong {
            color: #fff;
            font-size: .9rem;
            font-weight: 700;
        }

        .toolbar-info small {
            display: block;
            color: rgba(255, 255, 255, .55);
            font-size: .7rem;
            margin-top: 1px;
        }

        .tbtn {
            padding: .45rem 1.1rem;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: .78rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            transition: all .15s;
            text-decoration: none;
        }

        .tbtn-print {
            background: var(--accent);
            color: #fff;
        }

        .tbtn-print:hover {
            background: var(--accent-dark);
            transform: translateY(-1px);
        }

        .tbtn-back {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .25);
        }

        .tbtn-back:hover {
            background: rgba(255, 255, 255, .22);
        }

        /* ════════════════════════════════════════════════════════════════
   PAGE WRAPPER
════════════════════════════════════════════════════════════════ */
        .page-wrap {
            max-width: 780px;
            margin: 1.5rem auto;
        }

        /* ════════════════════════════════════════════════════════════════
   SINGLE SLIP CARD
════════════════════════════════════════════════════════════════ */
        .slip {
            background: #fff;
            margin-bottom: 2.5rem;
            page-break-after: always;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .12);
        }

        .slip:last-child {
            page-break-after: avoid;
            margin-bottom: 0;
        }

        /* ── Conditional border system ─────────────────────── */
        .slip.has-border {
            border: 3px solid var(--accent);
            outline: 1px solid var(--accent-dark);
            outline-offset: -6px;
        }

        /* Inner decorative border overlay */
        .slip.has-border::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 1px solid var(--accent-a35);
            border-radius: 1px;
            pointer-events: none;
            z-index: 2;
        }

        /* Corner ornaments */
        .slip.has-border::after {
            content: '';
            position: absolute;
            inset: 4px;
            background:
                linear-gradient(var(--accent), var(--accent)) top left / 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) top left / 3px 18px no-repeat,
                linear-gradient(var(--accent), var(--accent)) top right / 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) top right / 3px 18px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom left / 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom left / 3px 18px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom right / 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom right / 3px 18px no-repeat;
            pointer-events: none;
            z-index: 2;
        }

        /* ════════════════════════════════════════════════════════════════
   SCHOOL HEADER
════════════════════════════════════════════════════════════════ */
        .sch-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.1rem .9rem;
            border-bottom: 3px solid var(--accent);
            gap: 1rem;
        }

        .sch-logo-area {
            display: flex;
            align-items: center;
            gap: .7rem;
            flex-shrink: 0;
        }

        .sch-logo-area.sch-logo-area-right {
            justify-content: flex-end;
        }

        .sch-logo-box {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f4e8;
        }

        .sch-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sch-logo-box i {
            font-size: 2.6rem;
            color: var(--accent);
        }

        .sch-center {
            flex: 1;
            text-align: center;
            padding: 0 .5rem;
            min-width: 0;
        }

        .sch-name {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: .03em;
            color: #111;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .sch-arabic-name {
            font-size: 26px;
            font-weight: 600;
            direction: rtl;
            margin-top: 4px;
        }

        .sch-details {
            font-size: 16px;
            color: #333;
            margin-top: 7px;
            font-weight: 500;
            line-height: 1.5;
            font-weight: bold;
        }

        .sch-motto {
            font-size: 14px;
            font-style: italic;
            color: black;
            margin-top: 5px;
            font-weight: bold;
        }

        /* ════════════════════════════════════════════════════════════════
   ORANGE TITLE BAND
════════════════════════════════════════════════════════════════ */
        .title-band {
            background: var(--accent);
            padding: .45rem 1.1rem;
            text-align: center;
        }

        .title-band span {
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .07em;
            color: #fff;
            text-transform: uppercase;
        }

        /* ════════════════════════════════════════════════════════════════
   STUDENT INFO ROW
════════════════════════════════════════════════════════════════ */
        .stu-row {
            display: flex;
            align-items: stretch;
            padding: .7rem 1.1rem;
            gap: 1rem;
            border-bottom: 1.5px solid #e0e0e0;
        }

        .stu-photo {
            flex-shrink: 0;
            width: 90px;
            height: 110px;
            border: 1.5px solid #c8c8c8;
            border-radius: 4px;
            overflow: hidden;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stu-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .stu-photo .nophoto {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            gap: .2rem;
            background: #e8e8e8;
        }

        .stu-photo .nophoto i {
            font-size: 2.8rem;
            color: #b0b0b0;
        }

        .stu-photo .nophoto span {
            font-size: .55rem;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .stu-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: .3rem;
            border-right: 1.5px solid #e8e8e8;
            padding-right: 1rem;
        }

        .stu-field {
            font-size: .83rem;
            color: #111;
        }

        .stu-field strong {
            font-weight: 700;
        }

        .status-pill {
            display: inline-block;
            padding: .2rem .75rem;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            margin-top: .15rem;
        }

        .status-promoted {
            background: #d5f5e3;
            color: #1a7a4a;
            border: 1px solid #a9dfbf;
        }

        .status-repeat {
            background: #fdebd0;
            color: #a04000;
            border: 1px solid #f5cba7;
        }

        .status-fail {
            background: #fde8e8;
            color: #c0392b;
            border: 1px solid #f1948a;
        }

        /* Mini chart */
        .stu-chart-area {
            flex-shrink: 0;
            width: 215px;
            display: flex;
            flex-direction: column;
            padding-right: .4rem;
        }

        .stu-chart-title {
            font-size: .68rem;
            font-weight: 700;
            color: #555;
            text-align: center;
            margin-bottom: .25rem;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .stu-chart-area canvas {
            flex: 1;
        }

        /* QR column */
        .stu-qr-col {
            flex-shrink: 0;
            width: 145px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            border-left: 1.5px solid #e8e8e8;
            padding-left: 1rem;
        }

        .stu-qr-title {
            font-size: .65rem;
            font-weight: 800;
            color: #666;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .stu-qr-box {
            width: 130px;
            height: 130px;
            border: 2.5px solid var(--accent);
            border-radius: 10px;
            padding: 6px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px var(--accent-a22);
        }

        .stu-qr-box canvas,
        .stu-qr-box img,
        .stu-qr-box svg {
            width: 100% !important;
            height: 100% !important;
            display: block;
            object-fit: contain;
        }

        .stu-qr-label {
            font-size: .68rem;
            font-weight: 900;
            color: var(--accent);
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        /* ════════════════════════════════════════════════════════════════
   SUMMARY BAR
════════════════════════════════════════════════════════════════ */
        .sum-bar {
            display: flex;
            background: #e8ecf0;
            border-bottom: 1.5px solid #c8c8c8;
        }

        .sum-cell {
            flex: 1;
            padding: .55rem .5rem;
            text-align: center;
            border-right: 1.5px solid #c8c8c8;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .sum-cell:last-child {
            border-right: none;
        }

        .sum-lbl {
            font-size: .62rem;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #777;
            font-weight: 600;
        }

        .sum-val {
            font-size: 1.1rem;
            font-weight: 900;
            color: #111;
            line-height: 1.1;
            margin-top: .1rem;
        }

        .sum-sub {
            font-size: .65rem;
            color: #999;
            margin-top: .05rem;
        }

        .delta-inline {
            font-size: .72rem;
            font-weight: 700;
            margin-left: .25rem;
            vertical-align: middle;
        }

        .di-up {
            color: #1a7a4a;
        }

        .di-dn {
            color: #c0392b;
        }

        /* ════════════════════════════════════════════════════════════════
   MARKS TABLE
════════════════════════════════════════════════════════════════ */
        .marks-wrap {
            padding: 0 1.1rem .5rem;
        }

        .marks-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: .75rem;
            border: 2.5px solid #333;
            margin-top: .5rem;
        }

        .marks-tbl th {
            background: #1a1a1a;
            color: #fff;
            padding: .55rem .7rem;
            text-align: center;
            font-size: .67rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            border-right: 1.5px solid #555;
            border-bottom: 2px solid #333;
        }

        .marks-tbl th:last-child {
            border-right: none;
        }

        .marks-tbl th.tl {
            text-align: left;
        }

        .marks-tbl td {
            padding: .5rem .7rem;
            border: 1.5px solid #888;
            vertical-align: middle;
        }

        .marks-tbl tbody tr:nth-child(even) {
            background: #f9f9fb;
        }

        .marks-tbl tbody tr:hover {
            background: #fff8ee;
        }

        .grp-row td {
            background: var(--accent);
            color: #fff;
            font-weight: 800;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .07em;
            padding: .28rem .55rem;
        }

        .num-td {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .score-td {
            text-align: center;
            font-weight: 700;
            font-size: .8rem;
        }

        .g-pill {
            display: inline-block;
            min-width: 24px;
            text-align: center;
            padding: .1rem .35rem;
            border-radius: 3px;
            font-weight: 800;
            font-size: .7rem;
        }

        .g-A,
        .g-D {
            background: #d4f5e2;
            color: #1a7a4a;
        }

        .g-B,
        .g-C {
            background: #cfe2ff;
            color: #0a4191;
        }

        .g-P {
            background: #fff3cd;
            color: #856404;
        }

        .g-F {
            background: #fde8e8;
            color: #c0392b;
        }

        .g-x {
            background: #eeecff;
            color: #5351e4;
        }

        .dev-up {
            color: #1a7a4a;
            font-weight: 800;
            font-size: .72rem;
        }

        .dev-down {
            color: #c0392b;
            font-weight: 800;
            font-size: .72rem;
        }

        .dev-eq {
            color: #bbb;
            font-size: .72rem;
        }

        .totals-row td {
            background: #f0f0f0;
            font-weight: 800;
            font-size: .77rem;
            border-top: 3px solid #333;
        }

        /* Multi-exam header: exam group cell (row 1) + MARKS/GRADE sub-cells (row 2) */
        .marks-tbl th.exam-grp-th {
            border-bottom: 2px solid #555;
        }

        .marks-tbl th.sub-th {
            background: #2d2d2d;
            font-size: .6rem;
            padding: .4rem .5rem;
            width: 40px;
        }

        .division-row td {
            background: #fafafa;
            font-weight: 800;
            font-size: .72rem;
            border-top: 2px solid #333;
            text-align: center;
            letter-spacing: .03em;
        }

        .division-row td.division-label {
            text-align: right;
            color: #666;
            font-size: .68rem;
            padding-right: .8rem;
            background: #fff;
            font-weight: 600;
        }

        .div-pill {
            display: inline-block;
            padding: .12rem .5rem;
            border-radius: 3px;
            font-weight: 800;
        }

        .div-1,
        .div-2 {
            background: #d4f5e2;
            color: #1a7a4a;
        }

        .div-3,
        .div-4 {
            background: #fff3cd;
            color: #856404;
        }

        .div-ungraded {
            background: #fde8e8;
            color: #c0392b;
        }

        .div-x {
            background: #eeecff;
            color: #5351e4;
        }

        /* ════════════════════════════════════════════════════════════════
   BOTTOM SECTION
════════════════════════════════════════════════════════════════ */
        .bottom-section {
            display: flex;
            gap: 0;
            border-top: 1.5px solid #ddd;
            min-height: 200px;
        }

        .perf-chart-col {
            flex: 0 0 260px;
            padding: .7rem .9rem;
            border-right: 1.5px solid #ddd;
            display: flex;
            flex-direction: column;
        }

        .perf-chart-title {
            font-size: .7rem;
            font-weight: 800;
            color: #111;
            margin-bottom: .5rem;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .perf-chart-col canvas {
            flex: 1;
        }

        .remarks-col {
            flex: 1;
            padding: .7rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .remarks-section-title {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #555;
            border-bottom: 1px dashed #ccc;
            padding-bottom: .2rem;
            margin-bottom: .2rem;
        }

        .remark-block {
            margin-bottom: .4rem;
            font-weight: 800;
        }

        .remark-teacher {
            font-size: .74rem;
            font-weight: bold;
            color: #111;
        }

        .remark-text {
            font-size: .72rem;
            color: #000;
            line-height: 1.45;
            margin-top: .15rem;
            font-weight: Bold;
        }

        .sig-dashes {
            border-top: 1.5px dashed #444;
            margin: .55rem 0 .15rem;
            padding-top: .2rem;
            font-size: .6rem;
            color: #000;
            text-transform: uppercase;
        }

        .sig-col-right {
            flex: 0 0 130px;
            padding: .7rem .8rem;
            border-left: 1.5px solid #ddd;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            align-items: center;
        }

        .sig-col-title {
            font-size: .68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #555;
            border-bottom: 1px dashed #ccc;
            padding-bottom: .2rem;
            width: 100%;
        }

        .sig-slot {
            width: 100%;
            text-align: center;
            border-bottom: 1.5px solid #333;
            padding-bottom: .15rem;
            font-size: .6rem;
            color: #333;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: .4rem;
        }

        .sig-slot.has-sig {
            padding-top: 18px;
        }

        /* ════════════════════════════════════════════════════════════════
   QR + FOOTER
════════════════════════════════════════════════════════════════ */
        .slip-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .45rem 1.1rem;
            border-top: 1.5px solid #ddd;
            background: #fafafa;
            gap: .5rem;
            flex-wrap: wrap;
        }

        /* ════════════════════════════════════════════════════════════════
   WATERMARK STAMP
════════════════════════════════════════════════════════════════ */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: .08;
            z-index: 0;
            width: 55%;
            max-width: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .watermark img {
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: grayscale(100%);
        }

        .watermark-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: .06;
            z-index: 0;
            font-size: 5.5rem;
            font-weight: 900;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #000;
            white-space: nowrap;
            text-align: center;
            line-height: 1.2;
        }

        .slip>*:not(.watermark):not(.watermark-text) {
            position: relative;
            z-index: 1;
        }

        /* ════════════════════════════════════════════════════════════════
   PRINT
════════════════════════════════════════════════════════════════ */
        @media print {
            @page {
                margin: .5cm .65cm;
                size: A4;
            }

            body {
                background: #fff;
                font-size: 11px;
            }

            .toolbar {
                display: none !important;
            }

            .page-wrap {
                max-width: 100%;
                margin: 0;
            }

            .slip {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
                page-break-inside: avoid;
                width: 100%;
                min-height: calc(297mm - 1cm);
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
            }

            .slip-footer {
                margin-top: auto;
            }

            .marks-tbl tr {
                page-break-inside: avoid;
            }

            .bottom-section,
            .stu-row,
            .sch-header {
                page-break-inside: avoid;
            }

            .slip.has-border {
                border: 3px solid var(--accent);
                outline: 1px solid var(--accent-dark);
                outline-offset: -6px;
            }

            .slip:last-child {
                page-break-after: avoid;
            }

            .slip::before,
            .slip::after,
            .title-band,
            .grp-row td,
            .g-pill,
            .g-A,
            .g-B,
            .g-C,
            .g-D,
            .g-P,
            .g-F,
            .marks-tbl thead,
            .sch-header,
            .sum-bar,
            .slip-footer,
            .status-promoted,
            .status-repeat,
            .status-fail,
            .watermark,
            .watermark-text,
            .watermark img,
            .stu-qr-box,
            .stu-qr-box img {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
        }

        .slip::before,
        .slip::after,
        .title-band,
        .grp-row td,
        .g-pill,
        .g-A,
        .g-B,
        .g-C,
        .g-D,
        .g-P,
        .g-F,
        .marks-tbl thead,
        .sch-header,
        .sum-bar,
        .slip-footer,
        .status-promoted,
        .status-repeat,
        .status-fail,
        .watermark,
        .watermark-text,
        .watermark img,
        .stu-qr-box,
        .stu-qr-box img,
        .sig-dashes,
        .sig-slot,
        .remarks-col,
        .sig-col-right {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }
    </style>
</head>

<body>

    <?php use App\Http\Controllers\Helper; ?>

    {{-- ══ TOOLBAR ══════════════════════════════════════════════════ --}}
    <div class="toolbar">
        <div class="toolbar-info">
            <strong>
                <i class="fas fa-file-alt" style="margin-right:.35rem"></i>
                @if($mode === 'single') Report Card
                @elseif($mode === 'class') Class Report Cards
                @else All Report Cards @endif
                — {{ $exam->exam_name }}
            </strong>
            <small>
                @if($mode === 'single')
                    {{ $student->lastname }} {{ $student->firstname }}
                @elseif($mode === 'class')
                    {{ Helper::recordMdname($classId) }}
                    {{ isset($streamId) && $streamId ? '– ' . $streamId : '' }}
                @else All Classes @endif
                &bull; {{ $exam->term }} {{ $exam->academic_year }}
            </small>
        </div>
        <div style="display:flex;gap:.45rem;flex-wrap:wrap;">
            <button class="tbtn tbtn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print / Save PDF
            </button>
            <a href="{{ route('examination.passslips.index', $exam->id) }}" class="tbtn tbtn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @php
        /* ── Normalise to single render array ─────────────────────────── */
        if ($mode === 'single') {
            $renderSlips = [
                [
                    'student' => $student,
                    'subjectMarks' => $subjectMarks,
                    'totalObtained' => $totalObtained,
                    'totalMax' => $totalMax,
                    'percentage' => $percentage,
                    'overallGrade' => $overallGrade,
                    'overallRemark' => $overallRemark,
                    'classRank' => $classRank,
                    'classTotal' => $classTotal,
                    'growthData' => $growthData,
                    'previousSubjectMarks' => $previousSubjectMarks ?? [],
                    'qrText' => $qrText ?? '',
                    'isEarlyYears' => $isEarlyYears ?? false,
                    'earlyYearsAverage' => $earlyYearsAverage ?? null,
                    'earlyYearsMaxMark' => $earlyYearsMaxMark ?? 3,
                    'useAvg' => $useAvg ?? false,
                    'examSummary' => $examSummary ?? [],
                    'avgSummary' => $avgSummary ?? null,
                ]
            ];
        } else {
            $renderSlips = $slips;
        }

        $multiExam = $multiExam ?? false;
        $examsList = $examsList ?? collect([$exam]);

        /* ── Helpers ─────────────────────────────────────────────────── */
        $ord = function ($n) {
            if (!is_numeric($n))
                return $n;
            $s = ['th', 'st', 'nd', 'rd'];
            $v = $n % 100;
            return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
        };

        $gc = function ($grade) {
            if (!$grade || $grade === '—')
                return 'g-x';
            $first = strtoupper(substr(trim($grade), 0, 1));
            return match ($first) {
                'A', 'D' => 'g-A',
                'B' => 'g-B',
                'C' => 'g-C',
                'P' => 'g-P',
                'F' => 'g-F',
                default => 'g-x',
            };
        };

        $schoolName = Helper::schoolNameBySchoolID(Session('LoggedSchool')) ?? config('app.name', 'School');
        $slipCounter = 0;
    @endphp


    <div class="page-wrap">
        @foreach($renderSlips as $slipData)

            @php
                $slipCounter++;
                $s = (object) $slipData['student'];
                $subjMarks = collect($slipData['subjectMarks']);
                $totObt = $slipData['totalObtained'];
                $totMax = $slipData['totalMax'];
                $pct = $slipData['percentage'];
                $oGrade = $slipData['overallGrade'];
                $oRemark = $slipData['overallRemark'];
                $rank = $slipData['classRank'];
                $classTotalN = $slipData['classTotal'];
                $growth = $slipData['growthData'];
                $prevSubj = collect($slipData['previousSubjectMarks'] ?? []);
                $useAvgSlip = $slipData['useAvg'] ?? false;
                $examSummarySlip = collect($slipData['examSummary'] ?? []);
                $avgSummarySlip = $slipData['avgSummary'] ?? null;

                $divClass = function ($div) {
                    if (!$div || $div === '—')
                        return 'div-x';
                    $d = strtolower($div);
                    if (str_contains($d, 'ungraded'))
                        return 'div-ungraded';
                    if (str_contains($d, '1'))
                        return 'div-1';
                    if (str_contains($d, '2'))
                        return 'div-2';
                    if (str_contains($d, '3'))
                        return 'div-3';
                    if (str_contains($d, '4'))
                        return 'div-4';
                    return 'div-x';
                };

                $isEarlyYears = $slipData['isEarlyYears'] ?? false;
                $earlyYearsAvg = $slipData['earlyYearsAverage'] ?? null;
                $earlyYearsMax = $slipData['earlyYearsMaxMark'] ?? 3;

                // ── Per-class saved customisation ───────────────────────────
                // Each student's class can carry its own saved show/hide
                // profile (e.g. Baby Class vs S.4). Query-string params
                // (from the live preview toggles) still override these.
                $savedCfg = Helper::getPassslipSettings(Session('LoggedSchool'), $s->senior ?? null);

                $cfg = [
                    'border' => $on('show_border', true, $savedCfg),
                    'watermark' => $on('show_watermark', true, $savedCfg),
                    'logo' => $on('show_logo', true, $savedCfg),
                    'arabic' => $on('show_arabic', true, $savedCfg),
                    'motto' => $on('show_motto', true, $savedCfg),
                    'contact' => $on('show_contact', true, $savedCfg),
                    'photo' => $on('show_photo', true, $savedCfg),
                    'minichart' => $on('show_minichart', true, $savedCfg),
                    'qr' => $on('show_qr', true, $savedCfg),
                    'rank' => $on('show_rank', true, $savedCfg),
                    'dev' => $on('show_dev', true, $savedCfg),
                    'grade_pill' => $on('show_grade_pill', true, $savedCfg),
                    'teacher_col' => $on('show_teacher_col', true, $savedCfg),
                    'totals_row' => $on('show_totals_row', true, $savedCfg),
                    'perf_chart' => $on('show_perf_chart', true, $savedCfg),
                    'remarks' => $on('show_remarks', true, $savedCfg),
                    'signatures' => $on('show_signatures', true, $savedCfg),
                    'footer_timestamp' => $on('show_footer_timestamp', true, $savedCfg),
                    'confidential' => $on('show_confidential', true, $savedCfg),
                    // New toggles
                    'total_score' => $on('show_total_score', true, $savedCfg),
                    'average' => $on('show_average', true, $savedCfg),
                    'result' => $on('show_result', true, $savedCfg),
                    'score_col' => $on('show_score_col', true, $savedCfg),
                    'comment_col' => $on('show_comment_col', true, $savedCfg),
                ];

                // Early years classes aren't scored Pass/Fail against the
                // exam's normal pass_mark — they're Fair/Good/Excellent.
                $passed = $isEarlyYears ? true : ($pct >= $exam->pass_mark);
                $statusLabel = $s->status ?? ($isEarlyYears ? $oRemark : ($passed ? 'Promoted' : 'Repeat'));

                /* Resolve student photo */
                $photo = null;
                if (!empty($s->student_photo)) {
                    foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                        $fp = str_replace(
                            '/',
                            DIRECTORY_SEPARATOR,
                            public_path('uploads/studentPhotos/' . $s->student_photo . '.' . $ext)
                        );
                        if (file_exists($fp)) {
                            $photo = asset('uploads/studentPhotos/' . $s->student_photo . '.' . $ext);
                            break;
                        }
                    }
                }

                /* Group subjects */
                $grouped = $subjMarks->groupBy(fn($sm) => $sm->subject_type ?? '');
                $useGroups = $grouped->count() > 1 || ($grouped->count() === 1 && !$grouped->has(''));

                /* Mini chart arrays */
                $miniLabels = $subjMarks->map(fn($sm) => strtoupper(substr($sm->subject_name, 0, 4)))->values()->toArray();
                $miniStudent = $subjMarks->pluck('percentage')->values()->toArray();
                $miniClass = $subjMarks->map(fn($sm) => $sm->class_average ?? rand(55, 80))->values()->toArray();

                /* Growth chart arrays */
                $growthLabels = collect($growth)->pluck('label')->toArray();
                $growthValues = collect($growth)->pluck('percentage')->toArray();

                /* Term delta */
                $prevPct = isset($growth[count($growth) - 2]) ? $growth[count($growth) - 2]['percentage'] : null;
                $termDelta = $prevPct !== null ? round($pct - $prevPct, 1) : null;

                /* Unique IDs */
                $cMini = 'mini_' . $slipCounter;
                $cPerf = 'perf_' . $slipCounter;
                $qrId = 'qr_canvas_' . $slipCounter;

                /* School meta */
                $schoolPhone = Helper::schoolPhoneBySchoolID(Session('LoggedSchool')) ?? '';
                $schoolNameArabic = Helper::schoolNameArabic(Session('LoggedSchool')) ?? '';
                $schoolEmail = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('email');
                $schoolMotto = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('motto');
                $schoolLocation = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('school_type');
                $schoolLogo = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('logo');

                // Resolve logo URL the same way student photos are resolved
                $schoolLogoUrl = null;
                if ($schoolLogo) {
                    // New approach: stored in public/uploads/logos/
                    $directPath = public_path('uploads/logos/' . $schoolLogo);
                    if (file_exists($directPath)) {
                        $schoolLogoUrl = asset('uploads/logos/' . $schoolLogo);
                    }
                    // Fallback: old Storage::disk('public') approach (logos/filename.ext)
                    else {
                        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                            $fallback = public_path('storage/' . $schoolLogo);
                            if (file_exists($fallback)) {
                                $schoolLogoUrl = asset('storage/' . $schoolLogo);
                                break;
                            }
                            // Also try with extensions appended
                            $fallback2 = public_path('uploads/logos/' . pathinfo($schoolLogo, PATHINFO_FILENAME) . '.' . $ext);
                            if (file_exists($fallback2)) {
                                $schoolLogoUrl = asset('uploads/logos/' . pathinfo($schoolLogo, PATHINFO_FILENAME) . '.' . $ext);
                                break;
                            }
                        }
                    }
                }

                $qrText = $slipData['qrText'] ?? '';

                /*
                |──────────────────────────────────────────────────────────────
                | Build the dynamic table column list.
                | We hide/show columns based on $cfg flags so the header and
                | every data row always stay in sync.
                |──────────────────────────────────────────────────────────────
                */
                // Count visible columns for colspan calculations
                $visibleCols = 1 // Subject (always visible)
                    + ($cfg['score_col'] ? 1 : 0)
                    + ($cfg['dev'] ? 1 : 0)
                    + ($cfg['grade_pill'] && !$isEarlyYears ? 1 : 0)
                    + ($cfg['comment_col'] ? 1 : 0)
                    + ($cfg['teacher_col'] ? 1 : 0);
            @endphp

            {{-- ────────────────────────── SLIP CARD ────────────────────────── --}}
            <div class="slip {{ $cfg['border'] ? 'has-border' : '' }}">

                {{-- Watermark --}}
                @if($cfg['watermark'])
                    @if($schoolLogoUrl)
                        <div class="watermark">
                            <img src="{{ $schoolLogoUrl }}" alt="watermark">
                        </div>
                    @else
                        <div class="watermark-text">{{ $schoolName }}</div>
                    @endif
                @endif

                {{-- ══ SCHOOL HEADER ══════════════════════════════════════════ --}}
                <div class="sch-header">
                    <div class="sch-logo-area">
                        <div class="sch-logo-box">
                            @if($cfg['logo'] && $schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="logo">
                            @else
                                <i class="fas fa-school"></i>
                            @endif
                        </div>
                    </div>

                    <div class="sch-center">
                        <div class="sch-name">{{ $schoolName }}</div>

                        @if($cfg['arabic'] && $schoolNameArabic)
                            <div class="sch-arabic-name">{{ $schoolNameArabic }}</div>
                        @endif

                        @if($cfg['contact'] && ($schoolPhone || $schoolEmail || $schoolLocation))
                            <div class="sch-details">
                                @if($schoolPhone)<span>{{ $schoolPhone }}</span>@endif
                                @if($schoolEmail)<span> | {{ $schoolEmail }} | </span> <br> @endif
                                @if($schoolLocation)<span> {{ $schoolLocation }}</span>@endif
                            </div>
                        @endif

                        @if($cfg['motto'] && $schoolMotto)
                            <div class="sch-motto">MOTTO : "{{ $schoolMotto }}"</div>
                        @endif
                    </div>

                    <div class="sch-logo-area sch-logo-area-right">
                        <div class="sch-logo-box">
                            @if($cfg['logo'] && $schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="logo">
                            @else
                                <i class="fas fa-school"></i>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ══ TITLE BAND ═══════════════════════════════════════════════ --}}
                <div class="title-band">
                    <span>
                        ACADEMIC REPORT FORM — {{ Helper::recordMdname($s->senior) }}
                        — {{ strtoupper($exam->term) }} — ({{ $exam->academic_year }})
                    </span>
                </div>

                {{-- ══ STUDENT INFO ROW ═══════════════════════════════════════════ --}}
                <div class="stu-row">

                    {{-- Photo --}}
                    @if($cfg['photo'])
                        <div class="stu-photo">
                            @if($photo)
                                <img src="{{ $photo }}" alt="{{ $s->firstname }} {{ $s->lastname }}"
                                    style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <div class="nophoto">
                                    <i class="fas fa-user"></i>
                                    <span>No Photo</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Details --}}
                    <div class="stu-details">
                        <div class="stu-field">
                            <strong>NAME:</strong>
                            {{ $s->lastname }} {{ $s->firstname }} {{ $s->other_names ?? '' }}
                        </div>
                        <div class="stu-field"><strong>Gender:</strong> {{ $s->gender ?? ($s->index_no ?? '—') }}</div>
                        <div class="stu-field">
                            <strong>CLASS:</strong>
                            {{ Helper::recordMdname($s->senior) }}{{ ($s->stream ?? false) ? ' — ' . $s->stream : '' }}
                        </div>
                        <div class="stu-field" style="margin-top:.2rem;">
                            <strong>STATUS:</strong>
                            <span @php
                                $statusLower = strtolower($statusLabel);
                                if ($isEarlyYears) {
                                    $statusClass = in_array($statusLower, ['good', 'excellent']) ? 'status-promoted' : 'status-repeat';
                                } else {
                                    $statusClass = str_contains($statusLower, 'promot')
                                        ? 'status-promoted'
                                        : (str_contains($statusLower, 'fail') ? 'status-fail' : 'status-repeat');
                                }
                            @endphp class="status-pill {{ $statusClass }}">
                                {{ ucfirst($statusLabel) }}
                            </span>
                        </div>
                        <div class="stu-field"><strong>LIN:</strong> {{ $s->adm_no ?? ($s->index_no ?? '—') }}</div>
                        @if($cfg['rank'] && is_numeric($rank))
                            <div class="stu-field" style="margin-top:.2rem;">
                                <strong>POSITION:</strong>
                                <span style="font-weight:800;color:#888;">
                                    {{ $ord($rank) }}<span style="font-size:.7rem;color:#888;font-weight:500;"> of
                                        {{ $classTotalN }}</span>
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Mini Line Chart --}}
                    @if($cfg['minichart'] && count($miniLabels) > 0)
                        <div class="stu-chart-area">
                            <div class="stu-chart-title">Subject Performance — Student vs Class</div>
                            <canvas id="{{ $cMini }}" height="110"></canvas>
                        </div>
                    @endif

                    {{-- QR Code --}}
                    @if($cfg['qr'] && $qrText)
                        <div class="stu-qr-col">
                            <div class="stu-qr-title">Scan to Verify</div>
                            <div class="stu-qr-box">
                                <canvas id="{{ $qrId }}"></canvas>
                            </div>
                            <div class="stu-qr-label">SMASA</div>
                        </div>
                    @endif

                </div>



                {{-- ══ MARKS TABLE ════════════════════════════════════════════════ --}}
                <div class="marks-wrap">
                    @if($multiExam)
                        {{-- ── MULTI-EXAM TABLE (BOT | MID | EOT …) ── --}}
                        @php
                            $examLabels = [
                                'Beginning-of-Term' => 'BOT',
                                'Mid-Term' => 'MOT',
                                'End-of-Term' => 'EOT',
                                'Continuous Assessment' => 'CA',
                            ];
                            // Aggregate/Division columns only make sense for the
                            // graded (non-early-years) point-based scale.
                            $showAggDiv = !$isEarlyYears;
                        @endphp
                        <table class="marks-tbl">
                            <thead>
                                <tr>
                                    <th class="tl" rowspan="2" style="min-width:100px;">SUBJECTS</th>
                                    @foreach($examsList as $ex)
                                        <th class="exam-grp-th" colspan="2">
                                            {{ $examLabels[$ex->exam_type] ?? strtoupper($ex->term ?? $ex->exam_name) }}
                                        </th>
                                    @endforeach
                                    @if($cfg['grade_pill'])
                                        <th rowspan="2" style="width:38px;">GRADE</th>
                                    @endif
                                    @if($cfg['teacher_col'])
                                        <th class="tl" rowspan="2" style="min-width:100px;">TEACHER</th>
                                    @endif
                                </tr>
                                <tr>
                                    @foreach($examsList as $ex)
                                        <th class="sub-th">{{ $isEarlyYears ? 'SCORE' : 'MARKS' }}</th>
                                        <th class="sub-th">GRADE</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjMarks as $sm)
                                    <tr>
                                        <td style="font-weight:500;">{{ $sm->subject_name }}</td>
                                        @foreach($examsList as $ex)
                                            @php $ed = $sm->exams[$ex->id] ?? null; @endphp
                                            <td class="score-td">
                                                {{ $ed && $ed['marks_obtained'] !== null ? $ed['marks_obtained'] : '—' }}
                                            </td>
                                            <td class="num-td">
                                                @if($ed && $ed['grade'] && $ed['grade'] !== '—')
                                                    <span class="g-pill {{ $gc($ed['grade']) }}">{{ $ed['grade'] }}</span>
                                                @else
                                                    <span style="color:#bbb;">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @if($cfg['grade_pill'])
                                            <td class="num-td">
                                                <span class="g-pill {{ $gc($sm->grade) }}">{{ $sm->grade ?? '—' }}</span>
                                            </td>
                                        @endif
                                        @if($cfg['teacher_col'])
                                            <td style="font-size:.72rem;color:#555;">{{ $sm->teacher_name ?? '—' }}</td>
                                        @endif
                                    </tr>
                                @endforeach

                                {{-- TOTAL / AGGREGATE ROW --}}
                                @if($cfg['totals_row'])
                                    <tr class="totals-row">
                                        <td
                                            style="text-align:right;color:#666;font-size:.72rem;padding-right:.8rem;font-weight:600;">
                                            {{ $showAggDiv ? 'TOTAL / AGG' : 'TOTAL / ' . ($useAvgSlip ? 'AVERAGE' : 'COMBINED') }}
                                        </td>
                                        @foreach($examsList as $ex)
                                            @php $esum = $examSummarySlip->get($ex->id); @endphp
                                            @if($showAggDiv)
                                                <td class="score-td">{{ $esum['total_marks'] ?? '—' }}</td>
                                                <td class="num-td">{{ $esum['aggregate'] ?? '—' }}</td>
                                            @else
                                                @php
                                                    $examPctSum = $subjMarks->sum(fn($sm) => $sm->exams[$ex->id]['percentage'] ?? 0);
                                                    $examPctCnt = $subjMarks->filter(fn($sm) => ($sm->exams[$ex->id]['percentage'] ?? null) !== null)->count();
                                                    $examAvgPct = $examPctCnt > 0 ? round($examPctSum / $examPctCnt, 1) : null;
                                                @endphp
                                                <td class="score-td" colspan="2">{{ $examAvgPct !== null ? $examAvgPct . '%' : '—' }}</td>
                                            @endif
                                        @endforeach
                                        @if($cfg['grade_pill'])
                                            <td class="num-td"><span class="g-pill {{ $gc($oGrade) }}">{{ $oGrade }}</span></td>
                                        @endif
                                        @if($cfg['teacher_col'])
                                            <td></td>
                                        @endif
                                    </tr>
                                @endif

                                {{-- DIVISION ROW --}}
                                @if($cfg['totals_row'] && $showAggDiv)
                                    <tr class="division-row">
                                        <td class="division-label">DIVISION</td>
                                        @foreach($examsList as $ex)
                                            @php $div = $examSummarySlip->get($ex->id)['division'] ?? '—'; @endphp
                                            <td colspan="2">
                                                <span class="div-pill {{ $divClass($div) }}">{{ strtoupper($div) }}</span>
                                            </td>
                                        @endforeach
                                        @if($cfg['grade_pill'])
                                            <td></td>
                                        @endif
                                        @if($cfg['teacher_col'])
                                            <td></td>
                                        @endif
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    @else
                        <table class="marks-tbl">
                            <thead>
                                <tr>
                                    <th class="tl" style="min-width:110px;">SUBJECTS</th>
                                    @if($cfg['score_col'])
                                        <th style="width:48px;">{{ $isEarlyYears ? 'SCORE' : 'MARKS' }}</th>
                                    @endif
                                    @if($cfg['dev'])
                                        <th style="width:38px;">DEV.</th>
                                    @endif
                                    @if($cfg['grade_pill'] && !$isEarlyYears)
                                        <th style="width:38px;">GRADE</th>
                                    @endif
                                    @if($cfg['comment_col'])
                                        <th class="tl">COMMENT</th>
                                    @endif
                                    @if($cfg['teacher_col'])
                                        <th class="tl" style="min-width:100px;">TEACHER</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $rn = 0; @endphp

                                @if($useGroups)
                                    @foreach($grouped as $grpName => $grpSubjs)
                                        @if($grpName)
                                            <tr class="grp-row">
                                                <td colspan="{{ $visibleCols }}">{{ strtoupper($grpName) }}</td>
                                            </tr>
                                        @endif
                                        @foreach($grpSubjs as $sm)
                                            @php
                                                $rn++;
                                                $prevM = $prevSubj[$sm->subject_id] ?? null;
                                                $delta = null;
                                                if ($prevM && ($prevM->total_marks ?? 0) > 0) {
                                                    $pPct = round(($prevM->marks_obtained / $prevM->total_marks) * 100, 1);
                                                    $delta = round($sm->percentage - $pPct, 1);
                                                }
                                            @endphp
                                            <tr>
                                                <td style="font-weight:500;">{{ $sm->subject_name }}</td>
                                                @if($cfg['score_col'])
                                                    <td class="score-td">
                                                        @if($isEarlyYears)
                                                            {{ $sm->marks_obtained ?? '—' }}<span
                                                                style="font-size:.65rem;color:#aaa;">/{{ $earlyYearsMax }}</span>
                                                        @else
                                                            {{ $sm->percentage }}%
                                                        @endif
                                                    </td>
                                                @endif
                                                @if($cfg['dev'])
                                                    <td class="num-td">
                                                        @if($delta !== null)
                                                            @if($delta > 0) <span class="dev-up">+{{ $delta }} ↑</span>
                                                            @elseif($delta < 0) <span class="dev-down">{{ $delta }} ↓</span>
                                                            @else <span class="dev-eq">—</span>
                                                            @endif
                                                        @else <span class="dev-eq">—</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                @if($cfg['grade_pill'] && !$isEarlyYears)
                                                    <td class="num-td">
                                                        <span class="g-pill {{ $gc($sm->grade) }}">{{ $sm->grade ?? '—' }}</span>
                                                    </td>
                                                @endif
                                                @if($cfg['comment_col'])
                                                    <td>{{ $sm->grade_remark ?? '—' }}</td>
                                                @endif
                                                @if($cfg['teacher_col'])
                                                    <td style="font-size:.72rem;color:#555;">{{ $sm->teacher_name ?? '—' }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                    @foreach($subjMarks as $sm)
                                        @php
                                            $rn++;
                                            $prevM = $prevSubj[$sm->subject_id] ?? null;
                                            $delta = null;
                                            if ($prevM && ($prevM->total_marks ?? 0) > 0) {
                                                $pPct = round(($prevM->marks_obtained / $prevM->total_marks) * 100, 1);
                                                $delta = round($sm->percentage - $pPct, 1);
                                            }
                                        @endphp
                                        <tr>
                                            <td style="font-weight:500;">{{ $sm->subject_name }}</td>
                                            @if($cfg['score_col'])
                                                <td class="score-td">
                                                    @if($isEarlyYears)
                                                        {{ $sm->marks_obtained ?? '—' }}<span
                                                            style="font-size:.65rem;color:#aaa;">/{{ $earlyYearsMax }}</span>
                                                    @else
                                                        {{ $sm->percentage }}%
                                                    @endif
                                                </td>
                                            @endif
                                            @if($cfg['dev'])
                                                <td class="num-td">
                                                    @if($delta !== null)
                                                        @if($delta > 0) <span class="dev-up">+{{ $delta }} ↑</span>
                                                        @elseif($delta < 0) <span class="dev-down">{{ $delta }} ↓</span>
                                                        @else <span class="dev-eq">—</span>
                                                        @endif
                                                    @else <span class="dev-eq">—</span>
                                                    @endif
                                                </td>
                                            @endif
                                            @if($cfg['grade_pill'] && !$isEarlyYears)
                                                <td class="num-td">
                                                    <span class="g-pill {{ $gc($sm->grade) }}">{{ $sm->grade ?? '—' }}</span>
                                                </td>
                                            @endif
                                            @if($cfg['comment_col'])
                                                <td>{{ $sm->grade_remark ?? '—' }}</td>
                                            @endif
                                            @if($cfg['teacher_col'])
                                                <td style="font-size:.72rem;color:#555;">{{ $sm->teacher_name ?? '—' }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- TOTALS ROW --}}
                                @if($cfg['totals_row'])
                                    @php $resultColspan = ($cfg['comment_col'] ? 1 : 0) + ($cfg['teacher_col'] ? 1 : 0); @endphp
                                    <tr class="totals-row">
                                        <td
                                            style="text-align:right;color:#666;font-size:.72rem;padding-right:.8rem;font-weight:600;">
                                            TOTAL / AVERAGE
                                        </td>
                                        @if($cfg['score_col'])
                                            <td class="score-td">
                                                @if($isEarlyYears)
                                                    {{ $earlyYearsAvg }}<span
                                                        style="font-size:.65rem;color:#aaa;">/{{ $earlyYearsMax }}</span>
                                                @else
                                                    {{ $pct }}%
                                                @endif
                                            </td>
                                        @endif
                                        @if($cfg['dev'])
                                            <td class="num-td">
                                                @if($termDelta !== null)
                                                    @if($termDelta > 0) <span class="dev-up">+{{ $termDelta }} ↑</span>
                                                    @elseif($termDelta < 0) <span class="dev-down">{{ $termDelta }} ↓</span>
                                                    @else <span class="dev-eq">—</span>
                                                    @endif
                                                @else <span class="dev-eq">—</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if($cfg['grade_pill'] && !$isEarlyYears)
                                            <td class="num-td">
                                                <span class="g-pill {{ $gc($oGrade) }}">{{ $oGrade }}</span>
                                            </td>
                                        @endif
                                        @if($resultColspan > 0)
                                            <td colspan="{{ $resultColspan }}">
                                                <strong
                                                    style="color:{{ $isEarlyYears ? '#1a7a4a' : ($passed ? '#1a7a4a' : '#c0392b') }}">
                                                    {{ strtoupper($oRemark) }}
                                                </strong>
                                            </td>
                                        @endif
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- ══ BOTTOM SECTION ══════════════════════════════════════════════ --}}
                @if($cfg['perf_chart'] || $cfg['remarks'] || $cfg['signatures'])
                    <div class="bottom-section">

                        {{-- Performance Over Time chart --}}
                        @if($cfg['perf_chart'] && count($growth) > 0)
                            <div class="perf-chart-col">
                                <div class="perf-chart-title">{{ $s->firstname }}'s Performance over Time</div>
                                <canvas id="{{ $cPerf }}" height="140"></canvas>
                            </div>
                        @endif

                        {{-- Remarks --}}
                        @if($cfg['remarks'])
                            <div class="remarks-col">
                                <div class="remarks-section-title">Remarks</div>

                                @php
                                    $classTeacher = $subjMarks->first()?->class_teacher ?? null;
                                    $classTeacherName = $classTeacher ?? ($s->class_teacher ?? 'Class Teacher');
                                    $ctRemark = $s->class_teacher_remark ?? '';
                                @endphp
                                <div class="remark-block">
                                    <div class="remark-teacher">
                                        {{ $classTeacherName }}
                                    </div>
                                    <div class="remark-text">{{ $ctRemark ?: 'No remarks recorded.' }}</div>
                                </div>

                                <div class="sig-dashes" style="font-weight:Bold;">House Teacher</div>
                                <div style="height:18px;border-bottom:1.5px dashed #444;margin-bottom:.4rem;"></div>

                                <div class="remark-block">
                                    <div class="remark-teacher">
                                        {{ $s->head_teacher ?? (Session('HeadTeacherName') ?? 'Head Teacher') }}
                                    </div>
                                    <div class="remark-text">{{ $s->head_teacher_remark ?? '' }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Signatures --}}
                        @if($cfg['signatures'])
                            <div class="sig-col-right">
                                <div class="sig-col-title">Signature</div>
                                <div style="width:100%;margin-top:.5rem;">
                                    <div class="sig-slot">Class Teacher</div>
                                    <div class="sig-slot" style="margin-top:1.4rem;">House Teacher</div>
                                    <div class="sig-slot has-sig" style="margin-top:1.4rem;">
                                        @if(!empty($s->head_teacher_signature))
                                            <img src="{{ asset('signatures/' . $s->head_teacher_signature) }}"
                                                style="max-width:90px;max-height:22px;object-fit:contain;" alt="sig">
                                        @endif
                                        Head Teacher
                                    </div>
                                    <div class="sig-slot" style="margin-top:1.4rem;">Parent / Guardian</div>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif

                {{-- ══ FOOTER ════════════════════════════════════════════════════════ --}}
                @if($cfg['footer_timestamp'] || $cfg['confidential'])
                    <div class="slip-footer">
                        <div style="font-size:.58rem;color:#aaa;">
                            @if($cfg['footer_timestamp'])
                                Generated: {{ now()->format('d M Y, H:i') }}
                                &bull; {{ $exam->exam_code ?? '' }}
                                &bull; {{ $exam->term }} {{ $exam->academic_year }}
                            @endif
                        </div>
                        @if($cfg['confidential'])
                            <div style="font-size:.63rem;font-weight:800;color:#c0392b;letter-spacing:.07em;">CONFIDENTIAL</div>
                        @endif
                    </div>
                @endif

            </div>{{-- /.slip --}}

            {{-- ══ PER-SLIP SCRIPTS ═══════════════════════════════════════════════ --}}
            <script>
                (function () {
                    /* Mini student-vs-class line chart */
                    @if($cfg['minichart'] && count($miniLabels) > 0)
                        var miniCtx = document.getElementById('{{ $cMini }}');
                        if (miniCtx) {
                            new Chart(miniCtx.getContext('2d'), {
                                type: 'line',
                                data: {
                                    labels: {!! json_encode($miniLabels) !!},
                                    datasets: [
                                        {
                                            label: '{{ addslashes($s->firstname) }}',
                                            data: {!! json_encode($miniStudent) !!},
                                            borderColor: '#1a7a4a',
                                            backgroundColor: 'rgba(26,122,74,.08)',
                                            tension: 0.35, fill: true,
                                            pointRadius: 3, borderWidth: 2,
                                            pointBackgroundColor: '#1a7a4a',
                                        },
                                        {
                                            label: '{{ addslashes(Helper::recordMdname($s->senior)) }}',
                                            data: {!! json_encode($miniClass) !!},
                                            borderColor: '#aaa',
                                            backgroundColor: 'transparent',
                                            tension: 0.35, fill: false,
                                            pointRadius: 2, borderWidth: 1.5,
                                            borderDash: [4, 3],
                                            pointBackgroundColor: '#aaa',
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true, animation: false,
                                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 8, font: { size: 9 } } } },
                                    scales: {
                                        y: { min: 0, max: 100, ticks: { font: { size: 8 }, stepSize: 50 }, grid: { color: '#f0f0f0' } },
                                        x: { ticks: { font: { size: 8 } }, grid: { display: false } }
                                    }
                                }
                            });
                        }
                    @endif

                        /* Performance over time bar chart */
                        @if($cfg['perf_chart'] && count($growth) > 0)
                            var perfCtx = document.getElementById('{{ $cPerf }}');
                            if (perfCtx) {
                                var vals = {!! json_encode($growthValues) !!};
                                new Chart(perfCtx.getContext('2d'), {
                                    type: 'bar',
                                    data: {
                                        labels: {!! json_encode($growthLabels) !!},
                                        datasets: [{
                                            data: vals,
                                            backgroundColor: vals.map(function (v, i) {
                                                return i === vals.length - 1 ? '{{ $accent }}' : '#555';
                                            }),
                                            borderRadius: 3, borderSkipped: false,
                                        }]
                                    },
                                    options: {
                                        responsive: true, animation: false,
                                        plugins: { legend: { display: false } },
                                        scales: {
                                            y: { min: 0, max: 100, ticks: { font: { size: 8 }, stepSize: 50 }, grid: { color: '#f0f0f0' } },
                                            x: { ticks: { font: { size: 8 } }, grid: { display: false } }
                                        }
                                    }
                                });
                            }
                        @endif
                                            })();
            </script>

            {{-- QR Code --}}
            @if($cfg['qr'] && $qrText)
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var qrCanvas = document.getElementById('{{ $qrId }}');
                        if (!qrCanvas || typeof QRCode === 'undefined') return;

                        var qrData = `{{ addslashes(implode('\n', array_filter([
                    'Student: ' . trim($s->lastname . ' ' . $s->firstname . ' ' . ($s->other_names ?? '')),
                    'Adm No: ' . ($s->adm_no ?? ($s->index_no ?? '')),
                    'Class: ' . Helper::recordMdname($s->senior) . (($s->stream ?? false) ? ' - ' . $s->stream : ''),
                    'Exam: ' . $exam->exam_name,
                    'Term: ' . $exam->term,
                    'Year: ' . $exam->academic_year,
                    'Average: ' . ($isEarlyYears ? $earlyYearsAvg . '/' . $earlyYearsMax : $pct . '%'),
                    'Grade: ' . ($isEarlyYears ? $oRemark : $oGrade),
                    'Result: ' . ($isEarlyYears ? strtoupper($oRemark) : ($passed ? 'PASS' : 'FAIL')),
                    'School: ' . $schoolName,
                ]))) }}`;

                        QRCode.toCanvas(qrCanvas, qrData, {
                            width: 160, margin: 2,
                            errorCorrectionLevel: 'H',
                            color: { dark: '#000000', light: '#FFFFFF' }
                        }, function (error) {
                            if (error) console.error('QR Error:', error);
                        });
                    });
                </script>
            @endif

        @endforeach
    </div>{{-- /.page-wrap --}}

    <script>
        @if($mode === 'class' || $mode === 'all')
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 900);
            });
        @endif
    </script>
</body>

</html>