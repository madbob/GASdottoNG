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

			$table->string('name', 20)->unique();
			$table->string('email', 100);
			$table->text('description');
			$table->string('logo', 100);
			$table->text('message');

			$table->boolean('mail_activation');
			$table->string('mail_list');
			$table->json('mail_conf');
			$table->json('rid_conf');
			$table->json('fee_conf');

			$table->date('year_start_date');
			$table->decimal('bank_balance', 6, 2);
			$table->decimal('cash_balance', 6, 2);
			$table->decimal('suppliers_balance', 6, 2);
			$table->decimal('deposit_balance', 6, 2);

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('gas');
	}
}
