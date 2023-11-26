<?php

namespace App\Parameters\Config;

class Satispay extends Config
{
    public function identifier()
    {
        return 'satispay';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'public' => '',
            'secret' => '',
            'key' => '',
        ];
    }

    public function handleSave($gas, $request)
    {
        $satispay_info = null;

        if ($request->has('enable_satispay')) {
            $auth_code = $request->input('satispay_auth_code');
            if ($auth_code) {
                try {
                    $authentication = \SatispayGBusiness\Api::authenticateWithToken($auth_code);
                    $satispay_info = (object) [
                        'public' => $authentication->publicKey,
                        'secret' => $authentication->privateKey,
                        'key' => $authentication->keyId,
                    ];
                }
                catch(\Exception $e) {
                    \Log::error('Impossibile completare procedura di verifica su Satispay: ' . $e->getMessage());
                }
            }
        }
        else {
            $satispay_info = (object) [
                'public' => '',
                'secret' => '',
                'key' => '',
            ];
        }

        if ($satispay_info) {
            $gas->setConfig('satispay', $satispay_info);
        }
    }
}
