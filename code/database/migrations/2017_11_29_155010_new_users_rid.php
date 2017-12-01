<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\User;

class NewUsersRid extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('rid');
        });

        foreach(User::all() as $user) {
            $new_format = ['iban' => $user->iban, 'date' => '', 'id' => ''];
            $user->rid = $new_format;
            $user->save();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('iban');
            $table->dropColumn('sepa_subscribe');
            $table->dropColumn('sepa_first');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rid');

            $table->string('iban')->default('');
            $table->date('sepa_subscribe')->nullable();
            $table->date('sepa_first')->nullable();
        });
    }
}
