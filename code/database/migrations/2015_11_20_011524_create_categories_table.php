<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('parent_id')->nullable();
            $table->timestamps();
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::drop('categories');
    }
}
