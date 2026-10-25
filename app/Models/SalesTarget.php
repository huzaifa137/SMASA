<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $fillable = [
        'user_id', 'metric', 'target_value', 'period_label', 'period_start',
        'period_end', 'academic_term_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'target_value' => 'float',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function rep()
    {
        return $this->belongsTo(SalesRepProfile::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function metricConfig(): array
    {
        return config('sales_team.metrics.' . $this->metric, []);
    }
}
