<?php

namespace App\Parameters\Config;

class CreditHome extends Config
{
    public function identifier()
    {
        return 'credit_home';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'current_credit' => true,
            'to_pay' => true,
        ];
    }

    public function handleSave($gas, $request)
    {
        $gas->setConfig('credit_home', [
            'current_credit' => $request->input('credit_home->current_credit') ? true : false,
            'to_pay' => $request->input('credit_home->to_pay') ? true : false,
        ]);
    }
}
