<?php

namespace App\Listeners;

use App\Events\SluggableCreating;

class SlugModel
{
    private function testUnique($class, $id)
    {
        $index = 1;
        $template = $id;

        do {
            $test = $class::tFind($id);
            if (is_null($test)) {
                break;
            }

            $id = $template . '-' . $index++;
        } while(true);

        return $id;
    }

    public function handle(SluggableCreating $event)
    {
        if (empty($event->sluggable->id)) {
            $id = trim($event->sluggable->getSlugID());
            $class = get_class($event->sluggable);

            if ($class) {
                $id = $this->testUnique($class, $id);

                /*
                    Attenzione!!!
                    Quando il nome di una variabile in POST contiene un punto,
                    Laravel lo traduce silenziosamente in un underscore. Questo
                    per far funzionare la "dot notation" per accedere ad
                    informazioni strutturate in array.
                    Poiché gli ID degli oggetti sono spesso usati per costruire
                    i nomi delle variabili in POST, qui li sopprimiamo
                    direttamente
                */
                $id = str_replace('.', '', $id);

                /*
                    Dagli ID sopprimiamo anche gli slash, che se concatenati
                    agli URL li rompono malamente
                */
                $id = str_replace('/', '', $id);

                $id = str_replace("'", '', $id);

                $event->sluggable->id = $id;
            }
        }
    }
}
