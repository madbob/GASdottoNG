<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Gas;
use App\Movement;

class CheckFees extends Command
{
    protected $signature = 'check:fees';

    protected $description = "Controlla la scadenza delle quote di iscrizione alla chiusura dell'anno sociale";

    private function iterateUsers($users, $gas, $amount)
    {
        $auto_fee = $gas->getConfig('auto_fee');

        foreach ($users as $user) {
            try {
                $user->fee_id = 0;
                $user->save();

                if ($auto_fee && $user->plainStatus() == 'active') {
                    $new = Movement::generate('annual-fee', $user, $gas, $amount);
                    $new->method = 'credit';
                    $new->automatic = true;
                    $new->save();
                }
            }
            catch (\Exception $e) {
                Log::error('Impossibile aggiornare stato quota: ' . $e->getMessage());
            }
        }
    }

    public function handle()
    {
        $today = date('Y-m-d');

        foreach (Gas::all() as $gas) {
            $amount = $gas->getConfig('annual_fee_amount');
            if ($amount == 0) {
                continue;
            }

            $date_close = $gas->getConfig('year_closing');

            if ($date_close < $today) {
                Log::info('Scaduto anno sociale GAS ' . $gas->name);

                DB::beginTransaction();

                $users = $gas->users()->withTrashed()->whereHas('fee', function ($query) use ($date_close) {
                    $query->where('date', '<', $date_close);
                })->get();

                $this->iterateUsers($users, $gas, $amount);

                $date_close = date('Y-m-d', strtotime($date_close . ' +1 years'));
                $gas->setConfig('year_closing', $date_close);

                DB::commit();
            }
        }
    }
}
