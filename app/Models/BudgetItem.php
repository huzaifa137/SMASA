<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id', 'item_name', 'type', 'category_id',
        'budgeted_amount', 'actual_amount', 'notes',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'actual_amount'   => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function varianceAmount(): float
    {
        return (float) ($this->budgeted_amount - $this->actual_amount);
    }

    public function variancePct(): float
    {
        if ($this->budgeted_amount == 0) return 0;
        return round(($this->actual_amount / $this->budgeted_amount) * 100, 1);
    }
}