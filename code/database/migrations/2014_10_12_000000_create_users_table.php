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
			$table->string('firstname');
			$table->string('lastname');
			$table->string('email');
			$table->string('password');
			$table->date('birthday')->nullable();
			$table->string('phone')->nullable();
			$table->json('address')->nullable();
			$table->integer('family_members')->unsigned()->nullable();
			$table->string('picture')->nullable();
			$table->string('taxcode')->nullable();
			$table->date('member_since');
			$table->date('leaving_date')->nullable();
			$table->string('card_number')->nullable();
			$table->datetime('last_login')->nullable();
			$table->string('preferred_delivery_id')->nullable();

			$table->decimal('balance', 6, 2)->default(0);
			$table->integer('fee_id')->nullable();
			$table->integer('deposit_id')->nullable();

			$table->string('iban')->nullable();
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
