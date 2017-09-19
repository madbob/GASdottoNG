<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Order;

class CloseOrders extends Command
{
    protected $signature = 'check:orders';
    protected $description = 'Controlla lo stato degli ordini, ed eventualmente li chiude';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Order::where('status', 'open')->where('end', '<', date('Y-m-d'))->update(['status' => 'closed']);
    }
}
