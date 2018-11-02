<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Auth;

class ReceiptForward extends Mailable
{
    use Queueable, SerializesModels;

    private $temp_file = null;
    private $custom_subject = null;
    private $message = null;

    public function __construct($temp_file, $subject, $message)
    {
        $this->temp_file = $temp_file;
        $this->custom_subject = $subject;
        $this->message = $message;
    }

    public function build()
    {
        $message = $this->subject($this->custom_subject)->attach($this->temp_file)->view('emails.receipt', ['txt_message' => $this->message]);

        if (!empty($user->gas->email))
            $message->replyTo($user->gas->email);

        return $message;
    }
}
