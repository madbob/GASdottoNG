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

                        $table->string('target_type',1); /* G = GAS, U = User, S = Supplier */
                        $table->string('target_id',20);
                        $table->datetime('date');
                        $table->decimal('balance', 6, 2)->default(0);
                        $table->decimal('bank_balance',6,2)->default(0);
			$table->decimal('cash_balance',6,2)->default(0);
			$table->decimal('suppliers_balance',6,2)->default(0);
			$table->decimal('deposit_balance',6,2)->default(0);

                        $table->index('id');
                        $table->index(['target_type','target_id']);
                });
        }

        public function down()
        {
                Schema::drop('balances');
        }
}
