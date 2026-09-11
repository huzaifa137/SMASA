<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardRemark extends Model
{
    protected $table = 'report_card_remarks';

    protected $fillable = [
        'school_id',
        'examination_id',
        'student_id',
        'class_teacher_remark',
        'head_teacher_remark',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examination_id');
    }
}
