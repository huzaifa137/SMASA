<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class SmasaNotificationRecipient extends Model
{
    use BelongsToSchool;

    protected $table = 'smasa_notification_recipients';

    protected $fillable = [
        'notification_id',
        'recipient_type',
        'recipient_id',
        'school_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(SmasaNotification::class, 'notification_id');
    }
}