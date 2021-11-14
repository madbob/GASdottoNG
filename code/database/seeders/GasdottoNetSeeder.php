<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Gas;
use App\User;

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

        if (env('INITIAL_EMAIL', false)) {
            $admin = User::orderBy('id', 'asc')->first();
            $admin->addContact('email', env('INITIAL_EMAIL'));
        }
    }
}
