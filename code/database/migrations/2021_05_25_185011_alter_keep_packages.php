<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

use App\Order;

class AlterKeepPackages extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('keep_open_packages')->default('no')->change();
        });

        Order::where('keep_open_packages', '0')->update(['keep_open_packages' => 'no']);
        Order::where('keep_open_packages', '1')->update(['keep_open_packages' => 'each']);
    }

    public function down()
    {
        Order::where('id', '!=', '0')->update(['keep_open_packages' => '0']);

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('keep_open_packages')->default(false)->change();
        });
    }
}
