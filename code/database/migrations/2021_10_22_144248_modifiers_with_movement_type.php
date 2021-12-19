<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifiersWithMovementType extends Migration
{
    public function up()
    {
        Schema::table('modifiers', function (Blueprint $table) {
            $table->string('movement_type_id')->nullable()->default(null);
        });
    }

    public function down()
    {
        Schema::table('modifiers', function (Blueprint $table) {
            $table->dropColumn('movement_type_id');
        });
    }
}
