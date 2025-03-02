<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateModifierTypesTable extends Migration
{
    public function up()
    {
        Schema::create('modifier_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('name');
            $table->string('identifier')->default('')->after('system');
            $table->boolean('system')->default(false);
            $table->boolean('active')->default(true);
            $table->text('classes');
            $table->boolean('hidden')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('modifier_types');
    }
}
