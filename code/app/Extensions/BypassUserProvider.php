<?php

namespace App\Extensions;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Log;

use App\User;
use App\Contact;

class BypassUserProvider extends EloquentUserProvider
{
    private function mapEmail($email)
    {
        $contacts = Contact::whereIn('type', ['email', 'skip_email'])->where('value', $email)->get();
        if ($contacts->isEmpty()) {
            Log::error('Email not found while trying to reset password: ' . $email);
            return null;
        }
        else {
            /*
                Reminder: un Contact può essere associato ad uno User, ma non
                solo. Se uno stesso indirizzo email viene associato sia ad uno
                User che ad un Supplier, devo accertarmi di recuperare proprio
                l'istanza di User interessata
            */
            foreach($contacts as $contact) {
                if ($contact->target && is_a($contact->target, User::class)) {
                    return $contact->target;
                }
            }

            Log::error('Email exists but is not associated to any user: ' . $email);
            return null;
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
