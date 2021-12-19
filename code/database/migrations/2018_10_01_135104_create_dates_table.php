<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDatesTable extends Migration
{
    public function up()
    {
        Schema::create('dates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->date('date')->nullable();
            $table->string('type');
            $table->string('description');
            $table->string('target_type');
            $table->string('target_id');
            $table->text('recurring')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dates');
    }
}
