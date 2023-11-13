<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Exceptions\AuthException;

class BaseService
{
    public function ensureAuth($permissions = [], $or = true)
    {
        $user = Auth::user();

        if (is_null($user)) {
            /*
                Questo serve essenzialmente per i casi in cui un Service viene
                utilizzato all'interno di un Command
            */
            if (app()->runningInConsole()) {
                return true;
            }

            Log::info('Utente non autorizzato: non autenticato');
            throw new AuthException(401);
        }

        if (empty($permissions)) {
            return $user;
        }

        $has_something = false;

        foreach($permissions as $permission => $subject) {
            if ($subject == 'gas') {
                $subject = $user->gas;
            }

            if ($user->can($permission, $subject) == false) {
                if ($or == false) {
                    Log::info('Utente non autorizzato: ' . $user->id . ' non ha permesso ' . $permission);
                    throw new AuthException(403);
                }
            }
            else {
                $has_something = true;
            }
        }

        if ($has_something == false) {
            Log::info('Utente non autorizzato: ' . $user->id . ' non ha nessun permesso tra ' . join(', ', array_keys($permissions)));
            throw new AuthException(403);
        }

        return $user;
    }

    protected function setIfSet($target, array $source, $key, $default = null)
    {
        if (isset($source[$key])) {
            $target->$key = $source[$key];
        }
        else {
            if (is_null($default) == false) {
                $target->$key = $default;
            }
        }
    }

    protected function boolIfSet($target, array $source, $key, $default = null)
    {
        $target->$key = (isset($source[$key]) && $source[$key] !== false);
    }

    protected function transformAndSetIfSet($target, array $source, $key, $transformerFunction)
    {
        if (isset($source[$key])) {
            $target->$key = $transformerFunction($source[$key]);
        }
    }
}
