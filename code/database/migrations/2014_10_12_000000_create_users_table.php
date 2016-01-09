<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();
			$table->softDeletes();

			$table->string('gas_id');
			$table->string('username')->unique();
			$table->string('name');
			$table->string('surname');
			$table->string('email');
			$table->string('password');

			$table->date('birthday')->nullable();
			$table->string('phone')->nullable();
			$table->string('address')->nullable();
			$table->integer('family_members');
			$table->string('photo')->nullable();
			$table->string('taxcode')->nullable();

			$table->date('member_since')->nullable();
			$table->string('card_number')->nullable();
			$table->datetime('last_login')->nullable();
			$table->string('preferred_delivery_id');

			$table->float('current_balance', 5, 2);
			$table->float('previous_balance', 5, 2);
			$table->string('iban')->nullable();
			$table->date('sepa_subscribe')->nullable();
			$table->date('sepa_first')->nullable();

			$table->rememberToken();

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('users');
	}
}
