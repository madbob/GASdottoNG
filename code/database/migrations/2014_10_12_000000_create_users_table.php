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
			$table->string('username', 20)->unique();
			$table->string('firstname', 30);
			$table->string('lastname', 30);
			$table->string('email_1', 45);
			$table->string('email_2', 45);
			$table->string('password', 100);
			$table->date('birthday')->nullable();
			$table->string('phone', 15)->nullable();
			$table->json('address')->nullable();
			$table->integer('family_members')->unsigned();
			$table->string('picture', 100)->nullable();
			$table->string('taxcode', 16)->nullable();
			$table->date('member_since');
			$table->date('leaving_date');
			$table->string('card_number', 10)->nullable();
			$table->datetime('last_login')->nullable();
			// $table->string('preferred_delivery_id');

			$table->decimal('balance', 6, 2);
			$table->string('iban', 27)->nullable();
			$table->date('sepa_subscribe')->nullable();
			$table->date('sepa_first')->nullable();

			$table->rememberToken();

			$table->foreign('gas_id')->references('id')->on('gas');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('users');
	}
}
