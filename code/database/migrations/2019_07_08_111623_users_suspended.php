<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\User;
use App\Supplier;

class UsersSuspended extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('suspended_at')->nullable()->after('deleted_at')->default(null);
        });

        DB::statement('UPDATE users SET suspended_at = deleted_at WHERE suspended = 1');
        DB::statement('UPDATE users SET deleted_at = null WHERE suspended = 1');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suspended');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->date('suspended_at')->nullable()->after('deleted_at')->default(null);
        });

        DB::statement('UPDATE suppliers SET suspended_at = deleted_at WHERE suspended = 1');
        DB::statement('UPDATE suppliers SET deleted_at = null WHERE suspended = 1');

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('suspended');
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
