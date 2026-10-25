<?php

namespace App\Mail;

use App\Models\DemoRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sent to the school to confirm that its request was received. */
class DemoRequestAcknowledgement extends Mailable
{
    use SerializesModels;

    public function __construct(public DemoRequest $demo)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'We received your SMASA demo request (' . $this->demo->reference . ')');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demo-request-ack');
    }
}
