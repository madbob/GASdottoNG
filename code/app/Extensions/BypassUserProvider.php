<?php

namespace App\Extensions;

use Illuminate\Auth\EloquentUserProvider;

use Log;

use App\Contact;

class BypassUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        foreach($credentials as $key => $value) {
            if ($key == 'email') {
                $contact = Contact::where('type', 'email')->where('value', $value)->first();
                if (is_null($contact)) {
                    Log::error('Email not found while trying to reset password: ' . $value);
                    return null;
                }
                else {
                    return $contact->target;
                }
            }
        }

        Log::debug('Falling back searching user to reset password: ' . print_r($credentials, true));
        return parent::retrieveByCredentials($credentials);
    }
}
