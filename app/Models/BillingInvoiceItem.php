<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingInvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'type', 'description', 'quantity', 'unit_price', 'amount', 'sort_order'];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'amount'     => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }
}
