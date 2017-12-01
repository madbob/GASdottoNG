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
            $table->text('rid')->nullable();
        });

        if (Schema::hasColumn('users', 'iban')) {
            foreach(User::all() as $user) {
                $new_format = ['iban' => $user->iban, 'date' => '', 'id' => ''];
                $user->rid = $new_format;
                $user->save();
            }
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rid');
        });
    }
}
