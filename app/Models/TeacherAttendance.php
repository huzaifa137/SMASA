<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'attendance_date',
        'arrival_time',
        'departure_time',
        'status',
        'leave_type',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
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

    // Duration in minutes
    public function getDurationAttribute(): ?int
    {
        if ($this->arrival_time && $this->departure_time) {
            $arrival   = \Carbon\Carbon::parse($this->arrival_time);
            $departure = \Carbon\Carbon::parse($this->departure_time);
            return $arrival->diffInMinutes($departure);
        }
        return null;
    }

    public static function statusBadge($status): string
    {
        return match ($status) {
            'present'   => 'success',
            'absent'    => 'danger',
            'late'      => 'warning',
            'on_leave'  => 'info',
            'half_day'  => 'primary',
            'excused'   => 'secondary',
            default     => 'secondary',
        };
    }

    public static function statusLabel($status): string
    {
        return match ($status) {
            'present'   => 'Present',
            'absent'    => 'Absent',
            'late'      => 'Late',
            'on_leave'  => 'On Leave',
            'half_day'  => 'Half Day',
            'excused'   => 'Excused',
            default     => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}