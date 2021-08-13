<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixVariantsTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('variants', 'has_offset')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->dropColumn('has_offset');
            });
        }

        if (Schema::hasColumn('variant_values', 'price_offset')) {
            Schema::table('variant_values', function (Blueprint $table) {
                $table->dropColumn('price_offset');
            });
        }
    }

    public function down()
    {
        //
    }
}
