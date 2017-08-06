<input type="hidden" name="sender_type" value="{{ $sender_type }}" />
<input type="hidden" name="target_type" value="{{ $target_type }}" />

@if(!empty($senders))
    @if($senders->count() == 1)
        <input type="hidden" name="target_id" value="{{ $senders->first()->id }}">
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
    'postlabel' => '€',
    'fixed_value' => $fixed
])

<div class="form-group">
    <label for="method" class="col-sm-{{ $labelsize }} control-label">Metodo</label>
    <div class="col-sm-{{ $fieldsize }}">
        <div class="btn-group" data-toggle="buttons">
            <?php $index = 0 ?>
            @foreach($payments as $method_id => $info)
                <label class="btn btn-default {{ $index == 0 ? 'active' :'' }}">
                    <input type="radio" name="method" value="{{ $method_id }}" autocomplete="off" {{ $index == 0 ? 'checked' :'' }}> {{ $info->name }}
                </label>
                <?php $index++ ?>
            @endforeach
        </div>
    </div>
</div>
