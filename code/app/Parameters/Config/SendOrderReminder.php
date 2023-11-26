<?php

namespace App\Parameters\Config;

class SendOrderReminder extends Config
{
    public function identifier()
    {
        return 'send_order_reminder';
    }

    public function type()
    {
        return 'number';
    }

    public function default()
    {
        return 0;
    }

    public function handleSave($gas, $request)
    {
        $gas->setConfig('send_order_reminder', $request->has('enable_send_order_reminder') ? $request->input('send_order_reminder') : '0');
    }
}
