<?php

namespace App\Observers;

use App\Config;
use App\Currency;

class ConfigObserver
{
    public function saved(Config $config)
    {
        if ($config->name == 'integralces') {
            $value = json_decode($config->value);
            $integralces_currency = Currency::where('context', 'integralces')->first();

            if ($value->enabled) {
                if (is_null($integralces_currency)) {
                    $integralces_currency = new Currency();
                    $integralces_currency->context = 'integralces';
                }

                $integralces_currency->enabled = true;
                $integralces_currency->symbol = $value->symbol;
                $integralces_currency->save();
            }
            else {
                if ($integralces_currency) {
                    $integralces_currency->enabled = false;
                    $integralces_currency->save();
                }
            }
        }

        return true;
    }
}
