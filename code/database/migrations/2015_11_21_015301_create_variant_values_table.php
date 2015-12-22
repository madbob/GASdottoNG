<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVariantValuesTable extends Migration
{
	public function up()
	{
		Schema::create('variant_values', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();

			$table->string('variant_id');
			$table->string('value');

			$table->foreign('variant_id')->references('id')->on('variants')->onDelete('cascade');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('variant_values');
	}
}
