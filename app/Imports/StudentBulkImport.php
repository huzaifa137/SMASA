<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentALevelCombination;
use App\Models\StudentOLevelElective;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Imports a class/stream's students from Excel — and, when the class is
 * Secondary A-Level or O-Level, also assigns each student's own subject
 * combination / electives straight from the same row, using the
 * principal_1/principal_2/principal_3/subsidiary or elective_1/elective_2
 * columns StudentBulkTemplate adds for those levels.
 *
 * Subject names are matched case/whitespace-insensitively against the
 * merged list (master_datas + this school's own additions) passed in from
 * StudentController — the exact same list alevel-combinations /
 * o-level-electives use, so anything selectable there is importable here.
 * A name that doesn't match anything is never guessed at — it's reported
 * back as a warning (the student is still created; only that one subject
 * is skipped) so the school can fix the spelling or add it as a new
 * elective/subject first, then patch it up on the manual entry screen.
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

    public const PRINCIPAL_LIMIT = 3;
    public const ELECTIVE_LIMIT = 2;

    public array $errors = [];
    public int $importedCount = 0;

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
        array $electiveSubjects = []
    ) {
        $this->schoolId = $schoolId;
        $this->classId = $classId;
        $this->streamId = $streamId;
        $this->year = $year;
        $this->category = $category;
        $this->addedBy = $addedBy;
        $this->level = $level;

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

                $student = Student::create([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'gender' => $gender,
                    'senior' => $this->classId,
                    'stream' => $this->streamId,
                    'school_id' => $this->schoolId,
                    'registration_number' => $studentId,
                    'primary_contact' => trim(
                        $row['phone']
                        ?? $row['contact']
                        ?? $row['primary_contact']
                        ?? ''
                    ),
                    'date_of_birth' => !empty($row['date_of_birth'])
                        ? $row['date_of_birth']
                        : null,
                    'added_by' => $this->addedBy,
                ]);

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
