<?php

namespace App\Parameters\MailTypes;

class Welcome extends MailType
{
    public function identifier()
    {
        return 'welcome';
    }

    public function description()
    {
        $gas = currentAbsoluteGas();
        $manual = $gas->hasFeature('public_registrations') && $gas->public_registrations['manual'] == true;

        if ($manual) {
            return __('texts.mail.newuser.description_manual');
        }
        else {
            return __('texts.mail.newuser.description');
        }
    }

    public function params()
    {
        return [
            'username' => __('texts.auth.username'),
            'gas_login_link' => __('texts.mail.approved.link'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('public_registrations');
    }
}
