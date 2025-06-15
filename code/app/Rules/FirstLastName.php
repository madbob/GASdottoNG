<?php

/*
    Regola "fasulla" di validazione: fallisce sempre.
    Viene usata in RegisterController per segnalare i casi - non ammessi - di
    omonimia (o, più frequentemente, di utenti amici che si registrano
    nuovamente)
*/

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FirstLastName implements Rule
{
    public function passes($attribute, $value)
    {
        return false;
    }

    public function message()
    {
        return __('texts.user.help.duplicated_name_error');
    }
}
