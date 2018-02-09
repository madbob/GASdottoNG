<?php

use Illuminate\Database\Seeder;

use App\Gas;

/*
    Questo seeder viene usato per le istanze ospitate su gasdotto.net
    Cfr. code/app/Http/Middleware/CheckInstall.php
*/

class GasdottoNetSeeder extends Seeder
{
    public function run()
    {
        $gas = Gas::where('name', '!=', '')->first();

        $mail = (object) [
            'driver' => 'ses',
            'username' => '',
            'password' => '',
            'host' => '',
            'port' => '',
            'address' => '',
            'encryption' => '',
        ];

        $gas->setConfig('mail_conf', $mail);
    }
}
