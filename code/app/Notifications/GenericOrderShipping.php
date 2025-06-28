<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class GenericOrderShipping extends Mailable
{
    use Queueable, SerializesModels;

    private $tempFile = null;

    private $customSubject = null;

    private $message = null;

    public function __construct($temp_file, $subject, $message)
    {
        $this->tempFile = $temp_file;
        $this->customSubject = $subject;
        $this->message = $message;
    }

    public function build()
    {
        $message = $this->subject($this->customSubject)->attach($this->tempFile)->view('emails.supplier_summary', ['txt_message' => $this->message]);

        $user = Auth::user();
        if (! empty($user->email)) {
            $message->replyTo($user->email);
        }

        return $message;
    }
}
