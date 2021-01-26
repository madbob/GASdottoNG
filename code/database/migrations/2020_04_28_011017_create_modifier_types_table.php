<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModifierTypesTable extends Migration
{
    public function up()
    {
        Schema::create('modifier_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('name');
            $table->boolean('system')->default(false);
            $table->boolean('active')->default(true);
            $table->text('classes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modifier_types');
    }
}
