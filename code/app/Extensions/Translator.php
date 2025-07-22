<?php

/*
    Questo, che viene registrato da AppServiceProvider per rimpiazzare l'omonimo
    service provider nativo di Laravel, serve a trattare le stringhe vuote delle
    traduzioni come stringhe mancanti, e dunque da tradurre usando la lingua di
    fallback
*/

namespace App\Extensions;

use Illuminate\Translation\Translator as OriginalTranslator;

class Translator extends OriginalTranslator
{
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $line = parent::get($key, $replace, $locale, $fallback);
        if (is_string($line)) {
            if (blank($line)) {
                return $this->get($key, $replace, config('app.fallback_locale'), false);
            }
        }

        return $line;
    }
}
