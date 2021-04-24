<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersSuspended extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('suspended_at')->nullable()->after('deleted_at')->default(null);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->date('suspended_at')->nullable()->after('deleted_at')->default(null);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suspended_at');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('suspended_at');
        });
    }
}
