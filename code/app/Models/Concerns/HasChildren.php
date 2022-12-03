<?php

/*
    Attenzione: questa interfaccia viene usata praticamente solo come utility
    per generare widgets grafici e per determinare una gerarchia nella selezione
    dei modelli.
    Non va usata in tutti i casi in cui esista una relazione parent -> children
*/

namespace App\Models\Concerns;

interface HasChildren
{
    public function children();
}
