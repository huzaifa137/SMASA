<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory;

    // Table associated with the model (optional if the table name follows Laravel's convention)
    protected $table = 'grading_scales';

    // Allow mass assignment for these attributes
    protected $fillable = [
        'grading_scheme_id',
        'min_mark',
        'max_mark',
        'grade',
        'remark',
        'points',
        'sort_order',
        'school_id', // kept for backward compatibility with pre-scheme rows
    ];

    protected $casts = [
        'points'    => 'float',
        'min_mark'  => 'float',
        'max_mark'  => 'float',
        'sort_order' => 'integer',
    ];

    public function scheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }
}