<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentNotificationLog extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'parent_phone',
        'event',
        'status',
        'code',
        'reason',
        'devices_total',
        'devices_sent',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
