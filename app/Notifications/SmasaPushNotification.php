<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SmasaPushNotification extends Notification
{
    public function __construct(
        protected string $title,
        protected string $body,
        protected ?string $url = null,
        protected string $icon = '/favicon.ico'
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $msg = (new WebPushMessage())
            ->title($this->title)
            ->body($this->body)
            ->icon($this->icon)
            ->badge('/favicon.ico')
            ->vibrate([200, 100, 200]);

        if ($this->url) {
            $msg->action('Open', $this->url)->data(['url' => $this->url]);
        }

        return $msg;
    }
}