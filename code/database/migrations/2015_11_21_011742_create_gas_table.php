<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGasTable extends Migration
{
	public function up()
	{
		Schema::create('gas', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();

			$table->string('name');
			$table->string('email');
			$table->text('description');
			$table->text('message');

			$table->json('mail_conf');
			$table->json('rid_conf');
			$table->json('fee_conf');

			$table->float('bank_balance', 8, 2);
			$table->float('cash_balance', 8, 2);
			$table->float('orders_balance', 8, 2);
			$table->float('deposit_balance', 8, 2);

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('gas');
	}
}
