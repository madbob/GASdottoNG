<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('modifiers', function (Blueprint $table) {
            $table->string('applies_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modifiers', function (Blueprint $table) {
            $table->enum('applies_type', ['none', 'quantity', 'price', 'weight'])->change();
        });
    }
};
