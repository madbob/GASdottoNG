<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GasSoftDeletable extends Migration
{
    public function up()
    {
        Schema::table('gas', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->default(null)->after('updated_at');
        });
    }

    public function down()
    {
        Schema::table('gas', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
