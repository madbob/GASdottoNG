@foreach($obj->applicableModificationTypes() as $mod)
    <x-larastrap::field :label="$mod->name">
        @foreach($obj->modifiers()->where('modifier_type_id', $mod->id)->get() as $m)
            <x-larastrap::ambutton :label="$m->name" :data-modal-url="route('modifiers.show', $m->id)" />
        @endforeach
    </x-larastrap::field>
@endforeach
