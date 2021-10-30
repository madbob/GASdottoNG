<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\ModifierType;

class ModifierTypesSeeder extends Seeder
{
    public function run()
    {
        foreach (systemParameters('ModifierType') as $identifier => $instance) {
            if (ModifierType::find($identifier) == null) {
                $instance->create();
            }
        }
    }
}
