<?php

if (isset($target_update) == false) {
    $target_update = $typename.'-list';
}

if (isset($button_label) == false) {
    $button_label = _i('Crea %s', [$typename_readable]);
}

if (isset($autoread) == false) {
    $autoread = false;
}

$identifier = sprintf('create%s-%s', ucfirst($typename), Illuminate\Support\Str::random(10));

?>

@if(isset($dynamic_url))
    <x-larastrap::ambutton :label="$button_label" color="warning" :data-modal-url="$dynamic_url" classes="float-end" />
@else
    <x-larastrap::button :label="$button_label" color="warning" :triggers_modal="$identifier" classes="float-end" postlabel="<i class='bi-window'></i>" />

    <x-larastrap::modal :id="$identifier" :title="$button_label">
        <x-larastrap::iform method="POST" :obj="null" :action="$targeturl" :autoread="$autoread">
            <input type="hidden" name="void-form" value="1">
            <input type="hidden" name="test-feedback" value="1">
            <input type="hidden" name="close-modal" value="1">
            <input type="hidden" name="update-list" value="{{ $target_update }}">

            @include('commons.extrafields')
            @include($template, [$typename => null])
        </x-larastrap::iform>
    </x-larastrap::modal>
@endif
