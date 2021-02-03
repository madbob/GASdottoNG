@foreach($obj->applicableModificationTypes() as $mod)
    <div class="form-group">
        <label for="{{ $obj->id }}-{{ $mod->slug }}" class="col-sm-{{ $labelsize }} control-label">{{ $mod->name }}</label>

        <div class="col-sm-{{ $fieldsize }}">
            @foreach($obj->modifiers()->where('modifier_type_id', $mod->id)->get() as $m)
                <button class="btn btn-default btn-wide async-modal" data-target-url="{{ route('modifiers.show', $m->id) }}">
                    {{ $m->name }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span>
                </button>
            @endforeach
        </div>
    </div>
@endforeach
