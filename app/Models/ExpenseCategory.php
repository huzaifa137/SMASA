<?php

namespace App\Models;


use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'color', 'icon', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}