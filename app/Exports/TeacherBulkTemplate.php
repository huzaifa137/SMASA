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
            'email',
            'phonenumber',
            'gender',
            'othername',
            'national_id',
            'address',
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
                'john.mukasa@school.com',
                '0700000001',
                'male',
                '',
                '',
                '',
            ],
            [
                'Nakato',
                'Mary',
                'mary.nakato@school.com',
                '0700000002',
                'female',
                'Grace',
                '',
                '',
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
            'C' => 30,
            'D' => 18,
            'E' => 12,
            'F' => 18,
            'G' => 18,
            'H' => 25,
        ];
    }

    /**
     * Apply styling.
     */
    public function styles(Worksheet $sheet)
    {
        // Insert information rows
        $sheet->insertNewRowBefore(1, 2);

        $sheet->setCellValue(
            'A1',
            'School: ' . $this->schoolName
        );

        $sheet->setCellValue(
            'A2',
            'Required: surname, firstname, email, phonenumber | Optional: gender, othername, national_id, address'
        );

        // Merge metadata rows
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        // Metadata styling
        $sheet->getStyle('A1:H2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '333333'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FFF3CD',
                ],
            ],
        ]);

        // Header row styling
        $sheet->getStyle('A3:H3')->applyFromArray([
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