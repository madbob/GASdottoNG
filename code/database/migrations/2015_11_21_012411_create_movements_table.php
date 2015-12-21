<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementsTable extends Migration
{
	public function up()
	{
		Schema::create('movements', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->integer('registerer_id')->unsigned();

			$table->integer('user_id')->unsigned();
			$table->morphs('target');

			$table->decimal('amount', 5, 2);
			$table->enum('method', ['cash', 'bank']);
			$table->enum('type', ['deposit_payment', 'deposit_return', 'annual_payment', 'booking_payment', 'order_payment', 'user_credit', 'gas_expense', 'transfer', 'get', 'put', 'round']);
			$table->string('cro');
			$table->string('notes');
			$table->boolean('obsolete');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('movements');
	}
}
