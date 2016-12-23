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

            $table->index('id');
        });
    }

    public function down()
    {
        Schema::drop('configs');
    }
}
