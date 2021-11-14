<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Str;

use App;

use App\Supplier;
use App\Role;
use App\Notification;

class CheckRemoteProducts extends Command
{
    protected $signature = 'check:remote_products';
    protected $description = 'Controlla il repository remoto dei listini per aggiornamenti.';

    public function __construct()
    {
        parent::__construct();
    }

    private function notify($supplier, $e)
    {
        $body = _i('Nuovo aggiornamento disponibile per il listino %s (%s). Consultalo dal pannello Fornitori -> Indice Remoto.', [$supplier->printableName(), printableDate($e->lastchange)]);
        if (Notification::where('content', $body)->first() == null) {
            $new_notification = new Notification();
            $new_notification->creator_id = Str::random(10);
            $new_notification->content = $body;
            $new_notification->mailed = false;
            $new_notification->start_date = date('Y-m-d H:i:s');
            $new_notification->end_date = date('Y-m-d H:i:s', strtotime('+1 days'));
            $new_notification->save();

            $users_ids = [];
            $roles = Role::havingAction('supplier.modify');
            foreach($roles as $role) {
                $users = $role->usersByTarget($supplier);
                foreach($users as $u) {
                    $users_ids[] = $u->id;
                }
            }

            $new_notification->users()->sync(array_unique($users_ids));
        }
    }

    public function handle()
    {
        $entries = App::make('RemoteRepository')->getList();

        $suppliers = Supplier::whereNotNull('remote_lastimport')->get();
        foreach($suppliers as $supplier) {
            foreach($entries as $e) {
                if ($e->vat == $supplier->vat && $e->lastchange > $supplier->remote_lastimport) {
                    $this->notify($supplier, $e);
                    break;
                }
            }
        }
    }
}
