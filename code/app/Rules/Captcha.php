<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Session;

class Captcha implements Rule
{
    public function passes($attribute, $value)
    {
        return trim($value) == Session::get('captcha_solution');
    }

    public function message()
    {
        return _i('Il codice di controllo è sbagliato');
    }
}
