<div role="tabpanel" class="tab-pane" id="permissions-{{ $user->id }}-{{ $role->id }}">
    <div class="row">
        <div class="col-md-12">
            <div class="checkbox pull-right">
                <label>
                    <input type="checkbox" class="triggers-all-checkbox" data-target-class="supplier-for-{{ $user->id }}-{{ $role->id }}"> Seleziona/Deseleziona Tutti
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <ul class="list-group">
                <?php $r = $user->roles()->where('roles.id', $role->id)->first() ?>
                @foreach($role->targets as $target)
                    <li class="list-group-item">
                        {{ $target->printableName() }}
                        <span class="pull-right">
                            <input type="checkbox" class="supplier-for-{{ $user->id }}-{{ $role->id }}" data-toggle="toggle" data-size="mini" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="{{ $target->id }}" data-target-class="{{ get_class($target) }}" {{ $r->applies($target) ? 'checked' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-danger remove-role" data-role="{{ $role->id }}" data-user="{{ $user->id }}">Revoca Ruolo {{ $role->name }} a {{ $user->printableName() }}</button>
        </div>
    </div>
</div>
