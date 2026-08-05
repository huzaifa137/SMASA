<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TeacherBulkTemplate implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    protected $schoolName;

    public function __construct($schoolName)
    {
        $this->schoolName = $schoolName;
    }

    /**
     * Excel headers.
     */
    public function headings(): array
    {
        return [
            'surname',
            'firstname',
            'phonenumber',
        ];
    }

    /**
     * Sample records.
     */
    public function array(): array
    {
        return [
            [
                'Mukasa',
                'John',
                '0700000001',
            ],
            [
                'Nakato',
                'Mary',
                '0700000002',
            ],
        ];
    }

    /**
     * Sheet name.
     */
    public function title(): string
    {
        return 'Teachers';
    }

    /**
     * Column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 18,
            'C' => 18,
        ];
    }

    /**
     * Apply styling.
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