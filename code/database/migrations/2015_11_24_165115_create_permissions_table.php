<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
	public function up()
	{
		Schema::create('permissions', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->string('user_id');
			$table->string('target_type');
			$table->string('target_id');
			$table->string('action');

			/*
				Attenzione: user_id non deve essere referenza di
				users.id, in quanto è una stringa che può anche
				assumere il valore speciale '*'
			*/

			$table->index('id');
			$table->index('user_id');
		});
	}

	public function down()
	{
		Schema::drop('permissions');
	}
}
