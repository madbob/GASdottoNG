<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Auth;

class GenericOrderShipping extends Mailable
{
    use Queueable, SerializesModels;

    private $temp_file = null;
    private $message = null;

    public function __construct($temp_file, $message)
    {
        $this->temp_file = $temp_file;
        $this->message = $message;
    }

    public function build()
    {
        $message = $this->subject('Dettaglio Consegne')->attach($this->temp_file)->view('emails.supplier_summary', ['txt_message' => $this->message]);

        $user = Auth::user();
        if (!empty($user->email))
            $message->replyTo($user->email);

        return $message;
    }
}
