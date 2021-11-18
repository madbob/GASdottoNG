<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderStatusPendingPayment extends Migration
{
    public function up()
    {
        DB::raw("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('suspended', 'open', 'closed', 'shipped', 'user_payment', 'archived')");
    }

    public function down()
    {
        DB::raw("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('suspended', 'open', 'closed', 'shipped', 'archived')");
    }
}
