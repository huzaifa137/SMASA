{{--
    ═══════════════════════════════════════════════════════════════════════
    PROGRESSIVE ASSESSMENT RECORD — shared partial
    ═══════════════════════════════════════════════════════════════════════
    Included by slip-classic.blade.php / slip-modern.blade.php /
    slip-minimal.blade.php. A transposed summary of every sitting the
    student has taken this term/year — one row per sitting (Practicals,
    Test 1, Test 2, Test 3, Final …), one column per subject, with an
    AVG / AGG / DIV per row — as opposed to the main Marks Table, which
    is one row per SUBJECT.

    Expects:
      $progressive — the array ExaminationController::
                      buildProgressiveAssessmentData() returns for this
                      student, i.e. ['examsList' => ..., 'subjectMarks'
                      => ..., 'examSummary' => ...], or null when there
                      was nothing to summarise.
      $cfg['section_progressive'] — whole-section master switch.

    Rendered inside the existing .rc-table-wrap / .marks-tbl CSS classes
    each theme's partial already styles, so no template-specific CSS is
    needed here — Classic/Modern/Minimal's own bordered/zebra/ledger
    table look is picked up automatically, same as the main Marks Table.
--}}
@if(($cfg['section_progressive'] ?? true) && $progressive)
    @php
        $paExams = $progressive['examsList'] ?? collect();
        $paSubjects = $progressive['subjectMarks'] ?? collect();
        $paSummary = collect($progressive['examSummary'] ?? []);

        // Short row label for each sitting. The exam type is chosen when
        // the examination is set up (Beginning of Term / Mid Term / End of
        // Term / Continuous Assessment), so the row shows its short code —
        // BOT / MOT / EOT / CA — exactly like the main multi-exam Marks
        // Table does, instead of a long "MID-TERM" heading that eats the
        // first column. Older/free-text types are matched loosely
        // (case/spacing/hyphens ignored); anything unrecognised falls back
        // to the exam's own name so no row is ever left blank.
        $paTypeCodes = [
            'beginningofterm' => 'BOT', 'bot' => 'BOT',
            'midterm' => 'MOT', 'mot' => 'MOT',
            'endofterm' => 'EOT', 'eot' => 'EOT',
            'continuousassessment' => 'CA', 'ca' => 'CA',
        ];
        $paBaseLabel = function ($ex) use ($paTypeCodes) {
            $type = trim((string) ($ex->exam_type ?? ''));
            $norm = strtolower(preg_replace('/[^a-z]/i', '', $type));
            if ($norm !== '' && isset($paTypeCodes[$norm])) {
                return $paTypeCodes[$norm];
            }
            return strtoupper($type !== '' ? $type : trim((string) $ex->exam_name));
        };

        // Two sittings of the same type (e.g. 3 Continuous Assessments)
        // would otherwise read "CA, CA, CA" — number them CA 1, CA 2 …
        $paBaseCounts = $paExams->map($paBaseLabel)->countBy();
        $paSeen = [];
        $paLabels = [];
        foreach ($paExams as $paE) {
            $base = $paBaseLabel($paE);
            $paSeen[$base] = ($paSeen[$base] ?? 0) + 1;
            $paLabels[$paE->id] = ($paBaseCounts[$base] ?? 1) > 1 ? $base . ' ' . $paSeen[$base] : $base;
        }
        $paLabel = fn($ex) => $paLabels[$ex->id] ?? $paBaseLabel($ex);

        // Column header for each subject. Subjects aren't given a short
        // code anywhere else in the system today, so this falls back to
        // the first 4 letters of the subject name (full name still
        // available as a tooltip) — schools that want exact abbreviations
        // like "L/UG" or "KISWA" can rename the subject accordingly.
        $paSubjCode = fn($name) => strtoupper(mb_substr(trim((string) $name), 0, 4));

        // FIT-TO-PAGE — the table always spans exactly the slip's width
        // (table-layout: fixed, no per-column min-widths), so a class with
        // many subjects can no longer push it past the right edge (and
        // get clipped by the slip's overflow: hidden). Instead the columns
        // share the width equally and the font steps down as the subject
        // count grows.
        $paSubjCount = max(1, $paSubjects->count());
        $paFont = $paSubjCount <= 6 ? '.68rem' : ($paSubjCount <= 9 ? '.6rem' : ($paSubjCount <= 12 ? '.54rem' : ($paSubjCount <= 16 ? '.48rem' : '.42rem')));
        $paLabelW = 17;   // % — ASSESSMENT label column
        $paStatW = 6;     // % — each of AVG / AGG / DIV
        $paSubjW = round((100 - $paLabelW - 3 * $paStatW) / $paSubjCount, 3);
    @endphp
    @if($paExams->isNotEmpty() && $paSubjects->isNotEmpty())
        <div class="rc-table-wrap pa-wrap" style="margin-top:.6rem;">
            <div class="perf-chart-title" style="margin:0 0 .4rem;">PROGRESSIVE ASSESSMENT RECORD</div>
            <style>
                .pa-wrap { max-width: 100%; overflow: hidden; box-sizing: border-box; }
                .marks-tbl.progressive-tbl { width: 100%; max-width: 100%; table-layout: fixed; }
                .marks-tbl.progressive-tbl th,
                .marks-tbl.progressive-tbl td {
                    padding: .28rem .12rem !important;
                    font-size: {{ $paFont }} !important;
                    letter-spacing: 0 !important;
                    text-align: center;
                    overflow: hidden;
                    word-break: break-word;
                }
                .marks-tbl.progressive-tbl th.tl,
                .marks-tbl.progressive-tbl td.tl { text-align: left; padding-left: .3rem !important; white-space: nowrap; }
                .marks-tbl.progressive-tbl .score-td { font-size: {{ $paFont }} !important; }
            </style>
            <table class="marks-tbl progressive-tbl">
                <colgroup>
                    <col style="width:{{ $paLabelW }}%;">
                    @foreach($paSubjects as $paSm)
                        <col style="width:{{ $paSubjW }}%;">
                    @endforeach
                    <col style="width:{{ $paStatW }}%;">
                    <col style="width:{{ $paStatW }}%;">
                    <col style="width:{{ $paStatW }}%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="tl">ASSESSMENT</th>
                        @foreach($paSubjects as $paSm)
                            <th title="{{ $paSm->subject_name }}">{{ $paSubjCode($paSm->subject_name) }}</th>
                        @endforeach
                        <th>AVG</th>
                        <th>AGG</th>
                        <th>DIV</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paExams as $paEx)
                        @php
                            $paRowVals = $paSubjects
                                ->map(fn($sm) => $sm->exams[$paEx->id]['marks_obtained'] ?? null)
                                ->filter(fn($v) => $v !== null);
                            $paRowAvg = $paRowVals->isNotEmpty() ? round($paRowVals->avg()) : null;
                            $paEsum = $paSummary->get($paEx->id);
                        @endphp
                        <tr>
                            <td class="tl" style="font-weight:600;" title="{{ $paEx->exam_name }}">{{ $paLabel($paEx) }}</td>
                            @foreach($paSubjects as $paSm)
                                @php $paEd = $paSm->exams[$paEx->id] ?? null; @endphp
                                <td class="score-td">
                                    @whole($paEd['marks_obtained'] ?? null)
                                </td>
                            @endforeach
                            <td class="num-td">@whole($paRowAvg ?? null)</td>
                            <td class="num-td">{{ $paEsum['aggregate'] ?? '—' }}</td>
                            <td class="num-td">{{ $paEsum['division'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif