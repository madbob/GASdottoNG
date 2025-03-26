<?php

if (!isset($static_view)) {
    $static_view = false;
}

if (!isset($skip_void)) {
    $skip_void = false;
}

if ($static_view) {
    $route = 'modifiers.show';
}
else {
    $route = 'modifiers.edit';
}

$modificationTypes = $obj->applicableModificationTypes();

?>

@if(empty($modificationTypes))
    <x-larastrap::field :label="_i('Modificatori')">
        <div class="alert alert-info">
            {{ _i('Non ci sono modificatori assegnabili a questo tipo di elemento.') }}
        </div>
    </x-larastrap::field>
@else
    @foreach($modificationTypes as $mod)
        <?php

        $actual_modifiers = [];

        foreach ($obj->modifiers()->where('modifier_type_id', $mod->id)->get() as $m) {
            if ($skip_void == false || $m->active || $m->always_on) {
                $actual_modifiers[] = $m;
            }
        }

        ?>

        @if(!empty($actual_modifiers))
            <x-larastrap::field :label="$mod->name">
                @foreach($actual_modifiers as $m)
                    {{--
                        Qui evito di usare x-larastrap::button dovendo formattare un
                        contenuto particolarmente complesso all'interno del button
                    --}}
                    <button class="async-modal btn btn-light" data-modal-url="{{ route($route, $m->id) }}">
                        <span data-updatable-name="modifier-button-{{ $m->modifierType->id }}-{{ $m->target_id }}" data-updatable-field="name">{{ $m->name }}</span>
                    </button>
                @endforeach
            </x-larastrap::field>
        @endif
    @endforeach
@endif
