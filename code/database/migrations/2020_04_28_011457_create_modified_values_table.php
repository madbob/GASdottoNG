<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModifiedValuesTable extends Migration
{
    public function up()
    {
        Schema::create('modified_values', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('modifier_id')->unsigned();

            $table->string('target_type');
            $table->string('target_id');
            $table->decimal('amount', 7, 3)->default(0);

            $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('modified_values');
    }
}
