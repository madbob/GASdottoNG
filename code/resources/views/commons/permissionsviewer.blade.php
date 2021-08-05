<!--
Questo pannello viene ricaricato alla chiusura del modale di modifica dei
permessi, cfr. permissions.user-edit
-->
<div id="permissions-list-{{ sanitizeId($object->id) }}" data-reload-url="{{ route('roles.usertable', $object->id) }}">
    @foreach($object->roles as $role)
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
</div>
