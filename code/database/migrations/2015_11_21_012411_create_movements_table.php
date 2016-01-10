<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementsTable extends Migration
{
	public function up()
	{
		Schema::create('movements', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->date('registration_date');
			$table->string('registerer_id');

			$table->string('target_type');
			$table->string('target_id');

			$table->decimal('amount', 5, 2);
			$table->string('method_id');
			$table->string('type_id');
			$table->string('identifier');
			$table->string('notes');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('movements');
	}
}
