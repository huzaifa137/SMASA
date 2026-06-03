<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف النتائج — {{ $exam->exam_name }}</title>
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    {{-- Arabic-optimised typefaces: Noto Naskh Arabic (body) + Amiri (display headings) --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>

    @php
        /*
        |──────────────────────────────────────────────────────────────
        | CUSTOMISATION — same query-string params as English slip.
        |──────────────────────────────────────────────────────────────
        */
        $accent = request('accent', '#1a6b3c');   // Default: deep Islamic green
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $accent)) {
            $accent = '#1a6b3c';
        }

        $on = fn(string $key, bool $default = true): bool =>
            request()->has($key)
            ? in_array(request($key), ['1', 'true', 1, true], true)
            : $default;

        $cfg = [
            'border' => $on('show_border'),
            'watermark' => $on('show_watermark'),
            'logo' => $on('show_logo'),
            'arabic' => $on('show_arabic'),
            'motto' => $on('show_motto'),
            'contact' => $on('show_contact'),
            'photo' => $on('show_photo'),
            'minichart' => $on('show_minichart'),
            'qr' => $on('show_qr'),
            'rank' => $on('show_rank'),
            'dev' => $on('show_dev'),
            'grade_pill' => $on('show_grade_pill'),
            'teacher_col' => $on('show_teacher_col'),
            'totals_row' => $on('show_totals_row'),
            'perf_chart' => $on('show_perf_chart'),
            'remarks' => $on('show_remarks'),
            'signatures' => $on('show_signatures'),
            'footer_timestamp' => $on('show_footer_timestamp'),
            'confidential' => $on('show_confidential'),
        ];

        $hexToDark = function (string $hex): string {
            $hex = ltrim($hex, '#');
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            return sprintf('#%02x%02x%02x', max(0, (int) ($r * .82)), max(0, (int) ($g * .82)), max(0, (int) ($b * .82)));
        };
        $accentDark = $hexToDark($accent);

        $accentAlpha = function (string $hex, float $a): string {
            $hex = ltrim($hex, '#');
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            return "rgba({$r},{$g},{$b},{$a})";
        };
        $accentA08 = $accentAlpha($accent, 0.08);
        $accentA22 = $accentAlpha($accent, 0.22);
        $accentA35 = $accentAlpha($accent, 0.35);

        /* ── Arabic label map ─────────────────────────────────────────
           All UI strings that appear on the printed slip.
        ──────────────────────────────────────────────────────────────*/
        $ar = [
            // toolbar
            'report_card' => 'كشف النتائج',
            'class_cards' => 'كشوف نتائج الفصل',
            'all_cards' => 'جميع كشوف النتائج',
            'print_save' => 'طباعة / حفظ PDF',
            'back' => 'رجوع',

            // title band
            'academic_report' => 'سجل النتائج الدراسية',

            // student block labels
            'name' => 'الاسم',
            'adm_no' => 'رقم القيد',
            'class' => 'الصف',
            'status' => 'الحالة',
            'position' => 'الترتيب',
            'of' => 'من',
            'scan_verify' => 'امسح للتحقق',
            'subject_vs_class' => 'أداء المادة — الطالب مقابل الفصل',

            // status labels
            'promoted' => 'ناجح ومنقول',
            'repeat' => 'راسب',
            'fail' => 'راسب',

            // summary bar
            'total_marks' => 'مجموع الدرجات',
            'average' => 'المعدل',
            'grade' => 'التقدير',
            'result' => 'النتيجة',
            'pass' => 'ناجح',
            'fail_result' => 'راسب',

            // marks table headers
            'subjects' => 'المواد الدراسية',
            'marks' => 'الدرجات',
            'dev' => 'التطور',
            'comment' => 'الملاحظة',
            'teacher' => 'المعلم',
            'total_avg_row' => 'المجموع / المعدل',

            // bottom section
            'performance_chart' => 'مسيرة أداء الطالب',
            'remarks_title' => 'الملاحظات',
            'class_teacher' => 'معلم الفصل',
            'house_teacher' => 'المشرف',
            'head_teacher' => 'مدير المدرسة',
            'no_remarks' => 'لا توجد ملاحظات مسجلة.',
            'signature' => 'التوقيع',
            'parent' => 'ولي الأمر',

            // footer
            'generated' => 'تاريخ الإصدار',
            'confidential' => 'سري',

            // ordinal suffix (Arabic uses different grammar)
            'rank_label' => 'المرتبة',
        ];
    @endphp

    <style>
        /* ══════════════════════════════════════════════════════════════
           DESIGN TOKENS
        ══════════════════════════════════════════════════════════════ */
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
            --font-body: 'Noto Naskh Arabic', 'Times New Roman', serif;
            --font-display: 'Amiri', 'Times New Roman', serif;
        }

        /* ══════════════════════════════════════════════════════════════
           RESET & BASE  (RTL)
        ══════════════════════════════════════════════════════════════ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background: #dde1e7;
            color: #111;
            font-size: 13px;
            line-height: 1.6;
            direction: rtl;
            text-align: right;
        }

        /* ══════════════════════════════════════════════════════════════
           SCREEN TOOLBAR
        ══════════════════════════════════════════════════════════════ */
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
            direction: rtl;
        }

        .toolbar-info strong {
            color: #fff;
            font-size: .9rem;
            font-weight: 700;
            font-family: var(--font-display);
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
            font-family: var(--font-body);
            font-weight: 700;
            font-size: .82rem;
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

        /* ══════════════════════════════════════════════════════════════
           PAGE WRAPPER
        ══════════════════════════════════════════════════════════════ */
        .page-wrap {
            max-width: 800px;
            margin: 1.5rem auto;
        }

        /* ══════════════════════════════════════════════════════════════
           SINGLE SLIP CARD
        ══════════════════════════════════════════════════════════════ */
        .slip {
            background: #fff;
            margin-bottom: 2.5rem;
            page-break-after: always;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .12);
            direction: rtl;
        }

        .slip:last-child {
            page-break-after: avoid;
            margin-bottom: 0;
        }

        /* Decorative Islamic-inspired geometric top border */
        .slip.has-border {
            border: 3px solid var(--accent);
            outline: 1px solid var(--accent-dark);
            outline-offset: -6px;
        }

        .slip.has-border::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 1px solid var(--accent-a35);
            border-radius: 1px;
            pointer-events: none;
            z-index: 2;
        }

        .slip.has-border::after {
            content: '';
            position: absolute;
            inset: 4px;
            background:
                linear-gradient(var(--accent), var(--accent)) top right / 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) top right / 3px 18px no-repeat,
                linear-gradient(var(--accent), var(--accent)) top left / 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) top left / 3px 18px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom right/ 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom right/ 3px 18px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom left / 18px 3px no-repeat,
                linear-gradient(var(--accent), var(--accent)) bottom left / 3px 18px no-repeat;
            pointer-events: none;
            z-index: 2;
        }

        /* ══════════════════════════════════════════════════════════════
           DECORATIVE GEOMETRIC HEADER STRIP (Islamic-pattern motif)
        ══════════════════════════════════════════════════════════════ */
        .geo-strip {
            height: 8px;
            background: repeating-linear-gradient(90deg,
                    var(--accent) 0px,
                    var(--accent) 12px,
                    var(--accent-dark) 12px,
                    var(--accent-dark) 16px,
                    transparent 16px,
                    transparent 20px);
        }

        /* ══════════════════════════════════════════════════════════════
           SCHOOL HEADER
        ══════════════════════════════════════════════════════════════ */
        .sch-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .7rem 1.2rem .55rem;
            border-bottom: 3px solid var(--accent);
            direction: rtl;
            flex-direction: row-reverse;
            /* ← ADD THIS LINE */
        }

        /* Right side = text (RTL default) */
        .sch-text {
            flex: 1;
            text-align: right;
        }

        .sch-name {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
            color: #111;
            line-height: 1.2;
        }

        .sch-name-latin {
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-top: 2px;
            direction: ltr;
            text-align: right;
        }

        .sch-details {
            font-size: 15.5px;
            color: #555;
            margin-top: 3px;
        }

        .sch-motto {
            font-family: var(--font-display);
            font-size: 12px;
            font-style: italic;
            color: #777;
            margin-top: 3px;
        }

        /* Left side = logo */
        .sch-logo-box {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            border: 2.5px solid var(--accent);
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f8f0;
            margin-right: 1rem;
            margin-left: 0;
        }

        .sch-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sch-logo-box i {
            font-size: 1.8rem;
            color: var(--accent);
        }

        /* ══════════════════════════════════════════════════════════════
           TITLE BAND
        ══════════════════════════════════════════════════════════════ */
        .title-band {
            background: var(--accent);
            padding: .5rem 1.2rem;
            text-align: center;
        }

        .title-band span {
            font-family: var(--font-display);
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .04em;
            color: #fff;
        }

        /* ══════════════════════════════════════════════════════════════
           STUDENT INFO ROW
        ══════════════════════════════════════════════════════════════ */
        .stu-row {
            display: flex;
            align-items: stretch;
            padding: .75rem 1.2rem;
            gap: 1rem;
            border-bottom: 1.5px solid #e0e0e0;
            direction: rtl;
        }

        /* photo comes last visually (left side in RTL) */
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

        .nophoto {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            gap: .2rem;
            background: #e8e8e8;
        }

        .nophoto i {
            font-size: 2.8rem;
            color: #b0b0b0;
        }

        .nophoto span {
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
            gap: .32rem;
            border-left: 1.5px solid #e8e8e8;
            padding-left: 1rem;
        }

        .stu-field {
            font-size: .88rem;
            color: #111;
        }

        .stu-field strong {
            font-weight: 700;
        }

        .status-pill {
            display: inline-block;
            padding: .2rem .75rem;
            border-radius: 20px;
            font-size: .75rem;
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
            padding-left: .4rem;
        }

        .stu-chart-title {
            font-family: var(--font-display);
            font-size: .73rem;
            font-weight: 700;
            color: #555;
            text-align: center;
            margin-bottom: .25rem;
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
            border-right: 1.5px solid #e8e8e8;
            padding-right: 1rem;
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

        /* ══════════════════════════════════════════════════════════════
           SUMMARY BAR
        ══════════════════════════════════════════════════════════════ */
        .sum-bar {
            display: flex;
            background: #e8ecf0;
            border-bottom: 1.5px solid #c8c8c8;
            direction: rtl;
        }

        .sum-cell {
            flex: 1;
            padding: .55rem .5rem;
            text-align: center;
            border-left: 1.5px solid #c8c8c8;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .sum-cell:last-child {
            border-left: none;
        }

        .sum-lbl {
            font-family: var(--font-display);
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #777;
            font-weight: 700;
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
            margin-right: .25rem;
            vertical-align: middle;
        }

        .di-up {
            color: #1a7a4a;
        }

        .di-dn {
            color: #c0392b;
        }

        /* ══════════════════════════════════════════════════════════════
           MARKS TABLE  (RTL columns)
        ══════════════════════════════════════════════════════════════ */
        .marks-wrap {
            padding: 0 1.2rem .5rem;
        }

        .marks-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            border: 1.5px solid #c8c8c8;
            margin-top: .5rem;
            direction: rtl;
        }

        .marks-tbl th {
            background: #1a1a1a;
            color: #fff;
            padding: .4rem .55rem;
            text-align: center;
            font-family: var(--font-display);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .02em;
            border-left: 1px solid #333;
        }

        .marks-tbl th:last-child {
            border-left: none;
        }

        .marks-tbl th.tr {
            text-align: right;
        }

        .marks-tbl td {
            padding: .34rem .55rem;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        .marks-tbl tbody tr:nth-child(even) {
            background: #f9f9fb;
        }

        .marks-tbl tbody tr:hover {
            background: #f0faf3;
        }

        .grp-row td {
            background: var(--accent);
            color: #fff;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: .72rem;
            letter-spacing: .03em;
            padding: .3rem .55rem;
        }

        .num-td {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .score-td {
            text-align: center;
            font-weight: 700;
            font-size: .82rem;
        }

        .g-pill {
            display: inline-block;
            min-width: 24px;
            text-align: center;
            padding: .1rem .35rem;
            border-radius: 3px;
            font-weight: 800;
            font-size: .72rem;
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
            font-size: .78rem;
            border-top: 2px solid #b0b0b0;
        }

        /* ══════════════════════════════════════════════════════════════
           BOTTOM SECTION
        ══════════════════════════════════════════════════════════════ */
        .bottom-section {
            display: flex;
            gap: 0;
            border-top: 1.5px solid #ddd;
            min-height: 200px;
            direction: rtl;
        }

        .perf-chart-col {
            flex: 0 0 260px;
            padding: .7rem .9rem;
            border-left: 1.5px solid #ddd;
            display: flex;
            flex-direction: column;
        }

        .perf-chart-title {
            font-family: var(--font-display);
            font-size: .75rem;
            font-weight: 700;
            color: #111;
            margin-bottom: .5rem;
            letter-spacing: .01em;
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
            font-family: var(--font-display);
            font-size: .78rem;
            font-weight: 700;
            color: #555;
            border-bottom: 1px dashed #ccc;
            padding-bottom: .2rem;
            margin-bottom: .2rem;
        }

        .remark-block {
            margin-bottom: .4rem;
        }

        .remark-teacher {
            font-size: .77rem;
            font-weight: 700;
            color: #111;
        }

        .remark-text {
            font-size: .75rem;
            color: #444;
            line-height: 1.5;
            margin-top: .15rem;
        }

        .sig-dashes {
            border-top: 1px dashed #bbb;
            margin: .55rem 0 .15rem;
            padding-top: .2rem;
            font-size: .62rem;
            color: #aaa;
            text-transform: uppercase;
        }

        .sig-col-right {
            flex: 0 0 130px;
            padding: .7rem .8rem;
            border-right: 1.5px solid #ddd;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            align-items: center;
        }

        .sig-col-title {
            font-family: var(--font-display);
            font-size: .72rem;
            font-weight: 700;
            color: #555;
            border-bottom: 1px dashed #ccc;
            padding-bottom: .2rem;
            width: 100%;
        }

        .sig-slot {
            width: 100%;
            text-align: center;
            border-bottom: 1px solid #999;
            padding-bottom: .15rem;
            font-size: .63rem;
            color: #999;
            letter-spacing: .01em;
            margin-bottom: .4rem;
            font-family: var(--font-display);
        }

        .sig-slot.has-sig {
            padding-top: 18px;
        }

        /* ══════════════════════════════════════════════════════════════
           FOOTER
        ══════════════════════════════════════════════════════════════ */
        .slip-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .45rem 1.2rem;
            border-top: 1.5px solid #ddd;
            background: #fafafa;
            gap: .5rem;
            flex-wrap: wrap;
            direction: rtl;
        }

        /* ══════════════════════════════════════════════════════════════
           WATERMARK
        ══════════════════════════════════════════════════════════════ */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: .07;
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
            font-family: var(--font-display);
            font-size: 5rem;
            font-weight: 700;
            color: #000;
            white-space: nowrap;
            text-align: center;
        }

        .slip>*:not(.watermark):not(.watermark-text) {
            position: relative;
            z-index: 1;
        }

        /* ══════════════════════════════════════════════════════════════
           PRINT
        ══════════════════════════════════════════════════════════════ */
        @media print {
            @page {
                margin: .5cm .65cm;
                size: A4;
            }

            body {
                background: #fff;
                font-size: 12px;
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
            .stu-qr-box img,
            .geo-strip {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <?php use App\Http\Controllers\Helper; ?>

    {{-- ══ TOOLBAR ══════════════════════════════════════════════════ --}}
    <div class="toolbar">
        <div class="toolbar-info">
            <strong>
                <i class="fas fa-file-alt" style="margin-left:.35rem"></i>
                @if($mode === 'single') {{ $ar['report_card'] }}
                @elseif($mode === 'class') {{ $ar['class_cards'] }}
                @else {{ $ar['all_cards'] }} @endif
                — {{ $exam->exam_name }}
            </strong>
            <small>
                @if($mode === 'single')
                    {{ $student->lastname }} {{ $student->firstname }}
                @elseif($mode === 'class')
                    {{ Helper::recordMdname($classId) }}
                    {{ isset($streamId) && $streamId ? '— ' . $streamId : '' }}
                @else {{ $ar['all_cards'] }} @endif
                &bull; {{ $exam->term }} {{ $exam->academic_year }}
            </small>
        </div>
        <div style="display:flex;gap:.45rem;flex-wrap:wrap;">
            <button class="tbtn tbtn-print" onclick="window.print()">
                <i class="fas fa-print"></i> {{ $ar['print_save'] }}
            </button>
            <a href="{{ route('examination.passslips.index', $exam->id) }}" class="tbtn tbtn-back">
                <i class="fas fa-arrow-right"></i> {{ $ar['back'] }}
            </a>
        </div>
    </div>

    @php
        /* ── Normalise to single render array ── */
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
                ]
            ];
        } else {
            $renderSlips = $slips;
        }

        /* ── Helpers ── */
        $gc = function ($grade) {
            if (!$grade || $grade === '—')
                return 'g-x';
            $first = strtoupper(substr(trim($grade), 0, 1));
            return match ($first) {
                'A', 'D' => 'g-A', 'B' => 'g-B', 'C' => 'g-C',
                'P' => 'g-P', 'F' => 'g-F', default => 'g-x',
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

                $passed = $pct >= $exam->pass_mark;
                $statusLabel = $s->status ?? ($passed ? 'promoted' : 'repeat');

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
                $miniLabels = $subjMarks->map(fn($sm) => mb_substr($sm->subject_name, 0, 5))->values()->toArray();
                $miniStudent = $subjMarks->pluck('percentage')->values()->toArray();
                $miniClass = $subjMarks->map(fn($sm) => $sm->class_average ?? rand(55, 80))->values()->toArray();

                /* Growth chart arrays */
                $growthLabels = collect($growth)->pluck('label')->toArray();
                $growthValues = collect($growth)->pluck('percentage')->toArray();

                /* Term delta */
                $prevPct = isset($growth[count($growth) - 2]) ? $growth[count($growth) - 2]['percentage'] : null;
                $termDelta = $prevPct !== null ? round($pct - $prevPct, 1) : null;

                /* Unique canvas IDs */
                $cMini = 'mini_ar_' . $slipCounter;
                $cPerf = 'perf_ar_' . $slipCounter;
                $qrId = 'qr_ar_' . $slipCounter;

                /* School meta */
                $schoolNameArabic = Helper::schoolNameArabic(Session('LoggedSchool')) ?? '';
                $schoolPhone = Helper::toArabicNumberDate(
                    Helper::schoolPhoneBySchoolID(Session('LoggedSchool')) ?? ''
                );

                $schoolEmail = Helper::toArabicLettersCountriesAndWordsPackage(
                    DB::table('school_profiles')
                        ->where('school_id', Session('LoggedSchool'))
                        ->value('email') ?? ''
                );

                $schoolMotto = Helper::toArabicLettersCountriesAndWordsPackage(
                    DB::table('school_profiles')
                        ->where('school_id', Session('LoggedSchool'))
                        ->value('motto') ?? ''
                );

                $schoolLocation = Helper::toArabicLettersCountriesAndWordsPackage(
                    DB::table('school_profiles')
                        ->where('school_id', Session('LoggedSchool'))
                        ->value('school_type') ?? ''
                );
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

                /* Column count for colspan */
                $visibleCols = 2
                    + ($cfg['dev'] ? 1 : 0)
                    + ($cfg['grade_pill'] ? 1 : 0)
                    + 1
                    + ($cfg['teacher_col'] ? 1 : 0);
            @endphp

            {{-- ═══════════════════ SLIP CARD ═══════════════════ --}}
            <div class="slip {{ $cfg['border'] ? 'has-border' : '' }}">

                {{-- Decorative geometric strip --}}
                <div class="geo-strip"></div>


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

                {{-- ══ SCHOOL HEADER ══ --}}
                <div class="sch-header">
                    {{-- Logo LEFT side in RTL layout = end of flex row --}}
                    @if($cfg['logo'])
                       {{-- Logo in header --}}
                        <div class="sch-logo-box">
                            @if($cfg['logo'] && $schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="logo">
                            @else
                                <i class="fas fa-school"></i>
                            @endif
                        </div>
                    @endif

                    {{-- School name & details (RTL = right-aligned) --}}
                    <div class="sch-text">
                        {{-- Arabic name takes priority as the main display name --}}
                        @if($schoolNameArabic)
                            <div class="sch-name">{{ $schoolNameArabic }}</div>
                        @endif

                        {{-- Latin name shown smaller underneath --}}
                        <!-- <div class="sch-name-latin" style="direction:ltr; text-align:right;">
                                    {{ $schoolName }}
                                </div> -->

                        @if($cfg['contact'] && ($schoolPhone || $schoolEmail || $schoolLocation))
                            <div class="sch-details" style="direction:rtl;">
                                @if($schoolPhone)
                                    <span>{{ $schoolPhone }}</span>
                                @endif

                                @if($schoolEmail)
                                    <span> • {{ $schoolEmail }}</span>
                                @endif

                                @if($schoolLocation)
                                    <span> • {{ $schoolLocation }}</span>
                                @endif
                            </div>
                        @endif

                        @if($cfg['motto'] && $schoolMotto)
                            <div class="sch-motto">
                                الشعار : {{ $schoolMotto }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ══ TITLE BAND ══ --}}
                <div class="title-band">
                    <span>
                        {{ $ar['academic_report'] }} —
                        {{ Helper::recordMdname($s->senior) }}
                        — {{ $exam->term }} — ({{ $exam->academic_year }})
                    </span>
                </div>

                {{-- ══ STUDENT INFO ROW ══ --}}
                <div class="stu-row">

                    {{-- QR code (rightmost = first in RTL) --}}
                    @if($cfg['qr'] && $qrText)
                        <div class="stu-qr-col">
                            <div class="stu-qr-title">{{ $ar['scan_verify'] }}</div>
                            <div class="stu-qr-box">
                                <canvas id="{{ $qrId }}"></canvas>
                            </div>
                            <div class="stu-qr-label">SMASA</div>
                        </div>
                    @endif

                    {{-- Details --}}
                    <div class="stu-details">
                        <div class="stu-field">
                            <strong>{{ $ar['name'] }}:</strong>
                            {{ $s->lastname }} {{ $s->firstname }} {{ $s->other_names ?? '' }}
                        </div>
                        <div class="stu-field">
                            <strong>{{ $ar['adm_no'] }}:</strong>
                            {{ $s->adm_no ?? ($s->index_no ?? '—') }}
                        </div>
                        <div class="stu-field">
                            <strong>{{ $ar['class'] }}:</strong>
                            {{ Helper::recordMdname($s->senior) }}{{ ($s->stream ?? false) ? ' — ' . $s->stream : '' }}
                        </div>
                        <div class="stu-field" style="margin-top:.2rem;">
                            <strong>{{ $ar['status'] }}:</strong>
                            <span
                                class="status-pill {{ str_contains(strtolower($statusLabel), 'promot') ? 'status-promoted' : (str_contains(strtolower($statusLabel), 'fail') ? 'status-fail' : 'status-repeat') }}">
                                {{ str_contains(strtolower($statusLabel), 'promot') ? $ar['promoted'] : $ar['repeat'] }}
                            </span>
                        </div>
                        @if($cfg['rank'] && is_numeric($rank))
                            <div class="stu-field" style="margin-top:.2rem;">
                                <strong>{{ $ar['position'] }}:</strong>
                                <span style="font-weight:800;color:var(--accent);">
                                    {{ $rank }}
                                    <span style="font-size:.75rem;color:#888;font-weight:500;">
                                        {{ $ar['of'] }} {{ $classTotalN }}
                                    </span>
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Mini chart --}}
                    @if($cfg['minichart'] && count($miniLabels) > 0)
                        <div class="stu-chart-area">
                            <div class="stu-chart-title">{{ $ar['subject_vs_class'] }}</div>
                            <canvas id="{{ $cMini }}" height="110"></canvas>
                        </div>
                    @endif

                    {{-- Photo (leftmost in RTL) --}}
                    @if($cfg['photo'])
                        <div class="stu-photo">
                            @if($photo)
                                <img src="{{ $photo }}" alt="{{ $s->firstname }}">
                            @else
                                <div class="nophoto">
                                    <i class="fas fa-user"></i>
                                    <span>لا صورة</span>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- ══ SUMMARY BAR ══ --}}
                <div class="sum-bar">
                    <div class="sum-cell">
                        <div class="sum-lbl">{{ $ar['total_marks'] }}</div>
                        <div class="sum-val">
                            {{ number_format($totObt, 0) }}<span
                                style="font-size:.65rem;color:#aaa;font-weight:500;">/{{ $totMax }}</span>
                        </div>
                    </div>
                    <div class="sum-cell">
                        <div class="sum-lbl">{{ $ar['average'] }}</div>
                        <div class="sum-val"
                            style="color:{{ $pct >= 75 ? '#1a7a4a' : ($pct >= $exam->pass_mark ? '#856404' : '#c0392b') }}">
                            {{ $pct }}%
                        </div>
                        @if($termDelta !== null)
                            <div class="sum-sub">
                                @if($termDelta > 0) <span class="delta-inline di-up">{{ $termDelta }} ↑</span>
                                @elseif($termDelta < 0) <span class="delta-inline di-dn">{{ abs($termDelta) }} ↓</span>
                                @else <span style="font-size:.68rem;color:#aaa">—</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="sum-cell" style="flex:.7">
                        <div class="sum-lbl">{{ $ar['grade'] }}</div>
                        <div class="sum-val" style="font-size:1.3rem;">{{ $oGrade }}</div>
                    </div>
                    <div class="sum-cell">
                        <div class="sum-lbl">{{ $ar['result'] }}</div>
                        <div class="sum-val"
                            style="color:{{ $passed ? '#1a7a4a' : '#c0392b' }};font-size:.9rem;margin-top:.15rem;">
                            {{ $passed ? $ar['pass'] : $ar['fail_result'] }}
                        </div>
                    </div>
                </div>

                {{-- ══ MARKS TABLE ══ --}}
                <div class="marks-wrap">
                    <table class="marks-tbl">
                        <thead>
                            <tr>
                                <th class="tr" style="min-width:120px;">{{ $ar['subjects'] }}</th>
                                <th style="width:50px;">{{ $ar['marks'] }}</th>
                                @if($cfg['dev'])
                                <th style="width:42px;">{{ $ar['dev'] }}</th> @endif
                                @if($cfg['grade_pill'])
                                <th style="width:42px;">{{ $ar['grade'] }}</th> @endif
                                <th class="tr">{{ $ar['comment'] }}</th>
                                @if($cfg['teacher_col'])
                                <th class="tr" style="min-width:100px;">{{ $ar['teacher'] }}</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php $rn = 0; @endphp

                            @if($useGroups)
                                @foreach($grouped as $grpName => $grpSubjs)
                                    @if($grpName)
                                        <tr class="grp-row">
                                            <td colspan="{{ $visibleCols }}">{{ $grpName }}</td>
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
                                            <td class="score-td">{{ $sm->percentage }}%</td>
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
                                            @if($cfg['grade_pill'])
                                                <td class="num-td">
                                                    <span class="g-pill {{ $gc($sm->grade) }}">{{ $sm->grade ?? '—' }}</span>
                                                </td>
                                            @endif
                                            <td>{{ $sm->grade_remark ?? '—' }}</td>
                                            @if($cfg['teacher_col'])
                                                <td style="font-size:.74rem;color:#555;">{{ $sm->teacher_name ?? '—' }}</td>
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
                                        <td class="score-td">{{ $sm->percentage }}%</td>
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
                                        @if($cfg['grade_pill'])
                                            <td class="num-td">
                                                <span class="g-pill {{ $gc($sm->grade) }}">{{ $sm->grade ?? '—' }}</span>
                                            </td>
                                        @endif
                                        <td>{{ $sm->grade_remark ?? '—' }}</td>
                                        @if($cfg['teacher_col'])
                                            <td style="font-size:.74rem;color:#555;">{{ $sm->teacher_name ?? '—' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif

                            @if($cfg['totals_row'])
                                <tr class="totals-row">
                                    <td
                                        style="text-align:right;color:#666;font-size:.74rem;padding-left:.8rem;font-weight:600;">
                                        {{ $ar['total_avg_row'] }}
                                    </td>
                                    <td class="score-td">{{ $pct }}%</td>
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
                                    @if($cfg['grade_pill'])
                                        <td class="num-td">
                                            <span class="g-pill {{ $gc($oGrade) }}">{{ $oGrade }}</span>
                                        </td>
                                    @endif
                                    <td colspan="{{ 1 + ($cfg['teacher_col'] ? 1 : 0) }}">
                                        <strong style="color:{{ $passed ? '#1a7a4a' : '#c0392b' }}">
                                            {{ $passed ? $ar['pass'] : $ar['fail_result'] }} — {{ $oRemark }}
                                        </strong>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- ══ BOTTOM SECTION ══ --}}
                @if($cfg['perf_chart'] || $cfg['remarks'] || $cfg['signatures'])
                    <div class="bottom-section">

                        {{-- Signatures (rightmost in RTL) --}}
                        @if($cfg['signatures'])
                            <div class="sig-col-right">
                                <div class="sig-col-title">{{ $ar['signature'] }}</div>
                                <div style="width:100%;margin-top:.5rem;">
                                    <div class="sig-slot">{{ $ar['class_teacher'] }}</div>
                                    <div class="sig-slot" style="margin-top:1.4rem;">{{ $ar['house_teacher'] }}</div>
                                    <div class="sig-slot has-sig" style="margin-top:1.4rem;">
                                        @if(!empty($s->head_teacher_signature))
                                            <img src="{{ asset('signatures/' . $s->head_teacher_signature) }}"
                                                style="max-width:90px;max-height:22px;object-fit:contain;" alt="sig">
                                        @endif
                                        {{ $ar['head_teacher'] }}
                                    </div>
                                    <div class="sig-slot" style="margin-top:1.4rem;">{{ $ar['parent'] }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Remarks --}}
                        @if($cfg['remarks'])
                            <div class="remarks-col">
                                <div class="remarks-section-title">{{ $ar['remarks_title'] }}</div>
                                <div class="remark-block">
                                    <div class="remark-teacher">
                                        {{ $s->class_teacher ?? $ar['class_teacher'] }}
                                        — <span
                                            style="font-weight:400;color:#888;font-size:.72rem;">{{ $ar['class_teacher'] }}</span>
                                    </div>
                                    <div class="remark-text">{{ $s->class_teacher_remark ?? $ar['no_remarks'] }}</div>
                                </div>
                                <div class="sig-dashes">{{ $ar['house_teacher'] }}</div>
                                <div style="height:18px;border-bottom:1px dashed #ccc;margin-bottom:.4rem;"></div>
                                <div class="remark-block">
                                    <div class="remark-teacher">
                                        {{ $s->head_teacher ?? $ar['head_teacher'] }}
                                        — <span
                                            style="font-weight:400;color:#888;font-size:.72rem;">{{ $ar['head_teacher'] }}</span>
                                    </div>
                                    <div class="remark-text">{{ $s->head_teacher_remark ?? '' }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Performance chart (leftmost in RTL) --}}
                        @if($cfg['perf_chart'] && count($growth) > 0)
                            <div class="perf-chart-col">
                                <div class="perf-chart-title">{{ $ar['performance_chart'] }} — {{ $s->firstname }}</div>
                                <canvas id="{{ $cPerf }}" height="140"></canvas>
                            </div>
                        @endif

                    </div>
                @endif

                {{-- ══ FOOTER ══ --}}
                @if($cfg['footer_timestamp'] || $cfg['confidential'])
                    <div class="slip-footer">
                        <div style="font-size:.6rem;color:#aaa;">
                            @if($cfg['footer_timestamp'])
                                {{ $ar['generated'] }}: {{ now()->format('d M Y، H:i') }}
                                &bull; {{ $exam->exam_code ?? '' }}
                                &bull; {{ $exam->term }} {{ $exam->academic_year }}
                            @endif
                        </div>
                        @if($cfg['confidential'])
                            <div style="font-size:.65rem;font-weight:800;color:#c0392b;letter-spacing:.05em;">
                                {{ $ar['confidential'] }}
                            </div>
                        @endif
                    </div>
                @endif

            </div>{{-- /.slip --}}

            {{-- ══ PER-SLIP SCRIPTS ══ --}}
            <script>
                (function () {
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
                                            borderColor: '#1a6b3c', backgroundColor: 'rgba(26,107,60,.08)',
                                            tension: .35, fill: true, pointRadius: 3, borderWidth: 2,
                                            pointBackgroundColor: '#1a6b3c',
                                        },
                                        {
                                            label: '{{ addslashes(Helper::recordMdname($s->senior)) }}',
                                            data: {!! json_encode($miniClass) !!},
                                            borderColor: '#aaa', backgroundColor: 'transparent',
                                            tension: .35, fill: false, pointRadius: 2, borderWidth: 1.5,
                                            borderDash: [4, 3], pointBackgroundColor: '#aaa',
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

            @if($cfg['qr'] && $qrText)
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var qrCanvas = document.getElementById('{{ $qrId }}');
                        if (!qrCanvas || typeof QRCode === 'undefined') return;

                        var qrData = `{{ addslashes(implode('\n', array_filter([
                    'الاسم: ' . trim($s->lastname . ' ' . $s->firstname . ' ' . ($s->other_names ?? '')),
                    'رقم القيد: ' . ($s->adm_no ?? ($s->index_no ?? '')),
                    'الصف: ' . Helper::recordMdname($s->senior) . (($s->stream ?? false) ? ' - ' . $s->stream : ''),
                    'الامتحان: ' . $exam->exam_name,
                    'الفصل: ' . $exam->term,
                    'السنة: ' . $exam->academic_year,
                    'المعدل: ' . $pct . '%',
                    'التقدير: ' . $oGrade,
                    'النتيجة: ' . ($passed ? 'ناجح' : 'راسب'),
                    'المدرسة: ' . $schoolName,
                ]))) }}`;

                        QRCode.toCanvas(qrCanvas, qrData, {
                            width: 160, margin: 2,
                            errorCorrectionLevel: 'H',
                            color: { dark: '#000000', light: '#FFFFFF' }
                        }, function (error) { if (error) console.error('QR Error:', error); });
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