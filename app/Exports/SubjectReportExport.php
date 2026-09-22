<?php

namespace App\Exports;

use App\Exports\Concerns\FormatsReportSheet;
use App\Models\Examination;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel version of the Subject Performance Report PDF — same $data
 * shape buildSubjectReport() hands the Blade view and the PDF, with the
 * same school-name / report-title / class-subject-teacher heading block
 * and stat line the PDF prints above its table.
 */
class SubjectReportExport implements FromArray, WithTitle, WithStyles
{
    use FormatsReportSheet;

    protected array $data;
    protected Examination $exam;
    protected $subjectRow;
    protected string $schoolName;
    protected string $generatedAt;

    /** Row the column-header line actually lands on — computed from the
     *  array as it's built rather than hardcoded. Set by array(). */
    protected int $headerRow = 0;

    public function __construct(array $data, Examination $exam, $subjectRow, string $schoolName, string $generatedAt)
    {
        $this->data = $data;
        $this->exam = $exam;
        $this->subjectRow = $subjectRow;
        $this->schoolName = $schoolName;
        $this->generatedAt = $generatedAt;
    }

    public function array(): array
    {
        $stats = $this->data['stats'] ?? [];

        $rows = $this->buildHeadingRows([
            $this->schoolName,
            'Subject Performance Report — ' . $this->exam->exam_name . ' (' . $this->exam->exam_code . ')',
            $this->exam->term . ' • ' . $this->exam->academic_year
                . '  |  Class: ' . $this->data['className'] . ' — ' . $this->data['streamLabel']
                . '  |  Subject: ' . ($this->subjectRow->report_name ?? '—')
                . '  |  Teacher: ' . ($stats['teacher_name'] ?? '—'),
            'Students: ' . ($stats['total_students'] ?? 0)
                . '  |  Entered: ' . ($stats['entered_count'] ?? 0)
                . '  |  Average: ' . ($stats['average'] ?? '—') . '%'
                . '  |  Highest: ' . ($stats['highest'] ?? '—') . '%'
                . '  |  Lowest: ' . ($stats['lowest'] ?? '—') . '%'
                . '  |  Pass Rate: ' . ($stats['pass_rate'] ?? 'N/A') . (($stats['pass_rate'] ?? null) !== null ? '%' : ''),
        ]);

        $rows[] = $this->blankRow();
        $rows[] = ['Rank', 'Student', 'Admission No.', 'Gender', 'Marks', 'Total', '%', 'Grade', 'Remark'];
        $this->headerRow = count($rows);

        foreach ($this->data['rows'] as $row) {
            if ($row->entered) {
                $rows[] = [
                    $row->rank ?? '—',
                    trim($row->student->firstname . ' ' . $row->student->lastname),
                    $row->student->admission_number ?? '',
                    $row->student->gender ?? '',
                    $row->marks,
                    $row->total,
                    $row->percentage . '%',
                    $row->grade,
                    $row->remark,
                ];
            } else {
                $rows[] = [
                    $row->rank ?? '—',
                    trim($row->student->firstname . ' ' . $row->student->lastname),
                    $row->student->admission_number ?? '',
                    $row->student->gender ?? '',
                    'Marks not entered', '', '', '', '',
                ];
            }
        }

        $rows[] = $this->blankRow();
        $rows[] = ['Generated on ' . $this->generatedAt . ' — SMASA'];

        return $rows;
    }

    public function title(): string
    {
        return 'Subject Report';
    }

    public function styles(Worksheet $sheet)
    {
        return $this->applyReportHeaderStyles($sheet, 4, $this->headerRow);
    }
}