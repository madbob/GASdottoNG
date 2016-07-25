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

                        $table->string('gas_id');
                        $table->datetime('date');
                        $table->decimal('total', 6, 2)->default(0);
                        $table->decimal('bank', 6, 2)->default(0);
			$table->decimal('cash', 6, 2)->default(0);
			$table->decimal('suppliers', 6, 2)->default(0);
			$table->decimal('deposits', 6, 2)->default(0);

                        $table->index('id');
                });
        }

        public function down()
        {
                Schema::drop('balances');
        }
}
