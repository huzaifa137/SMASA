<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Marks-import template for one class-stream: one row per pupil (with the system ID, so re-importing can never
 * attach a mark to the wrong child), one column per examinable subject, current marks pre-filled.
 */
class MarksImportTemplateExport implements FromArray, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $subjectNames, private array $rows, private string $title = 'Marks')
    {
    }

    public function array(): array
    {
        return array_merge([array_merge(['Student ID', 'First Name', 'Last Name'], $this->subjectNames)], $this->rows);
    }

    public function title(): string
    {
        return mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $this->title), 0, 31) ?: 'Marks';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
