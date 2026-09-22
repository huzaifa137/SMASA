<?php

namespace App\Exports;

use App\Exports\Concerns\FormatsReportSheet;
use App\Models\Examination;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Excel version of the Grade Analysis screen — which, unlike the other
 * three reports, never had a PDF to mirror, so this lays out the same
 * four panels the screen shows (grade distribution, gender comparison,
 * top performers, subject averages) as stacked tables on one sheet,
 * under the same school-name / report-title heading block the other
 * report exports use.
 *
 * Section header rows are recorded as array() builds the sheet, so
 * styles() (called afterwards against the same instance) can bold them
 * without hardcoding row numbers that would drift as the data grows.
 */
class GradeAnalysisExport implements FromArray, WithTitle, WithStyles
{
    use FormatsReportSheet;

    protected array $data;
    protected Examination $exam;
    protected string $schoolName;
    protected string $generatedAt;
    protected array $sectionHeaderRows = [];

    public function __construct(array $data, Examination $exam, string $schoolName, string $generatedAt)
    {
        $this->data = $data;
        $this->exam = $exam;
        $this->schoolName = $schoolName;
        $this->generatedAt = $generatedAt;
    }

    public function array(): array
    {
        $this->sectionHeaderRows = [];

        $scopeLabel = $this->data['selectedClassId']
            ? ($this->data['classOptions']->firstWhere('class_id', $this->data['selectedClassId'])->class_name ?? 'Selected Class')
            : 'All Classes';

        $contextBits = [
            $this->exam->term . ' • ' . $this->exam->academic_year,
            'Scope: ' . $scopeLabel,
        ];
        if ($this->data['selectedSubjectName'] ?? null) {
            $contextBits[] = 'Subject: ' . $this->data['selectedSubjectName'];
        }
        if ($this->data['filters']['gender'] ?? null) {
            $contextBits[] = 'Gender: ' . $this->data['filters']['gender'];
        }

        $rows = $this->buildHeadingRows([
            $this->schoolName,
            'Grade Analysis — ' . $this->exam->exam_name . ' (' . $this->exam->exam_code . ')',
            implode('  |  ', $contextBits),
            'Students in Scope: ' . $this->data['studentsInScope']
                . '  |  Marks Entered: ' . $this->data['entriesInScope']
                . '  |  Overall Average: ' . ($this->data['overallAverage'] ?? '—') . (($this->data['overallAverage'] ?? null) !== null ? '%' : '')
                . '  |  Pass Rate: ' . ($this->data['passRate'] ?? 'N/A') . (($this->data['passRate'] ?? null) !== null ? '%' : ''),
        ]);

        $currentRow = count($rows);

        $addSectionHeader = function (string $title) use (&$rows, &$currentRow) {
            $rows[] = $this->blankRow();
            $currentRow++;
            $rows[] = [$title];
            $currentRow++;
            $this->sectionHeaderRows[] = $currentRow;
        };

        // ── Grade Distribution ──────────────────────────────────────────
        $addSectionHeader('GRADE DISTRIBUTION');
        $rows[] = ['Grade', 'Remark', 'Count', 'Percentage'];
        $currentRow++;
        foreach ($this->data['gradeDistribution'] as $g) {
            $rows[] = [$g->grade, $g->remark, $g->count, $g->percentage . '%'];
            $currentRow++;
        }

        // ── Gender Comparison ───────────────────────────────────────────
        $addSectionHeader('GENDER COMPARISON');
        $rows[] = ['Gender', 'Count', 'Average %'];
        $currentRow++;
        foreach ($this->data['genderComparison'] as $g) {
            $rows[] = [$g->gender, $g->count, $g->average ?? '—'];
            $currentRow++;
        }

        // ── Top Performers ──────────────────────────────────────────────
        $addSectionHeader('TOP PERFORMERS');
        $rows[] = ['Rank', 'Student', 'Average %', 'Grade'];
        $currentRow++;
        foreach ($this->data['topPerformers'] as $i => $p) {
            $rows[] = [
                $i + 1,
                trim($p->student->firstname . ' ' . $p->student->lastname),
                $p->average,
                $p->grade,
            ];
            $currentRow++;
        }

        // ── Subject Averages (weakest first) ────────────────────────────
        $addSectionHeader('SUBJECT AVERAGES (WEAKEST FIRST)');
        $rows[] = ['Subject', 'Average %', 'Entries', 'Highest %', 'Lowest %'];
        $currentRow++;
        foreach ($this->data['subjectAverages'] as $s) {
            $rows[] = [$s->subject_name, $s->average, $s->entries, $s->highest, $s->lowest];
            $currentRow++;
        }

        $rows[] = $this->blankRow();
        $rows[] = ['Generated on ' . $this->generatedAt . ' — SMASA'];

        return $rows;
    }

    public function title(): string
    {
        return 'Grade Analysis';
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyHeadingBlockStyles($sheet, 4);

        $lastCol = $sheet->getHighestColumn();

        foreach ($this->sectionHeaderRows as $row) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '2C29CA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);

            // Each section's own column-header row immediately follows
            // its title row — style it the same purple-accent way the
            // single-table exports style theirs.
            $this->applyColumnHeaderStyle($sheet, $row + 1, $lastCol);
        }

        // Freeze just below the heading block (row 5) rather than under
        // any one table's header, since several tables share this sheet.
        $this->freezeAndAutoSize($sheet, 4);

        return [];
    }
}