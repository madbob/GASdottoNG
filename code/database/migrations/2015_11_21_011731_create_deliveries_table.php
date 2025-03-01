<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeliveriesTable extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->string('updated_by')->default('');

            $table->string('name');
            $table->string('address');
            $table->boolean('default');
        });

        Schema::create('delivery_gas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->string('delivery_id');

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('cascade');
        });

        /*
            Questo è per mappare i luoghi di consegna abilitati per ogni ordine
        */
        Schema::create('delivery_order', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('delivery_id');
            $table->string('order_id');

            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('delivery_order');
        Schema::drop('delivery_gas');
        Schema::drop('deliveries');
    }
}
