<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nursery Report Card — Preview (Term 3, 2026)</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Roboto+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php use App\Http\Controllers\Helper; ?>

    @php
        /*
        |─────────────────────────────────────────────────────────────
        | CUSTOMISATION — Nursery Minimal ('nursery-minimal')
        |
        | Mirrors the exact accent/toggle mechanism the Primary designs
        | (slip-classic/modern/minimal.blade.php) already use — see
        | those files' identical $accent/$on blocks. Query-string always
        | wins (so the "Customize this design" live preview keeps
        | reacting instantly); failing that, falls back to this class's
        | saved profile (Helper::getPassslipSettings); failing that, the
        | hard default.
        |
        | 'show_border', 'show_watermark' plus the School Header / Student
        | Block toggles below (logos, motto, contact, photo, name, class,
        | LIN) are wired here — matching the capability list for
        | 'nursery-minimal' in config/passslip_templates.php. This markup
        | is still a static demo layout (not yet bound to real
        | $student/$subjectMarks data — see the in-progress conversion
        | work referenced in ExaminationController::resolveNurserySlipView()),
        | so each toggle here only shows/hides the existing static demo
        | content rather than swapping in live data — that data-binding
        | conversion is a separate, later step. Any FURTHER toggle only
        | gets added here once the matching section of this file is
        | converted, same rule the config file states for the whole
        | Nursery family.
        |─────────────────────────────────────────────────────────────
        */
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

        // Very light tint of the accent colour (mixed heavily with
        // white) — used for the Cognitive/Social-Emotional Development
        // column headings so their background follows whichever Accent
        // Colour the user picks instead of the old fixed #f5f4ff.
        $hexToTint = function (string $hex, float $mixWithWhite = 0.88): string {
            $hex = ltrim($hex, '#');
            [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
            $r = (int) ($r + (255 - $r) * $mixWithWhite);
            $g = (int) ($g + (255 - $g) * $mixWithWhite);
            $b = (int) ($b + (255 - $b) * $mixWithWhite);
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };
        $accentTint = $hexToTint($accent);

        // ── School meta (same as modern template) ──────────────────────
        $schoolName = Helper::schoolNameBySchoolID(Session('LoggedSchool')) ?? config('app.name', 'School');
        $schoolPhone = Helper::schoolPhoneBySchoolID(Session('LoggedSchool')) ?? '';
        $schoolEmail = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('email');
        $schoolMotto = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('motto');
        $schoolLocation = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('school_type');
        $schoolLogo = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('logo');

        // Resolve logo URL
        $schoolLogoUrl = null;
        if ($schoolLogo) {
            $directPath = public_path('uploads/logos/' . $schoolLogo);
            if (file_exists($directPath)) {
                $schoolLogoUrl = asset('uploads/logos/' . $schoolLogo);
            } else {
                foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                    $fallback = public_path('storage/' . $schoolLogo);
                    if (file_exists($fallback)) {
                        $schoolLogoUrl = asset('storage/' . $schoolLogo);
                        break;
                    }
                    $fallback2 = public_path('uploads/logos/' . pathinfo($schoolLogo, PATHINFO_FILENAME) . '.' . $ext);
                    if (file_exists($fallback2)) {
                        $schoolLogoUrl = asset('uploads/logos/' . pathinfo($schoolLogo, PATHINFO_FILENAME) . '.' . $ext);
                        break;
                    }
                }
            }
        }

        // ── Normalise to one-or-many render list ─────────────────────────
        // passslipStudent() passes a single $student (mode 'single'), while
        // passslipClass()/passslipAll() instead pass a $slips collection
        // (mode 'class'/'all') — one entry per student in that class. This
        // mirrors the exact $renderSlips pattern slip-classic/modern/
        // minimal.blade.php already use, so the loop further down prints
        // one .slip per student instead of just a single (blank, since
        // $student was never even defined) one regardless of how many
        // students are actually in the class.
        $mode = $mode ?? 'single';
        $renderSlips = $mode === 'single' ? [['student' => $student]] : $slips;

        // Helper: treat '1' / 'true' / missing (falls back to saved
        // per-class settings, then to $default) as ON. Query-string
        // always wins so the live customisation preview keeps working.
        $on = fn(string $key, bool $default = true, array $saved = []): bool =>
            request()->has($key)
            ? in_array(request($key), ['1', 'true', 1, true], true)
            : ($saved[$key] ?? $default);

        // Per-class saved customisation, same lookup Primary uses. Resolved
        // from $classId (class mode) / $student->senior (single mode)
        // rather than always reading the single $student — every student
        // in $renderSlips belongs to the same class here, so one lookup
        // covers all of them; the per-student photo below is the only
        // thing that still needs to be resolved separately for each one.
        $settingsClassId = $mode === 'single' ? ($student->senior ?? null) : ($classId ?? ($renderSlips[0]['student']->senior ?? null));
        $savedCfg = Helper::getPassslipSettings(Session('LoggedSchool'), $settingsClassId);

        $cfg = [
            'border' => $on('show_border', true, $savedCfg),
            'watermark' => $on('show_watermark', true, $savedCfg),

            // Two independent logos either side of the school name —
            // each falls back to the legacy single 'show_logo' key
            // first, same convention slip-modern/minimal use, so an old
            // saved profile that predates this split still behaves the
            // same until it's re-saved with the new granular keys.
            'logo_left' => $on('show_logo_left', $on('show_logo', true, $savedCfg), $savedCfg),
            'logo_right' => $on('show_logo_right', $on('show_logo', true, $savedCfg), $savedCfg),
            'motto' => $on('show_motto', true, $savedCfg),
            'contact' => $on('show_contact', true, $savedCfg),

            'photo' => $on('show_photo', true, $savedCfg),
            // Whole-block master for the NAME/CLASS/LIN text list next to
            // the photo — independent of 'photo' so it can come off on
            // its own, same as Modern/Minimal's 'show_stu_details_block'.
            'stu_details_block' => $on('show_stu_details_block', true, $savedCfg),
            'stu_name' => $on('show_stu_name', true, $savedCfg),
            'stu_class' => $on('show_stu_class', true, $savedCfg),
            'stu_admission' => $on('show_stu_admission', true, $savedCfg),
            'stu_exam' => $on('show_stu_exam', true, $savedCfg),

            // Class Teacher's/Head Teacher's Comments + Signature block —
            // reuses Primary's exact 'show_remarks'/'show_signatures' keys
            // since this ledger-style layout mirrors Primary Minimal's,
            // just with early-years remark text instead of marks-based ones.
            'remarks' => $on('show_remarks', true, $savedCfg),
            'signatures' => $on('show_signatures', true, $savedCfg),

            // TERM & FEES INFORMATION — whole-section master covering the
            // combined This Term Ends On / Next Term Starts On / Fees
            // Balance / Next Term Fees footer table, same key nursery-classic
            // and nursery-modern use for their equivalent section.
            'section_term_fees' => $on('show_section_term_fees', true, $savedCfg),
        ];
    @endphp

    <style>
        :root {
            --accent:
                {{ $accent }}
            ;
            --accent-dark:
                {{ $accentDark }}
            ;
            --accent-tint:
                {{ $accentTint }}
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
            background: var(--accent-tint);
        }

        .nursery-subject-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 16px;
            border-bottom: 1px solid #000;
            min-height: 84px;
        }

        .nursery-dev-col>.nursery-subject-row:last-child {
            border-bottom: none;
        }

        .nursery-icon-box {
            flex: 0 0 64px;
            width: 64px;
            height: 64px;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1.5px solid #ececec;
        }

        .nursery-icon-box img {
            max-width: 100%;
            max-height: 100%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 30%;
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

        .nursery-desc {
            font-size: 13px;
            margin-top: 4px;
            color: #555;
        }

        .nursery-comments-wrap {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }

        .nursery-comments-left {
            flex: 1;
            border-right: 1px solid #000;
            padding: 10px 14px;
        }

        .nursery-comment-line {
            margin-bottom: 26px;
        }

        .nursery-comment-line:last-child {
            margin-bottom: 4px;
        }

        .nursery-comment-line .label {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .nursery-comment-line .value {
            border-bottom: 1px dotted #999;
            min-height: 16px;
        }

        .nursery-comments-right {
            flex: 1;
            padding: 10px 14px;
        }

        .nursery-sig-line {
            font-size: 13px;
            margin-bottom: 14px;
        }

        .nursery-sig-line .lbl {
            font-weight: 700;
        }

        .nursery-sig-line .scribble {
            display: inline-block;
            width: 130px;
            border-bottom: 1px solid #666;
            margin-left: 4px;
        }

        .nursery-footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .nursery-footer-table td {
            border: 1px solid #000;
            padding: 7px 12px;
            font-size: 12.5px;
        }

        .nursery-footer-table .flabel {
            font-weight: 700;
            width: 22%;
        }

        .nursery-stamp-notice {
            text-align: center;
            font-style: italic;
            font-size: 11.5px;
            padding: 8px 0 4px;
            color: #333;
        }

        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: .06;
            pointer-events: none;
            z-index: 0;
        }

        .watermark img {
            width: 380px;
            height: 380px;
            object-fit: contain;
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
            .sch-header,
            .watermark {
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
    </style>
</head>

<body>

    <div class="toolbar">
        <div class="toolbar-info">
            <strong><i class="fas fa-file-alt" style="margin-right:.35rem"></i> Nursery Report Card — Design
                Preview</strong>
            <small>Standalone preview • not wired to live data yet</small>
        </div>
        <div style="display:flex;gap:.45rem;flex-wrap:wrap;">
            <button class="tbtn tbtn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    @php
        /* ── Normalise to single render array ─────────────────────────────
        | 'nursery-minimal' is used from two very different call sites:
        |   - passslipPreview() (the customize-panel iframe, and this
        |     view's "mode" is 'single') passes one $student/$subjectMarks/
        |     $overallRemark/... directly as top-level variables.
        |   - passslipClass()/passslipAll() (real bulk printing) already
        |     build one ['student'=>..., 'subjectMarks'=>..., ...] array
        |     per student and pass it as $slips.
        | Wrapping both into the same $renderSlips shape here — the exact
        | pattern slip-classic/modern/minimal.blade.php already use for
        | this — is what lets the one @@foreach below serve every mode.
        |──────────────────────────────────────────────────────────────── */
        if (($mode ?? 'single') === 'single') {
            $renderSlips = [
                [
                    'student' => $student,
                    'subjectMarks' => $subjectMarks ?? collect(),
                    'overallRemark' => $overallRemark ?? null,
                ],
            ];
        } else {
            $renderSlips = $slips ?? [];
        }
    @endphp

    <div class="page-wrap">
        @foreach($renderSlips as $slipData)
            @php
                $student = $slipData['student'];
                $subjectMarks = collect($slipData['subjectMarks'] ?? []);
                $overallRemark = $slipData['overallRemark'] ?? null;

                // Split this student's subjects across the two
                // Cognitive/Social-Emotional Development columns.
                $nurseryHalf = (int) ceil($subjectMarks->count() / 2);
                $nurseryLeft = $subjectMarks->slice(0, $nurseryHalf)->values();
                $nurseryRight = $subjectMarks->slice($nurseryHalf)->values();

                // ── Student photo (per-student — resolved fresh on every
                // loop iteration, since each student in $renderSlips has
                // their own) ────────────────────────────────────────────
                $photo = null;
                if (!empty($student->student_photo)) {
                    foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                        $fp = str_replace(
                            '/',
                            DIRECTORY_SEPARATOR,
                            public_path('uploads/studentPhotos/' . $student->student_photo . '.' . $ext)
                        );
                        if (file_exists($fp)) {
                            $photo = asset('uploads/studentPhotos/' . $student->student_photo . '.' . $ext);
                            break;
                        }
                    }
                }
            @endphp
        <div class="slip {{ $cfg['border'] ? 'has-border' : '' }}">

            @if($cfg['watermark'])
                <div class="watermark-text"
                    style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:64px;font-weight:900;color:#000;opacity:.04;text-transform:uppercase;pointer-events:none;">
                    {{ $schoolName }}
                </div>
            @endif

            <div class="sch-header">
                @if($cfg['logo_left'])
                    <div class="sch-logo-area">
                        <div class="sch-logo-box">
                            @if($schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="logo">
                            @else
                                <i class="fas fa-school"></i>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="sch-center">
                    <div class="sch-name">{{ $schoolName }}</div>
                    @if($cfg['contact'])
                        <div class="sch-details">
                            @if($schoolPhone)<span>{{ $schoolPhone }}</span>@endif
                            @if($schoolEmail)<span> | {{ $schoolEmail }} | </span> <br> @endif
                            @if($schoolLocation)<span>{{ $schoolLocation }}</span>@endif
                        </div>
                    @endif
                    @if($cfg['motto'])
                        <div class="sch-motto">MOTTO : "{{ $schoolMotto }}"</div>
                    @endif
                </div>

                @if($cfg['logo_right'])
                    <div class="sch-logo-area sch-logo-area-right">
                        <div class="sch-logo-box">
                            @if($schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="logo">
                            @else
                                <i class="fas fa-school"></i>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="title-band">
                <span>Academic Report Form
                    — {{ Helper::recordMdname($student->senior ?? null) ?: 'Nursery' }}
                    — {{ $exam->term ?? '' }}
                    — ({{ $exam->academic_year ?? '' }})</span>
            </div>

            <div class="stu-row">
                @if($cfg['photo'])
                    <div class="stu-photo">
                        @if($photo)
                            <img src="{{ $photo }}" alt="{{ $student->firstname ?? 'Student' }} {{ $student->lastname ?? '' }}">
                        @else
                            <div class="nophoto">
                                <i class="fas fa-user"></i>
                                <span>No Photo</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if($cfg['stu_details_block'])
                    <div class="stu-details">
                        @if($cfg['stu_name'])
                            <div class="stu-field"><strong>NAME:</strong>
                                {{ $student->lastname ?? '' }} {{ $student->firstname ?? '' }} {{ $student->other_names ?? '' }}
                            </div>
                        @endif
                        @if($cfg['stu_class'])
                            <div class="stu-field"><strong>CLASS:</strong>
                                {{ Helper::recordMdname($student->senior ?? null) }}
                                {{ ($student->stream ?? false) ? ' — ' . $student->stream : '' }}
                            </div>
                        @endif
                        @if($cfg['stu_admission'])
                            <div class="stu-field"><strong>LIN:</strong>
                                {{ $student->adm_no ?? ($student->index_no ?? '—') }}
                            </div>
                        @endif
                        @if($cfg['stu_exam'])
                            <div class="stu-field"><strong>EXAM:</strong>
                                {{ trim(($exam->exam_name ?? '') . (($exam->term ?? null) ? ' - ' . $exam->term : '') . (($exam->academic_year ?? null) ? ' - ' . $exam->academic_year : ''), ' -') }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="nursery-dev-wrap">
                <div class="nursery-dev-col left">
                    <div class="nursery-dev-heading">Cognitive Development</div>
                    @forelse($nurseryLeft as $subj)
                        <div class="nursery-subject-row">
                            <div class="nursery-icon-box">
                                @if($iconUrl = Helper::nurserySubjectIconUrl($subj->subject_name))
                                    <img src="{{ $iconUrl }}" alt="{{ $subj->subject_name }}">
                                @endif
                            </div>
                            <div class="nursery-subject-body">
                                <div class="nursery-subject-name-row">
                                    <span class="nursery-subject-name">{{ $subj->subject_name }}</span>
                                </div>
                                <div class="nursery-desc">{{ $subj->grade_remark ?: 'Pending' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="nursery-subject-row">
                            <div class="nursery-subject-body">
                                <div class="nursery-desc">No subjects recorded yet.</div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="nursery-dev-col right">
                    <div class="nursery-dev-heading">Social/Emotional Development</div>
                    @forelse($nurseryRight as $subj)
                        <div class="nursery-subject-row">
                            <div class="nursery-icon-box">
                                @if($iconUrl = Helper::nurserySubjectIconUrl($subj->subject_name))
                                    <img src="{{ $iconUrl }}" alt="{{ $subj->subject_name }}">
                                @endif
                            </div>
                            <div class="nursery-subject-body">
                                <div class="nursery-subject-name-row">
                                    <span class="nursery-subject-name">{{ $subj->subject_name }}</span>
                                </div>
                                <div class="nursery-desc">{{ $subj->grade_remark ?: 'Pending' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="nursery-subject-row">
                            <div class="nursery-subject-body">
                                <div class="nursery-desc">&nbsp;</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="nursery-comments-wrap">
                @php
                    $nurseryCtSigUrl = \App\Http\Controllers\Helper::signatureUrl($student->class_teacher_signature ?? null);
                    $nurseryHtSigUrl = \App\Http\Controllers\Helper::signatureUrl($student->head_teacher_signature ?? null);
                @endphp
                @if($cfg['remarks'])
                    <div class="nursery-comments-left">
                        <div class="nursery-comment-line">
                            <div class="label">Class Teacher's Comments</div>
                            <div class="value">{{ $student->class_teacher_remark ?? '' }}</div>
                        </div>
                        <div class="nursery-comment-line">
                            <div class="label">Head Teacher's Comments</div>
                            <div class="value head">{{ $student->head_teacher_remark ?? '' }}</div>
                        </div>
                    </div>
                @endif

                @if($cfg['signatures'])
                    <div class="nursery-comments-right">
                        <div class="nursery-sig-line"><span class="lbl">Name:</span> {{ $student->class_teacher ?? '' }}</div>
                        <div class="nursery-sig-line">
                            <span class="lbl">Signature:</span>
                            @if($nurseryCtSigUrl)
                                <img src="{{ $nurseryCtSigUrl }}" alt="signature" style="max-height:20px;max-width:80px;object-fit:contain;vertical-align:middle;">
                            @else
                                <span class="scribble"></span>
                            @endif
                        </div>
                        <div class="nursery-sig-line"><span class="lbl">Date:</span> {{ now()->format('d M Y') }}</div>
                        <div class="nursery-sig-line"><span class="lbl">Name:</span> {{ $student->head_teacher ?? 'Head Teacher' }}</div>
                        <div class="nursery-sig-line">
                            <span class="lbl">Signature:</span>
                            @if($nurseryHtSigUrl)
                                <img src="{{ $nurseryHtSigUrl }}" alt="signature" style="max-height:20px;max-width:80px;object-fit:contain;vertical-align:middle;">
                            @else
                                <span class="scribble"></span>
                            @endif
                        </div>
                        <div class="nursery-sig-line"><span class="lbl">Date:</span> {{ now()->format('d M Y') }}</div>
                    </div>
                @endif
            </div>

            @if($cfg['section_term_fees'])
                <table class="nursery-footer-table">
                    <tr>
                        <td class="flabel">This Term Ends On</td>
                        <td>
                            @if(!empty($termDates['term_ends_on']))
                                {{ \Carbon\Carbon::parse($termDates['term_ends_on'])->format('d M Y') }}
                            @endif
                        </td>
                        <td class="flabel">Next Term Starts On</td>
                        <td>
                            @if(!empty($termDates['next_term_starts_on']))
                                {{ \Carbon\Carbon::parse($termDates['next_term_starts_on'])->format('d M Y') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Fees Balance</td>
                        <td>&nbsp;</td>
                        <td class="flabel">Next Term Fees</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            @endif

            <div class="nursery-stamp-notice">This report is invalid without School Stamp</div>

        </div>
        @endforeach
    </div>

</body>

</html>