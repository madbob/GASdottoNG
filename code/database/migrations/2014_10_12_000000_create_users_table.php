<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->string('id',20)->primary(); /* username */
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('gas_id')->references('id')->on('gas');
		/*	$table->string('username')->unique(); */
			$table->string('first_name',30);
			$table->string('last_name',30);
			$table->string('email_1',45);
			$table->string('email_2',45);
			$table->string('password',100);
			$table->date('birthday')->nullable();
			$table->string('phone',15)->nullable();
			$table->string('address_street_1',45)->nullable();
			$table->string('address_street_2',45)->nullable();
			$table->string('address_city',45)->nullable();
			$table->string('address_zip_code',5)->nullable();
			$table->integer('family_members')->unsigned();
			
			$table->string('picture',100)->nullable();
			
			$table->string('fiscal_code',16)->nullable()->unique();
			$table->date('member_since');
			$table->date('leaving_date');
			$table->string('card_number',3)->unique();
			$table->datetime('last_login')->nullable();
		/*	$table->string('preferred_delivery_id'); */
		
			$table->decimal('balance', 6, 2);
			$table->foreign('deposit')->references('id')->on('acct_movements');
			$table->foreign('annual_fee')->references('id')->on('acct_movements');
			$table->string('iban',27)->nullable();
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
