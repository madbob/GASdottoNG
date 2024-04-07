<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function involvedTables()
    {
        return [
            'users',
            'suppliers',
            'products',
            'orders',
            'bookings',
            'booked_products',
            'modifiers',
            'movements',
            'invoices',
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $involved_tables = $this->involvedTables();

        foreach($involved_tables as $it) {
            Schema::table($it, function (Blueprint $table) {
                $table->string('updated_by')->default('')->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $involved_tables = $this->involvedTables();

        foreach($involved_tables as $it) {
            Schema::table($it, function (Blueprint $table) {
                $table->dropColumn('updated_by');
            });
        }
    }
};
