<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVariantCombosTable extends Migration
{
    public function up()
    {
        Schema::create('variant_combos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('code')->default('');
            $table->decimal('max_available', 7, 3)->default(0);
            $table->double('price_offset', 8, 3)->default(0);
            $table->double('weight_offset', 7, 4)->default(0);
        });

        Schema::create('variant_combo_values', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_combo_id');
            $table->string('variant_value_id');

            $table->primary(['variant_combo_id', 'variant_value_id']);

            $table->foreign('variant_combo_id')->references('id')->on('variant_combos')->onDelete('cascade');
            $table->foreign('variant_value_id')->references('id')->on('variant_values')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('variant_combo_values');
        Schema::dropIfExists('variant_combos');
    }
}
