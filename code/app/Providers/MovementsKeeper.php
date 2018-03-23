<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Log;
use App;
use Auth;

use App\Movement;

class MovementsKeeper extends ServiceProvider
{
    private function verifyConsistency($movement)
    {
        $metadata = $movement->type_metadata;

        if ($metadata == null) {
            Log::error('Impossibile recuperare informazioni su movimento tipo ' . $movement->type);
            return false;
        }

        if ($movement->archived == true) {
            Log::error(_i('Movimento: tentata modifica di movimento già storicizzato in bilancio passato'));
            return false;
        }

        if ($metadata->sender_type == null) {
            $movement->sender_type = null;
            $movement->sender_id = null;
        }
        else {
            if ($metadata->sender_type != $movement->sender_type) {
                Log::error(_i('Movimento: sender_type non coerente (%s != %s)', $metadata->sender_type, $movement->sender_type));
                return false;
            }
        }

        if ($metadata->target_type == null) {
            $movement->target_type = null;
            $movement->target_id = null;
        }
        else {
            if ($metadata->target_type != $movement->target_type) {
                Log::error(_i('Movimento: target_type non coerente (%s != %s)', $metadata->target_type, $movement->target_type));
                return false;
            }
        }

        $found = false;
        $operations = json_decode($metadata->function);
        foreach($operations as $op) {
            if ($movement->method == $op->method) {
                $found = true;
                break;
            }
        }
        if ($found == false) {
            Log::error(_i('Movimento: metodo non permesso'));
            return false;
        }

        if ($metadata->allow_negative == false && $movement->amount < 0) {
            Log::error(_i('Movimento: ammontare negativo non permesso'));
            return false;
        }

        return true;
    }

    public function boot()
    {
        Movement::saving(function ($movement) {
            if ($movement->date == null)
                $movement->date = date('Y-m-d G:i:s');
            if ($movement->registration_date == null)
                $movement->registration_date = date('Y-m-d G:i:s');
            if ($movement->registerer_id == null)
                $movement->registerer_id = Auth::user()->id;
            if ($movement->identifier == null)
                $movement->identifier = '';
            if ($movement->notes == null)
                $movement->notes = '';

            if ($movement->exists == false && $movement->archived == true)
                return true;

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
                    Log::error(_i('Movimento: salvataggio negato da pre-callback'));
                    return false;
                }
                else if ($pre == 2) {
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
            if ($movement->exists)
                $movement->updated_at = date('Y-m-d H:i:s');

            return $this->verifyConsistency($movement);
        });

        Movement::saved(function ($movement) {
            if ($movement->archived)
                return;

            $metadata = $movement->type_metadata;

            if (isset($metadata->callbacks['post'])) {
                $metadata->callbacks['post']($movement);
            }

            $movement->apply();
            $movement->saved = true;
        });

        /*
            Questo è per invertire l'effetto del movimento contabile modificato
            sui bilanci, in modo che possa poi essere riapplicato coi nuovi
            valori
        */
        Movement::updating(function ($movement) {
            /*
                Reminder: per invalidare il movimento devo sottoporre un
                ammontare negativo (pari al negativo dell'ammontare
                precedentemente salvato), il quale potrebbe non essere accettato
                dal tipo di movimento stesso
            */

            if ($this->verifyConsistency($movement) == false)
                return false;

            /*
                Se mi trovo in fase di ricalcolo dei saldi, non inverto
                l'effetto del movimento: in tal caso il saldo attuale è già
                stato riportato alla situazione di partenza, e rieseguo tutti i
                movimenti come se fosse la prima volta.
            */
            if (App::make('MovementsHub')->isRecalculating() == true)
                return true;

            $original = Movement::find($movement->id);
            $original->amount = $original->amount * -1;
            $original->apply();

            return true;
        });

        /*
            Questo è per invertire l'effetto del movimento contabile cancellato
            sui bilanci
        */
        Movement::deleting(function ($movement) {
            $metadata = $movement->type_metadata;

            if (isset($metadata->callbacks['delete'])) {
                $metadata->callbacks['delete']($movement);
            }

            $movement->amount = $movement->amount * -1;
            $movement->apply();
        });
    }

    public function register()
    {
    }
}
