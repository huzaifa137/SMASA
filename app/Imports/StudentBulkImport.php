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
     * Reads every StudentImportFields column present and non-empty on the
     * row, normalizing date columns along the way. Aliases 'phone' /
     * 'contact' onto 'primary_contact' for backwards compatibility with
     * the original 3-column template's phone handling.
     *
     * @return array field_key => value, only for columns actually present with a value
     */
    protected function extractOptionalFields($row): array
    {
        $values = [];

        $primaryContact = $row['primary_contact'] ?? $row['phone'] ?? $row['contact'] ?? null;
        if (!empty(trim((string) $primaryContact))) {
            $values['primary_contact'] = trim((string) $primaryContact);
        }

        foreach (StudentImportFields::keys() as $key) {
            if ($key === 'primary_contact') {
                continue; // handled above with its aliases
            }

            $raw = $row[$key] ?? null;
            if ($raw === null || $raw === '') {
                continue;
            }

            $values[$key] = in_array($key, StudentImportFields::DATE_KEYS, true)
                ? $this->normalizeDate($raw)
                : trim((string) $raw);
        }

        return array_filter($values, fn($v) => $v !== null && $v !== '');
    }

    /**
     * Normalizes a date cell to Y-m-d, whatever shape Excel/PhpSpreadsheet
     * handed back for it: a DateTime/Carbon instance (date-formatted
     * cell), a numeric Excel serial (rare with ToCollection, but cheap to
     * guard against), or a plain typed string (left as-is — Laravel's
     * `date` cast / MySQL both parse common formats like Y-m-d or d/m/Y
     * fine on their own).
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

        return $value === '' ? null : $value;
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

            // Normalize gender
            $gender = ucfirst(strtolower($gender));

            if (! in_array($gender, ['Male', 'Female', 'Other'])) {
                $gender = 'Other';
            }

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
                $gender = ucfirst(strtolower($gender));
                if (in_array($gender, ['Male', 'Female', 'Other'])) {
                    $updates['gender'] = $gender;
                }
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

            $identifier = trim((string) ($row[$column] ?? ($this->matchBy === 'lin' ? ($row['lin_no'] ?? '') : ($row['registration_no'] ?? ''))));

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
}
