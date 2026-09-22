<?php

namespace App\Exports;

use App\Exports\Concerns\FormatsReportSheet;
use App\Models\Examination;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel version of the Class Performance Summary PDF (subject x student
 * matrix) — same $data shape buildClassSummary() hands the Blade view
 * and the PDF, so all three are guaranteed to agree, with the same
 * school-name / report-title / class-and-term heading block the PDF
 * prints above its table.
 */
class ClassSummaryReportExport implements FromArray, WithTitle, WithStyles
{
    use FormatsReportSheet;

    protected array $data;
    protected Examination $exam;
    protected string $schoolName;
    protected string $generatedAt;

    /** Row the column-header line actually lands on — computed from the
     *  array as it's built rather than hardcoded, so it can't drift out
     *  of sync with the heading block above it. Set by array(). */
    protected int $headerRow = 0;

    public function __construct(array $data, Examination $exam, string $schoolName, string $generatedAt)
    {
        $this->data = $data;
        $this->exam = $exam;
        $this->schoolName = $schoolName;
        $this->generatedAt = $generatedAt;
    }

    public function array(): array
    {
        $rows = $this->buildHeadingRows([
            $this->schoolName,
            'Class Performance Summary — ' . $this->exam->exam_name . ' (' . $this->exam->exam_code . ')',
            $this->exam->term . ' • ' . $this->exam->academic_year
                . '  |  Class: ' . $this->data['className'] . ' — ' . $this->data['streamLabel']
                . '  |  Students: ' . $this->data['report']->count()
                . '  |  Class Average: ' . $this->data['classAverage'] . '%',
        ]);

        $rows[] = $this->blankRow();

        $headers = ['#', 'Student', 'Admission No.', 'Gender'];
        foreach ($this->data['subjects'] as $subject) {
            $headers[] = $subject->report_name;
        }
        $headers[] = 'Total';
        $headers[] = 'Avg %';
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
                $cell = $row->cells[$subject->report_key] ?? null;
                $line[] = $cell ? $cell->marks . '/' . $cell->total : '—';
            }

            $line[] = $row->total_obtained . '/' . $row->total_max;
            $line[] = $row->average . '%';
            $line[] = $row->grade;
            $line[] = $row->rank ?? '—';

            $rows[] = $line;
        }

        $footer = ['', 'Subject Average', '', ''];
        foreach ($this->data['subjects'] as $subject) {
            $avg = $this->data['subjectAverages'][$subject->report_key] ?? null;
            $footer[] = ($avg && $avg['average'] !== null) ? $avg['average'] . '%' : '—';
        }
        $footer = array_merge($footer, ['', '', '', '']);
        $rows[] = $footer;

        $rows[] = $this->blankRow();
        $rows[] = ['Generated on ' . $this->generatedAt . ' — SMASA'];

        return $rows;
    }

    public function title(): string
    {
        return 'Class Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return $this->applyReportHeaderStyles($sheet, 3, $this->headerRow);
    }
}