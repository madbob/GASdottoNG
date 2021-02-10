<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">
        @include('commons.helpbutton', ['help_popover' => $help_popover])
        {{ $label }}
    </label>

    <div class="col-sm-{{ $fieldsize }}">
        <label class="static-label text-muted" data-updatable-name="movement-date-{{ isset($rand) ? $rand : rand() }}" data-updatable-field="registration_date">
            @if (!$obj)
                Mai
            @else
                {!! $obj->printableName() !!}
            @endif
        </label>

        <div class="pull-right">
            @include('commons.detailsbutton', ['obj' => $obj])
        </div>
    </div>
</div>
