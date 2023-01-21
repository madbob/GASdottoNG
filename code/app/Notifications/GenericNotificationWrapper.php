<?php

namespace App\Notifications;

class GenericNotificationWrapper extends ManyMailNotification
{
    /*
        Reminder: le notifiche sono salvate all'interno di una transazione su
        DB, dunque è elevato il rischio di andare in race condition e attivare
        la funzione asincrona di invio email prima che la transazione stessa sia
        ultimata. Dunque qui forzo l'esecuzione del job a dopo il commit
    */
    public $afterCommit = true;

    private $notification = null;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
		$message->subject(_i('Nuova notifica da %s', [$notifiable->gas->name]));

		if (filled($this->notification->mailtype)) {
			$body = $this->notification->formattedContent($notifiable);
			$message->view('emails.empty', ['content' => $body]);
		}
		else {
        	$message->view('emails.notification', ['notification' => $this->notification]);
		}

        foreach($this->notification->attachments as $attachment) {
            $message->attach($attachment->path, ['as' => $attachment->name]);
        }

        return $message;
    }
}
