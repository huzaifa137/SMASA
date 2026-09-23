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

        // Short row label for each sitting — e.g. "Beginning of Term"
        // exam_type stored on the exam becomes "BEGINNING OF TERM";
        // falls back to the exam name if exam_type is blank.
        $paLabel = fn($ex) => strtoupper(trim($ex->exam_type ?: $ex->exam_name));

        // Column header for each subject. Subjects aren't given a short
        // code anywhere else in the system today, so this falls back to
        // the first 4 letters of the subject name (full name still
        // available as a tooltip) — schools that want exact abbreviations
        // like "L/UG" or "KISWA" can rename the subject accordingly.
        $paSubjCode = fn($name) => strtoupper(mb_substr(trim((string) $name), 0, 4));
    @endphp
    @if($paExams->isNotEmpty() && $paSubjects->isNotEmpty())
        <div class="rc-table-wrap" style="margin-top:.6rem;">
            <div class="perf-chart-title" style="margin:0 0 .4rem;">PROGRESSIVE ASSESSMENT RECORD</div>
            <table class="marks-tbl progressive-tbl">
                <thead>
                    <tr>
                        <th class="tl" style="min-width:90px;">ASSESSMENT</th>
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
                            <td class="tl" style="font-weight:600;">{{ $paLabel($paEx) }}</td>
                            @foreach($paSubjects as $paSm)
                                @php $paEd = $paSm->exams[$paEx->id] ?? null; @endphp
                                <td class="score-td">
                                    {{ $paEd && $paEd['marks_obtained'] !== null ? $paEd['marks_obtained'] : '—' }}
                                </td>
                            @endforeach
                            <td class="num-td">{{ $paRowAvg ?? '—' }}</td>
                            <td class="num-td">{{ $paEsum['aggregate'] ?? '—' }}</td>
                            <td class="num-td">{{ $paEsum['division'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif
