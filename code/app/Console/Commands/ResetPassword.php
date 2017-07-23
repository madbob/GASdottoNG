<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Hash;

use App\User;

class ResetPassword extends Command
{
    protected $signature = 'reset:password {user} {new_password}';
    protected $description = 'Resetta la password di un utente';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $username = $this->argument('user');
        $password = $this->argument('new_password');
        User::where('username', $username)->update(['password' => Hash::make($password)]);
        echo "Password resettata\n";
    }
}
