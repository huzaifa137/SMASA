<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
    protected $fillable = ['billing_term_id', 'invoice_id', 'school_id', 'action', 'description', 'user_id'];

    public function school()
    {
        // The school may have been deleted (its billing records are kept), so never return null:
        // fall back to the name saved on the row, then to a plain "Deleted school #id".
        return $this->belongsTo(School::class)->withDefault(function ($school, $row) {
            $school->name = $row->school_name ?: 'Deleted school #' . $row->school_id;
        });
    }

    public function invoice()
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }
}
