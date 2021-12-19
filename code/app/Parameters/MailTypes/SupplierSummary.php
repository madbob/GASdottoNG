<?php

namespace App\Parameters\MailTypes;

class SupplierSummary extends MailType
{
    public function identifier()
    {
        return 'supplier_summary';
    }

    public function description() {
        return _i("Notifica destinata ai fornitori alla chiusura automatica dell'ordine.");
    }

    public function params() {
        return [
            'supplier_name' => _i("Il nome del fornitore"),
            'order_number' => _i("Numero progressivo automaticamente assegnato ad ogni ordine"),
        ];
    }

    public function enabled($gas) {
        return $gas->auto_supplier_order_summary;
    }
}
