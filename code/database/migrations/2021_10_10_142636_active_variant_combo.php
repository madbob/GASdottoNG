<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ActiveVariantCombo extends Migration
{
    public function up()
    {
        Schema::table('variant_combos', function (Blueprint $table) {
            $table->boolean('active')->default(true);
        });
    }

    public function down()
    {
        Schema::table('variant_combos', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
}
