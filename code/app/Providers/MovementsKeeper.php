<?php

namespace app\Providers;

use Illuminate\Support\ServiceProvider;
use Log;
use App\Movement;

class MovementsKeeper extends ServiceProvider
{
    public function boot()
    {
        Movement::saving(function ($movement) {
            $metadata = $movement->type_metadata;

            if (isset($metadata->callbacks['pre'])) {
                $pre = $metadata->callbacks['pre']($movement);
                if ($pre == false) {
                    return false;
                }
            }

            if ($metadata->sender_type != $movement->sender_type) {
                Log::error('Movimento: sender_type non coerente ('.$metadata->sender_type.' != '.$movement->sender_type.')');

                return false;
            }

            if ($metadata->target_type != $movement->target_type) {
                Log::error('Movimento: target_type non coerente ('.$metadata->target_type.' != '.$movement->target_type.')');

                return false;
            }

            if ($metadata->allow_negative == false && $movement->amount < 0) {
                Log::error('Movimento: ammontare negativo non permesso');

                return false;
            }

            return true;
        });

        Movement::saved(function ($movement) {
            $metadata = $movement->type_metadata;

            if (isset($metadata->callbacks['post'])) {
                $metadata->callbacks['post']($movement);
            }
            if (isset($metadata->methods[$movement->method_id])) {
                $metadata->methods[$movement->method_id]->handler($movement);
            }
        });
    }

    public function register()
    {
    }
}
