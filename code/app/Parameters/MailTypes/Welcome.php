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
            return __('mail.newuser.description_manual');
        }
        else {
            return __('mail.newuser.description');
        }
    }

    public function params()
    {
        return [
            'username' => __('auth.username'),
            'gas_login_link' => __('mail.approved.link'),
        ];
    }

    public function enabled($gas)
    {
        return $gas->hasFeature('public_registrations');
    }
}
