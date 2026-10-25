<?php

namespace App\Mail;

use App\Models\DemoRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sent to the SMASA / TechSate team when a school asks for a demo. */
class DemoRequestReceived extends Mailable
{
    use SerializesModels;

    public function __construct(public DemoRequest $demo)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // Reply goes straight to the school.
            replyTo: [new Address($this->demo->email, $this->demo->contact_person)],
            subject: 'New demo request: ' . $this->demo->school_name . ' (' . $this->demo->reference . ')',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.demo-request-received');
    }
}
