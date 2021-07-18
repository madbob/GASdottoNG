<?php

if (!isset($static_view)) {
    $static_view = false;
}

if ($static_view) {
    $route = 'modifiers.show';
}
else {
    $route = 'modifiers.edit';
}

?>

@foreach($obj->applicableModificationTypes() as $mod)
    <x-larastrap::field :label="$mod->name">
        @foreach($obj->modifiers()->where('modifier_type_id', $mod->id)->get() as $m)
            <x-larastrap::ambutton :label="$m->name" :data-modal-url="route($route, $m->id)" />
        @endforeach
    </x-larastrap::field>
@endforeach
