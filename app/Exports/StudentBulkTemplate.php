<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

/**
 * The plain firstname/lastname/gender template, extended (only when the
 * class being imported into is Secondary A-Level or O-Level) with extra
 * columns so a student's subject combination / electives can be assigned
 * in the very same row they're created in — instead of a separate trip to
 * /a-level-combinations or /o-level-electives right after import.
 *
 * $subjectOptions shape (all optional — an empty array just falls back to
 * the original 3-column template):
 *   [
 *     'level'        => 'alevel' | 'olevel' | null,
 *     'principals'   => [ ['id' => int, 'name' => string, 'group' => string], ... ],
 *     'subsidiaries' => [ ['id' => int, 'name' => string], ... ],
 *     'electives'    => [ ['id' => int, 'name' => string], ... ],
 *   ]
 * Same {id, name} shape StudentBulkImport expects back for matching, and
 * the same merged (master_datas + this school's own additions) list the
 * alevel-combinations / o-level-electives pages already build — see
 * StudentController::resolveALevelSubjectOptions() /
 * resolveOLevelSubjectOptions().
 */
class StudentBulkTemplate implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths,
    WithEvents
{
    protected $className;
    protected $streamName;
    protected $year;
    protected $schoolName;
    protected $category;
    protected $subjectOptions;

    public function __construct(
        $className,
        $streamName,
        $year,
        $schoolName,
        $category = '',
        array $subjectOptions = []
    ) {
        $this->className     = $className;
        $this->streamName    = $streamName;
        $this->year           = $year;
        $this->schoolName    = $schoolName;
        $this->category      = $category;
        $this->subjectOptions = array_merge([
            'level'        => null,
            'principals'   => [],
            'subsidiaries' => [],
            'electives'    => [],
        ], $subjectOptions);
    }

    protected function level(): ?string
    {
        return $this->subjectOptions['level'] ?? null;
    }

    /**
     * Excel column headers — grows from the base 3 columns to include
     * the level-specific subject columns.
     */
    public function headings(): array
    {
        $base = ['firstname', 'lastname', 'gender'];

        if ($this->level() === 'alevel') {
            // 3 principals (a student normally takes exactly 3) + 1
            // optional subsidiary — same shape/limit as
            // ALevelCombinationController::save(). General Paper is
            // compulsory and implied, so it isn't a column here, same as
            // it isn't a checkbox on the manual entry screen.
            return array_merge($base, ['principal_1', 'principal_2', 'principal_3', 'subsidiary']);
        }

        if ($this->level() === 'olevel') {
            // Up to OLevelElectiveController::ELECTIVE_LIMIT (2) —
            // on top of the class's own compulsory subjects, which are
            // set once at class-creation time, not per student.
            return array_merge($base, ['elective_1', 'elective_2']);
        }

        return $base;
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
        $widths = [
            'A' => 20, // firstname
            'B' => 20, // lastname
            'C' => 12, // gender
        ];

        if ($this->level() === 'alevel') {
            $widths['D'] = 22; // principal_1
            $widths['E'] = 22; // principal_2
            $widths['F'] = 22; // principal_3
            $widths['G'] = 24; // subsidiary
        } elseif ($this->level() === 'olevel') {
            $widths['D'] = 22; // elective_1
            $widths['E'] = 22; // elective_2
        }

        return $widths;
    }

    /**
     * Apply worksheet styling.
     */
    public function styles(Worksheet $sheet)
    {
        $lastColumn = count($this->headings()) === 3
            ? 'C'
            : \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()));

        // Header row styling
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
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

    /**
     * Adds a "Valid Subjects" reference tab (so the school can see/copy
     * the exact spelling expected) and wires a dropdown data-validation
     * list on the subject columns pointing at it. The dropdown is a
     * convenience, not a hard lock — it's set to warn rather than block,
     * since a school might type a subject exactly right without picking
     * from the list, and the importer itself (StudentBulkImport) does
     * the real, authoritative matching and reports anything unrecognised
     * back to the school after import.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $level = $this->level();

                if ($level !== 'alevel' && $level !== 'olevel') {
                    return;
                }

                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $refSheet = $spreadsheet->createSheet();
                $refSheet->setTitle('Valid Subjects');

                if ($level === 'alevel') {
                    $principalNames = collect($this->subjectOptions['principals'])
                        ->pluck('name')->unique()->values();
                    $subsidiaryNames = collect($this->subjectOptions['subsidiaries'])
                        ->pluck('name')->unique()->values();

                    $refSheet->setCellValue('A1', 'Principal Subjects');
                    $refSheet->setCellValue('B1', 'Subsidiary Subjects');
                    foreach ($principalNames as $i => $name) {
                        $refSheet->setCellValue('A' . ($i + 2), $name);
                    }
                    foreach ($subsidiaryNames as $i => $name) {
                        $refSheet->setCellValue('B' . ($i + 2), $name);
                    }
                    $refSheet->getColumnDimension('A')->setWidth(28);
                    $refSheet->getColumnDimension('B')->setWidth(28);

                    $this->applyDropdown($event->sheet->getDelegate(), ['D', 'E', 'F'], "'Valid Subjects'!\$A\$2:\$A\$" . max(2, $principalNames->count() + 1));
                    $this->applyDropdown($event->sheet->getDelegate(), ['G'], "'Valid Subjects'!\$B\$2:\$B\$" . max(2, $subsidiaryNames->count() + 1));
                } else {
                    $electiveNames = collect($this->subjectOptions['electives'])
                        ->pluck('name')->unique()->values();

                    $refSheet->setCellValue('A1', 'Elective Subjects');
                    foreach ($electiveNames as $i => $name) {
                        $refSheet->setCellValue('A' . ($i + 2), $name);
                    }
                    $refSheet->getColumnDimension('A')->setWidth(28);

                    $this->applyDropdown($event->sheet->getDelegate(), ['D', 'E'], "'Valid Subjects'!\$A\$2:\$A\$" . max(2, $electiveNames->count() + 1));
                }

                $refSheet->getStyle('A1:B1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A4AE8']],
                ]);
            },
        ];
    }

    /**
     * Loop the given columns (rows 2..201 — generous for a class import
     * without bloating the file) and attach a soft dropdown pointing at
     * the given "Valid Subjects" range formula.
     */
    private function applyDropdown(Worksheet $sheet, array $columns, string $formula): void
    {
        $lastRow = 201;

        foreach ($columns as $column) {
            for ($row = 2; $row <= $lastRow; $row++) {
                $validation = $sheet->getCell("{$column}{$row}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setPromptTitle('Pick a subject');
                $validation->setPrompt('Choose from the list, or type the exact name from the "Valid Subjects" tab.');
                $validation->setErrorTitle('Not on the list');
                $validation->setError('This name isn\'t on the "Valid Subjects" tab — double check the spelling, or leave it out.');
                $validation->setFormula1($formula);
            }
        }
    }
}
