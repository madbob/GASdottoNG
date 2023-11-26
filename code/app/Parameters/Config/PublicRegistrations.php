<?php

namespace App\Parameters\Config;

use Illuminate\Support\Arr;

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

    public function handleSave($gas, $request)
    {
        if ($request->has('enable_public_registrations')) {
            $registrations_info = (object) [
                'enabled' => true,
                'privacy_link' => $request->input('public_registrations->privacy_link', ''),
                'terms_link' => $request->input('public_registrations->terms_link', ''),
                'mandatory_fields' => Arr::wrap($request->input('public_registrations->mandatory_fields', [])),
                'manual' => $request->has('public_registrations->manual'),
            ];
        }
        else {
            $registrations_info = (object) [
                'enabled' => false,
                'privacy_link' => '',
                'terms_link' => '',
                'mandatory_fields' => ['firstname', 'lastname', 'email', 'phone'],
                'manual' => false,
            ];
        }

        $gas->setConfig('public_registrations', $registrations_info);
    }
}
