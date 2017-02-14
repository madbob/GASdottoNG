<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Log;
use App\Movement;

class MovementsKeeper extends ServiceProvider
{
    private function verifyConsistency($movement)
    {
        $metadata = $movement->type_metadata;

        if ($metadata->sender_type != $movement->sender_type) {
            Log::error('Movimento: sender_type non coerente ('.$metadata->sender_type.' != '.$movement->sender_type.')');
            return false;
        }

        if ($metadata->target_type != $movement->target_type) {
            Log::error('Movimento: target_type non coerente ('.$metadata->target_type.' != '.$movement->target_type.')');
            return false;
        }

        if (isset($metadata->methods[$movement->method]) == false) {
            Log::error('Movimento: metodo non permesso');
            return false;
        }

        if ($metadata->allow_negative == false && $movement->amount < 0) {
            Log::error('Movimento: ammontare negativo non permesso');
            return false;
        }

        return true;
    }

    public function boot()
    {
        Movement::saving(function ($movement) {
            $metadata = $movement->type_metadata;

            /*
                La pre-callback può tornare:

                0 se il salvataggio viene negato
                1 se il salvataggio viene concesso
                2 se la callback stessa ha già provveduto a fare quanto
                  necessario. In tal caso blocchiamo il salvataggio e settiamo
                  artificiosamente l'attributo "saved" a true.
                  Per maggiori informazioni, cfr. Movement::saved
            */
            if (isset($metadata->callbacks['pre'])) {
                $pre = $metadata->callbacks['pre']($movement);
                if ($pre == 0) {
                    Log::error('Movimento: salvataggio negato da pre-callback');
                    return false;
                }
                else if ($pre == 2) {
                    $movement->saved = true;
                    return false;
                }
            }

            return $this->verifyConsistency($movement);
        });

        Movement::saved(function ($movement) {
            $metadata = $movement->type_metadata;

            if (isset($metadata->callbacks['post'])) {
                $metadata->callbacks['post']($movement);
            }
            if (isset($metadata->methods[$movement->method])) {
                $handler = $metadata->methods[$movement->method]->handler;
                $handler($movement);
            }

            $movement->saved = true;
        });

        /*
            Questo è per invertire l'effetto del movimento contabile modificato
            sui bilanci, in modo che possa poi essere riapplicato coi nuovi
            valori

            TODO: impedire aggiornamento di movimenti precedenti alla chiusura
            dell'ultimo bilancio
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

            $original = Movement::find($movement->id);
            $metadata = $original->type_metadata;
            $original->amount = $original->amount * -1;
            $handler = $metadata->methods[$original->method]->handler;
            $handler($original);

            return true;
        });

        /*
            Questo è per invertire l'effetto del movimento contabile cancellato
            sui bilanci
        */
        Movement::deleting(function ($movement) {
            $metadata = $movement->type_metadata;
            $movement->amount = $movement->amount * -1;
            $handler = $metadata->methods[$movement->method]->handler;
            $handler($movement);
        });
    }

    public function register()
    {
    }
}
