<?php

namespace App\Parameters\MailTypes;

class Approved extends MailType
{
    public function identifier()
    {
        return 'approved';
    }

    public function description()
    {
        return __('texts.mail.approved.description');
    }

    public function params()
    {
        return [
            'username' => __('texts.mail.approved.username'),
            'gas_login_link' => __('texts.mail.approved.link'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('public_registrations') && $gas->public_registrations['manual'] == true;
    }
}
