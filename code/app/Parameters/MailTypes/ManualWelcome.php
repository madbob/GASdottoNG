<?php

namespace App\Parameters\MailTypes;

class ManualWelcome extends MailType
{
    public function identifier()
    {
        return 'manual_welcome';
    }

    public function description()
    {
        return __('texts.mail.welcome.description');
    }

    public function params()
    {
        return [
            'username' => __('texts.mail.approved.username'),
            'gas_access_link' => __('texts.mail.welcome.link'),
            'gas_login_link' => __('texts.mail.approved.link'),
        ];
    }

    public function enabled($gas)
    {
        return true;
    }
}
