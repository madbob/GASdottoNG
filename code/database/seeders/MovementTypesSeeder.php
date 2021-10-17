<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\MovementType;

class MovementTypesSeeder extends Seeder
{
    public function run()
    {
        foreach (predefinedMovementTypes() as $identifier => $instance) {
            if (MovementType::find($identifier) == null) {
                $instance->create();
            }
        }
    }
}
