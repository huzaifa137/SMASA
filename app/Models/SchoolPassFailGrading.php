<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolPassFailGrading extends Model
{
    protected $table = 'school_pass_fail_gradings';

    protected $fillable = [
        'school_id', 'is_enabled', 'pass_mark', 'pass_grade', 'fail_grade',
        'pass_remark', 'fail_remark', 'pass_points', 'fail_points',
        'apply_general_paper', 'apply_subsidiary',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'apply_general_paper' => 'boolean',
        'apply_subsidiary' => 'boolean',
        'pass_mark' => 'float',
    ];
}
