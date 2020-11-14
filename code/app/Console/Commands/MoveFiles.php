<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Gas;
use App\Supplier;
use App\Order;
use App\Notification;

class MoveFiles extends Command
{
    protected $signature = 'move:files';
    protected $description = 'Comando temporaneo per aggiornare la disposizione dei files sul filesystem';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        foreach(Gas::all() as $gas) {
            $old_path = gas_storage_path($gas->name);
            $new_path = $gas->filesPath(false);
            @rename($old_path, $new_path);
        }

        foreach(Supplier::all() as $supplier) {
            $old_path = gas_storage_path($supplier->name);
            $new_path = $supplier->filesPath(false);
            @rename($old_path, $new_path);
        }

        foreach(Order::all() as $order) {
            $old_path = gas_storage_path($order->name);
            $new_path = $order->filesPath(false);
            @rename($old_path, $new_path);
        }

        foreach(Notification::all() as $notification) {
            $old_path = gas_storage_path($notification->name);
            $new_path = $notification->filesPath(false);
            @rename($old_path, $new_path);
        }
    }
}
