<?php

namespace Database\Seeders;

use App\Aggregate;
use App\Order;
use App\Supplier;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the Order database seeds.
     */
    public function run(): void
    {
        Order::factory()
            ->recycle(Supplier::all())
            ->recycle(Aggregate::all())
            ->create();
    }
}
