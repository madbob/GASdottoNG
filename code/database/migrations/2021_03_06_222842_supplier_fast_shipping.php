<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Config;
use App\Supplier;

class SupplierFastShipping extends Migration
{
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('fast_shipping_enabled')->default(false);
        });

        $actual = Config::where('name', 'fast_shipping_enabled')->first();
        if ($actual && $actual->value == '1') {
            Supplier::where('id', '!=', '')->withTrashed()->update(['fast_shipping_enabled' => 1]);
        }
    }

    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('fast_shipping_enabled');
        });
    }
}
