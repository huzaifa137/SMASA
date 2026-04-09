<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminationClass extends Model
{
    protected $fillable = [
        'examination_id',
        'class_id',
        'stream_id',
        'school_id',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }
}
