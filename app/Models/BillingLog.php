<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
    protected $fillable = ['billing_term_id', 'invoice_id', 'school_id', 'action', 'description', 'user_id'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function invoice()
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }
}
