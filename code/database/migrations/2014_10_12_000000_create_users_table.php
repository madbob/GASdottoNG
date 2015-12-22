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

			$table->date('birthday');
			$table->string('phone');
			$table->string('address');
			$table->integer('family_members');
			$table->string('photo');
			$table->string('taxcode');

			$table->date('member_since');
			$table->string('card_number');
			$table->datetime('last_login');
			$table->string('preferred_delivery_id');

			$table->float('current_balance', 5, 2);
			$table->float('previous_balance', 5, 2);
			$table->string('iban');
			$table->date('sepa_subscribe');
			$table->date('sepa_first');

			$table->rememberToken();

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('users');
	}
}
