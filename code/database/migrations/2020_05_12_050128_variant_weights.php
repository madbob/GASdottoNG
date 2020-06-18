<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VariantWeights extends Migration
{
    public function up()
    {
        Schema::table('variant_values', function (Blueprint $table) {
            $table->decimal('weight_offset', 7, 4)->default(0);
        });
    }

    public function down()
    {
        Schema::table('variant_values', function (Blueprint $table) {
            $table->dropColumn('weight_offset');
        });
    }
}
