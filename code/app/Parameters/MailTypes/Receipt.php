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
        return __('texts.mail.receipt.description');
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
