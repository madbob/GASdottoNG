<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInnerLogsTable extends Migration
{
    public function up()
    {
        Schema::create('inner_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('level');
            $table->string('type');
            $table->text('message');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inner_logs');
    }
}
