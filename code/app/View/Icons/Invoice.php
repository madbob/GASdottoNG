<?php

namespace App\View\Icons;

use App\Helpers\Status;

class Invoice extends IconsMap
{
    public static function commons($user)
    {
        return self::unrollStatuses([], Status::invoices());
    }
}
