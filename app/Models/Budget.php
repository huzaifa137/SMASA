<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'school_id', 'title', 'academic_year', 'term',
        'total_income_budget', 'total_expense_budget',
        'notes', 'status', 'created_by', 'approved_by',
    ];

    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function incomeItems()
    {
        return $this->items()->where('type', 'income');
    }

    public function expenseItems()
    {
        return $this->items()->where('type', 'expense');
    }
}