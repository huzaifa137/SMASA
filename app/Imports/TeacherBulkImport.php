<?php

namespace App\Imports;

use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeacherBulkImport implements ToCollection, WithHeadingRow
{
    protected $schoolId;
    protected $addedBy;

    public array $errors = [];
    public int $importedCount = 0;
    public int $skippedCount = 0;

    public function __construct($schoolId, $addedBy)
    {
        $this->schoolId = $schoolId;
        $this->addedBy = $addedBy;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            $rowNumber = $index + 2;

            $surname = trim(
                $row['surname']
                ?? $row['last_name']
                ?? ''
            );

            $firstname = trim(
                $row['firstname']
                ?? $row['first_name']
                ?? ''
            );

            $email = strtolower(
                trim($row['email'] ?? '')
            );

            $phone = trim(
                $row['phonenumber']
                ?? $row['phone']
                ?? $row['phone_number']
                ?? ''
            );

            if (
                empty($surname) ||
                empty($firstname) ||
                empty($email) ||
                empty($phone)
            ) {
                $this->errors[] =
                    "Row {$rowNumber}: surname, firstname, email and phonenumber are required.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] =
                    "Row {$rowNumber}: '{$email}' is not a valid email address.";
                continue;
            }

            // Check if teacher already exists in this school
            $exists = DB::table('teachers')
                ->where('email', $email)
                ->where('school_id', $this->schoolId)
                ->exists();

            if ($exists) {

                $this->errors[] =
                    "Row {$rowNumber}: Teacher with email '{$email}' already exists in this school.";

                $this->skippedCount++;

                continue;
            }

            try {

                Teacher::create([
                    'school_id' => $this->schoolId,
                    'surname' => $surname,
                    'firstname' => $firstname,
                    'email' => $email,
                    'phonenumber' => $phone,

                    'gender' => strtolower(
                        trim($row['gender'] ?? 'male')
                    ),

                    'othername' => trim(
                        $row['othername']
                        ?? $row['other_name']
                        ?? ''
                    ),

                    'address' => trim(
                        $row['address']
                        ?? ''
                    ),

                    'national_id' => trim(
                        $row['national_id']
                        ?? ''
                    ),

                    'password' => Hash::make('123456789'),

                    'must_change_password' => true,

                    'account_status' => 'active',
                ]);

                $this->importedCount++;

            } catch (\Exception $e) {

                $this->errors[] =
                    "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }
}