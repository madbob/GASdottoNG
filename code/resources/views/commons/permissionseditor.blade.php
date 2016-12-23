<?php $permissions = $object->getPermissions() ?>

@foreach($permissions as $identifier => $label)

<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>

    <div class="col-sm-{{ $fieldsize - 1 }}">
        <label class="static-label">
            <?php $info = $object->whoCanComplex($identifier) ?>

            @if ($info['behaviour'] == 'all')
                Tutti
            @else
                <?php

                    $final = array_map(function ($a) {
                        return $a->name;
                    }, $info['users'])

                ?>

                @if ($info['behaviour'] == 'selected')
                    {{ join(', ', $final) }}
                @elseif ($info['behaviour'] == 'except')
                    Tutti tranne {{ join(', ', $final) }}
                @endif
            @endif
        </label>
    </div>

    @if($object->userCan($master_permission))
        <div class="col-sm-1">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#editPermissions" data-subject="{{ $object->id }}" data-rule="{{ $identifier }}"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
        </div>
    @endif
</div>

@endforeach
