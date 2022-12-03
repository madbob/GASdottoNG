<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Log;
use App;

use App\Gas;
use App\Notification;

class CheckSystemNotifications extends Command
{
    protected $signature = 'check:system_notices';
    protected $description = 'Controlla la presenza di nuove notifiche provenienti da gasdotto.net';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (env('GASDOTTO_NET', false) == true) {
            try {
                $url = 'http://gasdotto.net/notice/0';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $data = curl_exec($ch);
                curl_close($ch);

                if (empty($data)) {
                    return;
                }

                $data = json_decode($data);

                $existing = Notification::where('creator_id', $data->identifier)->first();
                if ($existing != null) {
                    return;
                }

                $gas = Gas::all();
                $hub = App::make('GlobalScopeHub');
                $hub->enable(true);

                foreach($gas as $g) {
                    $hub->setGas($g->id);

                    $new_notification = new Notification();
                    $new_notification->creator_id = $data->identifier;
                    $new_notification->gas_id = $g->id;
                    $new_notification->content = $data->body;
                    $new_notification->mailed = false;
                    $new_notification->start_date = date('Y-m-d H:i:s');
                    $new_notification->end_date = date('Y-m-d H:i:s', strtotime('+3 days'));
                    $new_notification->save();

                    $users = everybodyCan('gas.config');
                    foreach($users as $u) {
                        $new_notification->users()->attach($u->id);
                    }
                }
            }
            catch(\Exception $e) {
                Log::error('Impossibile leggere aggiornamenti da gasdotto.net: ' . $e->getMessage());
            }
        }
    }
}
