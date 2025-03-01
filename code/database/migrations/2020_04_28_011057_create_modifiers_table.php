<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateModifiersTable extends Migration
{
    public function up()
    {
        Schema::create('modifiers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('updated_by')->default('');

            $table->string('modifier_type_id');
            $table->string('target_type');
            $table->string('target_id');
            $table->integer('priority')->unsigned()->default(0);
            $table->enum('value', ['absolute', 'percentage', 'price', 'mass'])->default('absolute');
            $table->enum('arithmetic', ['sum', 'sub', 'passive', 'apply'])->default('sum');
            $table->enum('scale', ['minor', 'major'])->default('minor');
            $table->enum('applies_type', ['none', 'quantity', 'price', 'weight'])->default('none');
            $table->enum('applies_target', ['product', 'booking', 'order'])->default('order');
            $table->enum('distribution_type', ['none', 'quantity', 'price', 'weight'])->default('none');
            $table->text('definition');
            $table->string('movement_type_id')->nullable()->default(null);
            $table->boolean('always_on')->default(false);

            $table->foreign('modifier_type_id')->references('id')->on('modifier_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('modifiers');
    }
}
