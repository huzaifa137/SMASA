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
    | Settings (query string wins, then the class's saved profile):
    |   card_lang, accent, show_photo, show_stamp, show_notes, show_decimals,
    |   arabic_digits
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

        .frame { position: absolute; inset: 6px; border: 2.5px solid var(--ink); border-radius: 6px; pointer-events: none; }
        .frame::before { content: ''; position: absolute; inset: 4px; border: 1px solid var(--ink); border-radius: 3px; }
        .corner { position: absolute; width: 14px; height: 14px; background: var(--ink); transform: rotate(45deg); }
        .corner.tl { top: 0; left: 0; margin: -8px 0 0 -8px; } .corner.tr { top: 0; right: 0; margin: -8px -8px 0 0; }
        .corner.bl { bottom: 0; left: 0; margin: 0 0 -8px -8px; } .corner.br { bottom: 0; right: 0; margin: 0 -8px -8px 0; }

        .card {
            position: relative; height: 100%;
            padding: 26px 30px 24px;
            display: flex; flex-direction: column; gap: 12px;
        }

        /* header */
        .hdr { display: flex; align-items: center; gap: 18px; }
        .hdr-text { flex: 1; text-align: center; min-width: 0; }
        .school { font-size: 27px; font-weight: 800; color: var(--ink); line-height: 1.3; overflow-wrap: anywhere; }
        .title { font-size: 21px; font-weight: 700; color: var(--ink); margin-top: 6px; }
        .exam-name { font-size: 12px; font-weight: 600; color: var(--accent); margin-top: 2px; }
        .rule { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 10px auto 0; width: 75%; }
        .rule span { flex: 1; height: 1.5px; background: var(--ink); opacity: .75; }
        .rule i { width: 9px; height: 9px; background: var(--ink); transform: rotate(45deg); display: block; }
        .hdr-logo { width: 118px; height: 118px; flex: 0 0 118px; display: flex; align-items: center; justify-content: center; }
        .hdr-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }

        /* meta row */
        .meta { display: flex; justify-content: space-between; gap: 12px; font-size: 14px; font-weight: 700; color: var(--ink); }
        .meta b { font-weight: 800; }

        /* student info */
        .info { display: flex; align-items: stretch; gap: 0; border: 1.5px solid var(--ink); border-radius: 3px; }
        .info-fields { flex: 1; display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 12px 22px; padding: 14px 18px; align-content: center; }
        .fld { display: flex; align-items: baseline; gap: 7px; min-width: 0; font-size: 14px; }
        .fld .k { font-weight: 800; color: var(--ink); white-space: nowrap; }
        .fld .v { flex: 1; min-width: 0; border-bottom: 1.2px dotted #475569; padding: 0 4px; min-height: 1.5em; font-weight: 600; text-align: center; overflow-wrap: anywhere; }
        .photo-cell { flex: 0 0 auto; padding: 10px; border-inline-start: 1.5px solid var(--ink); display: flex; align-items: center; }
        .photo { width: 92px; height: 108px; background: #e5e7eb; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; }
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

        /* footer */
        .foot { display: flex; justify-content: space-between; align-items: flex-end; gap: 22px; margin-top: 2px; }
        .notes { flex: 1; min-width: 0; font-size: 12.5px; }
        .notes h4 { font-size: 15px; font-weight: 800; color: var(--ink); margin-bottom: 4px; }
        .notes ul { padding-inline-start: 18px; }
        .notes li { margin-bottom: 3px; color: #1e293b; }
        .notes li::marker { color: var(--ink); }
        .date-line { margin-top: 10px; font-weight: 800; color: var(--ink); font-size: 14px; display: flex; gap: 6px; align-items: baseline; }
        .date-line .dots { letter-spacing: 1px; font-weight: 600; color: #475569; }
        .sign { flex: 0 0 150px; text-align: center; }
        .sign .who { font-weight: 800; color: var(--ink); font-size: 15px; margin-top: 6px; }
        .sign .who-name { font-size: 12px; font-weight: 600; color: #334155; }
        .sign .sline { margin-top: 26px; border-top: 1.2px solid #475569; }
        .stamp { width: 128px; height: 128px; margin: 0 auto; border-radius: 50%; border: 3px solid #1d4ed8; color: #1d4ed8; transform: rotate(-7deg); opacity: .92; padding: 6px; }
        .stamp-in { width: 100%; height: 100%; border-radius: 50%; border: 1.5px solid #1d4ed8; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 0 16px; position: relative; text-align: center; }
        .stamp-in .sn { font-size: 10.5px; font-weight: 800; line-height: 1.25; overflow-wrap: anywhere; }
        .stamp-in .sd { font-size: 13px; font-weight: 700; color: #dc2626; letter-spacing: .02em; }
        .stamp-in .st { position: absolute; top: 50%; transform: translateY(-50%); font-size: 11px; line-height: 1; }
        .stamp-in .st.l { left: 7px; } .stamp-in .st.r { right: 7px; }

        .slip, .frame, .corner, .marks th, .marks td, .stamp, .stamp-in, .rule span, .rule i, .photo {
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
            <a href="{{ route('examination.passslips.index', $exam->id) }}" class="tbtn">
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
                $showStamp = $on('show_stamp', true, $saved);
                $showNotes = $on('show_notes', true, $saved);

                $rows = collect($slipData['subjectMarks'])->values();
                $count = $rows->count();
                $density = $count <= 8 ? '' : ($count <= 12 ? 'd2' : 'd3');

                $schoolName = $lang === 'ar' && $schoolNameAr !== '' ? $schoolNameAr : $schoolNameEn;
                $stampName = $lang === 'ar' && $schoolNameAr !== '' ? $schoolNameAr : strtoupper($schoolNameEn);

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

                $stampDate = $lang === 'ar' ? $d(now()->format('d / m / Y')) : strtoupper(now()->format('d M Y'));
            @endphp

            <div class="slip" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $lang }}"
                style="--accent: {{ $accent }}; --ink: {{ $ink }};">
                <div class="frame">
                    <span class="corner tl"></span><span class="corner tr"></span>
                    <span class="corner bl"></span><span class="corner br"></span>
                </div>

                <div class="card">
                    {{-- header --}}
                    <div class="hdr">
                        <div class="hdr-text">
                            <div class="school"><bdi>{{ $schoolName }}</bdi></div>
                            <div class="title">{{ $L['title'] }}</div>
                            <div class="exam-name"><bdi>{{ $exam->exam_name }}</bdi></div>
                            <div class="rule"><span></span><i></i><span></span></div>
                        </div>
                        @if($schoolLogoUrl)
                            <div class="hdr-logo"><img src="{{ $schoolLogoUrl }}" alt="" crossorigin="anonymous"></div>
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

                    {{-- notes / stamp / signature --}}
                    <div class="foot">
                        <div class="notes">
                            @if($showNotes)
                                <h4>{{ $L['note'] }} :</h4>
                                <ul>
                                    <li>{{ $L['note_1'] }}</li>
                                    <li>{{ $L['note_2'] }}</li>
                                    <li>{{ $L['note_3'] }}</li>
                                </ul>
                            @endif
                            <div class="date-line">
                                <span>{{ $L['date'] }} :</span>
                                <span class="dots">.....</span> / <span class="dots">.....</span> / <span>{{ $d($exam->academic_year) }}</span>
                            </div>
                        </div>
                        <div class="sign">
                            @if($showStamp)
                                <div class="stamp">
                                    <div class="stamp-in">
                                        <span class="st l">★</span><span class="st r">★</span>
                                        <div class="sn"><bdi>{{ $stampName }}</bdi></div>
                                        <div class="sd">{{ $stampDate }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="sline"></div>
                            @endif
                            <div class="who">{{ $L['director'] }}</div>
                            @if(!empty($s->head_teacher))
                                <div class="who-name"><bdi>{{ $s->head_teacher }}</bdi></div>
                            @endif
                        </div>
                    </div>
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
