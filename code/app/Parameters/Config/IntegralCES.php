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
}
