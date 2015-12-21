<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
	public function up()
	{
		Schema::create('suppliers', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();

			$table->string('name')->unique();
			$table->string('description', 500);
			$table->string('comment', 500);
			$table->string('taxcode');
			$table->string('vat');
			$table->string('address');
			$table->string('phone');
			$table->string('mail');
			$table->string('website');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('suppliers');
	}
}
