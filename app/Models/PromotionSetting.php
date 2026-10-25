<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PromotionSetting extends Model
{
    use BelongsToSchool;

    public const MODE_MANUAL = 'manual';
    public const MODE_AUTO = 'auto';

    protected $fillable = [
        'school_id', 'mode', 'auto_trigger', 'auto_run_date', 'auto_apply',
        'cycle_academic_year_id', 'last_auto_run_at',
        'fee_policy', 'fee_max_balance', 'min_average', 'missing_results_policy',
        'final_class_action', 'configured_at', 'updated_by',
    ];

    protected $casts = [
        'auto_run_date' => 'date',
        'auto_apply' => 'boolean',
        'last_auto_run_at' => 'datetime',
        'configured_at' => 'datetime',
        'fee_max_balance' => 'float',
        'min_average' => 'float',
    ];

    public function isAuto(): bool
    {
        return $this->mode === self::MODE_AUTO;
    }

    public function isConfigured(): bool
    {
        return !is_null($this->configured_at);
    }
}
