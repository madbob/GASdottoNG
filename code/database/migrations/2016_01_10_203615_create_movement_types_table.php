<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementTypesTable extends Migration
{
        public function up()
        {
                Schema::create('movement_types', function (Blueprint $table) {
                        $table->string('id')->primary();
                        $table->timestamps();

                        $table->string('name');
                        $table->string('method')->nullable();
                        $table->boolean('negative_allowed');
                        $table->decimal('default_amount', 6, 2);
                        $table->enum('gas_bank_op', ['void', 'up', 'down']);
                        $table->enum('gas_cash_op', ['void', 'up', 'down']);
                        $table->enum('gas_suppliers_op', ['void', 'up', 'down']);
                        $table->enum('gas_deposits_op', ['void', 'up', 'down']);
                        $table->enum('supplier_op', ['void', 'up', 'down']);
                        $table->enum('user_op', ['void', 'up', 'down']);
                        $table->boolean('update_deposit');
                        $table->boolean('update_annual_fee');

                        $table->index('id');
                });
        }

        public function down()
        {
                Schema::drop('movement_types');
        }
}
