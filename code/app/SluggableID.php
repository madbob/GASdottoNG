<?php

namespace App;

use Illuminate\Support\Str;

trait SluggableID
{
    public function getSlugID()
    {
        $append = '';
        $index = 1;
        $classname = get_class($this);

        while (true) {
            $slug = Str::slug($this->name) . $append;

            /*
                Questo è per prevenire i (rari, ma possibili) casi in cui si
                cerca di creare una istanza con un nome vuoto. In tal caso anche
                l'ID sarebbe vuoto, cosa che spacca poi la costruzione degli URL
                e, di conseguenza, genera problemi apparentemente random nel
                frontend
            */
            if (blank($slug)) {
                $slug = Str::random(10);
            }

            if ($classname::withoutGlobalScope('gas')->where('id', $slug)->first() != null) {
                $append = '_' . $index;
                $index++;
            }
            else {
                break;
            }
        }

        return $slug;
    }
}
