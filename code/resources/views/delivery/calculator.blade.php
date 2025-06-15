<?php

$rand = rand();
$modal_id = sprintf('calculator-modal-%s', $rand);

?>

<div class="input-group-text inline-calculator-trigger" data-bs-toggle="modal" data-bs-target="#{{ $modal_id }}">
    <i class="bi-plus-lg"></i>
</div>

@push('postponed')
    <x-larastrap::modal classes="inline-calculator" :id="$modal_id" size="md">
        <x-larastrap::suggestion>
            {{ __('texts.orders.notices.calculator') }}
        </x-larastrap::suggestion>

        <x-larastrap::form>
            @for($i = 0; $i < $pieces; $i++)
                <div class="form-group mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control number" autocomplete="off" value="0">
                        <div class="input-group-text">{{ $measure }}</div>
                    </div>
                </div>
            @endfor
        </x-larastrap::form>
    </x-larastrap::modal>
@endpush
