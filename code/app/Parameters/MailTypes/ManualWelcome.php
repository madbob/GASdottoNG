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
        return __('mail.welcome.description');
    }

    public function params()
    {
        return [
            'username' => __('mail.approved.username'),
            'gas_access_link' => __('mail.welcome.link'),
            'gas_login_link' => __('mail.approved.link'),
        ];
    }

    public function enabled($gas)
    {
        return true;
    }
}
