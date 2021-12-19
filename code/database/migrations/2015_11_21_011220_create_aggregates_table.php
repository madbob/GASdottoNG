<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAggregatesTable extends Migration
{
    public function up()
    {
        Schema::create('aggregates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('aggregate_gas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->integer('aggregate_id')->unsigned();

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
            $table->foreign('aggregate_id')->references('id')->on('aggregates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('aggregate_gas');
        Schema::drop('aggregates');
    }
}
