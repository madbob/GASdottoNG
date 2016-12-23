<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        <label class="text-muted">
            @if($obj)
                <?php $final = [] ?>
                @foreach($obj->$name as $n)
                    <?php $final[] = $n->printableName() ?>
                @endforeach
                {{ join(', ', $final) }}
            @endif
        </label>
    </div>
</div>
