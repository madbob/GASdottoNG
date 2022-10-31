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
}
