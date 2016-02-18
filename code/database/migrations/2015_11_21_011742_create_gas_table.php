<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGasTable extends Migration
{
	public function up()
	{
		Schema::create('gas', function (Blueprint $table) {
			$table->string('id',2)->primary();
			$table->timestamps();

			$table->string('name',20)->unique();
			$table->string('email',100);
			$table->text('description');
			$table->string('logo',100);
			$table->text('message'); /* cosa contiene? */

			$table->boolean('mail_activation');
			$table->string('mail_list');
			$table->json('mail_conf');
		
			$table->boolean('rid_activation');
			$table->json('rid_conf');
			$table->json('fee_conf'); /* cosa contiene? */

			$table->boolean('acct_activation');
			$table->date('social_year_start_date');
			$table->foreign('order_payment_movement_type')->references('id')->on('movement_types');
			$table->decimal('bank_balance',6,2);
			$table->decimal('cash_balance',6,2);
			$table->decimal('suppliers_balance',6,2);
			$table->decimal('deposit_balance',6,2);

			$table->boolean('delivery_locations');
			$table->boolean('protected_user_data'); /* forse non serve se usiamo i ruoli */
			
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('gas');
	}
}
