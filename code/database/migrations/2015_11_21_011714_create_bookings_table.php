<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('order_id');
            $table->string('user_id');
            $table->enum('status', ['pending', 'partial', 'shipped', 'saved']);
            $table->text('notes');

            $table->string('deliverer_id');
            $table->date('delivery');
            $table->integer('payment_id');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('id');
        });
    }

    public function down()
    {
        Schema::drop('bookings');
    }
}
