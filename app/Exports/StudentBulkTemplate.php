<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentBulkTemplate implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    protected $className;
    protected $streamName;
    protected $year;
    protected $schoolName;
    protected $category;

    public function __construct(
        $className,
        $streamName,
        $year,
        $schoolName,
        $category = ''
    ) {
        $this->className   = $className;
        $this->streamName  = $streamName;
        $this->year        = $year;
        $this->schoolName  = $schoolName;
        $this->category    = $category;
    }

    /**
     * Excel column headers.
     */
    public function headings(): array
    {
        return [
            'firstname',
            'lastname',
            'gender',
        ];
    }

    /**
     * No sample rows — sheet is ready for real data entry.
     */
    public function array(): array
    {
        return [];
    }

    /**
     * Worksheet tab name.
     */
    public function title(): string
    {
        return 'Students';
    }

    /**
     * Set column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 12,
        ];
    }

    /**
     * Apply worksheet styling.
     */
    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '4A4AE8',
                ],
            ],
        ]);
    }
}