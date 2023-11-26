<?php

namespace App\Parameters\Config;

use App\Currency;

class IntegralCES extends Config
{
    public function identifier()
    {
        return 'integralces';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'enabled' => false,
            'identifier' => '',
            'symbol' => '',
        ];
    }

    public function asAttribute($gas)
    {
        $ret = (array) json_decode($gas->getConfig('integralces'));
        $ret['symbol'] = Currency::where('context', 'integralces')->first()->symbol ?? '';
        return $ret;
    }

    public function handleSave($gas, $request)
    {
        if ($request->has('enable_integralces')) {
            $integralces_info = (object) [
                'enabled' => true,
                'identifier' => $request->input('integralces->identifier'),
                'symbol' => $request->input('integralces->symbol'),
            ];
        }
        else {
            $integralces_info = (object) [
                'enabled' => false,
                'identifier' => '',
                'symbol' => '',
            ];
        }

        $gas->setConfig('integralces', $integralces_info);
    }
}
