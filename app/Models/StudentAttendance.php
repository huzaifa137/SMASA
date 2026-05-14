<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'class_id',
        'stream_id',
        'class_subject_id',
        'taken_by',
        'attendance_date',
        'session',
        'period_label',
        'status',
        'arrival_time',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'taken_by');
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('attendance_date', $date);
    }

    public function scopeForClass($query, $classId, $streamId)
    {
        return $query->where('class_id', $classId)->where('stream_id', $streamId);
    }

    // Helpers
    public static function statusBadge($status): string
    {
        return match ($status) {
            'present'  => 'success',
            'absent'   => 'danger',
            'late'     => 'warning',
            'excused'  => 'info',
            default    => 'secondary',
        };
    }

    public static function statusLabel($status): string
    {
        return match ($status) {
            'present'  => 'Present',
            'absent'   => 'Absent',
            'late'     => 'Late',
            'excused'  => 'Excused',
            default    => ucfirst($status),
        };
    }
}