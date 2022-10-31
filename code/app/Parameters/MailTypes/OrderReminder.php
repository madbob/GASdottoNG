<?php

namespace App\Parameters\MailTypes;

class OrderReminder extends MailType
{
    public function identifier()
    {
        return 'order_reminder';
    }

    public function description() {
        return _i('Notifica di promemoria per gli ordini in chiusura (inviato agli utenti che hanno esplicitamente abilitato le notifiche per il fornitore).');
    }

    public function params() {
        return [
            'supplier_name' => _i("Il nome del fornitore"),
            'order_comment' => _i("Testo di commento dell'ordine"),
            'gas_booking_link' => _i("Link per le prenotazioni"),
            'contacts' => _i("Indirizzi email dei referenti dell'ordine"),
            'closing_date' => _i("Data di chiusura dell'ordine")
        ];
    }

    public function enabled($gas) {
        return $gas->hasFeature('send_order_reminder');
    }
}
