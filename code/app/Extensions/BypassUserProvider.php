<?php

namespace App\Extensions;

use Illuminate\Auth\EloquentUserProvider;

use App\Contact;

class BypassUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        foreach($credentials as $key => $value) {
            if ($key == 'email') {
                $contact = Contact::where('type', 'email')->where('value', $value)->first();
                if ($contact == null)
                    return null;
                else
                    return $contact->target;
            }
        }

        return parent::retrieveByCredentials($credentials);
    }
}
