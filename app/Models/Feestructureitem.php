<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructureItem extends Model
{
    protected $fillable = [
        'fee_structure_id', 'item_name', 'category', 'amount',
        'is_mandatory', 'description', 'sort_order',
    ];

    protected $casts = ['is_mandatory' => 'boolean', 'amount' => 'decimal:2'];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }
}