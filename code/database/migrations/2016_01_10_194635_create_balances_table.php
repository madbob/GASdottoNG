<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalancesTable extends Migration
{
        public function up()
        {
                Schema::create('balances', function (Blueprint $table) {
                        $table->increments('id');
                        $table->timestamps();

                        $table->string('target_type');
                        $table->string('target_id');
                        $table->date('date');
                        $table->decimal('cash_amount', 5, 2)->default(0);
                        $table->decimal('bank_amount', 5, 2)->default(0);
                        $table->decimal('orders_amount', 5, 2)->default(0);
                        $table->decimal('deposits_amount', 5, 2)->default(0);

                        $table->index('id');
                });
        }

        public function down()
        {
                Schema::drop('balances');
        }
}
