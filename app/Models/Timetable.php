<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'class_id',
        'stream_id',
        'academic_year_id',
        'term',
        'name',
        'status',
        'created_by',
    ];

    public function slots()
    {
        return $this->hasMany(TimetableSlot::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public static function dayName($dayNum): string
    {
        return match ((int)$dayNum) {
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
            default => 'Day ' . $dayNum,
        };
    }

    public static function dayShort($dayNum): string
    {
        return match ((int)$dayNum) {
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
            7 => 'Sun',
            default => 'D' . $dayNum,
        };
    }
}