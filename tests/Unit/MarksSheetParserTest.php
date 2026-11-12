<?php

namespace Tests\Unit;

use App\Support\MarksSheetParser as P;
use PHPUnit\Framework\TestCase;

class MarksSheetParserTest extends TestCase
{
    /** A school's own marklist: subject name two rows above the "MRK" header, notes and a summary block around it. */
    private function clientSheet(): array
    {
        return [
            ['AL-HIJIRA PRIMARY SCHOOL'],
            ['CLASS TEACHER: TR. X ____CLASS/ STREAM: PRI FOUR EAST'],
            [null, 'SUBJECT', null, null, null, 'MID TERM III'],
            [null, 'TOTAL MARKS', null, null, null, 'ENG', null, 'MTC', null, 'TT', null],
            [],
            [null, 'FIRST NAME', 'MIDDLE NAME', 'LAST NAME', 'STR', 'MRK', 'Agg', 'MRK', 'Agg', 'MRK', 'AVG', '► First, list the names and assign IDs in the Names worksheet.'],
            [1, 'BAKAAKI', null, 'ABDALLAH', 'EAST', 84, '2', 72, '3', 156, 78],
            [2, 'NAIKOBA', 'ASMAH', 'SANYU', 'EAST', 70, '4', '', '', 70, 70],
            [3, 'NABIRYE', null, 'ZAINAB', 'EAST', 'abs', '', 55.5, '6', 55.5, 55.5],
            [4, 'WRONG', null, 'MARK', 'EAST', 140, '', 'x', '', null, null],
            [],
            [],
            [null, 'Class Avg:', null, null, null, 70],
        ];
    }

    public function test_it_finds_the_header_and_the_subject_columns(): void
    {
        $layout = P::detectLayout($this->clientSheet());

        $this->assertSame(5, $layout['header_row']);
        $this->assertSame(1, $layout['first']);
        $this->assertSame(3, $layout['last']);
        $this->assertSame('ENG', $layout['columns'][5]);
        $this->assertSame('MTC', $layout['columns'][7]);
        $this->assertArrayNotHasKey(6, $layout['columns']);    // "Agg"
        $this->assertArrayNotHasKey(11, $layout['columns']);   // the instruction note
    }

    public function test_the_pupil_list_ends_at_two_blank_rows_and_skips_summaries(): void
    {
        $sheet = $this->clientSheet();
        $rows = P::readRows($sheet, P::detectLayout($sheet));

        $this->assertCount(4, $rows);
        $this->assertSame('BAKAAKI', $rows[0]['first']);
        $this->assertSame(7, $rows[0]['line']);
    }

    public function test_a_pupil_is_matched_exactly_in_either_name_order_and_close_names_are_only_suggested(): void
    {
        $students = [
            ['id' => 1, 'firstname' => 'BAKAAKI', 'lastname' => 'ABDALLAH'],
            ['id' => 2, 'firstname' => 'SANYU', 'lastname' => 'NAIKOABA'],
            ['id' => 3, 'firstname' => 'NABIRYA', 'lastname' => 'ZAINAB'],
        ];
        $row = fn($f, $m, $l, $id = null) => ['id' => $id, 'first' => $f, 'middle' => $m, 'last' => $l];

        $this->assertSame(1, P::matchStudent($row('Bakaaki', '', 'Abdallah'), $students)['student']['id']);
        $this->assertSame(1, P::matchStudent($row('ABDALLAH', '', 'BAKAAKI'), $students)['student']['id']);
        $this->assertSame(3, P::matchStudent($row('X', '', 'Y', 3), $students)['student']['id']);

        $close = P::matchStudent($row('NABIRYE', '', 'ZAINAB'), $students);
        $this->assertSame('unmatched', $close['status']);       // never applied automatically
        $this->assertSame(3, $close['suggestions'][0]['id']);   // but offered
    }

    public function test_a_subject_column_is_matched_by_name_code_or_a_common_abbreviation(): void
    {
        $subjects = [
            ['key' => 10, 'name' => 'English', 'code' => 'ENG'],
            ['key' => 11, 'name' => 'Mathematics', 'code' => 'MTC'],
            ['key' => 12, 'name' => 'Integrated Science', 'code' => 'SCI'],
        ];

        $this->assertSame(10, P::matchSubject('english', $subjects));
        $this->assertSame(11, P::matchSubject('Maths', $subjects));
        $this->assertSame(12, P::matchSubject('SCI', $subjects));
        $this->assertNull(P::matchSubject('Luganda', $subjects));
    }

    public function test_cells_are_read_as_marks_absences_or_invalid(): void
    {
        $this->assertSame(['state' => 'ok', 'value' => 55.5, 'raw' => '55,5'], P::parseMark('55,5', 0, 100));
        $this->assertSame('ok', P::parseMark(0, 0, 100)['state']);          // a real zero is a mark
        $this->assertSame('blank', P::parseMark(null, 0, 100)['state']);
        $this->assertSame('absent', P::parseMark('ABS', 0, 100)['state']);
        $this->assertSame('invalid', P::parseMark(140, 0, 100)['state']);
        $this->assertSame('invalid', P::parseMark('x7', 0, 100)['state']);
        $this->assertSame('invalid', P::parseMark(4, 1, 3)['state']);       // outside an Early-Years 1-3 scale
    }
}
