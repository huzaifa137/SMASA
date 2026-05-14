<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimetableSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'timetable_id',
        'day_of_week',
        'period_id',
        'class_subject_id',
        'subject_id',
        'teacher_id',
        'room',
        'color',
        'notes',
    ];

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

    public function period()
    {
        return $this->belongsTo(TimetablePeriod::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}