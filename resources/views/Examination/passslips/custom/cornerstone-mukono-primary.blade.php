{{--
@report-card
name: Cornerstone Junior School - Mukono (Primary)
level: primary
description: Cornerstone Junior School Mukono Campus termly report card - blue framed A4 with Performance Record and
Progressive Assessment Record.
accent: #1d3da8
progressive: true
major_first: true
toggles: show_logo, show_photo, show_motto, show_remarks, show_signatures, show_section_progressive, show_term_ends_on,
show_next_term_starts_on
--}}
@php
    // ── Cornerstone-specific constants (edit here, nothing else needs touching) ──
    // Label printed before the head teacher's name in the signature column.
    $headLabel = 'HM:';
    // Students whose registration number doesn't carry DAY / BOARDING fall back to this.
    $defaultSection = 'DAY';
    // Centred line printed at the very bottom of every card.
    $footerText = 'CORNERSTONE JUNIOR SCHOOL MUKONO CAMPUS';

    // Column heads for the Progressive Assessment table. Anything not listed
    // falls back to the first 4 letters of the subject name.
    $subjectCodes = [
        'ENGLISH' => 'ENG',
        'MATHEMATICS' => 'MATH',
        'MATHS' => 'MATH',
        'SCIENCE' => 'SCI',
        'SOCIAL STUDIES' => 'SST',
        'RELIGIOUS EDUCATION' => 'RE',
        'ICT' => 'ICT',
        'LUGANDA' => 'LUG',
        'KISWAHILI' => 'KISWA',
        'WRITING' => 'WRI',
        'READING' => 'READ',
    ];
    $codeFor = fn($subject) => $subjectCodes[strtoupper(trim($subject->name))] ?? $subject->code;

    // Short forms printed in the ASSESSMENT column of the Progressive Assessment
    // Record instead of the full exam name. The builder already produces
    // BOT / MOT / EOT / CA (numbered CA 1, CA 2 ... when repeated); list a
    // replacement here to print something different, e.g. MOT => 'MID'.
    $assessmentCodes = ['MOT' => 'MID'];
    $assessmentLabel = function ($row) use ($assessmentCodes) {
        $label = trim((string) $row->label);
        // keep any trailing number: "CA 2" -> base "CA", suffix " 2"
        if (preg_match('/^(\D+?)(\s+\d+)?$/', $label, $m)) {
            return ($assessmentCodes[$m[1]] ?? $m[1]) . ($m[2] ?? '');
        }
        return $label;
    };
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $exam->exam_name }} — Cornerstone Report Cards</title>
    @include('Examination.passslips.custom._base-css')
    <style>
        .crc-sheet {
            background: #fff
        }

        .cs {
            --ac: #1d3da8;
            --ac-dark: #14307f;
            --tint: #e9f0fb;
            padding: 5mm;
            height:
                {{ $page->pageH }}
            ;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ac);
            background: linear-gradient(180deg, #f7faff 0%, #eef4fd 100%)
        }

        .cs-frame {
            height: 100%;
            border: 2.2px solid var(--ac);
            padding: 5mm 6mm 4mm;
            display: flex;
            flex-direction: column;
            position: relative
        }

        /* header */
        .cs-head {
            display: grid;
            grid-template-columns: 32mm 1fr 36mm;
            gap: 3mm;
            align-items: start
        }

        .cs-logo {
            width: 34mm;
            height: 34mm;
            object-fit: contain
        }

        .cs-center {
            text-align: center
        }

        .cs-center h1 {
            margin: 0;
            font-size: 22.5px;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: .1px;
            text-transform: uppercase;
            white-space: nowrap
        }

        .cs-center h1 small {
            display: block;
            font-size: 22.5px
        }

        .cs-center .addr {
            margin-top: 1.5mm;
            font-size: 15.5px;
            line-height: 1.3;
            color: #1b2340
        }

        .cs-center .addr div {
            white-space: nowrap
        }

        .cs-center .motto {
            margin-top: 1mm;
            font: italic 17px Georgia, "Times New Roman", serif;
            color: #1b2340
        }

        .cs-pill {
            display: inline-block;
            margin-top: 1.5mm;
            background: var(--ac-dark);
            color: #fff;
            border-radius: 7px;
            padding: 4px 22px;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: .4px
        }

        .cs-photo {
            width: 36mm;
            height: 43mm;
            object-fit: cover;
            border: 2px solid var(--ac);
            display: block;
            margin-left: auto
        }

        .cs-photo.ph {
            background: #dbe7f7
        }

        /* learner block */
        .cs-rule {
            border: 0;
            border-top: 1.4px solid var(--ac);
            margin: 3mm 0
        }

        .cs-info {
            display: grid;
            grid-template-columns: 1.7fr .95fr 1.15fr;
            gap: 2mm 4mm;
            font-size: 15px;
            padding: 0 1mm
        }

        .cs-info div {
            white-space: nowrap
        }

        .cs-info b {
            display: inline-block;
            min-width: 17mm;
            font-weight: 800;
            margin-right: 2mm
        }

        .cs-info .c2 b {
            min-width: 19mm
        }

        .cs-info .c3 b {
            min-width: 0;
            margin-right: 3mm
        }

        .cs-h {
            margin: 1.5mm 0 2mm;
            text-align: center;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: .2px
        }

        /* performance table */
        table.cs-t {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid var(--ac)
        }

        table.cs-t th {
            background: var(--tint);
            font-size: 15px;
            font-weight: 800;
            text-align: left;
            padding: 4px 8px;
            border: 1.4px solid var(--ac)
        }

        table.cs-t td {
            font-size: 15px;
            padding: 2.6px 8px;
            border: 1.4px solid var(--ac)
        }

        table.cs-t .ct {
            text-align: center
        }

        table.cs-t td.subj {
            text-transform: uppercase
        }

        table.cs-t tr.major td {
            font-weight: 800
        }

        .cs-tot {
            display: flex;
            justify-content: space-between;
            margin-top: 3mm;
            padding: 2.5mm 7mm;
            font-size: 15px;
            border-top: 1px dashed #555;
            border-bottom: 1px dashed #555
        }

        .cs-tot span {
            font-weight: 400;
            margin-right: 3mm
        }

        .cs-tot b {
            font-weight: 800
        }

        /* progressive */
        .cs-pa-t {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid var(--ac);
            table-layout: fixed
        }

        .cs-pa-t th {
            background: var(--tint);
            font-size: 11.5px;
            font-weight: 800;
            padding: 3px 1px;
            text-align: center;
            border: 1.4px solid var(--ac)
        }

        .cs-pa-t td {
            font-size: 12.5px;
            padding: 3px 0;
            text-align: center;
            border: 1px solid var(--ac);
            height: 8mm
        }

        .cs-pa-t td.pts {
            width: 5mm;
            font-size: 11px
        }

        .cs-pa-t th.lbl,
        .cs-pa-t td.lbl {
            text-align: left;
            padding-left: 5px;
            width: 28mm;
            font-size: 11.5px;
            white-space: nowrap
        }

        .cs-pa-t td.lbl {
            font-weight: 600;
            text-transform: uppercase
        }

        /* comments (left) + names/signatures (right) */
        .cs-cmt {
            display: grid;
            grid-template-columns: 1fr 41mm;
            column-gap: 6mm;
            margin-top: 4mm;
            font-size: 15px;
            align-items: start
        }

        .cs-cmt .row {
            display: contents
        }

        .cs-cmt .c {
            line-height: 1.75;
            padding-bottom: 2mm
        }

        .cs-cmt .c b {
            font-weight: 800;
            margin-right: 2mm;
            color: #111
        }

        .cs-cmt .c u {
            color: #2a47a8;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 4px
        }

        .cs-cmt .g {
            padding-top: 1.5mm;
            padding-bottom: 2mm;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1.3;
            color: #111;
            display: flex;
            flex-direction: column;
            align-items: flex-start
        }

        .cs-cmt .g img {
            display: block;
            max-height: 12mm;
            max-width: 40mm;
            margin-top: 0;
            align-self: flex-end
        }

        /* footer: term dates left / right, school name centred */

        .cs-foot {
            margin-top: auto;
            padding-top: 0;
            /* was 3mm — removes the extra gap above the border line */
        }

        .cs-dates {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* keeps both date lines vertically centered on their row */
            font-size: 14px;
            color: #111;
            padding: 1.5mm 1mm;
            /* was 2mm — slightly tighter top/bottom inside the row */
            border-top: 1.2px solid var(--ac);
        }

        .cs-dates b {
            font-weight: 800;
            margin-right: 1.5mm
        }

        .cs-brand {
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: var(--ac);
            padding-top: 1mm
        }

        /* push each teacher's name + signature to the right edge of the column */
.cs-cmt .g {
    align-items: flex-end;   /* was flex-start: short names stopped early */
    text-align: right;       /* also right-aligns the name if it wraps */
}
    </style>
</head>

<body>
    @unless($embed) @include('Examination.passslips.custom._toolbar') @endunless

    @foreach($reports as $r)
        @php
            $nameParts = preg_split('/\s+[-–—]\s+/u', (string) $r->school->name, 2);
            $classLine = trim($r->student->class_name . ' ' . $r->student->stream);
            $section = $r->student->section ?: $defaultSection;
            $showPa = $r->on('show_section_progressive') && $r->progressive;
            $paRows = $showPa ? collect($r->progressive->rows)->reject(fn($row) => $row->is_current)->values() : collect();
            $avgMark = $r->summary->average_mark;
        @endphp
        <div class="crc-sheet">
            <div class="cs" style="--ac: {{ $r->accent }}">
                <div class="cs-frame">

                    {{-- ── Letterhead ── --}}
                    <div class="cs-head">
                        <div>@if($r->on('show_logo') && $r->school->logo_url)<img class="cs-logo"
                        src="{{ $r->school->logo_url }}" alt="">@endif</div>
                        <div class="cs-center">
                            <h1>{{ $nameParts[0] }}@if(!empty($nameParts[1]))<small>{{ $nameParts[1] }}</small>@endif</h1>
                            <div class="addr">
                                @if($r->school->location)
                                <div>{{ $r->school->location }}</div>@endif
                                @if($r->school->phone)
                                <div>Tel: {{ $r->school->phone }}</div>@endif
                                @if($r->school->email)
                                <div>Email: {{ $r->school->email }}</div>@endif
                                @if($r->school->website)
                                <div>Website: {{ $r->school->website }}</div>@endif
                            </div>
                            @if($r->on('show_motto') && $r->school->motto)
                                <div class="motto">&quot;{{ mb_convert_case($r->school->motto, MB_CASE_TITLE, 'UTF-8') }}&quot;
                            </div>@endif
                            <div class="cs-pill">TERMLY REPORT CARD</div>
                        </div>
                        <div>
                            @if($r->on('show_photo'))
                                @if($r->student->photo_url)<img class="cs-photo" src="{{ $r->student->photo_url }}" alt="">@else
                                <div class="cs-photo ph"></div>@endif
                            @endif
                        </div>
                    </div>

                    <hr class="cs-rule">

                    {{-- ── Learner ── --}}
                    <div class="cs-info">
                        <div><b>NAME:</b>{{ strtoupper($r->student->full_name) }}</div>
                        <div class="c2"><b>CLASS:</b>{{ strtoupper($classLine) }}</div>
                        <div class="c3"><b>SCHOOL PAY:</b>{{ $r->student->school_pay }}</div>
                        <div><b>TERM:</b>{{ $r->exam->term_roman ?: $r->exam->term_label }} - {{ $r->exam->academic_year }}
                        </div>
                        <div class="c2"><b>SECTION:</b>{{ $section }}</div>
                        <div class="c3"><b>LIN:</b>{{ $r->student->lin }}</div>
                    </div>

                    <hr class="cs-rule">

                    {{-- ── Performance record ── --}}
                    <div class="cs-h">PERFORMANCE RECORD</div>
                    <table class="cs-t">
                        <thead>
                            <tr>
                                <th style="width:36%">SUBJECT</th>
                                <th class="ct" style="width:12%">E.O.T</th>
                                <th class="ct" style="width:12%">GRADE</th>
                                <th style="width:27%">REMARK</th>
                                <th style="width:13%">INITIALS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($r->subjects as $s)
                                <tr class="{{ $s->is_major ? 'major' : '' }}">
                                    <td class="subj">{{ $s->name }}</td>
                                    <td class="ct">{{ $s->marks_display }}</td>
                                    <td class="ct">{{ $s->grade }}</td>
                                    <td>{{ $s->remark }}</td>
                                    <td>{{ $s->initials }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="cs-tot">
                        <div><span>TOTAL:</span><b>{{ $r->summary->total_obtained_display }}</b></div>
                        <div><span>AVERAGE:</span><b>{{ $avgMark !== null ? round($avgMark) : '—' }}</b></div>
                        <div><span>AGGREGATE:</span><b>{{ $r->summary->aggregate ?? '—' }}</b></div>
                        <div><span>DIVISION:</span><b>{{ $r->summary->division_short }}</b></div>
                    </div>

                    {{-- ── Progressive assessment record ── --}}
                    @if($paRows->isNotEmpty())
                        <div class="cs-h" style="margin-top:2.5mm">PROGRESSIVE ASSESSMENT RECORD</div>
                        <table class="cs-pa-t">
                            <thead>
                                <tr>
                                    <th class="lbl">ASSESSMENT</th>
                                    @foreach($r->progressive->subjects as $ps)
                                    <th colspan="2">{{ $codeFor($ps) }}</th>@endforeach
                                    <th colspan="2">AVG</th>
                                    <th style="width:9mm">AGG</th>
                                    <th style="width:9mm">DIV</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paRows as $row)
                                    <tr>
                                        <td class="lbl" title="{{ $row->name }}">{{ $assessmentLabel($row) }}</td>
                                        @foreach($row->cells as $cell)
                                            <td>{{ $cell->marks !== null ? (int) round($cell->marks) : '—' }}</td>
                                            <td class="pts">{{ $cell->points ?? '' }}</td>
                                        @endforeach
                                        <td>{{ $row->avg ?? '—' }}</td>
                                        <td class="pts">{{ $row->avg_points ?? '' }}</td>
                                        <td>{{ $row->agg ?? '—' }}</td>
                                        <td>{{ $row->div_short }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    {{-- ── Comments (left) with each teacher's name + signature (right) ── --}}
                    @if($r->on('show_remarks') || $r->on('show_signatures'))
                        <div class="cs-cmt">
                            <div class="c">@if($r->on('show_remarks'))<b>Class teacher's
                            comment:</b><u>{{ $r->remarks->class_teacher->remark ?: '—' }}</u>@endif</div>
                            <div class="g">
                                @if($r->on('show_signatures'))
                                    {{ $r->remarks->class_teacher->name }}
                                    @if($r->remarks->class_teacher->signature_url)<img
                                    src="{{ $r->remarks->class_teacher->signature_url }}" alt="">@endif
                                @endif
                            </div>

                            <div class="c">@if($r->on('show_remarks'))<b>Headteacher's
                            comment:</b><u>{{ $r->remarks->head_teacher->remark ?: '—' }}</u>@endif</div>
                            <div class="g">
                                @if($r->on('show_signatures'))
                                    @if($r->remarks->head_teacher->name){{ $headLabel }} {{ $r->remarks->head_teacher->name }}@endif
                                    @if($r->remarks->head_teacher->signature_url)<img
                                    src="{{ $r->remarks->head_teacher->signature_url }}" alt="">@endif
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ── Footer: term dates + school name ── --}}
                    <div class="cs-foot">
                        @if($r->on('show_term_ends_on') || $r->on('show_next_term_starts_on'))
                            <div class="cs-dates">
                                <div>@if($r->on('show_term_ends_on'))<b>Term Ended on
                                :</b>{{ $r->term_dates->ends_on ?? '—' }}@endif</div>
                                <div>@if($r->on('show_next_term_starts_on'))<b>Next Term Begins
                                :</b>{{ $r->term_dates->next_starts_on ?? '—' }}@endif</div>
                            </div>
                        @endif
                        <!-- <div class="cs-brand">{{ $footerText }}</div> -->
                    </div>

                </div>
            </div>
        </div>
    @endforeach

    @include('Examination.passslips.custom._runtime')
</body>

</html>