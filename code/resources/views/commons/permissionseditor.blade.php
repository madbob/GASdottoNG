@foreach(App\Role::rolesByClass(get_class($object)) as $role)
    <x-larastrap::field :label="$role->name">
        <label class="static-label">
            <?php

            $final = [];

            $users = $role->usersByTarget($supplier);
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
