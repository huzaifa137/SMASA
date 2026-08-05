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

            $phone = trim(
                $row['phonenumber']
                ?? $row['phone']
                ?? $row['phone_number']
                ?? ''
            );

            if (
                empty($surname) ||
                empty($firstname) ||
                empty($phone)
            ) {
                $this->errors[] =
                    "Row {$rowNumber}: surname, firstname and phonenumber are required.";
                continue;
            }

            // Check if teacher already exists in this school
            $exists = DB::table('teachers')
                ->where('phonenumber', $phone)
                ->where('school_id', $this->schoolId)
                ->exists();

            if ($exists) {

                $this->errors[] =
                    "Row {$rowNumber}: Teacher with phonenumber '{$phone}' already exists in this school.";

                $this->skippedCount++;

                continue;
            }

            try {

                Teacher::create([
                    'school_id' => $this->schoolId,
                    'surname' => $surname,
                    'firstname' => $firstname,
                    'phonenumber' => $phone,

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