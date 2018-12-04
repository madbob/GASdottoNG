<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatesTable extends Migration
{
    public function up()
    {
        Schema::create('dates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->date('date');
            $table->string('type');
            $table->string('description');
            $table->string('target_type');
            $table->string('target_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dates');
    }
}
