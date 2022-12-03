<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Role;

class AdvancedRoles extends Migration
{
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('identifier')->default('');
            $table->boolean('system')->default(false);
        });

        foreach(Role::all() as $role) {
            switch($role->name) {
                case 'Utente':
                    $role->identifier = 'user';
                    $role->system = true;
                    break;

                case 'Amministratore':
                    $role->identifier = 'admin';
                    $role->system = true;
                    break;

                case 'Amico':
                    $role->identifier = 'friend';
                    $role->system = true;
                    break;

                case 'Referente':
                    $role->identifier = 'referent';
                    $role->system = true;
                    break;

                case 'Amministratore GAS Secondario':
                    $role->identifier = 'secondary_admin';
                    $role->system = true;
                    break;
            }

            $role->save();
        }
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('identifier');
            $table->dropColumn('system');
        });
    }
}
