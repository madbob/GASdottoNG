<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

use App\Supplier;
use App\Role;
use App\Gas;
use App\Notification;

class CheckRemoteProducts extends Command
{
    protected $signature = 'check:remote_products';

    protected $description = 'Controlla il repository remoto dei listini per aggiornamenti.';

    private function notify($supplier, $e)
    {
        $body = __('texts.imports.help.new_remote_products_list', [
            'supplier' => $supplier->printableName(),
            'date' => printableDate($e->lastchange),
        ]);

        if (Notification::where('content', $body)->first() == null) {
            $gas = Gas::all();
            $hub = App::make('GlobalScopeHub');
            $hub->enable(true);

            foreach ($gas as $g) {
                $hub->setGas($g->id);

                $new_notification = new Notification();
                $new_notification->creator_id = Str::random(10);
                $new_notification->gas_id = $g->id;
                $new_notification->content = $body;
                $new_notification->mailed = false;
                $new_notification->start_date = date('Y-m-d H:i:s');
                $new_notification->end_date = date('Y-m-d H:i:s', strtotime('+1 days'));
                $new_notification->save();

                $users_ids = [];
                $roles = Role::havingAction('supplier.modify');
                foreach ($roles as $role) {
                    $users = $role->usersByTarget($supplier);
                    foreach ($users as $u) {
                        $users_ids[] = $u->id;
                    }
                }

                $new_notification->users()->sync(array_unique($users_ids));
            }

            $hub->enable(false);
        }
    }

    public function handle()
    {
        $suppliers = Supplier::whereNotNull('remote_lastimport')->get();

        if ($suppliers->isEmpty() === false) {
            $entries = App::make('RemoteRepository')->getList();

            foreach ($suppliers as $supplier) {
                foreach ($entries as $e) {
                    if ($e->vat == $supplier->vat && $e->lastchange > $supplier->remote_lastimport) {
                        $this->notify($supplier, $e);
                        break;
                    }
                }
            }
        }
    }
}
