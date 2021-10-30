<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SystemModifiers extends Migration
{
    public function up()
    {
        Schema::table('modifier_types', function (Blueprint $table) {
            $table->boolean('hidden')->default(false);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('unmanaged_shipping_enabled')->default(false);
        });
    }

    public function down()
    {
        Schema::table('modifier_types', function (Blueprint $table) {
            $table->dropColumn('hidden');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('unmanaged_shipping_enabled');
        });
    }
}
