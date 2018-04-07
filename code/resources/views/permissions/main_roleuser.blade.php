<div role="tabpanel" class="tab-pane" id="permissions-{{ $user->id }}-{{ $role->id }}">
    <div class="row">
        <div class="col-md-12">
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
                            Tutti ({{ $last_class::commonClassName() }})<br/>
                            <small>
                                {{ _i("Questo permesso speciale si applica automaticamente a tutti i soggetti (presenti e futuri) e permette di agire su tutti, benché l'utente assegnatario non sarà esplicitamente visibile dagli altri.") }}
                            </small>
                            <span class="pull-right">
                                <input type="checkbox" class="all-{{ $user->id }}-{{ $role->id }}" data-toggle="toggle" data-size="mini" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="*" data-target-class="{{ $last_class }}" {{ $r->appliesAll($last_class) ? 'checked' : '' }}>
                            </span>
                        </li>
                    @endif

                    <li class="list-group-item">
                        {{ $target->printableName() }}
                        <span class="pull-right">
                            <input type="checkbox" data-toggle="toggle" data-size="mini" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="{{ $target->id }}" data-target-class="{{ get_class($target) }}" {{ $r->appliesOnly($target) ? 'checked' : '' }} {{ $user->id == $currentuser->id && $target->id == $currentgas->id ? 'disabled' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-danger remove-role" data-role="{{ $role->id }}" data-user="{{ $user->id }}">{{ _i('Revoca Ruolo') }} {{ $role->name }} a {{ $user->printableName() }}</button>
        </div>
    </div>
</div>
