<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SmasaPushNotification extends Notification
{
    protected string $icon;
    protected string $badge;

    public function __construct(
        protected string $title,
        protected string $body,
        protected ?string $url = null,
        ?string $icon = null,
        ?string $badge = null
    ) {
        // Absolute URL required — most browsers won't resolve a relative icon path in a push payload.
        // logo.png (full-colour company logo) is used for the visible notification icon since
        // most OS notification trays render on a light/white background — a light/white logo
        // variant would appear invisible there. uplogolight.png is kept for the badge, since
        // Android strips colour from the badge anyway and shows it as a plain silhouette.
        $this->icon  = $icon  ?? asset('assets/images/brand/logo.png');
        $this->badge = $badge ?? asset('assets/images/brand/uplogolight.png');
    }

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
            ->badge($this->badge)
            ->vibrate([200, 100, 200]);

        if ($this->url) {
            $msg->action('Open', $this->url)->data(['url' => $this->url]);
        }

        return $msg;
    }
}