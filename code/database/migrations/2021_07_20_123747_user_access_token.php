<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UserAccessToken extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('access_token')->default('');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('access_token');
        });
    }
}
