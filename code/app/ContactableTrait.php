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
            if (empty($id))
                $contact = new Contact();
            else
                $contact = Contact::find($id);

            $contact->target_id = $this->id;
            $contact->target_type = get_class($this);
            $contact->type = $types[$index];
            $contact->value = $values[$index];
            $contact->save();
            $contacts[] = $contact->id;
        }

        $this->contacts()->whereNotIn('id', $contacts)->delete();
    }
}
