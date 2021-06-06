<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\ModifierType;

class ModifierTypesSeeder extends Seeder
{
    public function run()
    {
        if (ModifierType::find('spese-trasporto') == null) {
            $m = new ModifierType();
            $m->id = 'spese-trasporto';
            $m->name = _i('Spese Trasporto');
            $m->system = true;
            $m->classes = ['App\Product', 'App\Supplier'];
            $m->save();
        }

        if (ModifierType::find('sconto') == null) {
            $m = new ModifierType();
            $m->id = 'sconto';
            $m->name = _i('Sconto');
            $m->system = true;
            $m->classes = ['App\Product', 'App\Supplier'];
            $m->save();
        }
    }
}
