<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row = one School Product category (master_datas row under the
 * SCHOOL_PRODUCTS master code) that a school is currently enrolled under.
 * A school with more than one row here has "merged" categories - see
 * App\Services\SchoolProductMergeService.
 */
class SchoolProduct extends Model
{
    protected $table = 'school_products';

    protected $fillable = [
        'school_id',
        'product_md_id',
        'is_primary',
        'added_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function product()
    {
        return $this->belongsTo(MasterData::class, 'product_md_id', 'md_id');
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}