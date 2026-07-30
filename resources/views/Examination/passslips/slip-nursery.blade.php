{{-- resources/views/Examination/passslips/slip-nursery.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nursery Report Card — {{ $exam->exam_name }}</title>
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Roboto+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @php
        $accent = request('accent', '#f0a500');
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $accent)) {
            $accent = '#f0a500';
        }

        $hexToDark = function (string $hex): string {
            $hex = ltrim($hex, '#');
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            $r = max(0, (int) ($r * 0.82));
            $g = max(0, (int) ($g * 0.82));
            $b = max(0, (int) ($b * 0.82));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };
        $accentDark = $hexToDark($accent);
    @endphp

    <style>
        :root {
            --accent:
                {{ $accent }}
            ;
            --accent-dark:
                {{ $accentDark }}
            ;
        }

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

        .page-wrap {
            max-width: 820px;
            margin: 1.5rem auto;
        }

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

        .slip.has-border {
            border: 3px solid var(--accent);
            outline: 1px solid var(--accent-dark);
            outline-offset: -6px;
        }

        .slip.has-border::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 1px solid rgba(240, 165, 0, .35);
            border-radius: 1px;
            pointer-events: none;
            z-index: 2;
        }

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

        /* Nursery Development Sections - 2 column grid */
        .nursery-dev-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1px solid #000;
            border-top: none;
        }

        .nursery-dev-col {
            border-right: 1px solid #000;
        }

        .nursery-dev-col:last-child {
            border-right: none;
        }

        .nursery-dev-heading {
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.3px;
            padding: 10px 4px;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
            background: #f5f4ff;
        }

        .nursery-subject-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-bottom: 1px solid #000;
            min-height: 78px;
        }

        .nursery-dev-col>.nursery-subject-row:last-child {
            border-bottom: none;
        }

        .nursery-icon-box {
            flex: 0 0 54px;
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nursery-icon-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .nursery-subject-body {
            flex: 1;
        }

        .nursery-subject-name-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nursery-subject-name {
            font-weight: 700;
            font-size: 16px;
        }

        .nursery-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 3px 10px;
            border-radius: 2px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .nursery-badge-fair {
            background: #f39c12;
            color: #fff;
        }

        .nursery-badge-good {
            background: #2ecc71;
            color: #fff;
        }

        .nursery-badge-excellent {
            background: #3498db;
            color: #fff;
        }

        .nursery-desc {
            font-size: 13px;
            margin-top: 4px;
            color: #555;
        }

        /* Comments section */
        .nursery-comments-wrap {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            border: 1px solid #000;
            border-top: none;
        }

        .nursery-comments-left {
            border-right: 1px solid #000;
            padding: 12px 16px;
        }

        .nursery-comments-right {
            padding: 12px 16px;
            position: relative;
        }

        .nursery-comment-line {
            margin-bottom: 16px;
        }

        .nursery-comment-line:last-child {
            margin-bottom: 0;
        }

        .nursery-comment-line .label {
            font-weight: 700;
            font-size: 14px;
        }

        .nursery-comment-line .value {
            font-size: 16px;
            font-weight: 700;
            margin-top: 4px;
        }

        .nursery-comment-line .value.head {
            font-style: italic;
            text-transform: none;
            font-weight: 600;
        }

        .nursery-sig-line {
            font-size: 13.5px;
            margin-bottom: 14px;
        }

        .nursery-sig-line .lbl {
            font-weight: 700;
        }

        .nursery-sig-line .scribble {
            display: inline-block;
            width: 110px;
            border-bottom: 1px solid #000;
            margin-left: 6px;
            height: 16px;
        }

        .nursery-footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .nursery-footer-table td {
            border: 1px solid #000;
            border-top: none;
            padding: 8px 10px;
            width: 25%;
        }

        .nursery-footer-table .flabel {
            font-weight: 700;
        }

        .nursery-stamp-notice {
            text-align: center;
            font-size: 12px;
            font-style: italic;
            margin-top: 10px;
        }

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

            .slip.has-border {
                border: 3px solid var(--accent);
                outline: 1px solid var(--accent-dark);
                outline-offset: -6px;
            }

            .slip::before,
            .slip::after,
            .title-band,
            .nursery-dev-heading,
            .nursery-badge,
            .sch-header,
            .slip-footer,
            .status-promoted,
            .status-repeat,
            .watermark,
            .watermark-text,
            .watermark img {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }

            .nursery-dev-wrap {
                page-break-inside: avoid;
            }

            .nursery-subject-row {
                page-break-inside: avoid;
            }
        }

        .slip::before,
        .slip::after,
        .title-band,
        .nursery-dev-heading,
        .nursery-badge,
        .sch-header,
        .slip-footer,
        .status-promoted,
        .status-repeat,
        .watermark,
        .watermark-text,
        .watermark img {
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
                @if($mode === 'single') Nursery Report Card
                @elseif($mode === 'class') Class Nursery Report Cards
                @else All Nursery Report Cards @endif
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
                    'isEarlyYears' => $isEarlyYears ?? true,
                    'earlyYearsAverage' => $earlyYearsAverage ?? null,
                    'earlyYearsMaxMark' => $earlyYearsMaxMark ?? 3,
                ]
            ];
        } else {
            $renderSlips = $slips;
        }

        // Get badge class based on remark
        function getBadgeClass($remark)
        {
            $remarkLower = strtolower($remark ?? '');
            if (str_contains($remarkLower, 'excellent'))
                return 'nursery-badge-excellent';
            if (str_contains($remarkLower, 'good'))
                return 'nursery-badge-good';
            if (str_contains($remarkLower, 'fair'))
                return 'nursery-badge-fair';
            return 'nursery-badge';
        }

        $schoolName = Helper::schoolNameBySchoolID(Session('LoggedSchool')) ?? config('app.name', 'School');
        $slipCounter = 0;
    @endphp

    <div class="page-wrap">
        @foreach($renderSlips as $slipData)
            @php
                $slipCounter++;
                $s = (object) $slipData['student'];
                $subjMarks = collect($slipData['subjectMarks']);
                $isEarlyYears = $slipData['isEarlyYears'] ?? true;
                $earlyYearsAvg = $slipData['earlyYearsAverage'] ?? null;
                $earlyYearsMax = $slipData['earlyYearsMaxMark'] ?? 3;

                // Group subjects for two-column display - split into Cognitive and Social/Emotional
                // Assuming first half subjects go to Cognitive, second half to Social/Emotional
                $subjectList = $subjMarks->values();
                $half = ceil($subjectList->count() / 2);
                $cognitiveSubjects = $subjectList->slice(0, $half);
                $socialSubjects = $subjectList->slice($half);

                // Student photo
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

                // School logo
                $schoolLogo = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('logo');
                $schoolLogoUrl = null;
                if ($schoolLogo) {
                    $directPath = public_path('uploads/logos/' . $schoolLogo);
                    if (file_exists($directPath)) {
                        $schoolLogoUrl = asset('uploads/logos/' . $schoolLogo);
                    }
                }

                $schoolPhone = Helper::schoolPhoneBySchoolID(Session('LoggedSchool')) ?? '';
                $schoolEmail = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('email');
                $schoolMotto = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('motto');
                $schoolLocation = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('school_type');

                // Status
                $passed = true; // Nursery always passes
                $statusLabel = $s->status ?? ($isEarlyYears ? ($earlyYearsAvg >= 2 ? 'Promoted' : 'Repeat') : ($passed ? 'Promoted' : 'Repeat'));
                $statusClass = str_contains(strtolower($statusLabel), 'promot') ? 'status-promoted' : 'status-repeat';
            @endphp

            {{-- ────────────────────────── SLIP CARD ────────────────────────── --}}
            <div class="slip has-border">

                {{-- Watermark --}}
                @if($schoolLogoUrl)
                    <div class="watermark">
                        <img src="{{ $schoolLogoUrl }}" alt="watermark">
                    </div>
                @else
                    <div class="watermark-text">{{ $schoolName }}</div>
                @endif

                {{-- ══ SCHOOL HEADER ══════════════════════════════════════════ --}}
                <div class="sch-header">
                    <div class="sch-logo-area">
                        <div class="sch-logo-box">
                            @if($schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="logo">
                            @else
                                <i class="fas fa-school"></i>
                            @endif
                        </div>
                    </div>

                    <div class="sch-center">
                        <div class="sch-name">{{ $schoolName }}</div>
                        @if($schoolPhone || $schoolEmail || $schoolLocation)
                            <div class="sch-details">
                                @if($schoolPhone)<span>{{ $schoolPhone }}</span>@endif
                                @if($schoolEmail)<span> | {{ $schoolEmail }} | </span> <br> @endif
                                @if($schoolLocation)<span> {{ $schoolLocation }}</span>@endif
                            </div>
                        @endif
                        @if($schoolMotto)
                            <div class="sch-motto">MOTTO : "{{ $schoolMotto }}"</div>
                        @endif
                    </div>

                    <div class="sch-logo-area sch-logo-area-right">
                        <div class="sch-logo-box">
                            @if($schoolLogoUrl)
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

                    {{-- Details --}}
                    <div class="stu-details">
                        <div class="stu-field">
                            <strong>NAME:</strong>
                            {{ $s->lastname }} {{ $s->firstname }} {{ $s->other_names ?? '' }}
                        </div>
                        <!--<div class="stu-field"><strong>Gender:</strong> {{ $s->gender ?? ($s->index_no ?? '—') }}</div>-->
                        <div class="stu-field">
                            <strong>CLASS:</strong>
                            {{ Helper::recordMdname($s->senior) }}{{ ($s->stream ?? false) ? ' — ' . $s->stream : '' }}
                        </div>

                        <div class="stu-field"><strong>LIN:</strong> {{ $s->adm_no ?? ($s->index_no ?? '—') }}</div>
                    </div>
                </div>

                {{-- ══ NURSERY DEVELOPMENT GRID ═══════════════════════════════════ --}}
                <div class="nursery-dev-wrap">
                    {{-- Left Column: Cognitive Development --}}
                    <div class="nursery-dev-col left">
                        <div class="nursery-dev-heading">Cognitive Development</div>

                        @foreach($cognitiveSubjects as $sm)
                               
                              
                                @php
                                    $remark = $sm->grade_remark ?? '—';
                                    $badgeClass = getBadgeClass($remark);
                                    $displayRemark = $remark !== '—' ? $remark : '—';
                                    $subject_remark_update = '';

                                   if ($remark === "Works with Minimum Supervision") {
    $subject_remark_update = "Good";
} elseif ($remark === "Works under Teacher's Guidance") {
    $subject_remark_update = "Fair";
} elseif ($remark === "Works Independently") {
    $subject_remark_update = "Excellent";
}
                                @endphp

                     
                                <div class="nursery-subject-row">
                                    <div class="nursery-icon-box">
                                        {{-- You can add your icon here using $sm->subject_name --}}
                                        <img src="/images/subject-icons/{{ trim($sm->subject_name) }}.png"
                                             alt="{{ $sm->subject_name }}"
                                             style="width:40px;height:40px;">
                                    </div>
                                    <div class="nursery-subject-body">
                                        <div class="nursery-subject-name-row">
                                            <span class="nursery-subject-name">{{ $sm->subject_name }}</span>
                                            <!--<span class="nursery-badge {{ $subject_remark_update }}">-->
                                            <!--    {{ $subject_remark_update}}-->
                                            <!--</span>-->
                                        </div>
                                        <div class="nursery-desc">{{ $displayRemark }}</div>
                                    </div>
                                </div>
                        @endforeach
                    </div>

                    {{-- Right Column: Social/Emotional Development --}}
                    <div class="nursery-dev-col right">
                        <div class="nursery-dev-heading">Social/Emotional Development</div>

                        @foreach($socialSubjects as $sm)
                            @php
                                $remark = $sm->grade_remark ?? '—';
                                $badgeClass = getBadgeClass($remark);
                                $displayRemark = $remark !== '—' ? $remark : '—';


                               if ($remark === "Works with Minimum Supervision") {
    $subject_remark_update = "Good";
} elseif ($remark == "Works under Teacher's Guidance") {
    $subject_remark_update = "Fair";
} elseif ($remark == "Works Independently") {
    $subject_remark_update = "Excellent";
}
                            @endphp
                            

                            <div class="nursery-subject-row">
                                <div class="nursery-icon-box">
                                    <img src="/images/subject-icons/{{ trim($sm->subject_name) }}.png"
                                             alt="{{ $sm->subject_name }}"
                                             style="width:40px;height:40px;">
                                </div>
                                <div class="nursery-subject-body">
                                    <div class="nursery-subject-name-row">
                                        <span class="nursery-subject-name">{{ $sm->subject_name }}</span>
                                        <!--<span class="nursery-badge {{ $subject_remark_update }}">-->
                                        <!--    {{ $subject_remark_update}}-->
                                        <!--</span>-->
                                    </div>
                                    <div class="nursery-desc">{{ $displayRemark }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ══ COMMENTS SECTION ═══════════════════════════════════════════ --}}
                <div class="nursery-comments-wrap">
                    <div class="nursery-comments-left">
                        <div class="nursery-comment-line">
                            <div class="label">Class Teacher's Comments</div>
                            <div class="value"></div>
                        </div>
                        <div class="nursery-comment-line">
                            <div class="label">Head Teacher's Comments</div>
                            <div class="value head"></div>
                        </div>
                    </div>

                    <div class="nursery-comments-right">
                        <div class="nursery-sig-line"><span class="lbl">Name:</span> {{ $s->class_teacher ?? '' }}
                        </div>
                        <div class="nursery-sig-line"><span class="lbl">Signature:</span> <span class="scribble"></span>
                        </div>
                        <div class="nursery-sig-line"><span class="lbl">Name:</span>
                            {{ $s->head_teacher ?? 'Tr. ' }}</div>
                        <div class="nursery-sig-line"><span class="lbl">Signature:</span> <span class="scribble"></span>
                        </div>
                    </div>
                </div>

                {{-- ══ FOOTER ════════════════════════════════════════════════════ --}}
                <table class="nursery-footer-table">
                    <tr>
                        <td class="flabel">This Term Ends On</td>
                        <td></td>
                        <td class="flabel">Next Term Starts On</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="flabel">Fees Balance</td>
                        <td>&nbsp;</td>
                        <td class="flabel">Next Term Fees</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>

                <div class="nursery-stamp-notice">This report is invalid without School Stamp</div>

            </div>{{-- /.slip --}}

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