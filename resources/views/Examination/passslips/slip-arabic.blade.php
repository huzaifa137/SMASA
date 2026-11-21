<!DOCTYPE html>
@php
    use App\Http\Controllers\Helper;
    use App\Helpers\NumberHelper;
    use App\Support\ArabicReportCard as AR;

    /*
    |─────────────────────────────────────────────────────────────────────
    | ARABIC REPORT CARD — Design Template 'arabic-classic'
    |
    | Assigned to a class from Report Cards → "Arabic Report Card" and saved
    | per class in passslip_settings (same mechanism as the other designs).
    | It renders on the same data every other slip uses, so single / class /
    | all-classes printing, release checks and the live preview all work.
    |
    | Language: Arabic by default. The toolbar switches the card between
    | Arabic and English (?card_lang=ar|en) without changing any data.
    | The text is real HTML text shaped by the browser, so Arabic letters
    | join correctly on screen, in Print → Save as PDF and in "Download PDF".
    |
    | The bottom of the card is fully dynamic (same data the Classic design
    | uses): Class Teacher's / Head Teacher's remarks and signatures come from
    | the remarks entered per student, and "This Term Ends On" / "Next Term
    | Starts On" come from the school's term dates. Nothing is pre-printed.
    |
    | Settings (query string wins, then the class's saved profile):
    |   card_lang, accent, show_photo, show_contact, show_motto,
    |   show_remarks, show_term_dates, show_decimals, arabic_digits
    |─────────────────────────────────────────────────────────────────────
    */
    $mode = $mode ?? 'single';

    $on = fn(string $key, bool $default = true, array $saved = []): bool =>
        request()->has($key)
        ? in_array(request($key), ['1', 'true', 1, true], true)
        : (bool) ($saved[$key] ?? $default);

    $schoolId = Session('LoggedSchool') ?: ($exam->school_id ?? ($student->school_id ?? null));
    $schoolNameEn = Helper::schoolNameBySchoolID($schoolId) ?? config('app.name', 'School');
    $schoolNameAr = trim((string) Helper::schoolNameArabic($schoolId !== null ? (int) $schoolId : null));

    ['pageW' => $pageW, 'pageH' => $pageH] = Helper::passslipPageSizing();

    $hexToDark = function (string $hex): string {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        return sprintf('#%02x%02x%02x', (int) ($r * 0.62), (int) ($g * 0.62), (int) ($b * 0.7));
    };

    /* ── Normalise to one render array (same shapes the other slips receive) ── */
    if ($mode === 'single') {
        $renderSlips = [[
            'student' => $student,
            'subjectMarks' => $subjectMarks,
            'totalObtained' => $totalObtained,
            'totalMax' => $totalMax,
            'percentage' => $percentage,
            'overallGrade' => $overallGrade,
            'overallRemark' => $overallRemark,
            'classRank' => $classRank,
            'classTotal' => $classTotal,
        ]];
    } else {
        $renderSlips = $slips;
    }

    /* The URL's own query string. Saved class settings are merged into request() for single / class
       prints, so request()->has() can't tell "the person picked a language" from "the class default". */
    parse_str((string) request()->server->get('QUERY_STRING', ''), $rawQuery);
    $langPicked = isset($rawQuery['card_lang']);

    /* Toolbar language: explicit choice, else the first class's saved default, else Arabic. */
    $firstStudent = (object) ($renderSlips[0]['student'] ?? ($student ?? []));
    $firstSaved = Helper::getPassslipSettings($schoolId, $firstStudent->senior ?? null);
    $toolbarLang = $langPicked ? AR::lang($rawQuery['card_lang']) : AR::lang($firstSaved['card_lang'] ?? 'ar');
    $T = AR::labels($toolbarLang);

    // Auto-open the print dialog for whole-class / all-classes runs, but not
    // right after the person flipped the language (that is just a preview).
    $autoPrint = in_array($mode, ['class', 'all'], true) && !$langPicked;

    // Computed here because a Blade json directive can't take a call that has several arguments.
    $pdfFilename = \App\Support\ExportName::file(['Report-Cards', $exam->exam_name ?? 'exam', $exam->academic_year ?? ''], 'pdf');

    // ?embed=1 — shown inside the assignment page's preview frame: no toolbar.
    $embed = request()->boolean('embed');

    $langUrl = fn(string $l) => request()->url() . '?' . http_build_query(array_merge($rawQuery, ['card_lang' => $l]));

    /* Letterhead details — same sources the Classic design uses (school profile). */
    $schoolProfile = DB::table('school_profiles')->where('school_id', $schoolId)->first();
    $schoolPhone = trim((string) (Helper::schoolPhoneBySchoolID($schoolId) ?? ''));
    $schoolEmail = trim((string) ($schoolProfile->email ?? ''));
    $schoolMotto = trim((string) ($schoolProfile->motto ?? ''));
    $schoolLocation = trim((string) ($schoolProfile->school_type ?? ''));
    $schoolWebsite = trim((string) (Helper::schoolWebsiteBySchoolID($schoolId) ?? ''));

    /* School logo — same lookup order the other slips use. */
    $schoolLogo = DB::table('school_profiles')->where('school_id', $schoolId)->value('logo');
    $schoolLogoUrl = null;
    if ($schoolLogo) {
        $logoBase = pathinfo($schoolLogo, PATHINFO_FILENAME);
        $logoCandidates = [
            'uploads/logos/' . $schoolLogo,
            'storage/' . $schoolLogo,
            'storage/logos/' . $schoolLogo,
        ];
        foreach (['jpg', 'jpeg', 'png', 'gif', 'webp', 'JPG', 'JPEG', 'PNG'] as $ext) {
            $logoCandidates[] = 'uploads/logos/' . $schoolLogo . '.' . $ext;
            $logoCandidates[] = 'uploads/logos/' . $logoBase . '.' . $ext;
            $logoCandidates[] = 'storage/' . $schoolLogo . '.' . $ext;
            $logoCandidates[] = 'storage/logos/' . $logoBase . '.' . $ext;
        }
        foreach ($logoCandidates as $rel) {
            if (is_file(public_path(str_replace('/', DIRECTORY_SEPARATOR, $rel)))) {
                $schoolLogoUrl = asset($rel);
                break;
            }
        }
    }
@endphp
<html lang="{{ $toolbarLang }}" dir="{{ $toolbarLang === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Support\ExportName::reportCardTitle($exam ?? null, $mode ?? 'single', $student ?? null, $slips ?? []) }}</title>
    <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
    {{-- Cairo is a clean, very readable Arabic + Latin face. If it can't load (offline), the
         system Arabic fonts in the stack below are used, so the text always stays readable. --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --slip-w: calc({{ $pageW }} - 1.3cm);
            --slip-h: calc({{ $pageH }} - 1.1cm);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, 'Noto Naskh Arabic', 'Arial', sans-serif;
            background: #dde1e7;
            color: #14213d;
            font-size: 13px;
            line-height: 1.45;
        }

        /* ── Toolbar (screen only) ── */
        .toolbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            padding: .7rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .3);
            direction: ltr;
        }
        .toolbar-info strong { color: #fff; font-size: .9rem; font-weight: 700; }
        .toolbar-info small { display: block; color: rgba(255, 255, 255, .6); font-size: .72rem; }
        .toolbar-actions { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
        .tbtn {
            padding: .45rem 1rem; border-radius: 8px; border: 0; cursor: pointer; font-family: inherit;
            font-weight: 700; font-size: .78rem; display: inline-flex; align-items: center; gap: .4rem;
            text-decoration: none; color: #fff; background: rgba(255, 255, 255, .14);
        }
        .tbtn:hover { background: rgba(255, 255, 255, .24); color: #fff; }
        .tbtn-print { background: #16a34a; } .tbtn-print:hover { background: #15803d; }
        .tbtn-pdf { background: #2563eb; } .tbtn-pdf:hover { background: #1d4ed8; }
        .tbtn[disabled] { opacity: .6; cursor: progress; }
        .lang-switch { display: inline-flex; background: rgba(255, 255, 255, .12); border-radius: 9px; padding: 3px; gap: 2px; }
        .lang-switch a {
            padding: .35rem .9rem; border-radius: 7px; font-size: .78rem; font-weight: 700;
            color: rgba(255, 255, 255, .8); text-decoration: none;
        }
        .lang-switch a.active { background: #fff; color: #1e3a8a; }

        .page-wrap { width: 100%; max-width: var(--slip-w); margin: 1.5rem auto; }
        body.embed .page-wrap { margin: .75rem auto; }

        /* ── The card ── */
        .slip {
            --accent: #2f63b8;
            --ink: #17399b;
            --tint: #eaf2fd;
            --line: #9fbbe6;
            position: relative;
            background: #fff;
            width: 100%;
            height: var(--slip-h);
            min-height: var(--slip-h);
            margin-bottom: 2rem;
            overflow: hidden;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .14);
            page-break-after: always; break-after: page;
            page-break-inside: avoid; break-inside: avoid;
        }
        .slip:last-of-type { page-break-after: avoid; break-after: auto; }

        /* ── Professional frame ──────────────────────────────────────────────
           Three ruled lines (thick ink / thin accent / thin ink) and a small
           diamond at the middle of the left and right sides. Follows --ink / --accent, so it re-colours with the
           card's accent picker. Pure CSS: prints and exports to PDF. */
        .frame {
            position: absolute; inset: 12px; z-index: 1; pointer-events: none;
            color: var(--ink);
            border: 3px solid var(--ink); border-radius: 4px;
        }
        .frame::before { content: ''; position: absolute; inset: 4px; border: 1px solid var(--accent); border-radius: 2px; }
        .frame::after  { content: ''; position: absolute; inset: 8px; border: 1.5px solid var(--ink); border-radius: 1px; }
        .mid { position: absolute; z-index: 2; width: 8px; height: 8px; background: var(--ink); border: 3px solid #fff; outline: 1px solid var(--ink); transform: rotate(45deg); }
        .mid.l { left: -9px; top: calc(50% - 7px); } .mid.r { right: -9px; top: calc(50% - 7px); }

        .card {
            position: relative; height: 100%;
            padding: 30px 36px 28px;
            display: flex; flex-direction: column; gap: 11px;
        }

        /* header */
        /* Logo always on the left, an equal invisible slot on the right, so the text block is
           centred on the PAGE (the 'balanced header' of the Classic design, always on here). */
        .hdr { display: flex; align-items: center; gap: 10px; direction: ltr; }
        .hdr-text { flex: 1; text-align: center; min-width: 0; }
        .hdr-logo { width: 104px; height: 104px; flex: 0 0 104px; display: flex; align-items: center; justify-content: center; }
        .hdr-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .hdr-logo.spacer { visibility: hidden; }
        .school { font-size: calc(28px * var(--hdr-name-scale, 1.15)); font-weight: 800; color: var(--ink); line-height: 1.25; overflow-wrap: anywhere; }
        .school2 { font-size: 18px; font-weight: 700; color: var(--accent); line-height: 1.3; margin-top: 1px; overflow-wrap: anywhere; }
        .contact { display: flex; flex-wrap: wrap; justify-content: center; gap: 2px 16px; margin-top: 6px; font-size: 16px; font-weight: 600; color: #334155; }
        .contact span { white-space: nowrap; }
        .contact i { color: var(--accent); margin-inline-end: 5px; font-size: .87em; }
        .motto { margin-top: 4px; font-size: 16px; font-style: italic; font-weight: 700; color: var(--ink); }
        .title { font-size: 25px; font-weight: 800; color: var(--ink); margin-top: 7px; overflow-wrap: anywhere; }
        .rule { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 8px auto 0; width: 80%; }
        .rule span { flex: 1; height: 1.5px; background: var(--ink); opacity: .75; }
        .rule i { width: 9px; height: 9px; background: var(--ink); transform: rotate(45deg); display: block; }

        /* meta row */
        .meta { display: flex; justify-content: space-between; gap: 12px; font-size: 17px; font-weight: 700; color: var(--ink); }
        .meta b { font-weight: 800; }

        /* student info */
        .info { display: flex; align-items: stretch; gap: 0; border: 1.5px solid var(--ink); border-radius: 3px; }
        .info-fields { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 14px 28px; padding: 16px 20px; align-content: center; }
        .fld { display: flex; align-items: baseline; gap: 7px; min-width: 0; font-size: 17px; }
        .fld .k { font-weight: 800; color: var(--ink); white-space: nowrap; }
        .fld .v { flex: 1; min-width: 0; border-bottom: 1.2px dotted #475569; padding: 0 4px; min-height: 1.5em; font-weight: 600; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .photo-cell { flex: 0 0 auto; padding: 10px; border-inline-start: 1.5px solid var(--ink); display: flex; align-items: center; }
        .photo { width: 100px; height: 120px; background: #e5e7eb; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .photo img { width: 100%; height: 100%; object-fit: cover; }
        .photo .ph { text-align: center; color: #94a3b8; font-size: 11px; font-weight: 800; letter-spacing: .06em; }
        .photo .ph svg { display: block; margin: 0 auto 3px; }

        /* marks table */
        .tbl-wrap { flex: 1 1 auto; min-height: 0; display: flex; }
        table.marks { width: 100%; height: 100%; border-collapse: collapse; table-layout: fixed; border: 1.5px solid var(--ink); }
        .marks th {
            background: var(--accent); color: #fff; font-weight: 800; font-size: 15px; line-height: 1.35;
            padding: 9px 6px; border: 1px solid var(--ink); text-align: center; vertical-align: middle;
        }
        .marks td { border: 1px solid var(--line); text-align: center; vertical-align: middle; font-size: 17px; font-weight: 700; color: var(--ink); padding: 6px 8px; }
        .marks td.subj { background: var(--tint); font-size: 16px; font-weight: 800; color: #1e293b; }
        .marks td.rem { font-size: 16px; font-weight: 700; color: #1e293b; }
        .marks td.na { color: #94a3b8; font-weight: 600; }
        .marks tr.grand td { background: var(--tint); border-top: 2px solid var(--ink); }
        .marks.d2 td { font-size: 15px; padding: 3px 7px; } .marks.d2 td.subj, .marks.d2 td.rem { font-size: 14px; } .marks.d2 th { font-size: 14px; padding: 7px 5px; }
        .marks.d3 td { font-size: 13px; padding: 1px 6px; } .marks.d3 td.subj, .marks.d3 td.rem { font-size: 12.5px; } .marks.d3 th { font-size: 12.5px; padding: 5px 4px; }
        .col-subj { width: 24%; } .col-avg { width: 15%; } .col-obt { width: 15%; } .col-tot { width: 15%; } .col-rem { width: 31%; }

        /* footer — remarks / signatures / term dates (all dynamic) */
        .remarks { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 2px; }
        .rbox { border: 1.5px solid var(--ink); border-radius: 3px; display: flex; flex-direction: column; min-width: 0; }
        .rbox .rhd { background: var(--tint); border-bottom: 1.2px solid var(--ink); padding: 6px 10px; font-size: 15px; font-weight: 800; color: var(--ink); }
        .rbox .rhd .rn { font-weight: 600; color: #334155; }
        .rbox .rtxt { padding: 7px 10px; min-height: 54px; font-size: 15px; font-weight: 600; color: #1e293b; line-height: 1.55; overflow-wrap: anywhere; }
        .rbox .rtxt.none { color: #94a3b8; font-weight: 500; }
        .rbox .rsig { display: flex; align-items: flex-end; gap: 8px; padding: 0 10px 6px; font-size: 14px; font-weight: 700; color: var(--ink); }
        .rbox .rsig .sl { flex: 1; min-height: 24px; border-bottom: 1.2px solid #475569; display: flex; align-items: flex-end; justify-content: center; }
        .rbox .rsig img { max-width: 110px; max-height: 26px; object-fit: contain; }
        .dates { display: flex; justify-content: space-between; gap: 12px; border: 1.5px solid var(--ink); border-radius: 3px; padding: 8px 14px; font-size: 16px; font-weight: 700; color: var(--ink); }
        .dates b { font-weight: 800; color: #1e293b; }

        .slip, .frame, .mid, .marks th, .marks td, .rbox .rhd, .rule span, .rule i, .photo {
            -webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact;
        }

        /* "Download PDF" captures the cards flat (no screen margins / shadow) */
        body.pdf-mode { background: #fff; }
        body.pdf-mode .toolbar { display: none; }
        body.pdf-mode .page-wrap { max-width: none; width: {{ $pageW }}; margin: 0; }
        body.pdf-mode .slip { margin: 0; box-shadow: none; width: {{ $pageW }}; height: calc({{ $pageH }} - 1mm); min-height: calc({{ $pageH }} - 1mm); }

        @media print {
            @page { margin: .5cm .65cm; size: {{ $pageW }} {{ $pageH }}; }
            body { background: #fff; }
            .toolbar { display: none !important; }
            .page-wrap { width: var(--slip-w); max-width: none; margin: 0; }
            .slip { margin: 0; box-shadow: none; width: 100%; height: var(--slip-h); min-height: var(--slip-h); max-height: var(--slip-h); }
        }
    </style>
</head>

<body class="{{ $embed ? 'embed' : '' }}">

    {{-- ══ TOOLBAR ══ --}}
    @unless($embed)
    <div class="toolbar">
        <div class="toolbar-info">
            <strong><i class="fas fa-file-alt"></i>
                @if($mode === 'single') {{ $T['title'] }}
                @elseif($mode === 'class') Class Report Cards
                @else All Report Cards @endif
                — {{ $exam->exam_name }}
            </strong>
            <small>
                @if($mode === 'single')
                    {{ trim(($firstStudent->lastname ?? '') . ' ' . ($firstStudent->firstname ?? '')) }}
                @elseif($mode === 'class')
                    {{ Helper::recordMdname($classId) }} {{ !empty($streamId) ? '– ' . $streamId : '' }}
                @else All Classes @endif
                &bull; {{ \App\Support\Term::label($exam->term) }} {{ $exam->academic_year }}
            </small>
        </div>
        <div class="toolbar-actions">
            <div class="lang-switch" title="Card language / لغة البطاقة">
                <a href="{{ $langUrl('ar') }}" class="{{ $toolbarLang === 'ar' ? 'active' : '' }}">العربية</a>
                <a href="{{ $langUrl('en') }}" class="{{ $toolbarLang === 'en' ? 'active' : '' }}">English</a>
            </div>
            <button type="button" class="tbtn tbtn-print" onclick="window.print()">
                <i class="fas fa-print"></i> {{ $T['print'] }}
            </button>
            <button type="button" class="tbtn tbtn-pdf" id="btnPdf" onclick="downloadCardsPdf(this)">
                <i class="fas fa-file-pdf"></i> {{ $T['download'] }}
            </button>
            <a href="{{ $backUrl ?? route('examination.passslips.index', $exam->id) }}" class="tbtn">
                <i class="fas fa-arrow-left"></i> {{ $T['back'] }}
            </a>
        </div>
    </div>
    @endunless

    <div class="page-wrap" id="cards">
        @foreach($renderSlips as $slipData)
            @php
                $s = (object) $slipData['student'];
                $saved = Helper::getPassslipSettings($schoolId, $s->senior ?? null);

                /* language for THIS card: explicit toolbar choice wins, else the class's saved default */
                $lang = $langPicked ? AR::lang($rawQuery['card_lang']) : AR::lang($saved['card_lang'] ?? 'ar');
                $L = AR::labels($lang);
                $eastern = $on('arabic_digits', true, $saved);
                $d = fn($v) => AR::digits($v, $lang, $eastern);

                NumberHelper::useDecimals($on('show_decimals', false, $saved));
                $fmt = fn($n) => ($x = NumberHelper::num($n, 2)) === '' ? '—' : $d($x);

                $accent = request('accent', $saved['accent'] ?? '#2f63b8');
                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $accent)) {
                    $accent = '#2f63b8';
                }
                $ink = $hexToDark($accent);

                $showPhoto = $on('show_photo', true, $saved);
                /* Header layout: honours a saved/previewed hdr_name_scale, else the balanced default (x1.15). */
                $hdrLayout = Helper::passslipHeaderLayout($saved);
                $nameScale = $hdrLayout['name_scale'] ?? 1.15;
                $showContact = $on('show_contact', true, $saved);
                $showMotto = $on('show_motto', true, $saved);
                $showRemarks = $on('show_remarks', true, $saved);
                $showTermDates = $on('show_term_dates', true, $saved);

                $rows = collect($slipData['subjectMarks'])->values();
                $count = $rows->count();
                $density = $count <= 7 ? '' : ($count <= 11 ? 'd2' : 'd3');

                /* Main name in the card language, the other language's name (when set) underneath. */
                $schoolName = $lang === 'ar' && $schoolNameAr !== '' ? $schoolNameAr : $schoolNameEn;
                $schoolName2 = '';
                if ($schoolNameAr !== '' && trim((string) $schoolNameEn) !== '') {
                    $schoolName2 = $lang === 'ar' ? $schoolNameEn : $schoolNameAr;
                }
                $reportTitle = trim((string) ($exam->exam_name ?? '')) !== '' ? $exam->exam_name : $L['title'];

                $fullName = trim(($s->firstname ?? '') . ' ' . ($s->lastname ?? '') . ' ' . ($s->other_names ?? ''));
                $admissionNo = $s->admission_number ?? ($s->adm_no ?? ($s->index_no ?? '—'));
                $className = Helper::recordMdname($s->senior ?? null);

                $rank = $slipData['classRank'] ?? '—';
                $total = $slipData['classTotal'] ?? 0;
                $position = ($rank !== '—' && $rank !== null && $rank !== '' && (int) $total > 0)
                    ? $d($rank) . ' / ' . $d($total)
                    : '—';

                $overallRemark = AR::remark($slipData['overallRemark'] ?? '', $slipData['overallGrade'] ?? '', $lang);
                $level = $overallRemark !== '' ? $overallRemark : '—';

                $photo = null;
                if (!empty($s->student_photo)) {
                    foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                        if (file_exists(public_path('uploads/studentPhotos/' . $s->student_photo . '.' . $ext))) {
                            $photo = asset('uploads/studentPhotos/' . $s->student_photo . '.' . $ext);
                            break;
                        }
                    }
                }

                /* Remarks + signatures: attached to the student by the controller (same source as Classic). */
                $ctName = trim((string) ($s->class_teacher ?? ''));
                $htName = trim((string) ($s->head_teacher ?? ''));
                $ctRemark = trim((string) ($s->class_teacher_remark ?? ''));
                $htRemark = trim((string) ($s->head_teacher_remark ?? ''));
                $ctSigUrl = Helper::signatureUrl($s->class_teacher_signature ?? null);
                $htSigUrl = Helper::signatureUrl($s->head_teacher_signature ?? null);

                /* Term dates: the school's active term / the next one (Helper::passslipTermDates). */
                $fmtDate = function ($v) use ($lang, $d) {
                    if (empty($v)) {
                        return null;
                    }
                    try {
                        $c = \Carbon\Carbon::parse($v);
                    } catch (\Throwable $e) {
                        return null;
                    }
                    return $lang === 'ar' ? $d($c->format('d / m / Y')) : $c->format('d M Y');
                };
                $termEndsOn = $fmtDate($termDates['term_ends_on'] ?? null);
                $nextTermStartsOn = $fmtDate($termDates['next_term_starts_on'] ?? null);
            @endphp

            <div class="slip" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $lang }}"
                style="--accent: {{ $accent }}; --ink: {{ $ink }};">
                <div class="frame">
                    <span class="mid l"></span><span class="mid r"></span>
                </div>

                <div class="card">
                    {{-- header --}}
                    <div class="hdr">
                        @if($schoolLogoUrl)
                            <div class="hdr-logo"><img src="{{ $schoolLogoUrl }}" alt="" crossorigin="anonymous"></div>
                        @endif
                        <div class="hdr-text" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="school" style="--hdr-name-scale: {{ $nameScale }};"><bdi>{{ $schoolName }}</bdi></div>
                            @if($schoolName2 !== '')
                                <div class="school2"><bdi>{{ $schoolName2 }}</bdi></div>
                            @endif
                            @if($showContact && ($schoolLocation !== '' || $schoolPhone !== '' || $schoolEmail !== '' || $schoolWebsite !== ''))
                                <div class="contact">
                                    @if($schoolLocation !== '')<span><i class="fas fa-location-dot"></i><bdi>{{ $schoolLocation }}</bdi></span>@endif
                                    @if($schoolPhone !== '')<span><i class="fas fa-phone"></i><bdi dir="ltr">{{ $schoolPhone }}</bdi></span>@endif
                                    @if($schoolEmail !== '')<span><i class="fas fa-envelope"></i><bdi dir="ltr">{{ $schoolEmail }}</bdi></span>@endif
                                    @if($schoolWebsite !== '')<span><i class="fas fa-globe"></i><bdi dir="ltr">{{ $schoolWebsite }}</bdi></span>@endif
                                </div>
                            @endif
                            @if($showMotto && $schoolMotto !== '')
                                <div class="motto">{{ $L['motto'] }} : "<bdi>{{ $schoolMotto }}</bdi>"</div>
                            @endif
                            {{-- The title is the report / examination name itself --}}
                            <div class="title"><bdi>{{ $reportTitle }}</bdi></div>
                            <div class="rule"><span></span><i></i><span></span></div>
                        </div>
                        @if($schoolLogoUrl)
                            <div class="hdr-logo spacer" aria-hidden="true"></div>
                        @endif
                    </div>

                    {{-- academic year / term / note --}}
                    <div class="meta">
                        <div>{{ $L['academic_year'] }} : <b>{{ $d($exam->academic_year) }}</b></div>
                        <div>{{ $L['term'] }} : <b>{{ AR::termLabel($exam->term, $lang) }}</b></div>
                        <div>{{ $L['based_on'] }}</div>
                    </div>

                    {{-- student information --}}
                    <div class="info">
                        <div class="info-fields">
                            <div class="fld"><span class="k">{{ $L['name'] }} :</span><span class="v"><bdi>{{ $fullName !== '' ? $fullName : '—' }}</bdi></span></div>
                            <div class="fld"><span class="k">{{ $L['class'] }} :</span><span class="v"><bdi>{{ $className ?: '—' }}</bdi></span></div>
                            <div class="fld"><span class="k">{{ $lang === 'ar' ? 'رقم القيد' : 'Adm. No.' }} :</span><span class="v"><bdi>{{ $admissionNo }}</bdi></span></div>
                            <div class="fld"><span class="k">{{ $L['position'] }} :</span><span class="v"><bdi>{{ $position }}</bdi></span></div>
                            <div class="fld"><span class="k">{{ $L['level'] }} :</span><span class="v"><bdi>{{ $level }}</bdi></span></div>
                            <div class="fld"><span class="k">{{ $L['section'] }} :</span><span class="v"><bdi>{{ !empty($s->stream) ? $s->stream : '—' }}</bdi></span></div>
                        </div>
                        @if($showPhoto)
                            <div class="photo-cell">
                                <div class="photo">
                                    @if($photo)
                                        <img src="{{ $photo }}" alt="" crossorigin="anonymous">
                                    @else
                                        <div class="ph">
                                            <svg width="44" height="44" viewBox="0 0 24 24" fill="#b6bdc9"><circle cx="12" cy="8" r="4.2"/><path d="M3.5 21c0-4.7 3.8-7.5 8.5-7.5s8.5 2.8 8.5 7.5z"/></svg>
                                            {{ $L['photo'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- marks --}}
                    <div class="tbl-wrap">
                        <table class="marks {{ $density }}">
                            <colgroup>
                                <col class="col-subj"><col class="col-avg"><col class="col-obt"><col class="col-tot"><col class="col-rem">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>{{ $L['subject'] }}</th>
                                    <th>{{ $L['class_average'] }}</th>
                                    <th>{{ $L['obtained'] }}</th>
                                    <th>{{ $L['total'] }}</th>
                                    <th>{{ $L['remarks'] }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $sm)
                                    @php
                                        $remarkText = AR::remark($sm->grade_remark ?? '', $sm->grade ?? '', $lang);
                                        $hasMark = isset($sm->marks_obtained) && $sm->marks_obtained !== null && $sm->marks_obtained !== '';
                                    @endphp
                                    <tr>
                                        <td class="subj"><bdi>{{ $sm->subject_name }}</bdi></td>
                                        <td class="{{ ($sm->class_average ?? null) === null ? 'na' : '' }}">{{ $fmt($sm->class_average ?? null) }}</td>
                                        <td class="{{ $hasMark ? '' : 'na' }}">{{ $hasMark ? $fmt($sm->marks_obtained) : '—' }}</td>
                                        <td>{{ $fmt($sm->total_marks ?? null) }}</td>
                                        <td class="rem"><bdi>{{ $remarkText !== '' ? $remarkText : '—' }}</bdi></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="na">—</td></tr>
                                @endforelse
                                <tr class="grand">
                                    <td class="subj">{{ $L['grand_total'] }}</td>
                                    <td class="na">—</td>
                                    <td>{{ $fmt($slipData['totalObtained'] ?? null) }}</td>
                                    <td>{{ $fmt($slipData['totalMax'] ?? null) }}</td>
                                    <td class="rem"><bdi>{{ $overallRemark !== '' ? $overallRemark : '—' }}</bdi></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- class teacher / head teacher remarks + signatures --}}
                    @if($showRemarks)
                        <div class="remarks">
                            <div class="rbox">
                                <div class="rhd">{{ $L['class_teacher_remarks'] }}@if($ctName !== '') <span class="rn">- <bdi>{{ $ctName }}</bdi></span>@endif</div>
                                <div class="rtxt {{ $ctRemark === '' ? 'none' : '' }}" dir="auto">{{ $ctRemark !== '' ? $ctRemark : $L['no_remarks'] }}</div>
                                <div class="rsig"><span>{{ $L['signature'] }} :</span><span class="sl">@if($ctSigUrl)<img src="{{ $ctSigUrl }}" alt="" crossorigin="anonymous">@endif</span></div>
                            </div>
                            <div class="rbox">
                                <div class="rhd">{{ $L['head_teacher_remarks'] }}@if($htName !== '') <span class="rn">- <bdi>{{ $htName }}</bdi></span>@endif</div>
                                <div class="rtxt {{ $htRemark === '' ? 'none' : '' }}" dir="auto">{{ $htRemark !== '' ? $htRemark : $L['no_remarks'] }}</div>
                                <div class="rsig"><span>{{ $L['signature'] }} :</span><span class="sl">@if($htSigUrl)<img src="{{ $htSigUrl }}" alt="" crossorigin="anonymous">@endif</span></div>
                            </div>
                        </div>
                    @endif

                    {{-- term dates --}}
                    @if($showTermDates && ($termEndsOn || $nextTermStartsOn))
                        <div class="dates">
                            <div>{{ $L['term_ends_on'] }} : <b><bdi>{{ $termEndsOn ?? '—' }}</bdi></b></div>
                            <div>{{ $L['next_term_starts_on'] }} : <b><bdi>{{ $nextTermStartsOn ?? '—' }}</bdi></b></div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <script>
        /* Download PDF — same approach as the certificates in iteb-academics: the browser lays out
           (and shapes) the Arabic, html2pdf captures each card as one A4 page. */
        function downloadCardsPdf(btn) {
            var el = document.getElementById('cards');
            var count = el.querySelectorAll('.slip').length;
            var label = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            document.body.classList.add('pdf-mode');

            var opt = {
                margin: 0,
                filename: @json($pdfFilename),
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: count > 6 ? 2 : 3, useCORS: true, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css'] }
            };

            var done = function () {
                document.body.classList.remove('pdf-mode');
                btn.disabled = false;
                btn.innerHTML = label;
            };

            var start = function () { html2pdf().set(opt).from(el).save().then(done, done); };
            (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(function () {
                setTimeout(start, 150);
            });
        }

        /* Keep a long school name on one line: shrink it (never below 70 %) until it fits. */
        function fitSchoolNames() {
            document.querySelectorAll('.school').forEach(function (el) {
                el.style.fontSize = '';
                var size = parseFloat(getComputedStyle(el).fontSize);
                var min = size * 0.7;
                while (el.getBoundingClientRect().height > size * 1.6 && size > min) {
                    size -= 1;
                    el.style.fontSize = size + 'px';
                }
            });
        }

        /* Contact line: keep location + phone + email (+ website) on ONE line whenever they can fit by
           shrinking the text a little (never below 13px). If they still can't, put it back to the normal
           size and let it wrap onto a second line as before. */
        function fitContactLines() {
            document.querySelectorAll('.contact').forEach(function (el) {
                var items = Array.prototype.slice.call(el.children);
                if (items.length < 2) { return; }
                el.style.fontSize = '';
                el.style.columnGap = '';
                var base = parseFloat(getComputedStyle(el).fontSize);
                var baseGap = parseFloat(getComputedStyle(el).columnGap) || 20;
                var wrapped = function () {
                    var top = items[0].offsetTop;
                    return items.some(function (i) { return Math.abs(i.offsetTop - top) > 2; });
                };
                if (!wrapped()) { return; }
                var size = base;
                while (wrapped() && size > 13) {
                    size -= 0.5;
                    el.style.fontSize = size + 'px';
                    el.style.columnGap = Math.max(8, baseGap * size / base) + 'px';
                }
                if (wrapped()) {
                    el.style.fontSize = '';
                    el.style.columnGap = '';
                }
            });
        }

        /* Student info values (Name, Class, ...) always stay on ONE line: when the text is wider than its
           slot the font is reduced until it fits (down to 9px; beyond that it is cut with an ellipsis). */
        function fitInfoFields() {
            document.querySelectorAll('.info-fields .v').forEach(function (el) {
                el.style.fontSize = '';
                var size = parseFloat(getComputedStyle(el).fontSize);
                while (el.scrollWidth > el.clientWidth + 1 && size > 9) {
                    size -= 0.5;
                    el.style.fontSize = size + 'px';
                }
            });
        }

        function fitHeader() { fitSchoolNames(); fitContactLines(); fitInfoFields(); }
        window.addEventListener('beforeprint', fitHeader);
        (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(fitHeader);
        window.addEventListener('load', fitHeader);

        @if($autoPrint)
            window.addEventListener('load', function () {
                var ready = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
                ready.then(function () {
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () { setTimeout(function () { window.print(); }, 300); });
                    });
                });
            });
        @endif
    </script>
</body>

</html>
