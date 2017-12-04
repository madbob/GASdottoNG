@foreach($object->roles as $role)
    @if($role->always)
        @continue
    @endif

    <div class="form-group">
        <label class="col-sm-{{ $labelsize }} control-label">{{ $role->name }}</label>

        <div class="col-sm-{{ $fieldsize - 1 }}">
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
        </div>
    </div>
@endforeach

@if($editable && (Gate::check('users.admin', $currentgas) || Gate::check('gas.permissions', $currentgas)))
    <button class="btn btn-default pull-right async-modal" data-target-url="{{ url('/roles/user/' . $object->id) }}">{{ _i('Edita Ruoli') }}</button>
@endif
