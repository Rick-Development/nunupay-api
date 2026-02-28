<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CardStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfPath;

    public function __construct($pdfPath)
    {
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->view('email.card_statement')
                    ->subject('Your Card Statement')
                    ->attach($this->pdfPath, [
                        'as' => 'Card_Statement.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
