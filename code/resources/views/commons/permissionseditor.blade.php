@foreach(App\Role::rolesByClass(get_class($object)) as $role)
    <div class="form-group">
        <label class="col-sm-{{ $labelsize }} control-label">{{ $role->name }}</label>

        <div class="col-sm-{{ $fieldsize - 1 }}">
            <label class="static-label">
                <?php

                $final = [];
                $users = $role->usersByTarget($supplier);

                foreach($users as $user)
                    $final[] = $user->printableName();

                ?>

                {{ join(', ', $final) }}
            </label>
        </div>
    </div>
@endforeach

@if(Gate::check('supplier.modify', $object) || Gate::check('gas.permissions', $currentgas))
    <button class="btn btn-default pull-right async-modal" data-target-url="{{ url('/roles/supplier/' . $object->id) }}">Edita Ruoli</button>
@endif
