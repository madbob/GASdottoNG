<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Invoice;

class RestrictToGas extends Migration
{
    public function up()
    {
        $main_gas = currentAbsoluteGas();

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('gas_id');
        });

        foreach(Invoice::withoutGlobalScopes()->get() as $invoice) {
            $invoice->gas_id = $main_gas->id;
            $invoice->save();
        }
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('gas_id');
        });
    }
}
