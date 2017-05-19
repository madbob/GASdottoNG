<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->string('name');
            $table->text('value');

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('configs');
    }
}
