<?php

/*
    Questo evento serve a forzare la relazione
    nuovo oggetto creato -> GAS corrente
    per tutti i tipi di oggetto che possono essere condivisi tra più GAS
*/

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class AttachableToGas
{
    use SerializesModels;

    public $group;
    public $attachable;

    public function __construct(Model $attachable)
    {
        $this->attachable = $attachable;

        $class = get_class($attachable);
        $group = Str::plural(mb_strtolower(class_basename($class)));

        if (in_array($group, ['suppliers', 'aggregates', 'deliveries'])) {
            $this->group = $group;
        }
        else {
            $this->group = null;
        }
    }
}
