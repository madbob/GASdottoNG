<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeliveriesForOrders extends Migration
{
    public function up()
    {
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
    }
}
