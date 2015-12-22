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

			$table->json('mail_conf');
			$table->json('rid_conf');
			$table->json('fee_conf');

			$table->float('current_balance', 10, 2);
			$table->float('current_bank_balance', 10, 2);
			$table->float('current_cash_balance', 10, 2);
			$table->float('current_orders_balance', 10, 2);
			$table->float('current_deposit_balance', 10, 2);
			$table->datetime('last_balance_check');
			$table->float('previous_balance', 10, 2);
			$table->float('previous_bank_balance', 10, 2);
			$table->float('previous_cash_balance', 10, 2);
			$table->float('previous_orders_balance', 10, 2);
			$table->float('previous_deposit_balance', 10, 2);

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('gas');
	}
}
