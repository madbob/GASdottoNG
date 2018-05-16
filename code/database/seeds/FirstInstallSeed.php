<?php

use Illuminate\Database\Seeder;

use App\Gas;

class FirstInstallSeed extends Seeder
{
    public function run()
    {
        $gas = Gas::where('name', '!=', '')->first();
        $gas->message = "<h2>Benvenuto in GASdotto!</h2><p>Per accedere la prima volta, usa le credenziali:</p><ul><li>username: root</li><li>password: root</li></ul>";
        $gas->save();
    }
}
