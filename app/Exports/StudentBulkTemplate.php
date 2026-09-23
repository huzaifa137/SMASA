<?php

namespace App\Exports;

use App\Support\StudentImportFields;
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
 * The firstname/lastname/gender template, extended two ways:
 *
 *  1. Optional bio-data columns — any subset of StudentImportFields::FIELDS
 *     (LIN No., contact, guardian details, date of birth, etc.) the school
 *     ticks on the bulk-import screen, so a school with full student
 *     bio-data on hand (e.g. migrating from another system) can enter it
 *     all in one pass instead of a name-only import followed by manual
 *     per-student edits.
 *
 *  2. A-Level/O-Level subject columns (only when the class being imported
 *     into is Secondary A-Level or O-Level), so a student's subject
 *     combination/electives can be assigned in the same row they're
 *     created in.
 *
 * In "update" mode (see $mode), the sheet is instead pre-filled with the
 * school's CURRENT students for the class/stream — one row each, with an
 * identifier column (LIN No. / Registration No. / nothing, per $matchBy)
 * plus their current value for every ticked optional column — so the
 * school only has to type into the blanks rather than retype everything,
 * then re-upload to have StudentBulkImport update those exact records.
 *
 * $subjectOptions shape (all optional — an empty array just omits the
 * subject columns):
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
 *
 * $extraFields: a subset of StudentImportFields::FIELDS, in the order
 * they should appear as columns (StudentImportFields::selected() already
 * returns them in canonical catalog order).
 *
 * $existingStudents (only used when $mode === 'update'): array of rows,
 * each shaped:
 *   [
 *     'identifier' => string,       // LIN No. / Registration No. value, or '' if $matchBy === 'name'
 *     'firstname'  => string,
 *     'lastname'   => string,
 *     'gender'     => string,
 *     'values'     => ['field_key' => 'current value', ...],  // for $extraFields only
 *   ]
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
    protected array $extraFields;
    protected string $mode;
    protected string $matchBy;
    protected array $existingStudents;

    public function __construct(
        $className,
        $streamName,
        $year,
        $schoolName,
        $category = '',
        array $subjectOptions = [],
        array $extraFields = [],
        string $mode = 'create',
        string $matchBy = 'reg',
        array $existingStudents = []
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
        $this->extraFields = $extraFields;
        $this->mode = $mode === 'update' ? 'update' : 'create';
        $this->matchBy = in_array($matchBy, ['lin', 'reg', 'name'], true) ? $matchBy : 'reg';
        $this->existingStudents = $existingStudents;
    }

    protected function level(): ?string
    {
        return $this->subjectOptions['level'] ?? null;
    }

    protected function identifierLabel(): ?string
    {
        return match ($this->matchBy) {
            'lin' => 'LIN No.',
            'reg' => 'Registration No.',
            default => null, // 'name' — no identifier column, matched by firstname+lastname instead
        };
    }

    /**
     * Excel column headers — base 3 (or 4, in update mode with an
     * identifier column) + any ticked optional fields + level-specific
     * subject columns, in that order.
     */
    public function headings(): array
    {
        $headers = [];

        if ($this->mode === 'update' && $this->identifierLabel()) {
            $headers[] = $this->identifierLabel();
        }

        $headers = array_merge($headers, ['firstname', 'lastname', 'gender']);

        foreach ($this->extraFields as $field) {
            $headers[] = $field['label'];
        }

        if ($this->level() === 'alevel') {
            // 3 principals (a student normally takes exactly 3) + 1
            // optional subsidiary — same shape/limit as
            // ALevelCombinationController::save(). General Paper is
            // compulsory and implied, so it isn't a column here, same as
            // it isn't a checkbox on the manual entry screen.
            $headers = array_merge($headers, ['principal_1', 'principal_2', 'principal_3', 'subsidiary']);
        } elseif ($this->level() === 'olevel') {
            // Up to OLevelElectiveController::ELECTIVE_LIMIT (2) —
            // on top of the class's own compulsory subjects, which are
            // set once at class-creation time, not per student.
            $headers = array_merge($headers, ['elective_1', 'elective_2']);
        }

        return $headers;
    }

    /**
     * Create mode: no sample rows — sheet is ready for real data entry.
     * Update mode: one row per current student, prefilled with their
     * name/gender/identifier and current value for each ticked field, so
     * the school edits/fills blanks rather than retyping from scratch.
     */
    public function array(): array
    {
        if ($this->mode !== 'update') {
            return [];
        }

        $rows = [];

        foreach ($this->existingStudents as $student) {
            $line = [];

            if ($this->identifierLabel()) {
                $line[] = $student['identifier'] ?? '';
            }

            $line[] = $student['firstname'] ?? '';
            $line[] = $student['lastname'] ?? '';
            $line[] = $student['gender'] ?? '';

            foreach ($this->extraFields as $field) {
                $line[] = $student['values'][$field['key']] ?? '';
            }

            $rows[] = $line;
        }

        return $rows;
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
        $widths = [];
        $columns = range('A', \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings())));

        foreach ($columns as $col) {
            $widths[$col] = 20;
        }

        return $widths;
    }

    /**
     * Apply worksheet styling.
     */
    public function styles(Worksheet $sheet)
    {
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()));

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

        $sheet->freezePane('A2');
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
     *
     * Two ways to actually search a long subject list once opened in
     * Excel/LibreOffice — no add-ins needed, both work out of the box:
     *   1. On the "Valid Subjects" tab itself: AutoFilter is turned on,
     *      so clicking the funnel icon on the header opens Excel's
     *      built-in filter panel, which has a real search box.
     *   2. On the subject columns of the "Students" sheet: with the
     *      dropdown's List validation in place, clicking a cell and just
     *      typing jumps/filters to matching entries — this is native
     *      Excel/LibreOffice behaviour for list-validated cells, so no
     *      extra wiring is needed for it, just names sorted alphabetically
     *      so the jump-to-match behaves predictably.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $level = $this->level();

                if ($level !== 'alevel' && $level !== 'olevel') {
                    return;
                }

                // Subject columns always sit at the end of the header row
                // — right after the base columns and any ticked optional
                // fields — so their starting column shifts with however
                // many optional fields were included.
                $subjectStartIndex = 3 + count($this->extraFields) + ($this->mode === 'update' && $this->identifierLabel() ? 1 : 0);
                $col = fn(int $offset) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($subjectStartIndex + $offset);

                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $refSheet = $spreadsheet->createSheet();
                $refSheet->setTitle('Valid Subjects');

                if ($level === 'alevel') {
                    $principalNames = collect($this->subjectOptions['principals'])
                        ->pluck('name')->unique()->sort()->values();
                    $subsidiaryNames = collect($this->subjectOptions['subsidiaries'])
                        ->pluck('name')->unique()->sort()->values();

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

                    $lastRow = max($principalNames->count(), $subsidiaryNames->count()) + 1;
                    $refSheet->setAutoFilter('A1:B' . max(2, $lastRow));

                    $this->applyDropdown($event->sheet->getDelegate(), [$col(0), $col(1), $col(2)], "'Valid Subjects'!\$A\$2:\$A\$" . max(2, $principalNames->count() + 1));
                    $this->applyDropdown($event->sheet->getDelegate(), [$col(3)], "'Valid Subjects'!\$B\$2:\$B\$" . max(2, $subsidiaryNames->count() + 1));
                } else {
                    $electiveNames = collect($this->subjectOptions['electives'])
                        ->pluck('name')->unique()->sort()->values();

                    $refSheet->setCellValue('A1', 'Elective Subjects');
                    foreach ($electiveNames as $i => $name) {
                        $refSheet->setCellValue('A' . ($i + 2), $name);
                    }
                    $refSheet->getColumnDimension('A')->setWidth(28);

                    $refSheet->setAutoFilter('A1:A' . max(2, $electiveNames->count() + 1));

                    $this->applyDropdown($event->sheet->getDelegate(), [$col(0), $col(1)], "'Valid Subjects'!\$A\$2:\$A\$" . max(2, $electiveNames->count() + 1));
                }

                // Keep the header (and its filter arrows) visible while
                // scrolling a long subject list.
                $refSheet->freezePane('A2');

                $refSheet->getStyle('A1:B1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A4AE8']],
                ]);
            },
        ];
    }

    /**
     * Loop the given columns (rows 2..201 in create mode — generous for
     * a class import without bloating the file; in update mode, just far
     * enough to cover every prefilled student row) and attach a soft
     * dropdown pointing at the given "Valid Subjects" range formula.
     */
    private function applyDropdown(Worksheet $sheet, array $columns, string $formula): void
    {
        $lastRow = $this->mode === 'update'
            ? max(201, count($this->existingStudents) + 1)
            : 201;

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
                $validation->setPrompt("Tip: click this cell and just start typing to jump to a matching subject, or open the \"Valid Subjects\" tab and use its filter icon to search the full list.");
                $validation->setErrorTitle('Not on the list');
                $validation->setError('This name isn\'t on the "Valid Subjects" tab — double check the spelling, or leave it out.');
                $validation->setFormula1($formula);
            }
        }
    }
}
