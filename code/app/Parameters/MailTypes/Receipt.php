<?php

namespace App\Parameters\MailTypes;

class Receipt extends MailType
{
    public function identifier()
    {
        return 'receipt';
    }

    public function description()
    {
        return _i('Mail di accompagnamento per le ricevute.');
    }

    public function params()
    {
        return [];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('extra_invoicing');
    }
}
