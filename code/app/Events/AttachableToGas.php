<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

/*
    Questo evento serve a forzare la relazione
    nuovo oggetto creato -> GAS corrente
    per tutti i tipi di oggetto che possono essere condivisi tra più GAS
*/

class AttachableToGas
{
    use SerializesModels;

    public $group;
    public $attachable;

    public function __construct(Model $attachable)
    {
        $this->attachable = $attachable;

        $class = get_class($attachable);
        if ($class == 'App\Supplier')
            $this->group = 'suppliers';
        else if ($class == 'App\Aggregate')
            $this->group = 'aggregates';
        else if ($class == 'App\Delivery')
            $this->group = 'deliveries';
        else
            $this->group = null;
    }
}
