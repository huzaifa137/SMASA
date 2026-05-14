<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimetablePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'start_time',
        'end_time',
        'type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function slots()
    {
        return $this->hasMany(TimetableSlot::class, 'period_id');
    }

    public function getDurationMinutesAttribute(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end   = \Carbon\Carbon::parse($this->end_time);
        return $start->diffInMinutes($end);
    }

    public function getFormattedTimeAttribute(): string
    {
        return \Carbon\Carbon::parse($this->start_time)->format('H:i') .
               ' – ' .
               \Carbon\Carbon::parse($this->end_time)->format('H:i');
    }
}