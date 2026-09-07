<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionBand extends Model
{
    use HasFactory;

    protected $table = 'division_bands';

    protected $fillable = [
        'grading_scheme_id',
        'min_aggregate',
        'max_aggregate',
        'division',
        'remark',
        'sort_order',
    ];

    protected $casts = [
        'min_aggregate' => 'integer',
        'max_aggregate' => 'integer',
        'sort_order'    => 'integer',
    ];

    public function scheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }
}
