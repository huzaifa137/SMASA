<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * "Cumulative Overview" sheet — every subject's cumulative average (across
 * the exams the user selected on the picker), one row per student, plus
 * the resulting overall cumulative average, grade and class rank.
 *
 * This mirrors the class-summary style subject x student matrix, but
 * every cell is already an AVERAGE across however many exams were
 * selected rather than a single exam's mark — the per-exam marks
 * themselves live on the companion "Subject Detail" sheet.
 */
class CumulativeOverviewSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnFormatting
{
    protected array $data;
    protected string $schoolName;
    protected string $academicYear;

    public function __construct(array $data, string $schoolName, string $academicYear)
    {
        $this->data = $data;
        $this->schoolName = $schoolName;
        $this->academicYear = $academicYear;
    }

    public function headings(): array
    {
        $headers = ['#', 'Student', 'Admission No.', 'Gender'];

        foreach ($this->data['subjects'] as $subject) {
            $headers[] = $subject->report_name . ' — Avg %';
        }

        $headers[] = 'Cumulative Avg %';
        $headers[] = 'Grade';
        $headers[] = 'Rank';

        return $headers;
    }

    public function array(): array
    {
        $rows = [];

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

        return $rows;
    }

    public function title(): string
    {
        return 'Cumulative Overview';
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2C29CA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->freezePane('B2');

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
