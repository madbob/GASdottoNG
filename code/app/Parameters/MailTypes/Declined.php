<?php

namespace App\Parameters\MailTypes;

class Declined extends MailType
{
    public function identifier()
    {
        return 'declined';
    }

    public function description()
    {
        return __('texts.mail.declined.description');
    }

    public function params()
    {
        return [];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('public_registrations') && $gas->public_registrations['manual'] == true;
    }
}
