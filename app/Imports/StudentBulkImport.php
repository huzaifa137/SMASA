<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentBulkImport implements ToCollection, WithHeadingRow
{
    protected $schoolId;
    protected $classId;
    protected $streamId;
    protected $year;
    protected $category;
    protected $addedBy;

    public array $errors = [];
    public int $importedCount = 0;

    public function __construct(
        $schoolId,
        $classId,
        $streamId,
        $year,
        $category,
        $addedBy
    ) {
        $this->schoolId = $schoolId;
        $this->classId = $classId;
        $this->streamId = $streamId;
        $this->year = $year;
        $this->category = $category;
        $this->addedBy = $addedBy;
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

                Student::create([
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

            } catch (\Exception $e) {

                $this->errors[] =
                    "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }
}