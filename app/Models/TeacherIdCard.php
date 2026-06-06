<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherIdCard extends Model
{
    protected $fillable = [
        'teacher_id',
        'school_id',
        'academic_year',
        'card_number',
        'status',
        'issue_date',
        'expiry_date',
        'issued_by',
        'qr_code_data',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
