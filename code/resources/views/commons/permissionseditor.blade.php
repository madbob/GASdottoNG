<!--
Questo pannello viene ricaricato alla chiusura del modale di modifica dei
permessi, cfr. permissions.supplier-edit
-->
<div id="permissions-list-{{ sanitizeId($object->id) }}" data-reload-url="{{ route('roles.suppliertable', $object->id) }}">
    @foreach(rolesByClass(get_class($object)) as $role)
        <x-larastrap::field :label="$role->name">
            <label class="static-label">
                <?php

                $final = [];

                $users = $role->usersByTarget($object);
                foreach($users as $user)
                    $final[] = $user->printableName();

                ?>

                {{ join(', ', $final) }}
            </label>
        </x-larastrap::field>
    @endforeach

    @if($editable && (Gate::check('supplier.modify', $object) || Gate::check('gas.permissions', $currentgas)))
        <x-larastrap::ambutton :label="_i('Edita Ruoli')" :data-modal-url="url('/roles/supplier/' . $object->id)" classes="float-end" />
    @endif
</div>
