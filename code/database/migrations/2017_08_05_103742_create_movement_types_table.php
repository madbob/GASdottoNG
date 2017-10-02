<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementTypesTable extends Migration
{
    public function up()
    {
        Schema::create('movement_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->softDeletes();

            $table->string('name');
            $table->string('sender_type')->nullable();
            $table->string('target_type')->nullable();
            $table->boolean('allow_negative')->default(false);
            $table->boolean('visibility')->default(true);
            $table->boolean('system')->default(false);
            $table->decimal('fixed_value', 6, 2)->nullable();
            $table->text('default_notes')->nullable();
            $table->text('function');
        });
    }

    public function down()
    {
        Schema::drop('movement_types');
    }
}
