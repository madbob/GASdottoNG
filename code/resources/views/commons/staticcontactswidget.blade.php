@foreach(($obj ? $obj->contacts : []) as $contact)
    @include('commons.staticstringfield', ['obj' => $contact, 'name' => 'value', 'label' => $contact->type_name])
@endforeach
