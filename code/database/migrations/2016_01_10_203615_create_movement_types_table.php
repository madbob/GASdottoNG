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
                        $table->boolean('method_required');
                        $table->decimal('default_amount', 5, 2);
                        $table->enum('gas_bank_op', ['void', 'up', 'down']);
                        $table->enum('gas_cash_op', ['void', 'up', 'down']);
                        $table->enum('gas_orders_op', ['void', 'up', 'down']);
                        $table->enum('gas_deposits_op', ['void', 'up', 'down']);
                        $table->enum('supplier_op', ['void', 'up', 'down']);
                        $table->enum('user_op', ['void', 'up', 'down']);
                });
        }

        public function down()
        {
                Schema::drop('movement_types');
        }
}
