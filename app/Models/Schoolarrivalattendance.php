<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SchoolArrivalAttendance extends Model
{
    protected $fillable = [
        'school_id',
        'person_id',
        'person_type',
        'attendance_date',
        'arrival_time',
        'departure_time',
        'status',
        'method',
        'card_number',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function getPersonAttribute()
    {
        if ($this->person_type === 'student') {
            return Student::find($this->person_id);
        }
        return Teacher::find($this->person_id);
    }

    public function getPersonNameAttribute(): string
    {
        $p = $this->person;
        if (!$p) return 'Unknown';
        if ($this->person_type === 'student') {
            return trim(($p->firstname ?? '') . ' ' . ($p->lastname ?? ''));
        }
        return trim(($p->firstname ?? '') . ' ' . ($p->surname ?? ''));
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('attendance_date', $date);
    }

    public function scopeStudents($query)
    {
        return $query->where('person_type', 'student');
    }

    public function scopeTeachers($query)
    {
        return $query->where('person_type', 'teacher');
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'present'  => 'success',
            'late'     => 'warning',
            'absent'   => 'danger',
            'half_day' => 'primary',
            'excused'  => 'info',
            default    => 'secondary',
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'present'  => 'Present',
            'late'     => 'Late',
            'absent'   => 'Absent',
            'half_day' => 'Half Day',
            'excused'  => 'Excused',
            default    => ucfirst($status),
        };
    }
}