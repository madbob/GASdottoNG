<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Log;

use App\Gas;
use App\User;

class CheckFees extends Command
{
    protected $signature = 'check:fees';
    protected $description = 'Controlla la scadenza delle quote di iscrizione alla chiusura dell\'anno sociale';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = date('Y-m-d');

        foreach(Gas::all() as $gas) {
            $amount = $gas->getConfig('annual_fee_amount');
            if ($amount == 0)
                continue;

            $date_close = $gas->getConfig('year_closing');
            if ($date_close < $today) {
                Log::info('Scaduto anno sociale GAS ' . $gas->name);

                DB::beginTransaction();

                User::withTrashed()->whereHas('fee', function($query) use ($date_close) {
                    $query->where('date', '<', $date_close);
                })->update(['fee_id' => 0]);

                $date_close = date('Y-m-d', strtotime($date_close . ' +1 years'));
                $gas->setConfig('year_closing', $date_close);

                DB::commit();
            }
        }
    }
}
