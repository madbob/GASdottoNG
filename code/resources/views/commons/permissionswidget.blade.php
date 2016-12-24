<div class="row">
    <div class="col-md-6 scrollable-tabs" style="height: 515px">
        <ul class="nav nav-tabs tabs-left" role="tablist">
            <?php $index = 0 ?>
            @foreach(App\Permission::allTargets() as $subject)
                <li class="presentation {{ $index++ == 0 ? 'active' : '' }}">
                    <a href="#permissions-{{ $user->id }}-{{ $subject->id }}" aria-controls="#permissions-{{ $user->id }}-{{ $subject->id }}" role="tab" data-toggle="tab">{{ $subject->printableName() }}</a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="col-md-6">
        <div class="tab-content">
            <?php $index = 0 ?>
            @foreach(App\Permission::allTargets() as $subject)
                <div role="tabpanel" class="tab-pane {{ $index++ == 0 ? 'active' : '' }}" id="permissions-{{ $user->id }}-{{ $subject->id }}">
                    <ul class="list-group">
                        @foreach($subject->getPermissions() as $identifier => $name)
                            <li class="list-group-item">
                                {{ $name }}
                                <span class="pull-right">
                                    <?php
                                        $can = $subject->userCan($identifier, $user);
                                        $really_can = $subject->userReallyCan($identifier, $user);
                                    ?>
                                    <input type="checkbox" data-toggle="toggle" data-size="mini" name="permissions[]" value="{{ ($can && $really_can) ? ($subject->id . '::' . $identifier) : '' }}" {{ $can ? 'checked' : '' }} {{ ($can && !$really_can) ? 'disabled' : '' }}>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</div>
