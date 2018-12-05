<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGasTable extends Migration
{
    public function up()
    {
        Schema::create('gas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('name', 20)->unique();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->text('message')->nullable();
        });
    }

    public function down()
    {
        Schema::drop('gas');
    }
}
