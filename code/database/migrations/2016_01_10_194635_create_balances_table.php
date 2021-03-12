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

            $table->string('target_id');
            $table->string('target_type');

            $table->datetime('date')->useCurrent();
            $table->boolean('current')->default(false);
            $table->decimal('bank', 10, 2)->default(0);
            $table->decimal('cash', 10, 2)->default(0);
            $table->decimal('gas', 10, 2)->default(0);
            $table->decimal('suppliers', 10, 2)->default(0);
            $table->decimal('deposits', 10, 2)->default(0);
            $table->decimal('paypal', 10, 2)->default(0);
            $table->decimal('satispay', 10, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::drop('balances');
    }
}
