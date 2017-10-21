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
                foreach($role->applications() as $targets)
                    $final[] = $targets->printableName();

                if (empty($final))
                    if ($role->appliesAll())
                        $final[] = 'Tutti';

                ?>

                {{ join(', ', $final) }}
            </label>
        </div>
    </div>
@endforeach

@if(Gate::check('users.admin', $currentgas) || Gate::check('gas.permissions', $currentgas))
    <button class="btn btn-default pull-right async-modal" data-target-url="{{ url('/roles/user/' . $object->id) }}">Edita Ruoli</button>
@endif
