<?php

namespace App\Notifications;

use Auth;

use App\Notifications\ManyMailNotification;

class SupplierOrderSummary extends ManyMailNotification
{
    private $temp_file = null;
    private $message = null;

    public function __construct($temp_file, $message)
    {
        $this->temp_file = $temp_file;
        $this->message = $message;
    }

    public function toMail($notifiable)
    {
        $user = Auth::user();
        $message = $this->initMailMessage($notifiable);
        $message->subject('nuovo ordine')->cc($user->email)->replyTo($user)->attach($this->temp_file)->view('emails.supplier_summary', ['txt_message' => $this->message]);
        return $message;
    }
}
