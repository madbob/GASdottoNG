<div role="tabpanel" class="tab-pane" id="permissions-{{ $user->id }}-{{ $role->id }}">
    <ul class="list-group">
        <?php $r = $user->roles()->where('roles.id', $role->id)->first() ?>
        @foreach($role->targets as $target)
            <li class="list-group-item">
                {{ $target->printableName() }}
                <span class="pull-right">
                    <input type="checkbox" data-toggle="toggle" data-size="mini" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="{{ $target->id }}" data-target-class="{{ get_class($target) }}" {{ $r->applies($target) ? 'checked' : '' }}>
                </span>
            </li>
        @endforeach
    </ul>

    <button class="btn btn-danger remove-role" data-role="{{ $role->id }}" data-user="{{ $user->id }}">Revoca Ruolo {{ $role->name }} a {{ $user->printableName() }}</button>
</div>
