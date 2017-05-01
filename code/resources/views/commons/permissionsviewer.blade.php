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

                ?>

                {{ join(', ', $final) }}
            </label>
        </div>
    </div>
@endforeach
