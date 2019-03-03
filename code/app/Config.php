<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public function gas()
    {
        $this->belongsTo('App\Gas');
    }

    public static function customMailTypes()
    {
        /*
            Nota bene: quando si aggiunge un nuovo parametro qui, è opportuno
            definire anche i valori
            - mail_identificativo_subject
            - mail_identificativo_body
            tra le configurazioni di default in Gas::handlingConfigs()
        */
        return [
            'welcome' => (object) [
                'description' => _i('Messaggio inviato ai nuovi iscritti sulla piattaforma.'),
                'params' => [
                    'username' => _i("Username assegnato al nuovo utente"),
                    'gas_login_link' => _i("Link della pagina di login")
                ]
            ],
            'password_reset' => (object) [
                'description' => _i('Messaggio per il ripristino della password.'),
                'params' => [
                    'gas_reset_link' => _i("Link per il reset della password")
                ]
            ],
            'new_order' => (object) [
                'description' => _i('Notifica per i nuovi ordini aperti (inviato agli utenti che hanno esplicitamente abilitato le notifiche per il fornitore).'),
                'params' => [
                    'supplier_name' => _i("Il nome del fornitore"),
                    'gas_booking_link' => _i("Link per le prenotazioni"),
                    'closing_date' => _i("Data di chiusura dell'ordine")
                ]
            ],
            'receipt' => (object) [
                'description' => _i('Mail di accompagnamento per le ricevute.'),
                'params' => [
                ]
            ],
        ];
    }
}
