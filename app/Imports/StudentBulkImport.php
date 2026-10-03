<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentALevelCombination;
use App\Models\StudentOLevelElective;
use App\Support\StudentImportFields;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Imports (or, in update mode, updates) a class/stream's students from
 * Excel — and, when the class is Secondary A-Level or O-Level, also
 * assigns each student's own subject combination / electives straight
 * from the same row, using the
 * principal_1/principal_2/principal_3/subsidiary or elective_1/elective_2
 * columns StudentBulkTemplate adds for those levels.
 *
 * Any of StudentImportFields::FIELDS (LIN No., contact, guardian details,
 * date of birth, etc.) present as a column — whether or not it was one of
 * the columns ticked when the template was generated, so a hand-edited
 * or reused template still works — is read and applied. A blank cell for
 * one of these never clears an existing value; it's just left alone.
 *
 * Two modes, chosen by $updateMode:
 *   - create (default): a new Student row is created per row, same as
 *     before, plus any optional fields present are set on creation.
 *   - update: no new students are created. Each row is matched to an
 *     EXISTING student — by LIN No. (admission_number), Registration No.
 *     (registration_number), or firstname+lastname (per $matchBy) — and
 *     only the non-empty cells on that row are written onto that
 *     student's record. Unmatched or ambiguous rows are reported as
 *     errors and skipped; nothing is created.
 *
 * Subject names are matched case/whitespace-insensitively against the
 * merged list (master_datas + this school's own additions) passed in from
 * StudentController — the exact same list alevel-combinations /
 * o-level-electives use, so anything selectable there is importable here.
 * A name that doesn't match anything is never guessed at — it's reported
 * back as a warning (the student is still created/updated; only that one
 * subject is skipped) so the school can fix the spelling or add it as a
 * new elective/subject first, then patch it up on the manual entry screen.
 */
class StudentBulkImport implements ToCollection, WithHeadingRow
{
    protected $schoolId;
    protected $classId;
    protected $streamId;
    protected $year;
    protected $category;
    protected $addedBy;

    /** 'alevel' | 'olevel' | null */
    protected $level;

    /** @var Collection lowercased-trimmed name => ['id' => int, 'group' => string] */
    protected $principalsByName;

    /** @var Collection lowercased-trimmed name => int id */
    protected $subsidiariesByName;

    /** @var Collection lowercased-trimmed name => int id */
    protected $electivesByName;

    /** Whether this run updates existing students instead of creating new ones. */
    protected bool $updateMode;

    /** 'lin' | 'reg' | 'name' — how a row is matched to an existing student in update mode. */
    protected string $matchBy;

    public const PRINCIPAL_LIMIT = 3;
    public const ELECTIVE_LIMIT = 2;

    public array $errors = [];
    public int $importedCount = 0;
    public int $updatedCount = 0;
    public int $skippedCount = 0;

    public function __construct(
        $schoolId,
        $classId,
        $streamId,
        $year,
        $category,
        $addedBy,
        ?string $level = null,
        array $principalSubjects = [],
        array $subsidiarySubjects = [],
        array $electiveSubjects = [],
        bool $updateMode = false,
        string $matchBy = 'reg'
    ) {
        $this->schoolId = $schoolId;
        $this->classId = $classId;
        $this->streamId = $streamId;
        $this->year = $year;
        $this->category = $category;
        $this->addedBy = $addedBy;
        $this->level = $level;
        $this->updateMode = $updateMode;
        $this->matchBy = in_array($matchBy, ['lin', 'reg', 'name'], true) ? $matchBy : 'reg';

        $this->principalsByName = collect($principalSubjects)
            ->keyBy(fn($s) => $this->normalizeName($s['name']));

        $this->subsidiariesByName = collect($subsidiarySubjects)
            ->keyBy(fn($s) => $this->normalizeName($s['name']));

        $this->electivesByName = collect($electiveSubjects)
            ->keyBy(fn($s) => $this->normalizeName($s['name']));
    }

    protected function normalizeName($name): string
    {
        return strtolower(trim((string) $name));
    }

    public function collection(Collection $rows)
    {
        if ($this->updateMode) {
            $this->runUpdate($rows);
        } else {
            $this->runCreate($rows);
        }
    }

    /**
     * Row -> [normalised heading => raw cell]. Headings are compared with
     * case, spaces and punctuation ignored, so "LIN No." (read as lin_no),
     * "LIN NO" and "admission_number" all resolve to the same column.
     */
    protected function rowByHeading($row): array
    {
        $cells = $row instanceof Collection ? $row->toArray() : (array) $row;
        $byHeading = [];

        foreach ($cells as $heading => $value) {
            $norm = StudentImportFields::normalizeHeading($heading);
            if ($norm !== '' && !array_key_exists($norm, $byHeading)) {
                $byHeading[$norm] = $value;
            }
        }

        return $byHeading;
    }

    /** First non-empty cell among the given headings (any spelling), as clean text. */
    protected function cellByHeadings($row, array $headings): ?string
    {
        $byHeading = $this->rowByHeading($row);

        foreach ($headings as $heading) {
            $norm = StudentImportFields::normalizeHeading($heading);
            if (isset($byHeading[$norm])) {
                $text = $this->cellToText($byHeading[$norm]);
                if ($text !== '') {
                    return $text;
                }
            }
        }

        return null;
    }

    /**
     * A cell as plain text. Numbers typed into Excel arrive as floats, and
     * a LIN or phone number must not become "1.2345E+15" or "700123456.0".
     */
    protected function cellToText($value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_float($value) || is_int($value)) {
            return floor($value) == $value
                ? sprintf('%.0f', $value)
                : rtrim(rtrim(sprintf('%.6f', $value), '0'), '.');
        }

        return trim((string) $value);
    }

    /**
     * Reads every StudentImportFields column present and non-empty on the
     * row, under ANY of its accepted headings (see
     * StudentImportFields::ALIASES), normalizing date and numeric columns.
     *
     * @return array field_key => value, only for columns actually present with a value
     */
    protected function extractOptionalFields($row): array
    {
        $values = [];
        $byHeading = $this->rowByHeading($row);

        foreach (StudentImportFields::keys() as $key) {
            $raw = null;

            foreach (StudentImportFields::headingCandidates($key) as $candidate) {
                if (isset($byHeading[$candidate]) && $this->cellToText($byHeading[$candidate]) !== '') {
                    $raw = $byHeading[$candidate];
                    break;
                }
            }

            if ($raw === null) {
                continue;
            }

            if (in_array($key, StudentImportFields::DATE_KEYS, true)) {
                $value = $this->normalizeDate($raw);
            } elseif (in_array($key, StudentImportFields::NUMERIC_KEYS, true)) {
                $value = $this->normalizeNumber($raw);
            } else {
                $value = $this->cellToText($raw);
            }

            if ($value !== null && $value !== '') {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    /** Score columns are decimal(5,2): keep digits and the decimal point only. */
    protected function normalizeNumber($value): ?string
    {
        $clean = preg_replace('/[^0-9.\-]/', '', $this->cellToText($value));

        return is_numeric($clean) ? $clean : null;
    }

    /**
     * Normalizes a date cell to Y-m-d, whatever shape Excel handed back: a
     * DateTime (date-formatted cell), an Excel serial number, or typed text
     * such as 2007-05-14, 14/05/2007, 14-05-2007, 14.05.2007 or
     * "14 May 2007". Slashed dates are read day-first. Text that can't be
     * read as a date is skipped rather than stored as garbage.
     */
    protected function normalizeDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y', 'j-n-Y', 'd/m/y', 'j/n/y', 'd M Y', 'j F Y', 'M j, Y', 'F j, Y', 'Y/m/d'] as $format) {
            $parsed = \DateTime::createFromFormat('!' . $format, $value);
            $errors = \DateTime::getLastErrors();
            if ($parsed && (!$errors || ($errors['warning_count'] == 0 && $errors['error_count'] == 0))) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    // ───────────────────────────── Create mode ─────────────────────────

    protected function runCreate(Collection $rows)
    {
        $schoolRegCode = DB::table('schools')
            ->where('id', $this->schoolId)
            ->value('registration_code');

        // Match the ID generation logic from generateStudentID:
        // Look up the house by registration_code to get the canonical house Number
        $houseId = DB::table('houses')
            ->where('Number', $schoolRegCode)
            ->value('ID');

        $houseNumber = DB::table('houses')
            ->where('ID', $houseId)
            ->value('Number') ?? $schoolRegCode;

        foreach ($rows as $index => $row) {

            $rowNumber = $index + 2;

            $firstname = trim(
                $row['firstname']
                ?? $row['first_name']
                ?? ''
            );

            $lastname = trim(
                $row['lastname']
                ?? $row['last_name']
                ?? $row['surname']
                ?? ''
            );

            $gender = trim(
                $row['gender']
                ?? ''
            );

            if (empty($firstname) || empty($lastname)) {
                $this->errors[] =
                    "Row {$rowNumber}: Firstname and lastname are required.";
                continue;
            }

            // Normalize gender (accepts M / F / Boy / Girl as well)
            $gender = self::normalizeGender($gender);

            // Find last registration number from students_basic
            $lastNumberBasic = DB::table('students_basic')
                ->where(
                    'Student_ID',
                    'LIKE',
                    $houseNumber . '-' . $this->category . '-%-' . $this->year
                )
                ->selectRaw("
                    MAX(
                        CAST(
                            SUBSTRING_INDEX(
                                SUBSTRING_INDEX(Student_ID, '-', 4),
                                '-',
                                -1
                            ) AS UNSIGNED
                        )
                    ) as max_number
                ")
                ->value('max_number');

            // Find last registration number from students table
            $lastNumberStudents = Student::where(
                'registration_number',
                'LIKE',
                $houseNumber . '-' . $this->category . '-%-' . $this->year
            )
                ->selectRaw("
                    MAX(
                        CAST(
                            SUBSTRING_INDEX(
                                SUBSTRING_INDEX(registration_number, '-', 4),
                                '-',
                                -1
                            ) AS UNSIGNED
                        )
                    ) as max_number
                ")
                ->value('max_number');

            $nextNumber = max(
                ($lastNumberBasic ?? 0),
                ($lastNumberStudents ?? 0)
            ) + 1 + $this->importedCount;

            $sequence = str_pad(
                $nextNumber,
                3,
                '0',
                STR_PAD_LEFT
            );

            $studentId =
                $houseNumber . '-' .
                $this->category . '-' .
                $sequence . '-' .
                $this->year;

            try {

                $attributes = array_merge(
                    [
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'gender' => $gender,
                        'senior' => $this->classId,
                        'stream' => $this->streamId,
                        'school_id' => $this->schoolId,
                        'registration_number' => $studentId,
                        'added_by' => $this->addedBy,
                    ],
                    $this->extractOptionalFields($row)
                );

                $student = Student::create($attributes);

                $this->importedCount++;

                if ($this->level === 'alevel') {
                    $this->assignALevelCombination($student, $row, $rowNumber);
                } elseif ($this->level === 'olevel') {
                    $this->assignOLevelElectives($student, $row, $rowNumber);
                }

            } catch (\Exception $e) {

                $this->errors[] =
                    "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    // ───────────────────────────── Update mode ─────────────────────────

    /**
     * Matches each row to an existing student in this school (scoped to
     * the chosen class/stream when matching by name, since names alone
     * aren't unique school-wide) and writes only the non-empty cells onto
     * that record. Never creates a student; a row that matches nothing —
     * or, for name-matching, matches more than one student — is reported
     * as an error and left untouched.
     */
    protected function runUpdate(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $firstname = trim($row['firstname'] ?? $row['first_name'] ?? '');
            $lastname = trim($row['lastname'] ?? $row['last_name'] ?? $row['surname'] ?? '');

            $student = $this->findExistingStudent($row, $firstname, $lastname, $rowNumber);

            if (!$student) {
                continue; // findExistingStudent already logged the reason
            }

            $updates = $this->extractOptionalFields($row);

            // Name/gender are prefilled by the update template but stay
            // editable — apply them too if the school changed them, same
            // "never overwrite with a blank" rule as every other field.
            if ($firstname !== '') {
                $updates['firstname'] = $firstname;
            }
            if ($lastname !== '') {
                $updates['lastname'] = $lastname;
            }
            $gender = trim($row['gender'] ?? '');
            if ($gender !== '') {
                $updates['gender'] = self::normalizeGender($gender);
            }

            try {
                if (!empty($updates)) {
                    $student->update($updates);
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }

                if ($this->level === 'alevel') {
                    $this->assignALevelCombination($student, $row, $rowNumber);
                } elseif ($this->level === 'olevel') {
                    $this->assignOLevelElectives($student, $row, $rowNumber);
                }
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    /**
     * Resolves the row to one existing Student, per $matchBy. Always
     * scoped to this school; 'name' matching is additionally scoped to
     * the chosen class/stream, since firstname+lastname isn't unique
     * school-wide the way LIN No./Registration No. are.
     */
    protected function findExistingStudent($row, string $firstname, string $lastname, int $rowNumber): ?Student
    {
        if ($this->matchBy === 'lin' || $this->matchBy === 'reg') {
            $column = $this->matchBy === 'lin' ? 'admission_number' : 'registration_number';
            $label = $this->matchBy === 'lin' ? 'LIN No.' : 'Registration No.';

            $identifier = (string) $this->cellByHeadings(
                $row,
                $this->matchBy === 'lin'
                    ? ['admission_number', 'LIN No.', 'LIN', 'LIN Number', 'Admission No']
                    : ['registration_number', 'Registration No.', 'Registration Number', 'Reg No']
            );

            if ($identifier === '') {
                $this->errors[] = "Row {$rowNumber}: No {$label} given — this row was skipped (update mode never creates new students).";
                return null;
            }

            $student = Student::where('school_id', $this->schoolId)
                ->where($column, $identifier)
                ->first();

            if (!$student) {
                $this->errors[] = "Row {$rowNumber}: No existing student found with {$label} \"{$identifier}\" — skipped.";
                return null;
            }

            return $student;
        }

        // matchBy === 'name'
        if (empty($firstname) || empty($lastname)) {
            $this->errors[] = "Row {$rowNumber}: Firstname and lastname are required to match an existing student by name.";
            return null;
        }

        $matches = Student::where('school_id', $this->schoolId)
            ->where('senior', $this->classId)
            ->where('stream', $this->streamId)
            ->whereRaw('LOWER(TRIM(firstname)) = ?', [strtolower($firstname)])
            ->whereRaw('LOWER(TRIM(lastname)) = ?', [strtolower($lastname)])
            ->get();

        if ($matches->isEmpty()) {
            $this->errors[] = "Row {$rowNumber}: No existing student named \"{$firstname} {$lastname}\" found in this class/stream — skipped.";
            return null;
        }

        if ($matches->count() > 1) {
            $this->errors[] = "Row {$rowNumber}: \"{$firstname} {$lastname}\" matches {$matches->count()} students in this class/stream — skipped. Re-download the update template (it includes a Registration No. column) and match by that instead.";
            return null;
        }

        return $matches->first();
    }

    /**
     * Reads principal_1/principal_2/principal_3 + subsidiary off the row,
     * matches each non-empty value against this school's merged subject
     * list, and saves a StudentALevelCombination — same shape/limit
     * ALevelCombinationController::save() writes, so the result is
     * indistinguishable from one entered by hand on that screen.
     */
    protected function assignALevelCombination(Student $student, $row, int $rowNumber): void
    {
        $principalIds = [];

        foreach (['principal_1', 'principal_2', 'principal_3'] as $column) {
            $value = trim((string) ($row[$column] ?? ''));
            if ($value === '') {
                continue;
            }

            $match = $this->principalsByName->get($this->normalizeName($value));
            if (!$match) {
                $this->errors[] = "Row {$rowNumber}: \"{$value}\" ({$column}) isn't a recognised principal subject — {$student->firstname} {$student->lastname} was still imported, but this subject wasn't assigned. Check the spelling against the \"Valid Subjects\" tab, or add it first on the A-Level Combinations page.";
                continue;
            }

            $principalIds[] = $match['id'];
        }

        $principalIds = array_slice(array_unique($principalIds), 0, self::PRINCIPAL_LIMIT);

        $subsidiaryId = null;
        $subsidiaryValue = trim((string) ($row['subsidiary'] ?? ''));
        if ($subsidiaryValue !== '') {
            $match = $this->subsidiariesByName->get($this->normalizeName($subsidiaryValue));
            if (!$match) {
                $this->errors[] = "Row {$rowNumber}: \"{$subsidiaryValue}\" (subsidiary) isn't a recognised subsidiary subject — {$student->firstname} {$student->lastname} was still imported, but this subject wasn't assigned. Check the spelling against the \"Valid Subjects\" tab, or add it first on the A-Level Combinations page.";
            } else {
                $subsidiaryId = $match['id'];
            }
        }

        if (empty($principalIds) && !$subsidiaryId) {
            return; // Nothing usable was provided — fine, leave it for manual entry later.
        }

        StudentALevelCombination::updateOrCreate(
            [
                'school_id' => $this->schoolId,
                'student_id' => $student->id,
            ],
            [
                'principal_subject_ids' => $principalIds,
                'subsidiary_subject_id' => $subsidiaryId,
                'entered_by' => $this->addedBy,
                'entered_at' => now(),
            ]
        );
    }

    /**
     * Reads elective_1/elective_2 off the row, matches each non-empty
     * value against this school's merged elective list, and saves a
     * StudentOLevelElective — same shape/limit
     * OLevelElectiveController::save() writes.
     */
    protected function assignOLevelElectives(Student $student, $row, int $rowNumber): void
    {
        $electiveIds = [];

        foreach (['elective_1', 'elective_2'] as $column) {
            $value = trim((string) ($row[$column] ?? ''));
            if ($value === '') {
                continue;
            }

            $match = $this->electivesByName->get($this->normalizeName($value));
            if (!$match) {
                $this->errors[] = "Row {$rowNumber}: \"{$value}\" ({$column}) isn't a recognised elective — {$student->firstname} {$student->lastname} was still imported, but this elective wasn't assigned. Check the spelling against the \"Valid Subjects\" tab, or add it first on the O-Level Electives page.";
                continue;
            }

            $electiveIds[] = $match['id'];
        }

        $electiveIds = array_slice(array_unique($electiveIds), 0, self::ELECTIVE_LIMIT);

        if (empty($electiveIds)) {
            return; // Nothing usable was provided — fine, leave it for manual entry later.
        }

        StudentOLevelElective::updateOrCreate(
            [
                'school_id' => $this->schoolId,
                'student_id' => $student->id,
            ],
            [
                'elective_subject_ids' => $electiveIds,
                'entered_by' => $this->addedBy,
                'entered_at' => now(),
            ]
        );
    }

    /** Map the many ways a sheet can spell gender onto Male / Female / Other. */
    private static function normalizeGender($value): string
    {
        $v = strtolower(trim((string) $value));

        if (in_array($v, ['male', 'm', 'boy', 'b'], true)) {
            return 'Male';
        }
        if (in_array($v, ['female', 'f', 'girl', 'g'], true)) {
            return 'Female';
        }

        return 'Other';
    }
}
