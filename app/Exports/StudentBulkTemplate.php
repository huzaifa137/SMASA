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
            'date_of_birth',
            'primary_contact',
        ];
    }

    /**
     * Sample rows shown to users.
     */
    public function array(): array
    {
        return [
            [
                'John',
                'Doe',
                'Male',
                '2005-01-15',
                '0700000001'
            ],
            [
                'Jane',
                'Smith',
                'Female',
                '2006-03-22',
                '0700000002'
            ],
        ];
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
            'D' => 18,
            'E' => 20,
        ];
    }

    /**
     * Apply worksheet styling.
     */
    public function styles(Worksheet $sheet)
    {
        // Insert metadata rows
        $sheet->insertNewRowBefore(1, 2);

        $sheet->setCellValue(
            'A1',
            'School: ' . $this->schoolName
        );

        $sheet->setCellValue(
            'A2',
            'Class: ' . $this->className .
            ' | Stream: ' . $this->streamName .
            ' | Category: ' . ($this->category ?: 'N/A') .
            ' | Year: ' . $this->year
        );

        // Merge metadata rows
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');

        // Metadata styling
        $sheet->getStyle('A1:E2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '333333'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E8F4FD',
                ],
            ],
        ]);

        // Header row styling
        $sheet->getStyle('A3:E3')->applyFromArray([
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

        // Sample data styling
        $sheet->getStyle('A4:E100')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FAFAFA',
                ],
            ],
        ]);
    }
}