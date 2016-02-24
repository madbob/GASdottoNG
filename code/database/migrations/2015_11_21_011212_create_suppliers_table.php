<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
	public function up()
	{
		Schema::create('suppliers', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();
			$table->softDeletes();

			$table->string('name')->unique();
			$table->string('description', 500);
			$table->string('comment', 500);

			$table->json('address')->nullable();
			$table->string('phone');
			$table->string('mail');
			$table->string('fax');
			$table->string('website');

			$table->string('taxcode');
			$table->string('vat');
			$table->float('balance', 10, 2);

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('suppliers');
	}
}
