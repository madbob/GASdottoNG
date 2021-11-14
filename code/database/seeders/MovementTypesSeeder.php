<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\MovementType;

class MovementTypesSeeder extends Seeder
{
    public function run()
    {
        foreach (systemParameters('MovementType') as $identifier => $instance) {
            if (MovementType::find($identifier) == null) {
                $instance->create();
            }
        }
    }
}
