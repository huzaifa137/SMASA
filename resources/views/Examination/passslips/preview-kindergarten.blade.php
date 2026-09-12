<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kindergarten Learning Journey — Preview</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Baloo+2:wght@500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php use App\Http\Controllers\Helper; ?>

    @php
        /*
        |─────────────────────────────────────────────────────────────
        | CUSTOMISATION — Nursery Classic ('nursery-classic')
        |
        | Mirrors the exact accent/toggle mechanism the other pass-slip
        | designs already use (see slip-classic/modern/minimal.blade.php
        | and slip-nursery.blade.php's identical $accent/$on blocks).
        | Query-string always wins (so the "Customize this design" live
        | preview keeps reacting instantly); failing that, falls back to
        | this class's saved profile (Helper::getPassslipSettings);
        | failing that, the hard default.
        |
        | 'show_border' and 'show_watermark' are wired here, matching the
        | 'nursery-classic' capability list in config/passslip_templates.php.
        | The student-info fields (Child's Name/Class/Teacher/Term) and the
        | Development Journey subject grid now read real $student/$exam/
        | $subjectMarks data (each subject's System Comment comes from
        | $subj->grade_remark, resolved server-side from whichever
        | Assessment Scale is attached to that class+subject) — NOT static
        | placeholder text. The Term & Fees box still shows placeholder
        | dates/amounts until real $term_ends_on/$fees_balance/... values
        | are wired up from the controller.
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

        // Helper: treat '1' / 'true' / missing (falls back to saved
        // per-class settings, then to $default) as ON. Query-string
        // always wins so the live customisation preview keeps working.
        $on = fn(string $key, bool $default = true, array $saved = []): bool =>
            request()->has($key)
            ? in_array(request($key), ['1', 'true', 1, true], true)
            : ($saved[$key] ?? $default);

        // Per-class saved customisation, same lookup slip-nursery.blade.php
        // uses, scoped to THIS template ('nursery-classic') specifically —
        // see the passslip_settings migration adding a `template` column —
        // so Classic's own saved accent/toggles never bleed into (or get
        // silently overwritten by) Modern's or Minimal's. Resolved from
        // $classId (bulk 'class'/'all' modes) rather than always reading a
        // single top-level $student, since passslipClass()/passslipAll()
        // pass a $slips collection instead — see the $renderSlips
        // normalisation below.
        $mode = $mode ?? 'single';
        $settingsClassId = $mode === 'single' ? ($student->senior ?? null) : ($classId ?? ($slips[0]['student']->senior ?? null));
        $savedCfg = Helper::getPassslipSettings(Session('LoggedSchool'), $settingsClassId, 'nursery-classic');

        $cfg = [
            'border' => $on('show_border', true, $savedCfg),
            'watermark' => $on('show_watermark', true, $savedCfg),
            'stu_exam' => $on('show_stu_exam', true, $savedCfg),
            'section_term_fees' => $on('show_section_term_fees', true, $savedCfg),
            'section_signatures' => $on('show_section_signatures', true, $savedCfg),
        ];

        $schoolName = Helper::schoolNameBySchoolID(Session('LoggedSchool')) ?? config('app.name', 'Your School Name');

        // ── School logo resolution (same as modern/nursery templates) ──
        // ── Get watermark logo (reuse school logo) ──────────────────────
        $schoolLogo = DB::table('school_profiles')->where('school_id', Session('LoggedSchool'))->value('logo');

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

        // ── Student photo resolution ────────────────────────────────────
        // Moved inside the @foreach below (one photo per student) — see
        // the $renderSlips normalisation.
        $watermarkLogoUrl = $schoolLogoUrl;

        // Short exam-type label (BOT / MOT / EOT / CA) shown in the new
        // "Exam:" field — same $examLabels convention slip-classic/modern/
        // minimal.blade.php already use for their BOT|MID|EOT comparison
        // table, so a school sees the same abbreviation everywhere rather
        // than nursery-minimal's full exam name and this template
        // disagreeing on terminology.
        $examLabels = [
            'Beginning-of-Term' => 'BOT',
            'Mid-Term' => 'MOT',
            'End-of-Term' => 'EOT',
            'Continuous Assessment' => 'CA',
        ];
        $examShortLabel = $examLabels[$exam->exam_type ?? null] ?? strtoupper($exam->term ?? $exam->exam_name ?? '');

        // ── Normalise to one-or-many render list ──────────────────────────
        // passslipPreview()/passslipStudent() pass a single $student (mode
        // 'single'), while passslipClass()/passslipAll() instead pass a
        // $slips collection (mode 'class'/'all') — one entry per student.
        // Mirrors the exact $renderSlips pattern slip-nursery.blade.php
        // already uses, so the one @foreach further down prints one sheet
        // per student instead of assuming a single top-level $student.
        $renderSlips = $mode === 'single'
            ? [['student' => $student, 'subjectMarks' => $subjectMarks ?? collect()]]
            : ($slips ?? []);

    @endphp

    <style>
        :root {
            --navy: #1c3f7c;
            --purple: #8a5fc7;
            --pink: #ec6ea8;
            --orange: #f2994a;
            --green: #4caf7d;
            --blue: #3aa8d8;
            --yellow: #f0b429;
            --paper: #fffdf7;
            --accent:
                {{ $accent }}
            ;
            --accent-dark:
                {{ $accentDark }}
            ;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Baloo 2', sans-serif;
            background: #dfe3ea;
        }

        .toolbar {
            background: linear-gradient(135deg, #1c3f7c, #3aa8d8);
            color: #fff;
            padding: .75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
            font-family: 'Fredoka', sans-serif;
        }

        .toolbar b {
            font-size: 1rem;
        }

        .toolbar small {
            opacity: .8;
            display: block;
        }

        .page-wrap {
            /* NOTE: this used to be `display:flex; justify-content:center;`,
       which centred a single .sheet fine for the live-preview iframe
       (always exactly one student) but silently laid MULTIPLE .sheet
       elements out as flex-row siblings — side-by-side horizontally —
       the moment this same template rendered a whole class via
       "Print by Class" (@foreach($renderSlips...) below produces one
       .sheet per student). Dropped the flex row entirely and rely on
       .sheet's own `margin: 0 auto` for centering instead — the exact
       same block-stacking approach slip-nursery.blade.php already uses
       for its .page-wrap/.slip, so multiple students stack vertically,
       one per page, same as every other template. */
            padding: 24px 0 60px;
        }

        .sheet {
            /* Fluid on screen — shrinks to fit narrow containers like the
       "Customize this design" live-preview iframe — but never
       grows past true A4 width. @media print below pins this
       back to an exact 210mm regardless of viewport, so printed
       output is unaffected. */
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            background: var(--paper);
            position: relative;
            padding: 10mm 11mm 8mm;
            box-shadow: 0 4px 30px rgba(0, 0, 0, .25);
            overflow: hidden;
            border-radius: 8px;
            margin: 0 auto 2.5rem;
            /* One student per printed page in bulk ("Print by Class"/"Print
       All") mode — same page-break-after:always / :last-child:avoid
       pairing slip-nursery.blade.php's .slip already uses. Harmless
       for the single-student live-preview iframe since there's only
       ever one .sheet there. */
            page-break-after: always;
        }

        .sheet:last-child {
            page-break-after: avoid;
            margin-bottom: 0;
        }

        /* ── Conditional border system (show_border toggle) ─────────
   Mirrors the has-border pattern used by slip-classic/modern/
   minimal/nursery — a coloured frame plus a thin inset line and
   chunky corner brackets, all tied to the Accent Colour picker. */
        .sheet.has-border {
            border: 3px solid var(--accent);
        }

        .sheet.has-border::before {
            content: '';
            position: absolute;
            inset: 6px;
            border: 1px solid var(--accent);
            opacity: .35;
            border-radius: 4px;
            pointer-events: none;
            z-index: 6;
        }

        .sheet.has-border::after {
            content: '';
            position: absolute;
            inset: 3px;
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
            z-index: 6;
        }

        /* ── Watermark (show_watermark toggle) ───────────────────────
   A large, faint, rotated school-name stamp centred behind all the
   sheet's content. Placed as the FIRST child of .sheet in the markup
   with z-index:0 so header-scene (z-index:auto→0, but painted after
   in DOM order) and .content (z-index:2) both layer on top of it. */
        .watermark-kg {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 0;
            opacity: 0.5;
            pointer-events: none;
            overflow: hidden;
        }

        .watermark-kg img {
            width: 50%;
            max-width: 350px;
            opacity: 0.5;
            object-fit: contain;
        }

        .watermark-kg .wm-text {
            font-family: 'Fredoka', sans-serif;
            font-weight: 800;
            font-size: 52px;
            color: var(--navy);
            text-transform: uppercase;
            text-align: center;
            line-height: 1.15;
            letter-spacing: .5px;
            transform: rotate(-18deg);
            white-space: nowrap;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .page-wrap {
                padding: 0;
            }

            .sheet {
                box-shadow: none;
                width: 210mm;
                min-height: 297mm;
                border-radius: 0;
                page-break-after: always;
                page-break-inside: avoid;
            }

            .sheet:last-child {
                page-break-after: avoid;
            }

            .sheet.has-border,
            .sheet.has-border::before,
            .sheet.has-border::after,
            .watermark-kg {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
        }

        /* decorative floaters */
        .deco {
            position: absolute;
            opacity: .95;
        }

        .deco.star {
            color: #f0b429;
            font-size: 20px;
        }

        .deco.heart {
            color: #ec6ea8;
            font-size: 16px;
        }

        .deco.dot {
            color: #3aa8d8;
            font-size: 14px;
        }

        /* HEADER SCENE - full-bleed illustrated banner */
        .header-scene {
            position: relative;
            margin: -10mm -11mm 0 -11mm;
            height: 58mm;
            background: linear-gradient(180deg, #fffdf7 0%, #fdfaf0 70%, #eef8ea 100%);
            overflow: hidden;
        }

        .header-scene .hill {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 16mm;
            background-image: url('{{ asset('images/passslip/kindergarten/') }}/hill_strip.png');
            background-size: cover;
            background-position: bottom;
            background-repeat: no-repeat;
            z-index: 1;
        }

        /* logo */
        .shield-logo {
            position: absolute;
            left: 4%;
            top: 4%;
            width: 13%;
            max-width: 80px;
            z-index: 4;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, .12));
        }

        .shield-logo svg {
            width: 100%;
            height: auto;
            display: block;
        }

        /* title block */
        .title-block {
            position: absolute;
            left: 48%;
            top: 4%;
            transform: translateX(-50%);
            text-align: center;
            width: 54%;
            z-index: 4;
        }

        .school-name {
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            color: var(--navy);
            font-size: 25px;
            line-height: 1.08;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .banner {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 7px;
            background: linear-gradient(180deg, #a575d9, #8a5fc7);
            color: #fff;
            padding: 6px 22px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12.5px;
            letter-spacing: .6px;
            box-shadow: 0 3px 0 rgba(0, 0, 0, .12);
            font-family: 'Fredoka', sans-serif;
        }

        .banner i {
            font-size: 11px;
            color: #ffe27a;
        }

        .academic-year {
            color: var(--blue);
            font-weight: 600;
            margin-top: 7px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .academic-year i {
            font-size: 9px;
            color: #8fc9e6;
        }

        /* sky group: rainbow, sun, balloon */
        .sky-group {
            position: absolute;
            right: 0;
            top: 1%;
            width: 34%;
            height: 46%;
            z-index: 3;
        }

        .rainbow-wrap {
            position: absolute;
            right: 0;
            top: 0;
            width: 88%;
        }

        .rainbow-wrap img {
            width: 100%;
            display: block;
        }

        .sun-wrap {
            position: absolute;
            left: 0;
            top: 10%;
            width: 30%;
        }

        .sun-wrap img {
            width: 100%;
            display: block;
        }

        .balloon-wrap {
            position: absolute;
            right: 0;
            top: 32%;
            width: 20%;
        }

        .balloon-wrap img {
            width: 100%;
            display: block;
        }

        /* left character group */
        .left-group {
            position: absolute;
            left: 2%;
            bottom: 10mm;
            width: 37%;
            height: 56%;
            z-index: 2;
        }

        .boy-wrap {
            position: absolute;
            left: 8%;
            bottom: 0;
            width: 58%;
            z-index: 3;
        }

        .boy-wrap img {
            width: 100%;
            display: block;
        }

        .teddy-wrap {
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 42%;
            z-index: 2;
        }

        .teddy-wrap img {
            width: 100%;
            display: block;
        }

        .blocks-wrap {
            position: absolute;
            left: 70%;
            bottom: 2%;
            width: 28%;
            z-index: 4;
        }

        .blocks-wrap img {
            width: 100%;
            display: block;
        }

        /* right character group */
        .right-group {
            position: absolute;
            right: 0;
            bottom: 10mm;
            width: 42%;
            height: 52%;
            z-index: 2;
        }

        .girl-wrap {
            position: absolute;
            right: 22%;
            bottom: 0;
            width: 46%;
            z-index: 3;
        }

        .girl-wrap img {
            width: 100%;
            display: block;
        }

        .palette-wrap {
            position: absolute;
            right: 64%;
            bottom: 2%;
            width: 28%;
            z-index: 2;
        }

        .palette-wrap img {
            width: 100%;
            display: block;
        }

        .globe-wrap {
            position: absolute;
            right: -2%;
            bottom: 0;
            width: 34%;
            z-index: 2;
        }

        .globe-wrap img {
            width: 100%;
            display: block;
        }

        /* floaters */
        .deco2 {
            position: absolute;
            z-index: 5;
        }

        .deco2.star {
            color: #f6c445;
            font-size: 15px;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, .08));
        }

        .deco2.star.sm {
            font-size: 11px;
        }

        .deco2.heart {
            color: #f394c0;
            font-size: 13px;
        }

        .deco2.butterfly {
            color: #b48ee0;
            font-size: 17px;
        }

        .deco2.dot {
            color: #7fc4e8;
            font-size: 11px;
        }

        .content {
            position: relative;
            z-index: 2;
            margin-top: 4mm;
        }

        /* CHILD INFO BOX */
        .info-box {
            background: #fff;
            border: 2px solid #eef1f6;
            border-radius: 16px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .06);
            padding: 8px 18px 10px;
            margin-bottom: 3.5mm;
        }

        .info-title {
            text-align: center;
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            color: var(--navy);
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 6px 22px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #333;
            padding: 4px 0;
            border-bottom: 1px dotted #ccc;
        }

        .info-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 10px;
            flex: none;
        }

        .info-row .label {
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }

        .info-row .value {
            flex: 1;
            color: #555;
        }

        /* DEVELOPMENT JOURNEY */
        .dev-title {
            text-align: center;
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin: 2.5mm 0 2.5mm;
        }

        .dev-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 3.5mm;
        }

        .dev-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .08);
            border: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
            padding-top: 5px;
        }

        .dev-card .thumb {
            width: 72%;
            display: block;
            margin: 0 auto;
            height: auto;
            border-radius: 10px;
        }

        /* Card title — replaces the wording that used to be baked into
   the PNG artwork. Coloured with each card's own --c so the look
   stays identical to the original baked-in labels. */
        .dev-card .dev-card-label {
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            font-size: 9.5px;
            line-height: 1.25;
            letter-spacing: .2px;
            text-transform: uppercase;
            color: var(--c, var(--navy));
            padding: 6px 6px 0;
        }

        /* Small heart + line divider — also used to be baked into the
   PNG artwork, now rendered live so both label and divider can be
   restyled or re-worded from this file alone. */
        .dev-card .dev-card-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 5px 10px 0;
        }

        .dev-card .dev-card-divider .line {
            flex: 1;
            height: 1px;
            background: var(--c, var(--navy));
            opacity: .55;
        }

        .dev-card .dev-card-divider i {
            color: var(--c, var(--navy));
            font-size: 9px;
        }

        .dev-card p {
            font-size: 9px;
            color: #555;
            line-height: 1.35;
            padding: 2px 10px 10px;
        }

        /* BOTTOM 4 PANELS */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr 1.1fr 1fr;
            gap: 8px;
            margin-bottom: 3mm;
        }

        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 8px 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            min-height: 130px;
            position: relative;
        }

        .panel h5 {
            font-size: 10px;
            font-family: 'Fredoka', sans-serif;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .panel h5 i {
            font-size: 11px;
        }

        .lines .line {
            border-bottom: 1px dotted #bbb;
            height: 14px;
            margin-bottom: 6px;
        }

        .moment-item {
            display: flex;
            gap: 6px;
            align-items: center;
            margin-bottom: 8px;
            font-size: 9px;
        }

        .moment-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 8px;
            flex: none;
        }

        .steps li {
            list-style: none;
            display: flex;
            gap: 6px;
            font-size: 8.7px;
            margin-bottom: 7px;
            align-items: flex-start;
        }

        .steps i {
            color: var(--blue);
            font-size: 9px;
            margin-top: 2px;
        }

        .message-text {
            font-size: 9.3px;
            line-height: 1.5;
            color: #444;
            text-align: center;
            font-style: italic;
        }

        /* FOOTER SIGNATURES */
        .sig-box {
            background: #fff;
            border-radius: 14px;
            padding: 10px 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            position: relative;
            margin-bottom: 4mm;
        }

        .sig-col {
            text-align: center;
            font-size: 10px;
        }

        .sig-col i {
            color: var(--navy);
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
        }

        .sig-line {
            border-bottom: 1px dotted #999;
            height: 18px;
            margin-bottom: 3px;
        }

        .sig-col span {
            font-weight: 600;
            color: #333;
        }

        .footer-art {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 15mm;
            background-image: url('{{ asset('images/passslip/kindergarten/') }}/hill_strip.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: bottom;
            z-index: 0;
        }

        .backpack-deco {
            position: absolute;
            left: 6mm;
            bottom: 1mm;
            width: 38px;
            z-index: 1;
        }

        .pencils-deco {
            position: absolute;
            right: 8mm;
            bottom: 1mm;
            width: 38px;
            z-index: 1;
        }

        /* =========================================================
     HEADER SCENE - POLISHED & UNCONGESTED
     ========================================================= */
        .header-scene {
            position: relative;
            margin: -10mm -11mm 0 -11mm;
            height: 58mm;
            background: linear-gradient(180deg, #fffdf7 0%, #fdfaf0 70%, #eef8ea 100%);
            overflow: hidden;
        }

        .header-scene .hill {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 16mm;
            background-image: url('{{ asset("images/passslip/kindergarten/hill_strip.png") }}');
            background-size: cover;
            background-position: bottom;
            background-repeat: no-repeat;
            z-index: 1;
        }

        /* --- LOGO (TOP LEFT) --- */
        .shield-logo {
            position: absolute;
            left: 2.5%;
            top: 5%;
            width: 65px;
            z-index: 10;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, .12));
        }

        .shield-logo svg {
            width: 100%;
            height: auto;
            display: block;
        }

        /* --- CENTER TITLE BLOCK --- */
        .title-block {
            position: absolute;
            left: 50%;
            top: 4%;
            transform: translateX(-50%);
            text-align: center;
            width: 44%;
            z-index: 10;
        }

        .school-name {
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            color: var(--navy);
            font-size: 23px;
            line-height: 1.08;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .banner {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 5px;
            background: linear-gradient(180deg, #a575d9, #8a5fc7);
            color: #fff;
            padding: 4px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .5px;
            box-shadow: 0 2px 0 rgba(0, 0, 0, .12);
            font-family: 'Fredoka', sans-serif;
        }

        .banner i {
            font-size: 9px;
            color: #ffe27a;
        }

        .academic-year {
            color: var(--blue);
            font-weight: 600;
            margin-top: 5px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .academic-year i {
            font-size: 8px;
            color: #8fc9e6;
        }

        /* --- TOP RIGHT SKY GROUP --- */
        .sky-group {
            position: absolute;
            right: 0;
            top: 0;
            width: 26%;
            height: 40%;
            z-index: 2;
        }

        .rainbow-wrap {
            position: absolute;
            right: 0%;
            top: 0%;
            width: 85%;
        }

        .rainbow-wrap img {
            width: 100%;
            display: block;
        }

        .sun-wrap {
            position: absolute;
            left: -5%;
            top: 10%;
            width: 28%;
        }

        .sun-wrap img {
            width: 100%;
            display: block;
        }

        .balloon-wrap {
            position: absolute;
            right: 2%;
            top: 48%;
            width: 16%;
        }

        .balloon-wrap img {
            width: 100%;
            display: block;
        }

        /* --- LEFT CHARACTER GROUP --- */
        .left-group {
            position: absolute;
            left: 1%;
            bottom: 2mm;
            width: 28%;
            height: 52%;
            z-index: 3;
        }

        .boy-wrap {
            position: absolute;
            left: 0%;
            bottom: 0;
            width: 50%;
            z-index: 3;
        }

        .boy-wrap img {
            width: 100%;
            display: block;
        }

        .teddy-wrap {
            position: absolute;
            left: 42%;
            bottom: 0;
            width: 34%;
            z-index: 2;
        }

        .teddy-wrap img {
            width: 100%;
            display: block;
        }

        .blocks-wrap {
            position: absolute;
            left: 68%;
            bottom: 0;
            width: 26%;
            z-index: 4;
        }

        .blocks-wrap img {
            width: 100%;
            display: block;
        }

        /* --- RIGHT CHARACTER GROUP --- */
        .right-group {
            position: absolute;
            right: 1%;
            bottom: 2mm;
            width: 29%;
            height: 52%;
            z-index: 3;
        }

        .girl-wrap {
            position: absolute;
            right: 20%;
            bottom: 0;
            width: 40%;
            z-index: 3;
        }

        .girl-wrap img {
            width: 100%;
            display: block;
        }

        .palette-wrap {
            position: absolute;
            right: 58%;
            bottom: 0;
            width: 26%;
            z-index: 2;
        }

        .palette-wrap img {
            width: 100%;
            display: block;
        }

        .globe-wrap {
            position: absolute;
            right: 0%;
            bottom: 0;
            width: 28%;
            z-index: 2;
        }

        .globe-wrap img {
            width: 100%;
            display: block;
        }

        .shield-logo {
            width: 75px;
            /* Increased width from 65px */
            max-width: 110px;
            Increased max-width from 80px to allow extra expansion
        }

        /* Floating Elements & Styles */
        .deco2 {
            position: absolute;
            z-index: 5;
            pointer-events: none;
        }

        .deco2.butterfly {
            color: #b87cd8;
            font-size: 20px;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.15));
        }

        .deco2.butterfly-sm {
            color: #9d68c9;
            font-size: 14px;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.12));
        }

        .deco2.star-gold {
            color: #f6c445;
            font-size: 16px;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, .08));
        }

        .deco2.star-gold.sm {
            font-size: 10px;
        }

        .deco2.heart-pink {
            color: #f394c0;
            font-size: 13px;
        }

        .deco2.heart-pink.sm {
            font-size: 9px;
        }

        .deco2.dot-blue {
            color: #7fc4e8;
            font-size: 14px;
            font-weight: bold;
        }

        .deco2.dot-pink {
            color: #f394c0;
            font-size: 14px;
            font-weight: bold;
        }

        .deco2.dot-green {
            color: #7fd0a1;
            font-size: 13px;
            font-weight: bold;
        }

        .deco2.dot-yellow {
            color: #f6c445;
            font-size: 13px;
            font-weight: bold;
        }

        /* =========================================================
     EXTRA HEADER DECORATIONS - hand-drawn SVGs (no icon-font gaps)
     ========================================================= */
        @keyframes flutter {

            0%,
            100% {
                transform: translateY(0) rotate(var(--r, 0deg));
            }

            50% {
                transform: translateY(-5px) rotate(calc(var(--r, 0deg) + 8deg));
            }
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: .45;
                transform: scale(.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.15);
            }
        }

        @keyframes drift {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(4px, -4px);
            }
        }

        @keyframes sway {

            0%,
            100% {
                transform: rotate(-4deg);
            }

            50% {
                transform: rotate(4deg);
            }
        }

        .deco-svg {
            position: absolute;
            z-index: 5;
            pointer-events: none;
            display: block;
        }

        .deco-butterfly {
            animation: flutter 3.2s ease-in-out infinite;
            transform-origin: center;
        }

        .deco-sparkle {
            animation: twinkle 2.1s ease-in-out infinite;
            transform-origin: center;
        }

        .deco-cloud-mini {
            animation: drift 6.5s ease-in-out infinite;
        }

        .deco-flower {
            animation: sway 3.6s ease-in-out infinite;
            transform-origin: bottom center;
        }

        /* stagger a few so they don't all pulse in unison */
        .deco-sparkle.d1 {
            animation-delay: .3s;
        }

        .deco-sparkle.d2 {
            animation-delay: .7s;
        }

        .deco-sparkle.d3 {
            animation-delay: 1.1s;
        }

        .deco-butterfly.d1 {
            animation-delay: .4s;
        }

        .deco-butterfly.d2 {
            animation-delay: .9s;
        }

        @media print {

            .deco-butterfly,
            .deco-sparkle,
            .deco-cloud-mini,
            .deco-flower {
                animation: none !important;
            }
        }

        /* Student photo in info box - responsive */
        .info-box .info-grid {
            grid-template-columns: 1fr 1fr 1fr;
            gap: 6px 22px;
        }

        @media (max-width: 600px) {
            .info-box>div {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .info-box .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* =========================================================
   TERM & FEES INFORMATION
   ========================================================= */

        .term-fees-box {
            background: #fff;
            border: 2px solid #eef1f6;
            border-radius: 14px;
            padding: 8px 12px 10px;
            margin-bottom: 3mm;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .term-fees-title {
            text-align: center;
            font-family: 'Fredoka', sans-serif;
            font-weight: 700;
            color: var(--navy);
            font-size: 12px;
            letter-spacing: .7px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .term-fees-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px 10px;
        }

        .term-fee-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fafbfd;
            border: 1px solid #edf0f5;
            border-radius: 10px;
            padding: 6px 9px;
            min-height: 35px;
        }

        .term-fee-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 10px;
            flex-shrink: 0;
        }

        .term-fee-content {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .term-fee-label {
            font-family: 'Fredoka', sans-serif;
            font-size: 8.5px;
            font-weight: 600;
            color: #777;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .term-fee-value {
            font-family: 'Fredoka', sans-serif;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.25;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <div>
            <b>Kindergarten Learning Journey — Design Preview</b>
            <small>Standalone HTML preview (not wired to live data)</small>
        </div>
        <div>
            <button onclick="window.print()"
                style="padding:6px 14px;border:none;border-radius:6px;background:#fff;color:var(--navy);font-weight:700;cursor:pointer;">Print
                / Save PDF</button>
        </div>
    </div>

    <div class="page-wrap">
        @foreach($renderSlips as $slipData)
            @php
                $student = $slipData['student'];
                $subjectMarks = collect($slipData['subjectMarks'] ?? []);

                // ── Student photo (per-student) ─────────────────────────
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

                // ── Development Journey cards (per-student) ─────────────
                // Built from this student's REAL nursery subjects
                // ($subjectMarks — the same collection slip-nursery.blade.php
                // already renders), instead of 8 fixed placeholder subjects.
                // 'desc' is $subj->grade_remark — the System Comment already
                // resolved server-side (buildPassslipData →
                // AssessmentScale::presetForScore()) from whichever
                // Assessment Scale is attached to that class+subject — e.g.
                // "Works Independently" / "Works with Minimum Supervision" /
                // "Works under Teacher's Guidance" — NOT static text. Accent
                // colour cycles through the same palette the original static
                // cards used, by position, so the grid keeps its varied,
                // colourful look; 'img' is a full icon URL
                // (Helper::nurserySubjectIconUrl()) with a null fallback
                // handled by the @forelse below.
                $devPalette = ['#4caf7d', '#f2994a', '#ec6ea8', '#8a5fc7', '#3aa8d8', '#4caf7d', '#f0b429', '#4caf7d'];
                $devCards = $subjectMarks->values()->map(function ($subj, $i) use ($devPalette) {
                    return [
                        'img' => Helper::nurserySubjectIconUrl($subj->subject_name ?? ''),
                        'label' => $subj->subject_name ?? '',
                        'c' => $devPalette[$i % count($devPalette)],
                        'desc' => $subj->grade_remark ?: 'Pending',
                    ];
                })->all();

                // ── Class/Head Teacher signatures (per-student) ─────────
                // $student->class_teacher / head_teacher / *_remark /
                // *_signature are populated by buildPassslipData() →
                // attachRemarksAndSignatures(), the same real data
                // slip-nursery.blade.php's signature block already reads
                // — this template's SIGNATURES row was still just static
                // "Class Teacher"/"Head Teacher" labels with a blank line.
                $nurseryCtSigUrl = Helper::signatureUrl($student->class_teacher_signature ?? null);
                $nurseryHtSigUrl = Helper::signatureUrl($student->head_teacher_signature ?? null);
            @endphp
        <div class="sheet {{ $cfg['border'] ? 'has-border' : '' }}">

            @if($cfg['watermark'])
                <div class="watermark-kg">
                    @if($watermarkLogoUrl)
                        <img src="{{ $watermarkLogoUrl }}" alt="watermark" style="width:60%;max-width:400px;opacity:0.15;">
                    @else
                        <div class="wm-text">{{ $schoolName }}</div>
                    @endif
                </div>
            @endif

            <div class="header-scene">

                <!-- Decorative Butterflies (hand-drawn SVG - fa-butterfly is Pro-only, this renders everywhere) -->
                <svg class="deco-svg deco-butterfly" style="left: 20%; top: 46%; width: 34px; --r: -12deg;"
                    viewBox="0 0 100 80" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 15 C46 8 40 5 36 6" stroke="#5a3b7a" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 15 C54 8 60 5 64 6" stroke="#5a3b7a" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 20 C30 -2 4 4 7 30 C9 46 30 43 50 28 Z" fill="#c68fe6" />
                    <path d="M50 20 C70 -2 96 4 93 30 C91 46 70 43 50 28 Z" fill="#b87cd8" />
                    <path d="M50 28 C34 36 18 56 29 66 C39 74 50 55 50 40 Z" fill="#e6b8f0" />
                    <path d="M50 28 C66 36 82 56 71 66 C61 74 50 55 50 40 Z" fill="#d9a3ec" />
                    <ellipse cx="50" cy="35" rx="3" ry="21" fill="#5a3b7a" />
                    <circle cx="22" cy="20" r="4" fill="#fff" opacity=".55" />
                    <circle cx="78" cy="20" r="4" fill="#fff" opacity=".55" />
                </svg>

                <svg class="deco-svg deco-butterfly d1" style="right: 31%; top: 16%; width: 22px; --r: 15deg;"
                    viewBox="0 0 100 80" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 15 C46 8 40 5 36 6" stroke="#b5417a" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 15 C54 8 60 5 64 6" stroke="#b5417a" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 20 C30 -2 4 4 7 30 C9 46 30 43 50 28 Z" fill="#f7a9cd" />
                    <path d="M50 20 C70 -2 96 4 93 30 C91 46 70 43 50 28 Z" fill="#f394c0" />
                    <path d="M50 28 C34 36 18 56 29 66 C39 74 50 55 50 40 Z" fill="#fbc4dd" />
                    <path d="M50 28 C66 36 82 56 71 66 C61 74 50 55 50 40 Z" fill="#f7a9cd" />
                    <ellipse cx="50" cy="35" rx="3" ry="21" fill="#b5417a" />
                    <circle cx="22" cy="20" r="4" fill="#fff" opacity=".55" />
                    <circle cx="78" cy="20" r="4" fill="#fff" opacity=".55" />
                </svg>

                <svg class="deco-svg deco-butterfly d2" style="left: 37%; top: 62%; width: 20px; --r: 8deg;"
                    viewBox="0 0 100 80" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 15 C46 8 40 5 36 6" stroke="#b8631f" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 15 C54 8 60 5 64 6" stroke="#b8631f" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 20 C30 -2 4 4 7 30 C9 46 30 43 50 28 Z" fill="#f7c17a" />
                    <path d="M50 20 C70 -2 96 4 93 30 C91 46 70 43 50 28 Z" fill="#f2994a" />
                    <path d="M50 28 C34 36 18 56 29 66 C39 74 50 55 50 40 Z" fill="#fbd9ab" />
                    <path d="M50 28 C66 36 82 56 71 66 C61 74 50 55 50 40 Z" fill="#f7c17a" />
                    <ellipse cx="50" cy="35" rx="3" ry="21" fill="#b8631f" />
                    <circle cx="22" cy="20" r="4" fill="#fff" opacity=".55" />
                    <circle cx="78" cy="20" r="4" fill="#fff" opacity=".55" />
                </svg>

                <svg class="deco-svg deco-butterfly d1" style="right: 40%; top: 56%; width: 20px; --r: -10deg;"
                    viewBox="0 0 100 80" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 15 C46 8 40 5 36 6" stroke="#1c6f8c" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 15 C54 8 60 5 64 6" stroke="#1c6f8c" stroke-width="2.5" fill="none"
                        stroke-linecap="round" />
                    <path d="M50 20 C30 -2 4 4 7 30 C9 46 30 43 50 28 Z" fill="#8fd4ef" />
                    <path d="M50 20 C70 -2 96 4 93 30 C91 46 70 43 50 28 Z" fill="#3aa8d8" />
                    <path d="M50 28 C34 36 18 56 29 66 C39 74 50 55 50 40 Z" fill="#bfe8f7" />
                    <path d="M50 28 C66 36 82 56 71 66 C61 74 50 55 50 40 Z" fill="#8fd4ef" />
                    <ellipse cx="50" cy="35" rx="3" ry="21" fill="#1c6f8c" />
                    <circle cx="22" cy="20" r="4" fill="#fff" opacity=".55" />
                    <circle cx="78" cy="20" r="4" fill="#fff" opacity=".55" />
                </svg>

                <!-- Decorative Stars & Hearts -->
                <i class="fa-solid fa-star deco2 star-gold" style="left: 20%; top: 10%;"></i>
                <i class="fa-solid fa-star deco2 star-gold sm" style="left: 42%; top: 3%;"></i>
                <i class="fa-solid fa-star deco2 star-gold sm" style="right: 34%; top: 8%;"></i>
                <i class="fa-solid fa-star deco2 star-gold sm" style="right: 6%; top: 44%;"></i>
                <i class="fa-solid fa-heart deco2 heart-pink" style="left: 26%; top: 25%;"></i>
                <i class="fa-solid fa-heart deco2 heart-pink sm" style="left: 43%; top: 68%;"></i>

                <!-- Decorative Dots -->
                <span class="deco2 dot-blue" style="left: 12%; top: 15%;">•</span>
                <span class="deco2 dot-pink" style="left: 31%; top: 12%;">•</span>
                <span class="deco2 dot-blue" style="right: 28%; top: 38%;">•</span>
                <span class="deco2 dot-green" style="left: 30%; top: 58%;">•</span>
                <span class="deco2 dot-yellow" style="right: 12%; top: 22%;">•</span>

                <!-- Decorative Sparkles (4-point twinkle stars) -->
                <svg class="deco-svg deco-sparkle" style="left: 15%; top: 20%; width: 14px;" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 0 L14.2 9.8 L24 12 L14.2 14.2 L12 24 L9.8 14.2 L0 12 L9.8 9.8 Z" fill="#ffe27a" />
                </svg>
                <svg class="deco-svg deco-sparkle d1" style="right: 19%; top: 13%; width: 11px;" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 0 L14.2 9.8 L24 12 L14.2 14.2 L12 24 L9.8 14.2 L0 12 L9.8 9.8 Z" fill="#ffffff" />
                </svg>
                <svg class="deco-svg deco-sparkle d2" style="left: 47%; top: 76%; width: 12px;" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 0 L14.2 9.8 L24 12 L14.2 14.2 L12 24 L9.8 14.2 L0 12 L9.8 9.8 Z" fill="#f394c0" />
                </svg>
                <svg class="deco-svg deco-sparkle d3" style="right: 14%; top: 58%; width: 13px;" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 0 L14.2 9.8 L24 12 L14.2 14.2 L12 24 L9.8 14.2 L0 12 L9.8 9.8 Z" fill="#8fd4ef" />
                </svg>

                <!-- Decorative Little Flowers (near the hill) -->
                <svg class="deco-svg deco-flower" style="left: 39%; top: 79%; width: 20px;" viewBox="0 0 40 40"
                    xmlns="http://www.w3.org/2000/svg">
                    <g fill="#f394c0">
                        <circle cx="20" cy="10" r="6.5" />
                        <circle cx="20" cy="30" r="6.5" />
                        <circle cx="10" cy="20" r="6.5" />
                        <circle cx="30" cy="20" r="6.5" />
                    </g>
                    <circle cx="20" cy="20" r="6" fill="#f6c445" />
                </svg>
                <svg class="deco-svg deco-flower" style="right: 44%; top: 77%; width: 16px; animation-delay: .5s;"
                    viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                    <g fill="#b87cd8">
                        <circle cx="20" cy="10" r="6.5" />
                        <circle cx="20" cy="30" r="6.5" />
                        <circle cx="10" cy="20" r="6.5" />
                        <circle cx="30" cy="20" r="6.5" />
                    </g>
                    <circle cx="20" cy="20" r="6" fill="#fff4cf" />
                </svg>

                <!-- Extra little fluffy cloud tucked beside the logo -->
                <svg class="deco-svg deco-cloud-mini" style="left: 15%; top: 6%; width: 46px; opacity:.9;"
                    viewBox="0 0 100 50" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="30" cy="30" rx="22" ry="15" fill="#ffffff" />
                    <ellipse cx="55" cy="21" rx="25" ry="19" fill="#ffffff" />
                    <ellipse cx="78" cy="31" rx="17" ry="13" fill="#ffffff" />
                </svg>

                <!-- Logo -->
                <div class="shield-logo">
                    @if($schoolLogoUrl)
                        <img src="{{ $schoolLogoUrl }}" alt="School Logo"
                            style="width:100%;height:auto;display:block;border-radius:8px;">
                    @else
                        <svg viewBox="0 0 120 140" xmlns="http://www.w3.org/2000/svg">
                            <path d="M60 4 C50 14 30 18 16 18 C16 60 20 96 60 132 C100 96 104 60 104 18 C90 18 70 14 60 4Z"
                                fill="#1c3f7c" stroke="#12274d" stroke-width="2" />
                            <path d="M60 12 C51 20 34 24 22 24 C22 60 26 90 60 120 C94 90 98 60 98 24 C86 24 69 20 60 12Z"
                                fill="none" stroke="#4d6fa8" stroke-width="1.4" />
                            <path d="M60 26 l5 11 12 1.5 -9 8.5 2.5 12 -10.5 -6 -10.5 6 2.5 -12 -9 -8.5 12 -1.5z"
                                fill="#ffffff" />
                            <path d="M42 55 h36 v6 c0 9 -8 14 -18 17 c-10 -3 -18 -8 -18 -17z" fill="none" stroke="#fff"
                                stroke-width="2.2" />
                            <path d="M60 55 v22" stroke="#fff" stroke-width="2" />
                            <text x="60" y="90" text-anchor="middle" fill="#fff" font-family="Fredoka, sans-serif"
                                font-weight="700" font-size="11">YOUR LOGO</text>
                            <text x="60" y="103" text-anchor="middle" fill="#fff" font-family="Fredoka, sans-serif"
                                font-weight="700" font-size="11">HERE</text>
                            <path
                                d="M16 34 C10 44 9 58 13 70 M16 34 c-6 3 -9 8 -10 13 M16 34 c-7 -1 -12 2 -16 6 M13 70 c-5 2 -8 6 -10 11 M13 70 c-6 0 -10 3 -13 7"
                                fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"
                                transform="translate(4,20)" />
                            <path
                                d="M104 34 C110 44 111 58 107 70 M104 34 c6 3 9 8 10 13 M104 34 c7 -1 12 2 16 6 M107 70 c5 2 8 6 10 11 M107 70 c6 0 10 3 13 7"
                                fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"
                                transform="translate(-4,20)" />
                        </svg>
                    @endif
                </div>

                <!-- Title Block -->
                <div class="title-block">
                    <div class="school-name">{{ $schoolName }}</div>
                    <div class="banner"><i class="fa-solid fa-star"></i> KINDERGARTEN LEARNING JOURNEY <i
                            class="fa-solid fa-star"></i></div>
                    <div class="academic-year"><i class="fas fa-graduation-cap"></i> Examination : {{ '' ?? '' }} <i
                            class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="academic-year"><i class="fa-solid fa-leaf"></i>
                        Academic Year : {{ $academic_year ?? '20XX' }} <i class="fa-solid fa-leaf"></i>
                    </div>
                </div>

                <!-- Sky Group -->
                <div class="sky-group">
                    <div class="rainbow-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/rainbow_clouds.png"
                            alt=""></div>
                    <div class="sun-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/sun.png" alt=""></div>
                    <div class="balloon-wrap"><img
                            src="{{ asset('images/passslip/kindergarten/') }}/hot_air_balloon.png" alt=""></div>
                </div>

                <!-- Left Group -->
                <div class="left-group">
                    <div class="teddy-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/teddy_bear.png"
                            alt=""></div>
                    <div class="boy-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/child_reading.png"
                            alt=""></div>
                    <div class="blocks-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/alphabet_blocks.png"
                            alt=""></div>
                </div>

                <!-- Right Group -->
                <div class="right-group">
                    <div class="palette-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/paint_palette.png"
                            alt=""></div>
                    <div class="girl-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/girl_waving.png"
                            alt=""></div>
                    <div class="globe-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/globe.png" alt="">
                    </div>
                </div>

                <div class="hill"></div>
            </div>

            <div class="content">

                <!-- CHILD'S INFORMATION - Option 2: Double Border -->
                <div class="info-box"
                    style="border: 2px solid var(--accent); background: #fff; border-radius: 16px; box-shadow: 0 3px 10px rgba(0,0,0,.06); padding: 8px 18px 10px; margin-bottom: 3.5mm; position: relative;">
                    <!-- Inner border -->
                    <div
                        style="position: absolute; inset: 5px; border: 1px dashed var(--accent); border-radius: 12px; pointer-events: none; opacity: 0.5;">
                    </div>

                    <div class="info-title"><i class="fa-solid fa-leaf"></i> Child's Information <i
                            class="fa-solid fa-leaf"></i></div>
                    <div style="display:flex; gap:20px; align-items:center;">
                        <!-- Student Photo -->
                        <div
                            style="flex-shrink:0; width:90px; height:110px; border:2px solid var(--accent); border-radius:12px; overflow:hidden; background:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                            @if($photo)
                                <img src="{{ $photo }}" alt="Student Photo"
                                    style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <div
                                    style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:100%;height:100%;background:#e8e8e8;color:#aaa;">
                                    <i class="fas fa-user" style="font-size:2rem;"></i>
                                    <span style="font-size:0.5rem;text-transform:uppercase;letter-spacing:0.04em;">No
                                        Photo</span>
                                </div>
                            @endif
                        </div>
                        <!-- Info Grid - 2 columns -->
                        <div class="info-grid"
                            style="flex:1; display:grid; grid-template-columns: 1fr 1fr; gap: 4px 20px;">
                            <div class="info-row">
                                <div class="info-icon" style="background:var(--blue)"><i class="fa-solid fa-user"></i>
                                </div>
                                <span class="label">Child's Name:</span>
                                <span class="value">{{ trim(($student->lastname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->other_names ?? '')) }}</span>
                            </div>

                            <div class="info-row">
                                <div class="info-icon" style="background:var(--pink)"><i
                                        class="fa-solid fa-user-large"></i></div>
                                <span class="label">Class:</span>
                                <span class="value">{{ Helper::recordMdname($student->senior ?? null) }}{{ ($student->stream ?? false) ? ' — ' . $student->stream : '' }}</span>
                            </div>

                            <div class="info-row">
                                <div class="info-icon" style="background:var(--yellow)"><i
                                        class="fa-solid fa-chalkboard-teacher"></i></div>
                                <span class="label">Teacher:</span>
                                <span class="value">{{ $student->class_teacher ?? '' }}</span>
                            </div>

                            <div class="info-row">
                                <div class="info-icon" style="background:var(--green)"><i
                                        class="fa-solid fa-calendar-days"></i></div>
                                <span class="label">Term:</span>
                                <span class="value">{{ trim(($exam->term ?? '') . ' ' . ($exam->academic_year ?? '')) }}</span>
                            </div>

                            @if($cfg['stu_exam'])
                                <div class="info-row">
                                    <div class="info-icon" style="background:var(--blue)"><i
                                            class="fa-solid fa-file-lines"></i></div>
                                    <span class="label">Exam:</span>
                                    <span class="value">{{ $examShortLabel }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- DEVELOPMENT JOURNEY -->
                <div class="dev-title"><i class="fa-solid fa-seedling"></i> My Development Journey <i
                        class="fa-solid fa-seedling"></i></div>
                <div class="dev-grid">
                    @forelse($devCards as $card)
                        <div class="dev-card" style="--c: {{ $card['c'] }}">
                            @if($card['img'])
                                <img class="thumb" src="{{ $card['img'] }}" alt="{{ $card['label'] }}">
                            @else
                                <i class="fa-solid fa-seedling thumb"
                                    style="display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:{{ $card['c'] }};"></i>
                            @endif
                            <div class="dev-card-label">{{ $card['label'] }}</div>
                            <div class="dev-card-divider">
                                <span class="line"></span>
                                <i class="fa-solid fa-heart"></i>
                                <span class="line"></span>
                            </div>
                            <p>{{ $card['desc'] }}</p>
                        </div>
                    @empty
                        <div class="dev-card">
                            <p>No subjects recorded yet.</p>
                        </div>
                    @endforelse
                </div>

                <!-- TERM & FEES INFORMATION - Option 2: Border with Accent Left Bars -->
                @if($cfg['section_term_fees'])
                <div class="term-fees-box"
                    style="background: #fff; border: 2px solid var(--accent); border-radius: 16px; box-shadow: 0 3px 10px rgba(0,0,0,.06); padding: 0; margin-bottom: 3.5mm; overflow: hidden;">

                    <!-- Accent header bar with background -->
                    <div
                        style="background: linear-gradient(135deg, var(--accent), var(--accent-dark)); padding: 10px 16px; position: relative;">
                        <!-- Decorative leaf icons in background -->
                        <div
                            style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); opacity: 0.15; font-size: 24px; color: #fff;">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <div
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); opacity: 0.15; font-size: 24px; color: #fff;">
                            <i class="fa-solid fa-leaf"></i>
                        </div>

                        <div
                            style="text-align: center; font-family: 'Fredoka', sans-serif; font-weight: 700; color: #fff; font-size: 13px; letter-spacing: .5px; text-transform: uppercase; position: relative; z-index: 1;">
                            <i class="fa-solid fa-leaf" style="margin-right: 8px;"></i>
                            Term & Fees Information
                            <i class="fa-solid fa-leaf" style="margin-left: 8px;"></i>
                        </div>
                    </div>

                    <div style="padding: 12px 14px 14px;">
                        <div class="term-fees-grid">
                            <!-- This Term Ends -->
                            <div class="term-fee-item"
                                style="display:flex; align-items:center; gap:10px; background:#fafbfd; border:1px solid #edf0f5; border-left: 4px solid var(--accent); border-radius:10px; padding:8px 10px; min-height:35px;">
                                <div class="term-fee-icon"
                                    style="width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:11px; flex-shrink:0; background:var(--blue);">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <div class="term-fee-content">
                                    <span class="term-fee-label"
                                        style="font-family:'Fredoka',sans-serif; font-size:8.5px; font-weight:600; color:#777; line-height:1.1; text-transform:uppercase;">This
                                        Term Ends On</span>
                                    <span class="term-fee-value"
                                        style="font-family:'Fredoka',sans-serif; font-size:10.5px; font-weight:700; color:var(--navy); line-height:1.25; margin-top:2px;">{{ $term_ends_on ?? '20 December 2026' }}</span>
                                </div>
                            </div>

                            <!-- Next Term Starts -->
                            <div class="term-fee-item"
                                style="display:flex; align-items:center; gap:10px; background:#fafbfd; border:1px solid #edf0f5; border-left: 4px solid var(--accent); border-radius:10px; padding:8px 10px; min-height:35px;">
                                <div class="term-fee-icon"
                                    style="width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:11px; flex-shrink:0; background:var(--green);">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                </div>
                                <div class="term-fee-content">
                                    <span class="term-fee-label"
                                        style="font-family:'Fredoka',sans-serif; font-size:8.5px; font-weight:600; color:#777; line-height:1.1; text-transform:uppercase;">Next
                                        Term Starts On</span>
                                    <span class="term-fee-value"
                                        style="font-family:'Fredoka',sans-serif; font-size:10.5px; font-weight:700; color:var(--navy); line-height:1.25; margin-top:2px;">{{ $next_term_starts_on ?? '05 January 2027' }}</span>
                                </div>
                            </div>

                            <!-- Fees Balance -->
                            <div class="term-fee-item"
                                style="display:flex; align-items:center; gap:10px; background:#fafbfd; border:1px solid #edf0f5; border-left: 4px solid var(--accent); border-radius:10px; padding:8px 10px; min-height:35px;">
                                <div class="term-fee-icon"
                                    style="width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:11px; flex-shrink:0; background:var(--orange);">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <div class="term-fee-content">
                                    <span class="term-fee-label"
                                        style="font-family:'Fredoka',sans-serif; font-size:8.5px; font-weight:600; color:#777; line-height:1.1; text-transform:uppercase;">Fees
                                        Balance</span>
                                    <span class="term-fee-value"
                                        style="font-family:'Fredoka',sans-serif; font-size:10.5px; font-weight:700; color:var(--navy); line-height:1.25; margin-top:2px;">{{ $fees_balance ?? 'UGX 150,000' }}</span>
                                </div>
                            </div>

                            <!-- Next Term Fees -->
                            <div class="term-fee-item"
                                style="display:flex; align-items:center; gap:10px; background:#fafbfd; border:1px solid #edf0f5; border-left: 4px solid var(--accent); border-radius:10px; padding:8px 10px; min-height:35px;">
                                <div class="term-fee-icon"
                                    style="width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:11px; flex-shrink:0; background:var(--purple);">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <div class="term-fee-content">
                                    <span class="term-fee-label"
                                        style="font-family:'Fredoka',sans-serif; font-size:8.5px; font-weight:600; color:#777; line-height:1.1; text-transform:uppercase;">Next
                                        Term Fees</span>
                                    <span class="term-fee-value"
                                        style="font-family:'Fredoka',sans-serif; font-size:10.5px; font-weight:700; color:var(--navy); line-height:1.25; margin-top:2px;">{{ $next_term_fees ?? 'UGX 500,000' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- SIGNATURES -->
                @if($cfg['section_signatures'])
                <div class="sig-box"
                    style="background: #fff; border-radius: 14px; padding: 10px 14px; box-shadow: 0 2px 8px rgba(0,0,0,.06); display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; position: relative; margin-bottom: 4mm;">
                    <div class="sig-col" style="text-align: center; font-size: 10px;">
                        <i class="fa-solid fa-pen-nib"
                            style="color: var(--accent); font-size: 14px; margin-bottom: 6px; display: block;"></i>
                        <div class="sig-line"
                            style="border-bottom: 1px dotted #999; height: 18px; margin-bottom: 3px; display:flex; align-items:flex-end; justify-content:center;">
                            @if($nurseryCtSigUrl)
                                <img src="{{ $nurseryCtSigUrl }}" alt="signature" style="max-height:16px;max-width:70px;object-fit:contain;">
                            @endif
                        </div>
                        <span style="font-weight: 600; color: #333;">{{ $student->class_teacher ?? 'Class Teacher' }}</span><br><small
                            style="color: #777;">Class Teacher</small>
                    </div>
                    <div class="sig-col" style="text-align: center; font-size: 10px;">
                        <i class="fa-solid fa-award"
                            style="color: var(--accent); font-size: 14px; margin-bottom: 6px; display: block;"></i>
                        <div class="sig-line"
                            style="border-bottom: 1px dotted #999; height: 18px; margin-bottom: 3px; display:flex; align-items:flex-end; justify-content:center;">
                            @if($nurseryHtSigUrl)
                                <img src="{{ $nurseryHtSigUrl }}" alt="signature" style="max-height:16px;max-width:70px;object-fit:contain;">
                            @endif
                        </div>
                        <span style="font-weight: 600; color: #333;">{{ $student->head_teacher ?? 'Head Teacher' }}</span><br><small
                            style="color: #777;">Head Teacher</small>
                    </div>
                    <div class="sig-col" style="text-align: center; font-size: 10px;">
                        <i class="fa-regular fa-calendar"
                            style="color: var(--accent); font-size: 14px; margin-bottom: 6px; display: block;"></i>
                        <div class="sig-line" style="border-bottom: 1px dotted #999; height: 18px; margin-bottom: 3px;">
                        </div>
                        <span style="font-weight: 600; color: #333;">Date</span>
                    </div>
                </div>
                @endif

            </div>

            <div class="footer-art"></div>
            <img class="backpack-deco" src="{{ asset('images/passslip/kindergarten/') }}/backpack.png" alt="">
            <img class="pencils-deco" src="{{ asset('images/passslip/kindergarten/') }}/pencils.png" alt="">
        </div>
        @endforeach
    </div>
</body>

</html>