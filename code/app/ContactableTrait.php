<?php

namespace App;

use Illuminate\Http\Request;

trait ContactableTrait
{
    public function contacts()
    {
        return $this->morphMany('App\Contact', 'target');
    }

    public function updateContacts($request)
    {
        $ids = [];
        $types = [];
        $values = [];

        if (is_array($request)) {
            if (isset($request['contact_id'])) {
                $ids = $request['contact_id'];
                $types = $request['contact_type'];
                $values = $request['contact_value'];
            }
        }
        else {
            $ids = $request->input('contact_id', []);
            $types = $request->input('contact_type', []);
            $values = $request->input('contact_value', []);
        }

        $contacts = [];

        foreach($ids as $index => $id) {
            if (empty($values[$index]))
                continue;

            if (empty($id)) {
                $contact = new Contact();

                $test_existing = $this->contacts()->where('type', $types[$index])->where('value', $values[$index])->first();
                if (!empty($test_existing)) {
                    $contacts[] = $test_existing->id;
                    continue;
                }
            }
            else {
                $contact = Contact::find($id);
            }

            $contact->target_id = $this->id;
            $contact->target_type = get_class($this);
            $contact->type = $types[$index];
            $contact->value = $values[$index];
            $contact->save();

            $contacts[] = $contact->id;
        }

        $this->contacts()->whereNotIn('id', $contacts)->delete();
    }

    public function addContact($type, $value)
    {
        $contact = new Contact();
        $contact->target_id = $this->id;
        $contact->target_type = get_class($this);
        $contact->type = $type;
        $contact->value = $value;
        $contact->save();
    }

    /*
        Questa viene usata da ManyMailNotification per popolare la notifica
        mail con tutte le destinazioni possibili. La prima mail disponibile
        viene già aggiunta dal sistema delle notifiche (e acceduta per mezzo del
        mutator getEmailAttribute()), le eventuali altre sono messe in CC.
        Sconsigliato usare questa funzione altrove
    */
    public function messageAll(&$message)
    {
        $master_mail = $this->email;

        foreach($this->contacts as $contact) {
            if ($contact->type == 'email' && $contact->value != $master_mail) {
                $message->cc($contact->value);
            }
        }
    }

    /*
        Questo è per sopperire alla mancanza di un attributo "email", richiesto
        ad esempio dalle funzioni per recuperare la password.
    */
    public function getEmailAttribute()
    {
        $contact = $this->contacts()->where('type', 'email')->first();
        if ($contact != null)
            return $contact->value;
        else
            return '';
    }

    public function getMobileAttribute()
    {
        $contact = $this->contacts()->where('type', 'mobile')->first();
        if ($contact != null)
            return $contact->value;
        else
            return '';
    }

    public function getAddress()
    {
        $address = $this->contacts()->where('type', 'address')->first();
        if (is_null($address) || empty($address->value)) {
            return ['', '', ''];
        }

        // @phpstan-ignore-next-line
        return $address->asAddress();
    }

    public function getContactsByType($type)
    {
        $ret = [];

        $contacts = $this->contacts()->where('type', $type)->get();
        foreach($contacts as $contact)
            $ret[] = $contact->value;

        return $ret;
    }
}
