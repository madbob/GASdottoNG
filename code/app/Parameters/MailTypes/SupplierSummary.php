<?php

namespace App\Parameters\MailTypes;

class SupplierSummary extends MailType
{
    public function identifier()
    {
        return 'supplier_summary';
    }

    public function description()
    {
        return _i("Notifica destinata ai fornitori alla chiusura automatica dell'ordine.");
    }

    public function params()
    {
        return [
            'supplier_name' => _i('Il nome del fornitore'),
            'order_number' => _i('Numero progressivo automaticamente assegnato ad ogni ordine'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->suppliers()->where('notify_on_close_enabled', '!=', 'none')->count() != 0;
    }
}
