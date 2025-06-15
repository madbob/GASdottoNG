<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Notifications\Concerns\ManyMailNotification;

class NewUserNotification extends ManyMailNotification implements ShouldQueue
{
    use Queueable;

    private $user = null;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        $message->subject(__('mail.new_user_notification.defaults.subject'))->view('emails.newuser', ['user' => $this->user]);

        return $message;
    }
}
