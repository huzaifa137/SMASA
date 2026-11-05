<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Web-push delivery for a notification broadcast. The in-app notification
 * rows are saved immediately; this only does the slow part (one HTTP call
 * to the push service per device) in the background.
 */
class SendPushNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        public array $recipients,   // [['type' => 'admin'|'teacher', 'id' => n], ...]
        public array $payload       // title, body, url
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        NotificationService::pushNow($this->recipients, $this->payload);
    }
}
