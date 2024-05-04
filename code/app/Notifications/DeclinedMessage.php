<?php

/*
    Nota bene: questa email non deve essere inoltrata sulla queue asincrona, in
    quanto l'utente destinatario viene eliminato subito dopo l'invio e si
    rischia una race condition sull'elemento mancante
*/

namespace App\Notifications;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;

class DeclinedMessage extends ManyMailNotification
{
    use MailFormatter;

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);
        return $this->formatMail($message, $notifiable, 'declined');
    }
}
