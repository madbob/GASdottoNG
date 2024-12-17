<?php

namespace App\Extensions;

use Illuminate\Auth\EloquentUserProvider;

use Log;

use App\Contact;

class BypassUserProvider extends EloquentUserProvider
{
    private function mapEmail($email)
    {
        $contact = Contact::whereIn('type', ['email', 'skip_email'])->where('value', $email)->first();
        if (is_null($contact)) {
            Log::error('Email not found while trying to reset password: ' . $email);

            return null;
        }
        else {
            return $contact->target;
        }
    }

    public function retrieveByCredentials(array $credentials)
    {
        foreach ($credentials as $key => $value) {
            if ($key == 'email') {
                return $this->mapEmail($value);
            }
        }

        Log::debug('Falling back searching user to reset password: ' . print_r($credentials, true));

        return parent::retrieveByCredentials($credentials);
    }
}
