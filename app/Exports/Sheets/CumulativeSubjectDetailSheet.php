<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * "Subject Detail" sheet — for the one subject picked on the Cumulative
 * Analysis screen, every selected exam's individual mark for every
 * student, laid out left-to-right in chronological order, BEFORE the
 * average column — exactly the "marks in that subject for the student"
 * view called for alongside the averaged overview.
 */
class CumulativeSubjectDetailSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected array $data;
    protected $selectedExams;

    public function __construct(array $data, $selectedExams, string $schoolName, string $academicYear)
    {
        $this->data = $data;
        $this->selectedExams = $selectedExams;
    }

    public function headings(): array
    {
        $headers = ['#', 'Student', 'Admission No.'];

        foreach ($this->selectedExams as $exam) {
            $headers[] = $exam->term . ' — ' . $exam->exam_type . ' (%)';
        }

        $headers[] = 'Average %';
        $headers[] = 'Grade';
        $headers[] = 'Rank';

        return $headers;
    }

    public function array(): array
    {
        $detail = $this->data['subjectDetail'];
        $rows = [];

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

        return $rows;
    }

    public function title(): string
    {
        return 'Subject Detail';
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
