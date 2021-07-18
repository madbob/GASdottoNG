<?php

if (isset($target_update) == false) {
    $target_update = $typename.'-list';
}
if (isset($button_label) == false) {
    $button_label = _i('Crea Nuovo %s', $typename_readable);
}

$identifier = sprintf('create%s-%s', ucfirst($typename), Illuminate\Support\Str::random(10));

?>

@if(isset($dynamic_url))
    <x-larastrap::ambutton :label="$button_label" color="warning" :data-modal-url="$dynamic_url" classes="float-end" />
@else
    <x-larastrap::button :label="$button_label" color="warning" :triggers_modal="$identifier" classes="float-end" />

    <x-larastrap::modal :id="$identifier" :title="$button_label">
        <x-larastrap::form method="POST" :action="$targeturl" classes="creating-form">
            <input type="hidden" name="update-list" value="{{ $target_update }}">
            @include('commons.extrafields')

            <div class="modal-body">
                @include($template, [$typename => null])
            </div>
        </x-larastrap::form>
    </x-larastrap::modal>
@endif
