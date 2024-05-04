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
        Schema::create('groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->string('updated_by')->default('');
            $table->string('name');
            $table->string('description')->default('');
            $table->string('context')->default('user');
            $table->string('cardinality')->default('single');
            $table->boolean('user_selectable')->default(false);
            $table->boolean('filters_orders')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
