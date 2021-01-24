<?php

if (is_null($obj))
    $obj = $default;

if (!isset($dom_id))
    $dom_id = rand();

if (!isset($editable))
    $editable = false;
if (!isset($amount_editable))
    $amount_editable = false;
if (!isset($amount_label))
    $amount_label = 'Valore';

?>

<div class="modal fade movement-modal" id="editMovement-{{ $dom_id }}" tabindex="-1" role="dialog" aria-labelledby="editMovement-{{ $dom_id }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-form" method="POST" action="{{ $obj->exists ? route('movements.update', $obj->id) : route('movements.store') }}" data-toggle="validator">
                @csrf
                <input type="hidden" name="update-field" value="movement-id-{{ $dom_id }}">
                <input type="hidden" name="update-field" value="movement-date-{{ $dom_id }}">
                <input type="hidden" name="close-modal" value="">
                <input type="hidden" name="post-saved-function" value="refreshFilter">
                <input type="hidden" name="post-saved-function" value="reloadLoadableHeaders">
                <input type="hidden" name="data-refresh-target" value="#movements-filter">

                @if($obj->exists)
                    <input type="hidden" name="_method" value="PUT">
                @endif

                @include('commons.extrafields')

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Modifica Movimento') }}</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="{{ $obj->type }}" />
                    <input type="hidden" name="sender_type" value="{{ $obj->sender_type }}" />
                    <input type="hidden" name="sender_id" value="{{ $obj->sender_id }}" />
                    <input type="hidden" name="target_type" value="{{ $obj->target_type }}" />
                    <input type="hidden" name="target_id" value="{{ $obj->target_id }}" />

                    @include('commons.decimalfield', [
                        'obj' => $obj,
                        'name' => 'amount',
                        'label' => $amount_label,
                        'is_price' => true,
                        'fixed_value' => $amount_editable ? false : ($editable ? false : $obj->amount)
                    ])

                    <div class="col-sm-{{ $fieldsize }} col-sm-offset-{{ $labelsize }}">
                        @if($obj->sender && array_search('App\CreditableTrait', class_uses($obj->sender)) !== false && count($obj->sender->balanceFields()) == 1)
                            <p class="sender-credit-status alert alert-{{ $obj->amount < $obj->sender->current_balance_amount ? 'success' : 'danger' }}">
                                Credito Attuale {{ $obj->sender->printableName() }}: <span class="current-sender-credit">{{ $obj->sender->current_balance_amount }}</span> {{ $currentgas->currency }}
                            </p>
                        @endif

                        @if($obj->target && array_search('App\CreditableTrait', class_uses($obj->target)) !== false && count($obj->target->balanceFields()) == 1)
                            <p class="alert alert-success">
                                {{ $obj->target->printableName() }}: {{ $obj->target->current_balance_amount }} {{ $currentgas->currency }}
                            </p>
                        @endif

                        <br/>
                    </div>

                    @include('commons.radios', [
                        'name' => 'method',
                        'label' => _i('Metodo'),
                        'values' => $obj ? $obj->valid_payments : App\MovementType::payments()
                    ])

                    @include('commons.datefield', [
                        'obj' => $obj,
                        'name' => 'date',
                        'label' => _i('Data'),
                        'defaults_now' => true
                    ])

                    @include('commons.textfield', [
                        'obj' => $obj,
                        'name' => 'identifier',
                        'label' => _i('Identificativo'),
                        'extra_wrap_class' => 'when-method-bank' . ($obj->method != 'bank' ? ' hidden' : '')
                    ])

                    @include('commons.textarea', [
                        'obj' => $obj,
                        'name' => 'notes',
                        'label' => _i('Note')
                    ])
                </div>

                <div class="modal-footer">
                    @if($editable && $obj && $obj->exists)
                        <button type="button" class="btn btn-danger spare-modal-delete-button" data-delete-url="{{ route('movements.destroy', $obj->id) }}">{{ _i('Elimina') }}</button>
                    @endif

                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
