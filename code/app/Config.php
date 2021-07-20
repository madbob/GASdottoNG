<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HierarcableTrait;

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
                'description' => _i('Messaggio inviato ai nuovi iscritti registrati sulla piattaforma.'),
                'params' => [
                    'username' => _i("Username assegnato al nuovo utente"),
                    'gas_login_link' => _i("Link della pagina di login"),
                ],
                'enabled' => function($gas) {
                    return $gas->hasFeature('public_registrations');
                }
            ],
            'manual_welcome' => (object) [
                'description' => _i('Messaggio inviato ai nuovi utenti creati sulla piattaforma.'),
                'params' => [
                    'username' => _i("Username assegnato al nuovo utente"),
                    'gas_access_link' => _i("Link per accedere la prima volta"),
                    'gas_login_link' => _i("Link della pagina di login"),
                ],
                'enabled' => function($gas) {
                    return true;
                }
            ],
            'password_reset' => (object) [
                'description' => _i('Messaggio per il ripristino della password.'),
                'params' => [
                    'username' => _i("Username dell'utente"),
                    'gas_reset_link' => _i("Link per il reset della password"),
                ],
                'enabled' => function($gas) {
                    return true;
                }
            ],
            'new_order' => (object) [
                'description' => _i('Notifica per i nuovi ordini aperti (inviato agli utenti che hanno esplicitamente abilitato le notifiche per il fornitore).'),
                'params' => [
                    'supplier_name' => _i("Il nome del fornitore"),
                    'order_comment' => _i("Testo di commento dell'ordine"),
                    'gas_booking_link' => _i("Link per le prenotazioni"),
                    'contacts' => _i("Indirizzi email dei referenti dell'ordine"),
                    'closing_date' => _i("Data di chiusura dell'ordine")
                ],
                'enabled' => function($gas) {
                    return true;
                }
            ],
            'supplier_summary' => (object) [
                'description' => _i("Notifica destinata ai fornitori alla chiusura automatica dell'ordine."),
                'params' => [
                    'supplier_name' => _i("Il nome del fornitore"),
                    'order_number' => _i("Numero progressivo automaticamente assegnato ad ogni ordine"),
                ],
                'enabled' => function($gas) {
                    return $gas->auto_supplier_order_summary;
                }
            ],
            'receipt' => (object) [
                'description' => _i('Mail di accompagnamento per le ricevute.'),
                'params' => [
                ],
                'enabled' => function($gas) {
                    return $gas->hasFeature('extra_invoicing');
                }
            ],
        ];
    }
}
