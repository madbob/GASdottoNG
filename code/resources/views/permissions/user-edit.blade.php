<div class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close reloader" data-dismiss="modal" data-reload-target="#user-list" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Configura Ruoli per {{ $user->printableName() }}</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid role-editor">
                    @foreach($currentuser->managed_roles as $role)
                        <?php $urole = $user->roles()->where('roles.id', $role->id)->first() ?>

                        <div class="row">
                            <h3>{{ $role->name }}</h3>

                            <?php

                            $targets = $role->targets;
                            $last_class = null;

                            ?>

                            @foreach($targets as $target)
                                @if ($targets->count() > 1 && $last_class != get_class($target))
                                    <?php $last_class = get_class($target) ?>
                                    <div class="col-md-4 alert-danger">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" class="all-{{ $user->id }}-{{ $role->id }}" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="*" data-target-class="{{ $last_class }}" {{ $urole && $urole->appliesAll($last_class) ? 'checked' : '' }}> Tutti ({{ $last_class::commonClassName() }})
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" data-role="{{ $role->id }}" data-user="{{ $user->id }}" data-target-id="{{ $target->id }}" data-target-class="{{ get_class($target) }}" {{ $urole && $urole->appliesOnly($target) ? 'checked' : '' }}> {{ $target->printableName() }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default reloader" data-dismiss="modal" data-reload-target="#user-list">Chiudi</button>
            </div>
        </div>
    </div>
</div>
