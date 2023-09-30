<x-larastrap::field :pophelp="$help_popover" :label="$label" :squeeze="$squeeze">
    <label class="static-label text-body-secondary" data-updatable-name="movement-date-{{ isset($rand) ? $rand : rand() }}" data-updatable-field="registration_date">
        @if (!$obj)
            Mai
        @else
            {!! $obj->printableName() !!}
        @endif
    </label>

    <div class="float-end">
        @include('commons.detailsbutton', ['obj' => $obj])
    </div>
</x-larastrap::field>
