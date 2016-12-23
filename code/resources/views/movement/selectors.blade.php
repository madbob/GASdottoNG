<input type="hidden" name="sender_type" value="{{ $sender_type }}" />
<input type="hidden" name="target_type" value="{{ $target_type }}" />

@if(!empty($senders))
    @include('commons.selectobjfield', [
        'obj' => null,
        'name' => 'sender_id',
        'objects' => $senders,
        'label' => 'Pagante'
    ])
@endif

@if(!empty($targets))
    @include('commons.selectobjfield', [
        'obj' => null,
        'name' => 'target_id',
        'objects' => $targets,
        'label' => 'Pagato'
    ])
@endif

@include('commons.decimalfield', [
    'obj' => null,
    'name' => 'amount',
    'label' => 'Valore',
    'postlabel' => '€',
    'fixed_value' => $fixed
])

<div class="form-group">
    <label for="method" class="col-sm-{{ $labelsize }} control-label">Metodo</label>
    <div class="col-sm-{{ $fieldsize }}">
        <div class="btn-group" data-toggle="buttons">
            @foreach($payments as $method_id => $info)
                <label class="btn btn-primary">
                    <input type="radio" name="method" value="{{ $method_id }}" autocomplete="off"> {{ $info->name }}
                </label>
            @endforeach
        </div>
    </div>
</div>
