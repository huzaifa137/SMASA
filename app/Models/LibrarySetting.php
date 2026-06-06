<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $table = "library_settings";
    
    protected $fillable = [
        "school_id", "fine_per_day", "student_max_books", "teacher_max_books",
        "student_loan_days", "teacher_loan_days", "max_renewals",
        "enable_reservations", "enable_ebooks", "enable_recommendations"
    ];
    
    protected $casts = [
        "fine_per_day" => "decimal:2",
        "enable_reservations" => "boolean",
        "enable_ebooks" => "boolean",
        "enable_recommendations" => "boolean"
    ];
    
    public static function forSchool(int $schoolId): self
    {
        return self::firstOrCreate(
            ["school_id" => $schoolId],
            [
                "fine_per_day" => 100.00,
                "student_max_books" => 3,
                "teacher_max_books" => 5,
                "student_loan_days" => 14,
                "teacher_loan_days" => 30,
                "max_renewals" => 2,
                "enable_reservations" => true,
                "enable_ebooks" => true,
                "enable_recommendations" => true
            ]
        );
    }
}
