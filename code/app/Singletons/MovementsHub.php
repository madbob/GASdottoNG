<?php

/*
    Questa class funge solo da lock generale per la gestione dei movimenti
    contabili. Da usare in casi molto specifici
*/

namespace App\Singletons;

class MovementsHub
{
    private $recalculating = false;
    private $suspended = false;

    /*
        Questo viene impostato durante il ricalcolo dei saldi.
        Cfr. MovementObserver::updating() per i dettagli
    */
    public function setRecalculating($active)
    {
        $this->recalculating = $active;
    }

    public function isRecalculating()
    {
        return $this->recalculating;
    }

    /*
        Questo viene impostato per ignorare del tutto le operazioni altrimenti
        gestite da MovementObserver.
        Cfr. DynamicBookingsService::dynamicModifiers() per i dettagli
    */
    public function setSuspended($active)
    {
        $this->suspended = $active;
    }

    public function isSuspended()
    {
        return $this->suspended;
    }
}
