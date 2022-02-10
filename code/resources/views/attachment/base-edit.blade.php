<x-larastrap::text name="name" :label="_i('Nome')" required />
<x-larastrap::file name="file" :label="_i('File')" required />
@include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => _i('Destinatari')])
