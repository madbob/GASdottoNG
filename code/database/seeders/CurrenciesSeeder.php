<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Currency;

class CurrenciesSeeder extends Seeder
{
    public function run()
    {
        if (Currency::where('symbol', '€')->first() == null) {
            $c = new Currency();
            $c->symbol = '€';
            $c->context = 'default';
            $c->enabled = true;
            $c->save();
        }
    }
}
