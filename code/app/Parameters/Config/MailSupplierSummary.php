<?php

namespace App\Parameters\Config;

class MailSupplierSummary extends Config
{
    public function identifier()
    {
        return 'mail_supplier_summary';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i('Prenotazione ordine %[gas_name]'),
            'body' => _i("Buongiorno.\nIn allegato trova - in duplice copia, PDF e CSV - la prenotazione dell'ordine da parte di %[gas_name].\nPer segnalazioni, può rivolgersi ai referenti in copia a questa mail.\nGrazie."),
        ];
    }
}
