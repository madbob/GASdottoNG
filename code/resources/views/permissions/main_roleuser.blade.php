<div role="tabpanel" class="tab-pane fade" id="permissions-{{ sanitizeId($user->id) }}-{{ $role->id }}">
    <div class="row">
        <div class="col">
            <ul class="list-group">
                <?php

                $r = $user->roles()->where('roles.id', $role->id)->first();
                $targets = $role->targets;
                $last_class = null;

                ?>

                @foreach($targets as $target)
                    @if ($targets->count() > 1 && $last_class != get_class($target))
                        <?php $last_class = get_class($target) ?>
                        <li class="list-group-item list-group-item-danger">
                            {{ _i('Tutti (%s)', [$last_class::commonClassName()]) }}<br/>
                            <small>
                                {{ _i("Questo permesso speciale si applica automaticamente a tutti i soggetti (presenti e futuri) e permette di agire su tutti, benché l'utente assegnatario non sarà esplicitamente visibile dagli altri.") }}
                            </small>
                            <span class="float-end">
                                <input type="checkbox" class="all-{{ $user->id }}-{{ $role->id }}" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="*" data-target-class="{{ $last_class }}" {{ $r->appliesAll($last_class) ? 'checked' : '' }}>
                            </span>
                        </li>
                    @endif

                    <li class="list-group-item">
                        {{ $target->printableName() }}
                        <span class="float-end">
                            <input type="checkbox" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="{{ $target->id }}" data-target-class="{{ get_class($target) }}" {{ $r->appliesOnly($target) ? 'checked' : '' }} {{ $user->id == $currentuser->id && $target->id == $currentgas->id ? 'disabled' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            @if($role->enabledAction('gas.permissions') && $user->id == $currentuser->id)
                <div class="alert alert-info">{{ _i('Non puoi auto-revocarti questo ruolo amministrativo') }}</div>
            @else
                <button class="btn btn-danger remove-role" data-role="{{ $role->id }}" data-user="{{ $user->id }}">{{ _i('Revoca Ruolo') }} {{ $role->name }} a {{ $user->printableName() }}</button>
            @endif
        </div>
    </div>
</div>
