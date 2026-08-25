<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePaymentItem extends Model
{
    protected $fillable = [
        'fee_payment_id', 'fee_structure_item_id', 'fee_category_id',
        'label', 'amount', 'is_external', 'description',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'is_external' => 'boolean',
    ];

    public function payment()
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    public function structureItem()
    {
        return $this->belongsTo(FeeStructureItem::class, 'fee_structure_item_id');
    }

    public function category()
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }
}
