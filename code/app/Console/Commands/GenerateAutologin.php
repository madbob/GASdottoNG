<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use App\User;

class GenerateAutologin extends Command
{
    protected $signature = 'generate:autologin {user}';
    protected $description = "Controlla la scadenza delle quote di iscrizione alla chiusura dell'anno sociale";

    public function handle()
    {
        $username = $this->argument('user');

        $user = User::withoutGlobalScopes()->where('username', $username)->orWhere('id', $username)->first();
        if ($user) {
            if (blank($user->access_token)) {
                $user->access_token = Str::random(10);
                $user->save();
            }

            echo route('autologin', ['token' => $user->access_token]) . "\n";
        }
        else {
            echo "Utente non trovato\n";
        }
    }
}
