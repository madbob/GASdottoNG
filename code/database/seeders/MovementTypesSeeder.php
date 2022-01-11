<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\MovementType;

class MovementTypesSeeder extends Seeder
{
    public function run()
    {
        foreach (systemParameters('MovementType') as $identifier => $instance) {
            if (MovementType::withTrashed()->where('id', $identifier)->first() == null) {
                $instance->create();
            }
        }
    }
}
