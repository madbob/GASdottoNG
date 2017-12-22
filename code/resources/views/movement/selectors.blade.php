<input type="hidden" name="sender_type" value="{{ $sender_type }}" />
<input type="hidden" name="target_type" value="{{ $target_type }}" />

@if(!empty($senders))
    @if($senders->count() == 1)
        <input type="hidden" name="sender_id" value="{{ $senders->first()->id }}">
    @else
        @include('commons.selectobjfield', [
            'obj' => null,
            'name' => 'sender_id',
            'objects' => $senders,
            'label' => $sender_type::commonClassName()
        ])
    @endif
@endif

@if(!empty($targets))
    @if($targets->count() == 1)
        <input type="hidden" name="target_id" value="{{ $targets->first()->id }}">
    @else
        @include('commons.selectobjfield', [
            'obj' => null,
            'name' => 'target_id',
            'objects' => $targets,
            'label' => $target_type::commonClassName()
        ])
    @endif
@endif

@include('commons.decimalfield', [
    'obj' => null,
    'name' => 'amount',
    'label' => _i('Valore'),
    'is_price' => true,
    'fixed_value' => $fixed
])

@include('commons.radios', [
    'name' => 'method',
    'label' => _i('Metodo'),
    'values' => $payments
])

@include('commons.textfield', [
    'obj' => null,
    'name' => 'identifier',
    'label' => _i('Identificativo'),
    'extra_wrap_class' => 'when-method-bank' . ($default_method == 'bank' ? '' : ' hidden')
])

@include('commons.textarea', [
    'obj' => null,
    'name' => 'notes',
    'label' => _i('Note'),
    'default_value' => $default_notes
])
