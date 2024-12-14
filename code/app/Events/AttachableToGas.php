<?php

/*
    Questo evento serve a forzare la relazione
    nuovo oggetto creato -> GAS corrente
    per tutti i tipi di oggetto che possono essere condivisi tra più GAS (ovvero
    i modelli che usano il trait WithinGas)
*/

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

use App\Models\Concerns\WithinGas;

class AttachableToGas
{
    use SerializesModels;

    public $group;

    public $attachable;

    private function simplifyClassname($class)
    {
        /*
            In sostanza, qui si genera il nome dell'attributo della classe Gas
            che rappresenta la relazione qui interessata.
            Ad esempio, se $attachable è un Supplier qui viene generata la
            stringa "suppliers" che è anche il nome della funzione di Gas con la
            relazione coi Supplier
        */
        return Str::plural(mb_strtolower(class_basename($class)));
    }

    public function __construct(Model $attachable)
    {
        $this->attachable = $attachable;
        $class = get_class($attachable);

        if (in_array($class, array_keys(modelsUsingTrait(WithinGas::class)))) {
            $this->group = $this->simplifyClassname($class);
        }
        else {
            $this->group = null;
        }
    }
}
