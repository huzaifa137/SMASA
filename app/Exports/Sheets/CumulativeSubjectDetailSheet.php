<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\FormatsReportSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * "Subject Detail" sheet — for the one subject picked on the Cumulative
 * Analysis screen, every selected exam's individual mark for every
 * student, laid out left-to-right in chronological order, BEFORE the
 * average column — exactly the "marks in that subject for the student"
 * view called for alongside the averaged overview.
 *
 * Built from a manual array() (like CumulativeOverviewSheet) rather than
 * WithHeadings, so the same school-name / subject / generated-at heading
 * block sits above the column headers here too.
 */
class CumulativeSubjectDetailSheet implements FromArray, WithTitle, WithStyles
{
    use FormatsReportSheet;

    protected array $data;
    protected $selectedExams;
    protected $selectedSubject;
    protected string $schoolName;
    protected string $academicYear;
    protected string $generatedAt;

    /** Row the column-header line actually lands on — computed from the
     *  array as it's built rather than hardcoded. Set by array(). */
    protected int $headerRow = 0;

    public function __construct(array $data, $selectedExams, $selectedSubject, string $schoolName, string $academicYear, string $generatedAt)
    {
        $this->data = $data;
        $this->selectedExams = $selectedExams;
        $this->selectedSubject = $selectedSubject;
        $this->schoolName = $schoolName;
        $this->academicYear = $academicYear;
        $this->generatedAt = $generatedAt;
    }

    public function array(): array
    {
        $subjectName = $this->selectedSubject->report_name ?? 'Selected Subject';

        $examsLine = collect($this->selectedExams)->map(
            fn($e) => str_replace('-', ' ', $e->exam_type) . ' (' . $e->term . ')'
        )->implode(' | ');

        $rows = $this->buildHeadingRows([
            $this->schoolName,
            'Subject Detail — ' . $subjectName . ' — ' . $this->academicYear,
            'Includes: ' . $examsLine,
        ]);

        $rows[] = $this->blankRow();

        $headers = ['#', 'Student', 'Admission No.'];
        foreach ($this->selectedExams as $exam) {
            $headers[] = $exam->term . ' — ' . $exam->exam_type . ' (%)';
        }
        $headers[] = 'Average %';
        $headers[] = 'Grade';
        $headers[] = 'Rank';
        $rows[] = $headers;
        $this->headerRow = count($rows);

        $detail = $this->data['subjectDetail'];

        if (!$detail) {
            return $rows;
        }

        foreach ($detail['rows'] as $i => $row) {
            $line = [
                $i + 1,
                trim($row->student->firstname . ' ' . $row->student->lastname),
                $row->student->admission_number ?? '',
            ];

            foreach ($this->selectedExams as $exam) {
                $entry = $row->exams[$exam->id] ?? null;
                $line[] = $entry ? $entry->percentage : '—';
            }

            $line[] = $row->average ?? '—';
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
        return 'Subject Detail';
    }

    public function styles(Worksheet $sheet)
    {
        return $this->applyReportHeaderStyles($sheet, 3, $this->headerRow);
    }
}
