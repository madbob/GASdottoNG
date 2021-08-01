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
            {{--
                Qui evito di usare x-larastrap::button dovendo formattare un
                contenuto particolarmente complesso all'interno del button
            --}}
            <button class="async-modal btn btn-light" data-modal-url="{{ route($route, $m->id) }}" dusk="modifier_{{ \Illuminate\Support\Str::slug($mod->name) }}">
                <span data-updatable-name="modifier-button-{{ $m->modifierType->id }}-{{ $m->target_id }}" data-updatable-field="name">{{ $m->name }}</span>
            </button>
        @endforeach
    </x-larastrap::field>
@endforeach
