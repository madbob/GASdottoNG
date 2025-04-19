@php

if (!isset($static_view)) {
    $static_view = false;
}

if (!isset($skip_void)) {
    $skip_void = false;
}

if (!isset($suggestion)) {
    $suggestion = '';
}

if ($static_view) {
    $route = 'modifiers.show';
}
else {
    $route = 'modifiers.edit';
}

$modificationTypes = $obj->applicableModificationTypes();

$actual_modifiers = [];
foreach($modificationTypes as $mod) {
    $am = [];

    foreach ($obj->modifiers()->where('modifier_type_id', $mod->id)->get() as $m) {
        if ($skip_void == false || $m->active || $m->always_on) {
            $am[] = $m;
        }
    }

    if (empty($am) == false) {
        $actual_modifiers[$mod->name] = $am;
    }
}

@endphp

@if(empty($modificationTypes) || empty($actual_modifiers) == false)
    <div class="card mb-4">
        <div class="card-header">
            {{ _i('Modificatori') }}
        </div>
        <div class="card-body">
            @if(empty($modificationTypes))
                <div class="alert alert-info">
                    {{ _i('Non ci sono modificatori assegnabili a questo tipo di elemento.') }}
                </div>
            @else
                @if(filled($suggestion))
                    <x-larastrap::suggestion>
                        {!! $suggestion !!}
                    </x-larastrap::suggestion>
                @endif

                @foreach($modificationTypes as $mod)
                    @if(!empty($actual_modifiers[$mod->name]))
                        <x-larastrap::field :label="$mod->name">
                            @foreach($actual_modifiers[$mod->name] as $m)
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
        </div>
    </div>
@endif
