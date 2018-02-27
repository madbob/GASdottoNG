<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Log;

use App\Role;
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
                $data = file_get_contents($url);
                if (empty($data))
                    return;

                $data = json_decode($data);

                $existing = Notification::where('creator_id', $data->identifier)->first();
                if ($existing != null)
                    return;

                $new_notification = new Notification();
                $new_notification->creator_id = $data->identifier;
                $new_notification->content = $data->body;
                $new_notification->mailed = false;
                $new_notification->start_date = date('Y-m-d H:i:s');
                $new_notification->end_date = date('Y-m-d H:i:s', strtotime('+3 days'));
                $new_notification->save();

                $users = Role::everybodyCan('gas.config');
                foreach($users as $u)
                    $new_notification->users()->attach($u->id);
            }
            catch(\Exception $e) {
                Log::error('Impossibile leggere aggiornamenti da gasdotto.net: ' . $e->getMessage());
            }
        }
    }
}
