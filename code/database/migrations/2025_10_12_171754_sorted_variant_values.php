<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Variant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('variant_values', function (Blueprint $table) {
            $table->integer('sorting')->default(0);
        });

        $variants = Variant::all();
        foreach($variants as $variant) {
            $index = 0;

            foreach($variant->values()->orderBy('value', 'asc')->get() as $val) {
                $val->sorting = $index++;
                $val->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variant_values', function (Blueprint $table) {
            $table->dropColumn('sorting');
        });
    }
};
