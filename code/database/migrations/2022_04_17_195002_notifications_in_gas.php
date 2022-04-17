<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Gas;
use App\Notification;

class NotificationsInGas extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('gas_id')->default('');
        });

        $first_gas = Gas::orderBy('created_at', 'asc')->first();

        foreach(Notification::all() as $notification) {
            if ($notification->users->isEmpty() == false) {
                $notification->gas_id = $notification->users->first()->gas_id;
            }
            else {
                $notification->gas_id = $first_gas->id;
            }

            $notification->save();
        }
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('gas_id');
        });
    }
}
