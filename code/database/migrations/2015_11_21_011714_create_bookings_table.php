<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
	public function up()
	{
		Schema::create('bookings', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->integer('order_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->enum('status', ['pending', 'partial', 'shipped', 'saved']);
			$table->text('notes');

			$table->integer('deliverer_id')->unsigned();
			$table->date('delivery');
			$table->integer('payment_id')->unsigned();

			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('bookings');
	}
}
