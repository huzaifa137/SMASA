<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card (Secondary) — {{ $exam->exam_name }}</title>
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>

    <?php use App\Http\Controllers\Helper; ?>

    @php
        /*
        |─────────────────────────────────────────────────────────────
        | SECONDARY (O-Level / A-Level) REPORT CARD — 'secondary-classic'
        |
        | Same customisation mechanism as the Primary / Nursery slips:
        | query-string always wins (so the "Customize this design" live
        | preview reacts instantly), then the class's saved profile
        | (Helper::getPassslipSettings), then the hard default.
        | Which toggles exist for this design is listed under
        | 'secondary-classic' in config/passslip_templates.php.
        |─────────────────────────────────────────────────────────────
        */
        $accent = request('accent', '#ff9800');
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $accent)) {
            $accent = '#ff9800';
        }

        $hexToDark = function (string $hex): string {
            $hex = ltrim($hex, '#');
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            return sprintf('#%02x%02x%02x', (int) ($r * 0.82), (int) ($g * 0.82), (int) ($b * 0.82));
        };
        $accentDark = $hexToDark($accent);

        // Colour of the blue "cap" (left strip top + horizontal top band) —
        // its own setting, independent of the accent colour.
        $capColor = request('cap_color', '#1e88e5');
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $capColor)) {
            $capColor = '#1e88e5';
        }

        // Thickness (px) of the band: ONE value drives both the left strip's
        // width / cap and the top band's height, so they always match.
        $capSize = request('cap_size');
        $capSize = is_numeric($capSize) ? (int) min(60, max(12, (float) $capSize)) : 28;

        ['pageW' => $pageW, 'pageH' => $pageH, 'pageScale' => $pageScale] = Helper::passslipPageSizing();

        $mode = $mode ?? 'single';

        $on = fn(string $key, bool $default = true, array $saved = []): bool =>
            request()->has($key)
            ? in_array(request($key), ['1', 'true', 1, true], true)
            : ($saved[$key] ?? $default);

        // $schoolId: the Parent Portal never sets the session, so fall back
        // to the exam / student which always belong to exactly one school.
        $schoolId = Session('LoggedSchool') ?: ($exam->school_id ?? ($student->school_id ?? null));
        $schoolName = Helper::schoolNameBySchoolID($schoolId) ?? config('app.name', 'School');
    @endphp

    <style>
        :root {
            --accent: {{ $accent }};
            --accent-dark: {{ $accentDark }};
            --blue: {{ $capColor }};
            --strip: #e1f3fa;
            --page-scale: {{ $pageScale }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            zoom: var(--page-scale);
            font-family: 'Poppins', sans-serif;
            background: #dde1e7;
            color: #111;
            font-size: 12px;
            line-height: 1.35;
        }

        /* ── Toolbar (screen only) ── */
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
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: .78rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            text-decoration: none;
        }

        .tbtn-print {
            background: var(--accent);
            color: #fff;
        }

        .tbtn-back {
            background: rgba(255, 255, 255, .12);
            color: #fff;
        }

        .page-wrap {
            max-width: calc(820px / var(--page-scale, 1));
            margin: 1.5rem auto;
        }

        /* ── Slip ── */
        .slip {
            background: #fff;
            margin-bottom: 2.5rem;
            page-break-after: always;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .12);
            min-height: 1040px;
            display: flex;
            flex-direction: column;
        }

        .slip:last-child {
            page-break-after: avoid;
            margin-bottom: 0;
        }

        .slip.has-border {
            border: 2px solid var(--accent);
        }

        /* Left colour strip: accent with a blue cap */
        .side-strip {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--cap-size, 28px);
            background: var(--accent);
            z-index: 1;
        }

        .side-strip.has-cap::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: calc(var(--cap-size, 28px) + 34px);
            background: var(--blue);
        }

        /* Horizontal band along the top edge, continuing the cap to the right */
        .cap-bar {
            position: absolute;
            left: var(--cap-size, 28px);
            right: 0;
            top: 0;
            height: var(--cap-size, 28px);
            background: var(--blue);
            z-index: 1;
        }

        .slip.has-top-band .slip-body {
            /* clear the top band (the letterhead already has ~14px of its own padding) */
            margin-top: max(0px, calc(var(--cap-size, 28px) - 14px));
        }

        .slip-body {
            position: relative;
            z-index: 2;
            margin-left: var(--cap-size, 28px);
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .watermark {
            position: absolute;
            left: 50%;
            top: 56%;
            width: 56%;
            transform: translate(-50%, -50%);
            opacity: .09;
            z-index: 0;
            pointer-events: none;
            text-align: center;
        }

        .watermark img {
            width: 100%;
            filter: grayscale(1);
        }

        .watermark-text {
            font-size: 2.6rem;
            font-weight: 800;
            color: #000;
            transform: rotate(-24deg);
            line-height: 1.1;
        }

        /* ── Letterhead ── */
        .sch-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.3rem .8rem 1.4rem;
        }

        .sch-logo-box {
            width: 74px;
            height: 74px;
            border: 1px solid #d5d5d5;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            flex-shrink: 0;
        }

        .sch-logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .sch-text {
            flex: 1;
            min-width: 0;
            text-align: right;
        }

        .sch-name {
            font-size: 1.45rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #111;
            letter-spacing: .2px;
            line-height: 1.2;
        }

        .sch-sub {
            font-size: .72rem;
            font-weight: 600;
            color: #222;
            margin-top: 2px;
        }

        .sch-sub.muted {
            font-weight: 400;
            color: #555;
        }

        .sch-item {
            display: inline-block;
            margin-left: 14px;
            white-space: nowrap;
        }

        .sch-item i {
            font-size: .68rem;
            margin-right: 2px;
        }

        /* ── Title banner ── */
        .title-band {
            background: var(--accent);
            color: #fff;
            text-align: center;
            font-weight: 600;
            font-size: .95rem;
            letter-spacing: .4px;
            padding: .42rem 1rem;
            text-transform: uppercase;
        }

        /* ── Student row ── */
        .stu-row {
            display: flex;
            align-items: stretch;
            gap: 0;
            padding: .6rem 1rem .5rem .6rem;
        }

        .stu-photo {
            width: 120px;
            height: 150px;
            background: #cfd8e3;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .stu-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .stu-photo i {
            font-size: 3.4rem;
            color: #9aa8bb;
        }

        .stu-details {
            flex: 0 0 255px;
            padding: .15rem .8rem;
            margin: 0 .6rem 0 .6rem;
            border-right: 1px solid #bdbdbd;
            font-size: var(--val-size, .8rem);
            font-weight: 600;
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .stu-details .k {
            font-weight: 700;
            font-size: var(--lbl-size, .8rem);
        }

        .stu-chart {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .chart-title {
            font-size: .72rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 2px;
        }

        .chart-box {
            position: relative;
            flex: 1;
            min-height: 120px;
        }

        .status-pill {
            font-weight: 700;
        }

        .status-promoted {
            color: #1b7f3b;
        }

        .status-repeat {
            color: #c2410c;
        }

        .status-fail {
            color: #b91c1c;
        }

        /* ── Summary strip ── */
        .sum-bar {
            margin: .4rem 1rem .7rem .6rem;
            background: var(--strip);
            display: flex;
            align-items: stretch;
            padding: .55rem 0;
        }

        .sum-cell {
            flex: 1 1 0;
            text-align: center;
            padding: 0 1rem;
            border-left: 2px solid #fff;
            position: relative;
        }

        .sum-cell:first-child {
            border-left: none;
        }

        .sum-lbl {
            font-size: .72rem;
            font-weight: 700;
            color: #111;
        }

        .sum-val {
            font-size: .8rem;
            font-weight: 600;
            color: #111;
        }

        .sum-main {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.2rem;
        }

        .sum-delta {
            font-size: .82rem;
            font-weight: 700;
        }

        /* ── Marks table ── */
        .tbl-wrap {
            margin: 0 1rem .7rem .6rem;
        }

        .marks-tbl {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            font-size: .68rem;
        }

        .marks-tbl th,
        .marks-tbl td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: .26rem .45rem;
            text-align: left;
            vertical-align: middle;
        }

        .marks-tbl thead th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            font-weight: 700;
            font-size: .68rem;
            text-transform: uppercase;
            background: #fff;
        }

        .marks-tbl tbody td {
            border-top: none;
            border-bottom: none;
            font-weight: 500;
        }

        .marks-tbl tbody tr:last-child td {
            border-bottom: 1.5px solid #000;
        }

        .marks-tbl .c {
            text-align: center;
        }

        .marks-tbl th.col-teacher,
        .marks-tbl td.col-teacher {
            white-space: nowrap;
        }

        .dev-up {
            color: #2e9e4f;
            font-weight: 600;
        }

        .dev-down {
            color: #f08a24;
            font-weight: 600;
        }

        .dev-eq {
            color: #888;
        }

        /* ── Bottom: chart + remarks ── */
        .bottom {
            display: flex;
            align-items: stretch;
            margin: 0 1rem 0 .6rem;
            flex: 1;
            border-top: 1px solid #cfcfcf;
        }

        .bottom-left {
            flex: 0 0 41%;
            padding: .6rem .8rem .5rem 0;
            border-right: 1px solid #bdbdbd;
            display: flex;
            flex-direction: column;
        }

        .bottom-left .chart-box {
            min-height: 170px;
        }

        .bottom-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: .6rem 0 .5rem .8rem;
            min-width: 0;
        }

        .rem-head {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: .76rem;
            margin-bottom: .35rem;
        }

        .rem-grid {
            display: flex;
            gap: 1rem;
        }

        .rem-col {
            flex: 1;
            min-width: 0;
        }

        .rem-sig-line {
            display: flex;
            align-items: flex-end;
            gap: .5rem;
            margin-top: .35rem;
        }

        .rem-sig-line .sig-lbl {
            font-size: .68rem;
            font-weight: 700;
            color: #444;
            flex-shrink: 0;
        }

        .rem-sig-line .sig-slot {
            flex: 1;
            margin-bottom: 0;
            height: 30px;
            justify-content: flex-start;
        }

        .rem-block {
            margin-bottom: .6rem;
        }

        .rem-who {
            font-weight: 700;
            font-size: .72rem;
        }

        .rem-text {
            font-size: .72rem;
            text-align: justify;
            line-height: 1.45;
            color: #222;
        }

        .rem-dash {
            border-top: 1px dashed #666;
            height: 0;
            margin: .85rem 0 0;
        }

        .sig-slot {
            border-bottom: 1px solid #555;
            height: 34px;
            margin-bottom: .6rem;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .sig-slot img {
            max-width: 100%;
            max-height: 32px;
            object-fit: contain;
        }

        .qr-row {
            display: flex;
            align-items: center;
            gap: .8rem;
            margin-top: auto;
            padding-top: .4rem;
            border-top: 1px solid #bdbdbd;
        }

        .qr-box {
            width: 78px;
            height: 78px;
            flex-shrink: 0;
        }

        .qr-box canvas,
        .qr-box img {
            width: 78px !important;
            height: 78px !important;
        }

        .qr-text {
            font-size: .72rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .slip-footer {
            display: flex;
            justify-content: space-between;
            font-size: .58rem;
            color: #777;
            padding: .3rem 1rem .4rem .6rem;
        }

        .slip,
        .side-strip,
        .side-strip::before,
        .cap-bar,
        .title-band,
        .sum-bar,
        .watermark img {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        @media print {
            @page {
                margin: .5cm .65cm;
                size: {{ $pageW }} {{ $pageH }};
            }

            body {
                background: #fff;
                font-size: 11px;
            }

            .toolbar {
                display: none !important;
            }

            .page-wrap {
                width: calc(({{ $pageW }} - 1.3cm) / var(--page-scale, 1));
                max-width: none;
                margin: 0;
            }

            .slip {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
                page-break-inside: avoid;
                width: 100%;
                min-height: calc(({{ $pageH }} - 1.1cm) / var(--page-scale, 1));
                max-height: calc(({{ $pageH }} - 1.1cm) / var(--page-scale, 1));
            }

            .slip:last-child {
                page-break-after: avoid;
            }
        }
    </style>
    @include('Examination.passslips.partials.text-sizes')
</head>

<body class="tpl-secondary-classic">

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
        /* ── Normalise to a single render array ─────────────────────── */
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
                    'useAvg' => $useAvg ?? false,
                    'examSummary' => $examSummary ?? [],
                    'avgSummary' => $avgSummary ?? null,
                ]
            ];
        } else {
            $renderSlips = $slips;
        }

        $slipCounter = 0;

        // School identity (same for every slip — one school per exam)
        $schoolPhone = Helper::schoolPhoneBySchoolID($schoolId) ?? '';
        $schoolEmail = DB::table('school_profiles')->where('school_id', $schoolId)->value('email');
        // Same source the Primary templates use: P.O Box / location is stored in `school_type`.
        $schoolLocation = DB::table('school_profiles')->where('school_id', $schoolId)->value('school_type');
        $schoolWebsite = Helper::schoolWebsiteBySchoolID($schoolId);
        $schoolLogo = DB::table('school_profiles')->where('school_id', $schoolId)->value('logo');

        // Same resolution order the Primary / Nursery slips use: the stored
        // value may be a full filename OR a bare name with no extension, and
        // the file may live under uploads/logos/ (current) or storage/ (old).
        $schoolLogoUrl = null;
        if ($schoolLogo) {
            $logoBase = pathinfo($schoolLogo, PATHINFO_FILENAME);
            $logoCandidates = [
                'uploads/logos/' . $schoolLogo,
                'storage/' . $schoolLogo,
                'storage/logos/' . $schoolLogo,
                'uploads/logos/' . $logoBase . '.' . pathinfo($schoolLogo, PATHINFO_EXTENSION),
            ];
            foreach (['jpg', 'jpeg', 'png', 'gif', 'webp', 'JPG', 'JPEG', 'PNG'] as $ext) {
                $logoCandidates[] = 'uploads/logos/' . $schoolLogo . '.' . $ext;
                $logoCandidates[] = 'uploads/logos/' . $logoBase . '.' . $ext;
                $logoCandidates[] = 'storage/' . $schoolLogo . '.' . $ext;
                $logoCandidates[] = 'storage/logos/' . $logoBase . '.' . $ext;
            }
            foreach ($logoCandidates as $rel) {
                $abs = public_path(str_replace('/', DIRECTORY_SEPARATOR, $rel));
                if (is_file($abs)) {
                    $schoolLogoUrl = asset($rel);
                    break;
                }
            }
        }
    @endphp

    <div class="page-wrap">
        @foreach($renderSlips as $slipData)

            @php
                $slipCounter++;
                $s = (object) $slipData['student'];
                $subjMarks = collect($slipData['subjectMarks'])->values();
                $totObt = $slipData['totalObtained'];
                $totMax = $slipData['totalMax'];
                $pct = $slipData['percentage'];
                $growth = collect($slipData['growthData'] ?? [])->values();
                $prevSubj = collect($slipData['previousSubjectMarks'] ?? []);
                $examSummarySlip = collect($slipData['examSummary'] ?? []);
                $avgSummarySlip = $slipData['avgSummary'] ?? null;

                /* ── Customisation (per class saved profile, query wins) ── */
                $savedCfg = Helper::getPassslipSettings($schoolId, $s->senior ?? null);
                $cfg = [
                    'border' => $on('show_border', true, $savedCfg),
                    'top_band' => $on('show_top_band', true, $savedCfg),
                    'watermark' => $on('show_watermark', true, $savedCfg),
                    'logo' => $on('show_logo', true, $savedCfg),
                    'contact' => $on('show_contact', true, $savedCfg),
                    'photo' => $on('show_photo', true, $savedCfg),
                    'minichart' => $on('show_minichart', true, $savedCfg),
                    'qr' => $on('show_qr', true, $savedCfg),
                    'dev' => $on('show_dev', true, $savedCfg),
                    'col_grade' => $on('show_col_grade', true, $savedCfg),
                    'comment_col' => $on('show_comment_col', true, $savedCfg),
                    'teacher_col' => $on('show_teacher_col', true, $savedCfg),
                    'perf_chart' => $on('show_perf_chart', true, $savedCfg),
                    'remarks' => $on('show_remarks', true, $savedCfg),
                    'signatures' => $on('show_signatures', true, $savedCfg),
                    'footer_timestamp' => $on('show_footer_timestamp', true, $savedCfg),
                    'confidential' => $on('show_confidential', true, $savedCfg),

                    'section_student_info' => $on('show_section_student_info', true, $savedCfg),
                    'section_summary' => $on('show_section_summary', true, $savedCfg),
                    'section_marks_table' => $on('show_section_marks_table', true, $savedCfg),

                    'stu_name' => $on('show_stu_name', true, $savedCfg),
                    'stu_admission' => $on('show_stu_admission', true, $savedCfg),
                    'stu_class' => $on('show_stu_class', true, $savedCfg),
                    'stu_stream' => $on('show_stu_stream', true, $savedCfg),
                    'stu_exam' => $on('show_stu_exam', false, $savedCfg),
                    'stu_status' => $on('show_stu_status', true, $savedCfg),

                    // Same Student Information fields (and keys) as the Primary Classic slip
                    'stu_paycode' => $on('show_stu_paycode', true, $savedCfg),
                    'stu_dob' => $on('show_stu_dob', true, $savedCfg),
                    'stu_report_date' => $on('show_stu_report_date', true, $savedCfg),
                    'stu_academic_year' => $on('show_stu_academic_year', false, $savedCfg),
                    'stu_term' => $on('show_stu_term', false, $savedCfg),
                    'stu_gender' => $on('show_stu_gender', false, $savedCfg),
                    'stu_class_teacher' => $on('show_stu_class_teacher', false, $savedCfg),
                    'stu_house' => $on('show_stu_house', false, $savedCfg),

                    'sum_total_marks' => $on('show_sum_total_marks', true, $savedCfg),
                    'sum_average_pct' => $on('show_sum_average_pct', true, $savedCfg),
                    'sum_division' => $on('show_sum_division', true, $savedCfg),
                ];

                $passed = $pct >= $exam->pass_mark;
                $statusLabel = $s->status ?? ($passed ? 'Promoted' : 'Repeat');
                $statusLower = strtolower($statusLabel);
                $statusClass = str_contains($statusLower, 'promot')
                    ? 'status-promoted'
                    : (str_contains($statusLower, 'fail') ? 'status-fail' : 'status-repeat');

                $admissionNo = $s->admission_number ?? ($s->adm_no ?? ($s->index_no ?? '—'));
                $className = Helper::recordMdname($s->senior);
                $dobFormatted = !empty($s->date_of_birth) ? date('d M Y', strtotime($s->date_of_birth)) : '—';
                $infoClassTeacher = $subjMarks->first()?->class_teacher ?? ($s->class_teacher ?? '—');
                $fullName = trim(($s->firstname ?? '') . ' ' . ($s->lastname ?? '') . ' ' . ($s->other_names ?? ''));

                /* Student photo */
                $photo = null;
                if (!empty($s->student_photo)) {
                    foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                        if (file_exists(public_path('uploads/studentPhotos/' . $s->student_photo . '.' . $ext))) {
                            $photo = asset('uploads/studentPhotos/' . $s->student_photo . '.' . $ext);
                            break;
                        }
                    }
                }

                /* DEV. per subject: this exam % minus the previous exam's % */
                $devFor = function ($sm) use ($prevSubj) {
                    $prevM = $prevSubj[Helper::subjectKey($sm)] ?? ($prevSubj[$sm->subject_id] ?? null);
                    if ($prevM && ($prevM->total_marks ?? 0) > 0) {
                        $pPct = round(($prevM->marks_obtained / $prevM->total_marks) * 100, 1);
                        return round($sm->percentage - $pPct, 1);
                    }
                    return null;
                };

                /* Total-marks delta vs the previous exam (matching subjects only) */
                $prevTotal = 0;
                $prevMatched = 0;
                foreach ($subjMarks as $sm) {
                    $prevM = $prevSubj[Helper::subjectKey($sm)] ?? ($prevSubj[$sm->subject_id] ?? null);
                    if ($prevM && ($prevM->total_marks ?? 0) > 0) {
                        $prevTotal += $prevM->marks_obtained;
                        $prevMatched++;
                    }
                }
                $totalDelta = $prevMatched > 0 ? round($totObt - $prevTotal, 1) : null;

                /* Average delta vs previous sitting */
                $prevPct = $growth->count() >= 2 ? ($growth[$growth->count() - 2]['percentage'] ?? null) : null;
                $avgDelta = $prevPct !== null ? round($pct - $prevPct, 1) : null;

                $divisionLabel = $avgSummarySlip['division']
                    ?? $examSummarySlip->last()['division']
                    ?? $slipData['division']
                    ?? null;

                /* Chart data */
                $miniLabels = $subjMarks->map(fn($sm) => strtoupper(substr($sm->subject_name, 0, 4)))->values()->toArray();
                $miniStudent = $subjMarks->pluck('percentage')->values()->toArray();
                $hasClassAvg = $subjMarks->filter(fn($sm) => ($sm->class_average ?? null) !== null)->isNotEmpty();
                $miniClass = $subjMarks->map(fn($sm) => $sm->class_average !== null ? (float) $sm->class_average : null)->values()->toArray();
                $growthLabels = $growth->pluck('label')->toArray();
                $growthValues = $growth->pluck('percentage')->toArray();

                $cMini = 'mini_' . $slipCounter;
                $cPerf = 'perf_' . $slipCounter;
                $qrId = 'qr_' . $slipCounter;

                $classTeacherName = $s->class_teacher ?? 'Class Teacher';
                $headTeacherName = $s->head_teacher ?? 'Head Teacher';
                $ctSigUrl = Helper::signatureUrl($s->class_teacher_signature ?? null);
                $htSigUrl = Helper::signatureUrl($s->head_teacher_signature ?? null);

                $titleBand = strtoupper('Academic Report Form - ' . $className . ' - ' . $exam->exam_name . ' - (' . $exam->academic_year . ' ' . $exam->term . ')');

                $qrData = implode("\n", array_filter([
                    'Student: ' . $fullName,
                    'Adm No: ' . $admissionNo,
                    'Class: ' . $className . (!empty($s->stream) ? ' - ' . $s->stream : ''),
                    'Exam: ' . $exam->exam_name,
                    'Term: ' . $exam->term,
                    'Year: ' . $exam->academic_year,
                    'Average: ' . $pct . '%',
                    'Result: ' . ($passed ? 'PASS' : 'FAIL'),
                    'School: ' . $schoolName,
                ]));
            @endphp

            <div class="slip {{ $cfg['border'] ? 'has-border' : '' }} {{ $cfg['top_band'] ? 'has-top-band' : '' }}"
                style="--cap-size: {{ $cfg['top_band'] ? $capSize : 28 }}px;">
                <div class="side-strip {{ $cfg['top_band'] ? 'has-cap' : '' }}"></div>
                @if($cfg['top_band'])
                    <div class="cap-bar"></div>
                @endif

                @if($cfg['watermark'])
                    <div class="watermark">
                        @if($schoolLogoUrl)
                            <img src="{{ $schoolLogoUrl }}" alt="">
                        @else
                            <div class="watermark-text">{{ $schoolName }}</div>
                        @endif
                    </div>
                @endif

                <div class="slip-body">

                    {{-- ══ LETTERHEAD ══ --}}
                    <div class="sch-header">
                        @if($cfg['logo'])
                            <div class="sch-logo-box">
                                @if($schoolLogoUrl)
                                    <img src="{{ $schoolLogoUrl }}" alt="Logo">
                                @else
                                    <i class="fas fa-school" style="font-size:1.8rem;color:#999;"></i>
                                @endif
                            </div>
                        @endif
                        <div class="sch-text">
                            <div class="sch-name">{{ $schoolName }}</div>
                            @if($cfg['contact'] && ($schoolLocation || $schoolPhone || $schoolEmail || $schoolWebsite))
                                <div class="sch-sub">
                                    @if($schoolLocation)
                                        <span class="sch-item"><i class="fas fa-location-dot"></i> {{ $schoolLocation }}</span>
                                    @endif
                                    @if($schoolPhone)
                                        <span class="sch-item"><i class="fas fa-phone"></i> {{ $schoolPhone }}</span>
                                    @endif
                                    @if($schoolEmail)
                                        <span class="sch-item"><i class="fas fa-envelope"></i> {{ $schoolEmail }}</span>
                                    @endif
                                    @if($schoolWebsite)
                                        <span class="sch-item"><i class="fas fa-globe"></i> {{ $schoolWebsite }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="title-band">{{ $titleBand }}</div>

                    {{-- ══ STUDENT INFO + SUBJECT CHART ══ --}}
                    @if($cfg['section_student_info'] || $cfg['minichart'])
                        <div class="stu-row">
                            @if($cfg['section_student_info'])
                                @if($cfg['photo'])
                                    <div class="stu-photo">
                                        @if($photo)
                                            <img src="{{ $photo }}" alt="{{ $fullName }}">
                                        @else
                                            <i class="fas fa-user"></i>
                                        @endif
                                    </div>
                                @endif
                                <div class="stu-details">
                                    @if($cfg['stu_name'])
                                        <div><span class="k">NAME:</span> {{ $fullName }}</div>
                                    @endif
                                    @if($cfg['stu_admission'])
                                        <div><span class="k">LIN No.:</span> {{ $admissionNo }}</div>
                                    @endif
                                    @if($cfg['stu_paycode'])
                                        <div><span class="k">PAY CODE:</span> {{ $s->paycode ?? '—' }}</div>
                                    @endif
                                    @if($cfg['stu_class'] || $cfg['stu_stream'])
                                        <div>
                                            @if($cfg['stu_class'])<span class="k">{{ strtoupper($className) }}</span>@endif
                                            @if($cfg['stu_class'] && $cfg['stu_stream'])<span class="k">:</span>@endif
                                            @if($cfg['stu_stream']) {{ $s->stream ?? '—' }} @endif
                                        </div>
                                    @endif
                                    @if($cfg['stu_academic_year'])
                                        <div><span class="k">ACADEMIC YEAR:</span> {{ $exam->academic_year }}</div>
                                    @endif
                                    @if($cfg['stu_term'])
                                        <div><span class="k">TERM:</span> {{ $exam->term }}</div>
                                    @endif
                                    @if($cfg['stu_exam'])
                                        <div><span class="k">EXAM:</span> {{ $exam->exam_name }}</div>
                                    @endif
                                    @if($cfg['stu_dob'])
                                        <div><span class="k">DATE OF BIRTH:</span> {{ $dobFormatted }}</div>
                                    @endif
                                    @if($cfg['stu_gender'])
                                        <div><span class="k">GENDER:</span> {{ $s->gender ?? '—' }}</div>
                                    @endif
                                    @if($cfg['stu_class_teacher'])
                                        <div><span class="k">CLASS TEACHER:</span> {{ $infoClassTeacher }}</div>
                                    @endif
                                    @if($cfg['stu_house'])
                                        <div><span class="k">HOUSE / TEAM:</span> {{ $s->house ?? '—' }}</div>
                                    @endif
                                    @if($cfg['stu_report_date'])
                                        <div><span class="k">DATE OF REPORT:</span> {{ now()->format('d M Y') }}</div>
                                    @endif
                                    @if($cfg['stu_status'])
                                        <div><span class="k">STATUS:</span>
                                            <span class="status-pill {{ $statusClass }}">{{ ucfirst($statusLabel) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($cfg['minichart'])
                                <div class="stu-chart">
                                    <div class="chart-title">Subject Performance - Student vs Class</div>
                                    <div class="chart-box"><canvas id="{{ $cMini }}"></canvas></div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ══ SUMMARY STRIP ══ --}}
                    @if($cfg['section_summary'])
                        <div class="sum-bar">
                            @if($cfg['sum_total_marks'])
                                <div class="sum-cell">
                                    <div class="sum-lbl">Total Marks</div>
                                    <div class="sum-main">
                                        <div class="sum-val">@whole($totObt)/@whole($totMax)</div>
                                        @if($totalDelta !== null)
                                            <div class="sum-delta {{ $totalDelta >= 0 ? 'dev-up' : 'dev-down' }}">
                                                {{ $totalDelta > 0 ? '+' : '' }}{{ $totalDelta }}
                                                {{ $totalDelta >= 0 ? '↑' : '↓' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($cfg['sum_average_pct'])
                                <div class="sum-cell">
                                    <div class="sum-lbl">Average Score</div>
                                    <div class="sum-main">
                                        <div class="sum-val">{{ round($pct) }}%</div>
                                        @if($avgDelta !== null)
                                            <div class="sum-delta {{ $avgDelta >= 0 ? 'dev-up' : 'dev-down' }}">
                                                {{ $avgDelta > 0 ? '+' : '' }}{{ $avgDelta }}
                                                {{ $avgDelta >= 0 ? '↑' : '↓' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($cfg['sum_division'])
                                <div class="sum-cell">
                                    <div class="sum-lbl">Result</div>
                                    <div class="sum-val">{{ $divisionLabel ? strtoupper($divisionLabel) : ($passed ? 'PASS' : 'FAIL') }}</div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ══ MARKS TABLE ══ --}}
                    @if($cfg['section_marks_table'])
                        <div class="tbl-wrap">
                            <table class="marks-tbl">
                                <thead>
                                    <tr>
                                        <th>Subjects</th>
                                        <th class="c">Marks</th>
                                        @if($cfg['dev'])<th class="c">Dev.</th>@endif
                                        @if($cfg['col_grade'])<th class="c">Grade</th>@endif
                                        @if($cfg['comment_col'])<th>Comment</th>@endif
                                        @if($cfg['teacher_col'])<th class="col-teacher">Teacher</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjMarks as $sm)
                                        @php $delta = $devFor($sm); @endphp
                                        <tr>
                                            <td>{{ $sm->subject_name }}</td>
                                            <td class="c">@whole($sm->percentage)%</td>
                                            @if($cfg['dev'])
                                                <td class="c">
                                                    @if($delta === null || $delta == 0)
                                                        <span class="dev-eq">—</span>
                                                    @elseif($delta > 0)
                                                        <span class="dev-up">+{{ $delta }} ↑</span>
                                                    @else
                                                        <span class="dev-down">{{ $delta }} ↓</span>
                                                    @endif
                                                </td>
                                            @endif
                                            @if($cfg['col_grade'])
                                                <td class="c">{{ $sm->grade ?? '—' }}</td>
                                            @endif
                                            @if($cfg['comment_col'])
                                                <td>{{ $sm->grade_remark ?? '—' }}</td>
                                            @endif
                                            @if($cfg['teacher_col'])
                                                <td class="col-teacher">{{ $sm->teacher_name ?? '—' }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- ══ BOTTOM: PERFORMANCE OVER TIME + REMARKS ══ --}}
                    @if(($cfg['perf_chart'] && $growth->count() > 0) || $cfg['remarks'] || $cfg['qr'])
                        <div class="bottom">
                            @if($cfg['perf_chart'] && $growth->count() > 0)
                                <div class="bottom-left">
                                    <div class="chart-title">{{ $s->firstname }} {{ $s->lastname }}'s Performance over Time</div>
                                    <div class="chart-box"><canvas id="{{ $cPerf }}"></canvas></div>
                                </div>
                            @endif

                            <div class="bottom-right">
                                @if($cfg['remarks'])
                                    <div class="rem-head">
                                        <span>Remarks</span>
                                    </div>
                                    <div class="rem-grid">
                                        <div class="rem-col">
                                            <div class="rem-block">
                                                <div class="rem-who">{{ $classTeacherName }} - Class Teacher</div>
                                                <div class="rem-text">{{ ($s->class_teacher_remark ?? '') ?: 'No remarks recorded.' }}</div>
                                                @if($cfg['signatures'])
                                                    <div class="rem-sig-line">
                                                        <span class="sig-lbl">Signature:</span>
                                                        <div class="sig-slot">
                                                            @if($ctSigUrl)<img src="{{ $ctSigUrl }}" alt="signature">@endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="rem-block">
                                                <div class="rem-who">{{ $headTeacherName }} - Head Teacher</div>
                                                <div class="rem-text">{{ ($s->head_teacher_remark ?? '') ?: 'No remarks recorded.' }}</div>
                                                @if($cfg['signatures'])
                                                    <div class="rem-sig-line">
                                                        <span class="sig-lbl">Signature:</span>
                                                        <div class="sig-slot">
                                                            @if($htSigUrl)<img src="{{ $htSigUrl }}" alt="signature">@endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($cfg['qr'])
                                    <div class="qr-row">
                                        <div class="qr-box"><canvas id="{{ $qrId }}"></canvas></div>
                                        <div class="qr-text">
                                            Scan to verify this report card.<br>
                                            Admission No: {{ $admissionNo }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($cfg['footer_timestamp'] || $cfg['confidential'])
                        <div class="slip-footer">
                            <span>@if($cfg['footer_timestamp']) Generated {{ now()->format('d M Y, H:i') }} @endif</span>
                            <span>@if($cfg['confidential']) Confidential — {{ $schoolName }} @endif</span>
                        </div>
                    @endif
                </div>
            </div>

            <script>
                (function () {
                    if (typeof Chart === 'undefined') return;

                    @if($cfg['minichart'])
                        var mini = document.getElementById('{{ $cMini }}');
                        if (mini) {
                            var datasets = [{
                                label: {!! json_encode(trim($s->firstname ?? 'Student')) !!},
                                data: {!! json_encode($miniStudent) !!},
                                borderColor: '#43a047', backgroundColor: '#43a047',
                                pointRadius: 3, borderWidth: 2, tension: .2, fill: false, order: 1
                            }];
                            @if($hasClassAvg)
                                datasets.push({
                                    label: {!! json_encode($className) !!},
                                    data: {!! json_encode($miniClass) !!},
                                    borderColor: '#bdbdbd', backgroundColor: 'rgba(189,189,189,.55)',
                                    pointRadius: 0, borderWidth: 1, tension: .2, fill: true, order: 2
                                });
                            @endif
                            new Chart(mini.getContext('2d'), {
                                type: 'line',
                                data: { labels: {!! json_encode($miniLabels) !!}, datasets: datasets },
                                options: {
                                    responsive: true, maintainAspectRatio: false, animation: false,
                                    plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, font: { size: 9 } } } },
                                    scales: {
                                        y: { min: 0, max: 150, ticks: { font: { size: 8 }, stepSize: 50 }, grid: { color: '#eee' } },
                                        x: { ticks: { font: { size: 8 }, maxRotation: 0, autoSkip: true }, grid: { display: false } }
                                    }
                                }
                            });
                        }
                    @endif

                    @if($cfg['perf_chart'] && $growth->count() > 0)
                        var perf = document.getElementById('{{ $cPerf }}');
                        if (perf) {
                            new Chart(perf.getContext('2d'), {
                                type: 'bar',
                                data: {
                                    labels: {!! json_encode($growthLabels) !!},
                                    datasets: [{ data: {!! json_encode($growthValues) !!}, backgroundColor: '#29a9f5', borderRadius: 2, maxBarThickness: 38 }]
                                },
                                options: {
                                    responsive: true, maintainAspectRatio: false, animation: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: { min: 0, max: 100, ticks: { font: { size: 9 }, stepSize: 50 }, grid: { color: '#eee' } },
                                        x: { ticks: { font: { size: 8 } }, grid: { display: false } }
                                    }
                                }
                            });
                        }
                    @endif

                    @if($cfg['qr'])
                        var qr = document.getElementById('{{ $qrId }}');
                        if (qr && typeof QRCode !== 'undefined') {
                            QRCode.toCanvas(qr, {!! json_encode($qrData) !!}, {
                                width: 160, margin: 1, errorCorrectionLevel: 'M',
                                color: { dark: '#000000', light: '#FFFFFF' }
                            }, function (e) { if (e) console.error('QR Error:', e); });
                        }
                    @endif
                })();
            </script>

        @endforeach
    </div>{{-- /.page-wrap --}}

    @include('Examination.passslips.partials.fit-school-name')
    <script>
        @if($mode === 'class' || $mode === 'all')
            window.addEventListener('load', function () {
                setTimeout(function () {
                    fitSchoolNames();
                    window.print();
                }, 900);
            });
        @endif
    </script>
</body>

</html>
