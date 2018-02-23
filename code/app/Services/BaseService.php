<?php

namespace App\Services;

use App\Exceptions\AuthException;

use Auth;

class BaseService
{
    protected function ensureAuth($permissions = [], $or = true)
    {
        $user = Auth::user();
        if ($user == null) {
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
                    Log::info('Utente non autorizzato: non ha permesso ' . $permission);
                    throw new AuthException(403);
                }
            }
            else {
                $has_something = true;
            }
        }

        if ($has_something == false) {
            Log::info('Utente non autorizzato: non ha nessun permesso tra ' . join(', ', $permissions));
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
            if ($default != null) {
                $target->$key = $default;
            }
        }
    }

    protected function transformAndSetIfSet($target, array $source, $key, $transformerFunction)
    {
        if (isset($source[$key])) {
            $target->$key = $transformerFunction($source[$key]);
        }
    }
}
