<?php

/*
    Questa classe rappresenta un tipo di movimento contabile di default.
    Tutte le altre classi in App\Parameters\MovementType vengono usate dal
    seeder di base e - per i movimenti di sistema, che non sono eliminabili -
    forniscono le callback che permettono di costruire e rimuovere le referenze
    tra i movimenti contabili ed i soggetti coinvolti
*/

namespace App\Parameters\ModifierType;

use App\ModifierType as MovementTypeModel;

abstract class ModifierType
{
    public function create()
    {
        $type = new MovementTypeModel();
        $type->id = $this->identifier();
        $type = $this->initNew($type);
        $type->save();
    }

    public abstract function identifier();
    public abstract function initNew($type);
}
