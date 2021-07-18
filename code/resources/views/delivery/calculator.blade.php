<?php $rand = rand() ?>

<div class="input-group-addon inline-calculator-trigger" data-toggle="modal" data-target="#calculator-modal-{{ $rand }}">
    <i class="bi-plus-lg"></i>
</div>

<x-larastrap::modal :title="_i('Calcola Quantità')" classes="inline-calculator">
    @for($i = 0; $i < $pieces; $i++)
        <div class="form-group">
            <div class="input-group">
                <input type="text" class="form-control number" autocomplete="off" value="0">
                <div class="input-group-text">{{ $measure }}</div>
            </div>
        </div>
    @endfor
</x-larastrap::modal>
