<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SuspendedSuppliers extends Migration
{
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('suspended')->default(false);
        });
    }

    public function down()
    {
        if (Schema::hasColumn('suppliers', 'suspended')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('suspended');
            });
        }
    }
}
