<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->string('updated_by')->default('');

            $table->string('order_id');
            $table->string('user_id');
            $table->enum('status', ['pending', 'partial', 'shipped', 'saved']);
            $table->text('notes');

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
