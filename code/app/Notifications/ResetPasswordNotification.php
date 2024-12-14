<?php

/*
    Per scrupolo, è meglio non inoltrare questa mail per mezzo della queue ma
    direttamente onde evitare pasticci con l'inizializzazione del payload
    identificativo dell'istanza (in questa sede, non avendo un utente, non ho
    neppure nessun GAS selezionato)
*/

namespace App\Notifications;

use Illuminate\Bus\Queueable;

use App\Notifications\Concerns\ManyMailNotification;
use App\Notifications\Concerns\MailFormatter;

class ResetPasswordNotification extends ManyMailNotification
{
    use MailFormatter, Queueable;

    private $reset_token = null;

    public function __construct($token)
    {
        $this->reset_token = $token;
    }

    public function toMail($notifiable)
    {
        $message = $this->initMailMessage($notifiable);

        return $this->formatMail($message, $notifiable, 'password_reset', [
            'gas_reset_link' => route('password.reset', ['token' => $this->reset_token]),
        ]);
    }
}
