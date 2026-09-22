<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\FormatsReportSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * "Cumulative Overview" sheet — every subject's cumulative average (across
 * the exams the user selected on the picker), one row per student, plus
 * the resulting overall cumulative average, grade and class rank.
 *
 * This mirrors the class-summary style subject x student matrix, but
 * every cell is already an AVERAGE across however many exams were
 * selected rather than a single exam's mark — the per-exam marks
 * themselves live on the companion "Subject Detail" sheet.
 *
 * Built from a manual array() (like the other report exports) rather
 * than WithHeadings, so the same school-name / report-title / class
 * heading block the PDF prints above its table can sit above the
 * column headers here too.
 */
class CumulativeOverviewSheet implements FromArray, WithTitle, WithStyles
{
    use FormatsReportSheet;

    protected array $data;
    protected $selectedExams;
    protected string $schoolName;
    protected string $academicYear;
    protected string $generatedAt;

    /** Row the column-header line actually lands on — computed from the
     *  array as it's built rather than hardcoded. Set by array(). */
    protected int $headerRow = 0;

    public function __construct(array $data, $selectedExams, string $schoolName, string $academicYear, string $generatedAt)
    {
        $this->data = $data;
        $this->selectedExams = $selectedExams;
        $this->schoolName = $schoolName;
        $this->academicYear = $academicYear;
        $this->generatedAt = $generatedAt;
    }

    public function array(): array
    {
        $examsLine = $this->selectedExams->map(
            fn($e) => str_replace('-', ' ', $e->exam_type) . ' (' . $e->term . ')'
        )->implode(' | ');

        $rows = $this->buildHeadingRows([
            $this->schoolName,
            'Cumulative Performance Analysis — ' . $this->academicYear,
            'Includes: ' . $examsLine,
            'Class: ' . $this->data['className'] . ' — ' . $this->data['streamLabel']
                . '  |  Students: ' . $this->data['report']->count()
                . '  |  Cumulative Class Average: '
                . ($this->data['classCumulativeAverage'] !== null ? $this->data['classCumulativeAverage'] . '%' : '—'),
        ]);

        $rows[] = $this->blankRow();

        $headers = ['#', 'Student', 'Admission No.', 'Gender'];
        foreach ($this->data['subjects'] as $subject) {
            $headers[] = $subject->report_name . ' — Avg %';
        }
        $headers[] = 'Cumulative Avg %';
        $headers[] = 'Grade';
        $headers[] = 'Rank';
        $rows[] = $headers;
        $this->headerRow = count($rows);

        foreach ($this->data['report'] as $i => $row) {
            $line = [
                $i + 1,
                trim($row->student->firstname . ' ' . $row->student->lastname),
                $row->student->admission_number ?? '',
                $row->student->gender ?? '',
            ];

            foreach ($this->data['subjects'] as $subject) {
                $avg = $row->subjectAverages[$subject->report_key]->average ?? null;
                $line[] = $avg ?? '—';
            }

            $line[] = $row->cumulativeAverage ?? '—';
            $line[] = $row->grade;
            $line[] = $row->rank ?? '—';

            $rows[] = $line;
        }

        $rows[] = $this->blankRow();
        $rows[] = ['Generated on ' . $this->generatedAt . ' — SMASA'];

        return $rows;
    }

    public function title(): string
    {
        return 'Cumulative Overview';
    }

    public function styles(Worksheet $sheet)
    {
        return $this->applyReportHeaderStyles($sheet, 4, $this->headerRow);
    }
}