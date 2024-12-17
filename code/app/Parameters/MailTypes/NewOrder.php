<?php

namespace App\Parameters\MailTypes;

class NewOrder extends MailType
{
    public function identifier()
    {
        return 'new_order';
    }

    public function description()
    {
        return _i('Notifica per i nuovi ordini aperti (inviato agli utenti che hanno esplicitamente abilitato le notifiche per il fornitore).');
    }

    public function params()
    {
        return [
            'supplier_name' => _i('Il nome del fornitore'),
            'order_comment' => _i("Testo di commento dell'ordine"),
            'gas_booking_link' => _i('Link per le prenotazioni'),
            'contacts' => _i("Indirizzi email dei referenti dell'ordine"),
            'closing_date' => _i("Data di chiusura dell'ordine"),
        ];
    }

    public function enabled($gas)
    {
        return true;
    }
}
