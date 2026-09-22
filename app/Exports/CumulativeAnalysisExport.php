<?php

namespace App\Exports;

use App\Exports\Sheets\CumulativeOverviewSheet;
use App\Exports\Sheets\CumulativeSubjectDetailSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Excel workbook for the Cumulative Analysis report.
 *
 * Sheet 1 is always present: every subject's cumulative average per
 * student, class-wide. Sheet 2 is only added when a subject was picked
 * on the screen the export was triggered from — it lists that one
 * subject's mark on every selected exam, per student, so the raw marks
 * behind the averages on Sheet 1 are one tab away rather than lost.
 */
class CumulativeAnalysisExport implements WithMultipleSheets
{
    protected array $data;
    protected $selectedExams;
    protected $selectedSubject;
    protected string $schoolName;
    protected string $academicYear;
    protected string $generatedAt;

    public function __construct(array $data, $selectedExams, $selectedSubject, string $schoolName, string $academicYear, string $generatedAt)
    {
        $this->data = $data;
        $this->selectedExams = $selectedExams;
        $this->selectedSubject = $selectedSubject;
        $this->schoolName = $schoolName;
        $this->academicYear = $academicYear;
        $this->generatedAt = $generatedAt;
    }

    public function sheets(): array
    {
        $sheets = [
            new CumulativeOverviewSheet($this->data, $this->selectedExams, $this->schoolName, $this->academicYear, $this->generatedAt),
        ];

        if ($this->data['subjectDetail']) {
            $sheets[] = new CumulativeSubjectDetailSheet($this->data, $this->selectedExams, $this->selectedSubject, $this->schoolName, $this->academicYear, $this->generatedAt);
        }

        return $sheets;
    }
}