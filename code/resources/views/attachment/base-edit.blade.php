<x-larastrap::text name="name" tlabel="generic.name" required />

<x-larastrap::radios name="type" tlabel="generic.type" value="file" :options="['file' => __('texts.generic.file'), 'url' => __('texts.generic.url')]" classes="selective-display" :attributes="['data-target' => '.attachment_type']" />

<div class="attachment_type" data-type="file">
    <x-larastrap::file name="file" tlabel="generic.file" />
</div>
<div class="attachment_type" data-type="url">
    <x-larastrap::url name="url" tlabel="generic.url" />
</div>

@include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => __('texts.generic.recipients')])
