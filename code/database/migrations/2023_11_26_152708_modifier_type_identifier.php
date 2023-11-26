<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('modifier_types', function (Blueprint $table) {
            $table->string('identifier')->default('')->after('system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modifier_types', function (Blueprint $table) {
            $table->dropColumn('identifier');
        });
    }
};
