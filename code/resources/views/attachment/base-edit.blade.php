<x-larastrap::text name="name" :label="_i('Nome')" required />

<x-larastrap::radios name="type" :label="_i('Tipo')" value="file" :options="['file' => _i('File'), 'url' => _i('URL')]" classes="selective-display" :attributes="['data-target' => '.attachment_type']" />

<div class="attachment_type" data-type="file">
    <x-larastrap::file name="file" :label="_i('File')" />
</div>
<div class="attachment_type" data-type="url">
    <x-larastrap::url name="url" :label="_i('URL')" />
</div>

@include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => _i('Destinatari')])
