<?php

namespace App\Listeners;

use App\Events\SluggableCreating;

class SlugModel
{
    public function __construct()
    {
        //
    }

    public function handle(SluggableCreating $event)
    {
        if (empty($event->sluggable->id)) {
            $id = $template = $event->sluggable->getSlugID();
            $class = get_class($event->sluggable);
            $index = 1;

            do {
                if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
                    $test = $class::where('id', $id)->withTrashed()->first();
                else
                    $test = $class::where('id', $id)->first();

                if ($test == null)
                    break;

                $id = $template . '-' . $index++;
            } while(true);

            /*
                Attenzione!!!
                Quando il nome di una variabile in POST contiene un punto,
                Laravel lo traduce silenziosamente in un underscore. Questo per
                far funzionare la "dot notation" per accedere ad informazioni
                strutturate in array.
                Poiché gli ID degli oggetti sono spesso usati per costruire i
                nomi delle variabili in POST, qui li sopprimiamo direttamente
                sostituendoli
            */
            $id = str_replace('.', '_', $id);

            $event->sluggable->id = $id;
        }
    }
}
