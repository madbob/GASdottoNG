<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
        static public function fallbackColumn()
        {
                return 'cash_amount';
        }
}
