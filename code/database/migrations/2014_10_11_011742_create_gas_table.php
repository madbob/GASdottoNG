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
            $table->string('email', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('logo', 100)->nullable();
            $table->text('message')->nullable();

            $table->index('id');
        });
    }

    public function down()
    {
        Schema::drop('gas');
    }
}
