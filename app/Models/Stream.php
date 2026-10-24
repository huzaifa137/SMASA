<?php

namespace App\Models;


use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use BelongsToSchool;

    use HasFactory;
    protected $fillable = [
        'school_id',
        'class_id',
        'stream_id',
        'class_teacher',
        'added_by',
    ];
}
