<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMeasuresTable extends Migration
{
    public function up()
    {
        Schema::create('measures', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->text('name');
            $table->boolean('discrete')->default(false);
        });
    }

    public function down()
    {
        Schema::drop('measures');
    }
}
