<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmasaNotification extends Model
{
    protected $table = 'smasa_notifications';

    protected $fillable = [
        'school_id',
        'title',
        'body',
        'type',
        'icon',
        'color',
        'url',
        'module',
        'triggered_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Type constants
    const TYPE_GENERAL = 'general';
    const TYPE_EXAM = 'exam';
    const TYPE_FEE = 'fee';
    const TYPE_LIBRARY = 'library';
    const TYPE_ATTENDANCE = 'attendance';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_MAINTENANCE = 'maintenance';

    // Icon + color map per type
    public static function typeConfig(): array
    {
        return [
            self::TYPE_GENERAL => ['icon' => 'bell', 'color' => 'primary'],
            self::TYPE_EXAM => ['icon' => 'graduation-cap', 'color' => 'success'],
            self::TYPE_FEE => ['icon' => 'money-bill', 'color' => 'warning'],
            self::TYPE_LIBRARY => ['icon' => 'book', 'color' => 'info'],
            self::TYPE_ATTENDANCE => ['icon' => 'user-check', 'color' => 'danger'],
            self::TYPE_ANNOUNCEMENT => ['icon' => 'bullhorn', 'color' => 'secondary'],
            self::TYPE_MAINTENANCE => ['icon' => 'tools', 'color' => 'dark'],
        ];
    }

    public function recipients()
    {
        return $this->hasMany(SmasaNotificationRecipient::class, 'notification_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}