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
    'label' => 'Valore',
    'is_price' => true,
    'fixed_value' => $fixed
])

<div class="form-group">
    <label for="method" class="col-sm-{{ $labelsize }} control-label">Metodo</label>
    <div class="col-sm-{{ $fieldsize }}">
        <div class="btn-group" data-toggle="buttons">
            @foreach($payments as $method_id => $info)
                <label class="btn btn-default {{ $default_method == $method_id ? 'active' :'' }}">
                    <input type="radio" name="method" value="{{ $method_id }}" autocomplete="off" {{ $default_method == $method_id ? 'checked' :'' }}> {{ $info->name }}
                </label>
            @endforeach
        </div>
    </div>
</div>

@include('commons.textfield', [
    'obj' => null,
    'name' => 'identifier',
    'label' => 'Identificativo',
    'extra_wrap_class' => 'when-method-bank' . ($default_method == 'bank' ? '' : ' hidden')
])

@include('commons.textarea', [
    'obj' => null,
    'name' => 'notes',
    'label' => 'Note',
    'default_value' => $default_notes
])
