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
            $table->decimal('transport', 6, 2)->default(0);

            $table->string('deliverer_id')->nullable();
            $table->date('delivery')->nullable();
            $table->integer('payment_id')->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('bookings');
    }
}
