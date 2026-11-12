<?php

namespace App\Support;

/**
 * Pure helpers (no database, no framework) for reading a marks spreadsheet:
 *   - finds the header row and the name / ID / subject columns, whether the
 *     sheet is the SMASA template or a school's own marklist layout
 *     (subject name on one row, "MRK" / "Agg" underneath),
 *   - reads the pupil rows,
 *   - matches a row to a pupil of the class,
 *   - turns a cell into a mark.
 *
 * Everything takes and returns plain arrays so it can be unit-tested with
 * nothing but PHP (see tests/Unit/MarksSheetParserTest.php).
 */
class MarksSheetParser
{
    /** Header cells that are never a subject. */
    private const IGNORED = [
        'STR', 'STREAM', 'AGG', 'AGGREGATE', 'TT', 'TOTAL', 'TOTALMARKS', 'AVG', 'AVERAGE', 'GD', 'GRADE', 'PST', 'POS',
        'POSITION', 'DIV', 'DIVISION', 'REMARK', 'REMARKS', 'COMMENT', 'COMMENTS', 'GENDER', 'SEX', 'NO', 'SN', 'S', 'NUMBER',
        'MIDDLENAME', 'MIDDLE', 'FIRSTNAME', 'LASTNAME', 'SURNAME', 'NAME', 'STUDENTNAME', 'FULLNAME', 'STUDENTID', 'ID',
    ];

    /** Header cells that mean "the mark for the subject named above this cell". */
    private const MARK_WORDS = ['MRK', 'MRKS', 'MARK', 'MARKS', 'SCORE', 'SCORES'];

    /** A "name" that is really a summary line under the list. */
    private const SUMMARY_ROWS = ['CLASSAVG', 'CLASSAVERAGE', 'AVERAGE', 'CLASSMEAN', 'MEAN', 'TOTAL', 'TOTALS', 'ANALYSIS', 'SUBJECT', 'DISPLAYID'];

    /** Cell contents that mean "did not sit", not "zero". */
    private const ABSENT = ['', '-', '--', 'X', 'AB', 'ABS', 'ABSENT', 'NA', 'N/A', 'NIL'];

    /** Upper-case letters and digits only, so "Kibwika  Adam" == "KIBWIKA ADAM" == "kibwika-adam". */
    public static function norm($value): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $value)));
    }

    /**
     * Locate the header row and columns.
     *
     * @param  array $rows  2-D array of cell values (row-major), 0-indexed.
     * @return array|null   ['header_row'=>int, 'id'=>?int, 'first'=>?int, 'middle'=>?int, 'last'=>?int, 'name'=>?int,
     *                       'columns'=>[colIndex => label]]   (null when no header row is found)
     */
    public static function detectLayout(array $rows): ?array
    {
        $limit = min(count($rows), 40);

        for ($r = 0; $r < $limit; $r++) {
            $cells = array_map([self::class, 'norm'], array_values($rows[$r]));
            $first = self::firstIndex($cells, ['FIRSTNAME', 'FIRST', 'FNAME', 'GIVENNAME', 'GIVENNAMES']);
            $last = self::firstIndex($cells, ['LASTNAME', 'SURNAME', 'LAST', 'LNAME', 'SECONDNAME']);
            $name = self::firstIndex($cells, ['NAME', 'STUDENTNAME', 'FULLNAME', 'PUPILNAME', 'LEARNERNAME', 'NAMES']);
            $id = self::firstIndex($cells, ['STUDENTID', 'SYSTEMID', 'ID', 'PUPILID']);

            $hasName = ($first !== null && $last !== null) || $name !== null || ($first !== null) || ($last !== null);
            if (!$hasName) {
                continue;
            }

            $middle = self::firstIndex($cells, ['MIDDLENAME', 'MIDDLE', 'OTHERNAMES', 'OTHERNAME']);
            $nameCols = array_filter([$id, $first, $middle, $last, $name], fn($v) => $v !== null);

            $columns = [];
            foreach ($cells as $c => $cell) {
                if (in_array($c, $nameCols, true)) {
                    continue;
                }
                $label = null;
                $headerText = trim((string) $rows[$r][$c]);
                if (strlen($headerText) > 40) {
                    continue;                   // an instruction / note cell, not a subject
                }
                if ($cell !== '' && (in_array($cell, self::MARK_WORDS, true) || ctype_digit($cell))) {
                    // "MRK" under a subject name: the subject is written above, on the nearest filled cell
                    for ($up = $r - 1; $up >= max(0, $r - 4); $up--) {
                        $above = trim((string) ($rows[$up][$c] ?? ''));
                        if ($above !== '') {
                            $label = $above;
                            break;
                        }
                    }
                } elseif ($cell !== '' && !in_array($cell, self::IGNORED, true)) {
                    $label = trim((string) $rows[$r][$c]);
                }
                if ($label !== null && !in_array(self::norm($label), self::IGNORED, true)) {
                    $columns[$c] = $label;
                }
            }

            if ($columns) {
                return ['header_row' => $r, 'id' => $id, 'first' => $first, 'middle' => $middle, 'last' => $last, 'name' => $name, 'columns' => $columns];
            }
        }

        return null;
    }

    /**
     * Pupil rows below the header.
     *
     * @return array list of ['line'=>int (1-based sheet row), 'id'=>?int, 'first'=>string, 'middle'=>string, 'last'=>string, 'cells'=>[col=>raw]]
     */
    public static function readRows(array $rows, array $layout): array
    {
        $out = [];
        $blankRun = 0;
        for ($r = $layout['header_row'] + 1; $r < count($rows); $r++) {
            $row = $rows[$r];
            $cell = fn($i) => $i === null ? '' : trim((string) ($row[$i] ?? ''));

            $first = $cell($layout['first']);
            $middle = $cell($layout['middle']);
            $last = $cell($layout['last']);
            if ($layout['name'] !== null && $first === '' && $last === '') {
                $last = $cell($layout['name']);
            }
            if ($first === '' && $last === '') {
                // A blank line is skipped; two in a row after the list has started mean the list is over
                // (what follows is usually a class-average / analysis block, never pupils).
                if ($out && ++$blankRun >= 2) {
                    break;
                }
                continue;
            }
            $blankRun = 0;
            if (in_array(self::norm($first . $middle . $last), self::SUMMARY_ROWS, true)) {
                continue;
            }

            $idRaw = $cell($layout['id']);
            $cells = [];
            foreach ($layout['columns'] as $c => $_) {
                $cells[$c] = $row[$c] ?? null;
            }

            $out[] = [
                'line' => $r + 1,
                'id' => ctype_digit($idRaw) ? (int) $idRaw : null,
                'first' => $first, 'middle' => $middle, 'last' => $last,
                'cells' => $cells,
            ];
        }

        return $out;
    }

    /**
     * Find the pupil a sheet row refers to. Exact matches only (in either name order, with or without the
     * middle name) - a "close" name is reported as a suggestion, never applied.
     *
     * @param array $students list of ['id'=>int,'firstname'=>string,'lastname'=>string]
     * @return array ['status'=>'ok'|'ambiguous'|'unmatched', 'student'=>?array, 'candidates'=>[ids], 'suggestions'=>[students]]
     */
    public static function matchStudent(array $row, array $students): array
    {
        if ($row['id'] !== null) {
            foreach ($students as $s) {
                if ((int) $s['id'] === $row['id']) {
                    return ['status' => 'ok', 'student' => $s, 'candidates' => [$s['id']], 'suggestions' => []];
                }
            }
            return ['status' => 'unmatched', 'student' => null, 'candidates' => [], 'suggestions' => []];
        }

        $keys = array_unique(array_filter([
            self::norm($row['first'] . $row['last']),
            self::norm($row['last'] . $row['first']),
            self::norm($row['first'] . $row['middle'] . $row['last']),
            self::norm($row['last'] . $row['middle'] . $row['first']),
            self::norm($row['middle'] . $row['first'] . $row['last']),
            self::norm($row['first'] . $row['last'] . $row['middle']),
        ]));

        $hits = [];
        foreach ($students as $s) {
            $a = self::norm($s['firstname'] . $s['lastname']);
            $b = self::norm($s['lastname'] . $s['firstname']);
            if (in_array($a, $keys, true) || in_array($b, $keys, true)) {
                $hits[] = $s;
            }
        }

        if (count($hits) === 1) {
            return ['status' => 'ok', 'student' => $hits[0], 'candidates' => [$hits[0]['id']], 'suggestions' => []];
        }
        if (count($hits) > 1) {
            return ['status' => 'ambiguous', 'student' => null, 'candidates' => array_column($hits, 'id'), 'suggestions' => $hits];
        }

        $probe = self::norm($row['first'] . $row['last']);
        $scored = [];
        foreach ($students as $s) {
            similar_text($probe, self::norm($s['firstname'] . $s['lastname']), $p1);
            similar_text($probe, self::norm($s['lastname'] . $s['firstname']), $p2);
            $p = max($p1, $p2);
            if ($p >= 75) {
                $scored[] = [$p, $s];
            }
        }
        usort($scored, fn($x, $y) => $y[0] <=> $x[0]);

        return ['status' => 'unmatched', 'student' => null, 'candidates' => [], 'suggestions' => array_map(fn($x) => $x[1], array_slice($scored, 0, 2))];
    }

    /**
     * Guess which class subject a sheet column is for.
     *
     * @param array $subjects list of ['key'=>mixed,'name'=>string,'code'=>?string]
     * @return mixed|null the matching subject's key
     */
    public static function matchSubject(string $label, array $subjects)
    {
        $l = self::norm($label);
        if ($l === '') {
            return null;
        }
        foreach ($subjects as $s) {
            $variants = [self::norm($s['name']), self::norm($s['code'] ?? ''), self::norm($s['name'] . ($s['code'] ?? '')), self::norm($s['name'] . '(' . ($s['code'] ?? '') . ')')];
            if (in_array($l, array_filter($variants), true)) {
                return $s['key'];
            }
        }
        // common school abbreviations
        static $aliases = [
            'MATHS' => 'MATHEMATICS', 'MATH' => 'MATHEMATICS', 'MTC' => 'MATHEMATICS', 'ENG' => 'ENGLISH', 'SCI' => 'INTEGRATEDSCIENCE',
            'SCIENCE' => 'INTEGRATEDSCIENCE', 'SST' => 'SOCIALSTUDIES', 'LITI' => 'LITERACYI', 'LITII' => 'LITERACYII', 'LIT1' => 'LITERACYI',
            'LIT2' => 'LITERACYII', 'LUG' => 'LUGANDA', 'RE' => 'RELIGIOUSEDUCATION', 'CRE' => 'RELIGIOUSEDUCATION', 'IRE' => 'RELIGIOUSEDUCATION',
        ];
        if (isset($aliases[$l])) {
            foreach ($subjects as $s) {
                if (self::norm($s['name']) === $aliases[$l]) {
                    return $s['key'];
                }
            }
        }

        return null;
    }

    /**
     * Interpret one cell.
     *
     * @return array ['state'=>'blank'|'absent'|'ok'|'invalid', 'value'=>?float, 'raw'=>string]
     */
    public static function parseMark($raw, float $min, float $max): array
    {
        $text = trim((string) $raw);
        if ($raw === null || $text === '') {
            return ['state' => 'blank', 'value' => null, 'raw' => ''];
        }
        if (in_array(strtoupper($text), self::ABSENT, true)) {
            return ['state' => 'absent', 'value' => null, 'raw' => $text];
        }
        $num = str_replace(',', '.', $text);
        if (!is_numeric($num)) {
            return ['state' => 'invalid', 'value' => null, 'raw' => $text];
        }
        $v = (float) $num;
        if ($v < $min || $v > $max) {
            return ['state' => 'invalid', 'value' => null, 'raw' => $text];
        }

        return ['state' => 'ok', 'value' => round($v, 2), 'raw' => $text];
    }

    private static function firstIndex(array $normCells, array $wanted): ?int
    {
        foreach ($normCells as $i => $c) {
            if ($c !== '' && in_array($c, $wanted, true)) {
                return $i;
            }
        }

        return null;
    }
}
