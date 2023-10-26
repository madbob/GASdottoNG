<?php

namespace App\Parameters\Config;

class PublicRegistrations extends Config
{
    public function identifier()
    {
        return 'public_registrations';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'enabled' => false,
            'privacy_link' => 'http://gasdotto.net/privacy',
            'terms_link' => '',
            'mandatory_fields' => ['firstname', 'lastname', 'email', 'phone'],
            'manual' => false,
        ];
    }
}
