<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fromEmail;
    public $siteTitle;
    public $msg;

    public function __construct($emailFrom, $subject, $message, $fromName = null)
    {
        $basic = basicControl();
        
        $this->fromEmail = $emailFrom;
        $this->siteTitle = $fromName ?: $basic->site_title;
        $this->subject = $subject;
        $this->msg = $message;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail, $this->siteTitle),
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'layouts.mail',
            with: [
                'msg' => $this->msg,
                'site_title' => $this->siteTitle,
            ],
        );
    }
}