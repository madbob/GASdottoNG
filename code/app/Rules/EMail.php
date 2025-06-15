<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Contact;

class EMail implements Rule
{
    public function passes($attribute, $value)
    {
        return Contact::where('type', 'email')->where('value', $value)->count() == 0;
    }

    public function message()
    {
        return __('texts.user.help.existing_email_error');
    }
}
