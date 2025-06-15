<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Movement;

class MovementObserver
{
    private $movements_hub;

    public function __construct()
    {
        $this->movements_hub = App::make('MovementsHub');
    }

    private function testPeer(&$movement, $metadata, $peer): bool
    {
        $type = sprintf('%s_type', $peer);
        $id = sprintf('%s_id', $peer);

        if (is_null($metadata->$type)) {
            $movement->$type = null;
            $movement->$id = null;
        }
        else {
            if ($metadata->$type != $movement->$type) {
                Log::error('Movimento ' . $movement->id . ': ' . $type . ' non coerente');

                return false;
            }
        }

        return true;
    }

    private function verifyConsistency($movement): bool
    {
        $metadata = $movement->type_metadata;

        if (is_null($metadata)) {
            Log::error('Impossibile recuperare informazioni su movimento tipo ' . $movement->type);

            return false;
        }

        if ($movement->archived == true) {
            Log::error('Movimento: tentata modifica di movimento già storicizzato in bilancio passato');

            return false;
        }

        foreach (['sender', 'target'] as $peer) {
            if ($this->testPeer($movement, $metadata, $peer) === false) {
                return false;
            }
        }

        $operations = json_decode($metadata->function);
        $valid = count(array_filter($operations, fn ($op) => $movement->method == $op->method)) > 0;
        if ($valid === false) {
            Log::error(sprintf('Movimento %d: metodo "%s" non permesso su tipo "%s"', [
                $movement->id,
                $movement->printablePayment(),
                $movement->printableType(),
            ]));

            return false;
        }

        if (!$metadata->allow_negative && $movement->amount < 0) {
            Log::error('Movimento: ammontare negativo non permesso');

            return false;
        }

        return true;
    }

    public function saving(Movement $movement)
    {
        if ($this->movements_hub->isSuspended()) {
            return true;
        }

        $movement->date = $movement->date ?: date('Y-m-d G:i:s');
        $movement->registration_date = $movement->registration_date ?: date('Y-m-d G:i:s');
        $movement->registerer_id = $movement->registerer_id ?: Auth::user()->id;
        $movement->identifier = $movement->identifier ?: '';
        $movement->notes = $movement->notes ?: '';
        $movement->currency_id = $movement->currency_id ?: defaultCurrency()->id;

        if (!$movement->exists && $movement->archived) {
            return true;
        }

        $metadata = $movement->type_metadata;

        /*
            La pre-callback può tornare:

            0 se il salvataggio viene negato
            1 se il salvataggio viene concesso
            2 se la callback stessa ha già provveduto a fare quanto
              necessario. In tal caso blocchiamo il salvataggio e settiamo
              artificiosamente l'attributo "saved" a true
        */
        if (isset($metadata->callbacks['pre'])) {
            $pre = $metadata->callbacks['pre']($movement);
            if ($pre == 0) {
                Log::error('Movimento: salvataggio negato da pre-callback');

                return false;
            }
            elseif ($pre == 2) {
                $movement->saved = true;

                return false;
            }
        }

        /*
            Se un movimento esistente viene salvato pur non essendo stato
            modificato, non viene chiamata la callback Movement::updating().
            Però viene sempre chiamata Movement::saved(), che applica il
            movimento ai saldi. Se updating() non viene chiamata, e pertanto
            l'effetto del movimento non viene invalidato prima di essere
            ri-applicato, ci si ritrova coi saldi sballati.
            Pertanto qui forzo la modifica di almeno un attributo per i
            movimenti esistenti, affinché updating() sia sempre eseguito
            (magari a vuoto, ma meglio in più che in meno)
        */
        if ($movement->exists) {
            $movement->updated_at = Carbon::now();
        }

        return $this->verifyConsistency($movement);
    }

    public function saved(Movement $movement)
    {
        if ($this->movements_hub->isSuspended()) {
            return;
        }

        if ($movement->archived) {
            return;
        }

        $metadata = $movement->type_metadata;

        if (isset($metadata->callbacks['post'])) {
            $metadata->callbacks['post']($movement);
        }

        $movement->apply();
        $movement->saved = true;
    }

    /*
        Questo è per invertire l'effetto del movimento contabile modificato
        sui bilanci, in modo che possa poi essere riapplicato coi nuovi
        valori
    */
    public function updating(Movement $movement)
    {
        /*
            Se mi trovo in fase di ricalcolo dei saldi, non inverto
            l'effetto del movimento: in tal caso il saldo attuale è già
            stato riportato alla situazione di partenza, e rieseguo tutti i
            movimenti come se fosse la prima volta.
        */
        if ($this->movements_hub->isRecalculating() || $this->movements_hub->isSuspended()) {
            return true;
        }

        /*
            Reminder: per invalidare il movimento devo sottoporre un
            ammontare negativo (pari al negativo dell'ammontare
            precedentemente salvato), il quale potrebbe non essere accettato
            dal tipo di movimento stesso
        */

        if ($this->verifyConsistency($movement) === false) {
            return false;
        }

        $original = Movement::find($movement->id);
        $original->amount = $original->amount * -1;
        $original->apply();

        return true;
    }

    /*
        Questo è per invertire l'effetto del movimento contabile cancellato
        sui bilanci
    */
    public function deleting(Movement $movement)
    {
        if ($this->movements_hub->isSuspended()) {
            return;
        }

        $metadata = $movement->type_metadata;

        if (isset($metadata->callbacks['delete'])) {
            $metadata->callbacks['delete']($movement);
        }

        $movement->amount = $movement->amount * -1;
        $movement->apply();

        foreach ($movement->related as $rel) {
            $rel->delete();
        }
    }
}
