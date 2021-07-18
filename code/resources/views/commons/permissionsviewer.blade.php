@foreach($object->roles as $role)
    @if($role->always)
        @continue
    @endif

    <x-larastrap::field :label="$role->name">
        <label class="static-label">
            <?php

            $final = [];

            if ($role->appliesAll())
                $final[] = 'Tutti';

            foreach($role->applications() as $targets)
                $final[] = $targets->printableName();

            ?>

            {{ join(', ', $final) }}
        </label>
    </x-larastrap::field>
@endforeach

@if($editable && (Gate::check('users.admin', $currentgas) || Gate::check('gas.permissions', $currentgas)))
    <x-larastrap::ambutton classes="float-end" :label="_i('Edita Ruoli')" :data-modal-url="url('/roles/user/' . $object->id)" />
@endif
