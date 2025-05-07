<?php

namespace App\Observers;

use App\Contact;

class ContactObserver
{
    public function saving(Contact $contact)
    {
        switch ($contact->type) {
            case 'address':
                $items = $contact->asAddress();
                $contact->value = normalizeAddress($items[0], $items[1], $items[2]);
                break;

            case 'email':
                if (filter_var($contact->value, FILTER_VALIDATE_EMAIL) === false) {
                    return false;
                }
                break;

            case 'website':
                $contact->value = normalizeUrl($contact->value);
                break;
        }

        return true;
    }
}
