<?php

if (is_null($obj)) {
    $obj = $default;
}

if (!isset($dom_id)){
    $dom_id = rand();
}

if (!isset($editable)) {
    $editable = false;
}

if (!isset($amount_editable)) {
    $amount_editable = false;
}

if (!isset($amount_label)) {
    $amount_label = 'Valore';
}

$buttons = [];
if ($editable && $obj && $obj->exists) {
    $buttons[] = ['color' => 'danger', 'label' => _i('Elimina'), 'classes' => ['float-start', 'spare-modal-delete-button'], 'attributes' => ['data-delete-url' => route('movements.destroy', $obj->id)]];
}

$buttons[] = ['color' => 'success', 'label' => _i('Salva'), 'attributes' => ['type' => 'submit']];

?>

<x-larastrap::modal :title="_i('Modifica Movimento')" :id="sprintf('editMovement-%s', $dom_id)" classes="movement-modal">
    <x-larastrap::iform :obj="$obj" method="POST" :action="$obj->exists ? route('movements.update', $obj->id) : route('movements.store')" :buttons="$buttons">
        <input type="hidden" name="void-form" value="1">
        <input type="hidden" name="test-feedback" value="1">
        <input type="hidden" name="close-modal" value="1">
        <input type="hidden" name="update-field" value="movement-id-{{ $dom_id }}">
        <input type="hidden" name="update-field" value="movement-date-{{ $dom_id }}">
        <input type="hidden" name="close-modal" value="">
        <input type="hidden" name="post-saved-function" value="refreshFilter">
        <input type="hidden" name="post-saved-function" value="reloadLoadableHeaders">
        <input type="hidden" name="data-refresh-target" value="#movements-filter">

        @if($obj->exists)
            @method('PUT')
        @endif

        @include('commons.extrafields')

        <x-larastrap::hidden name="type" />
        <x-larastrap::hidden name="sender_type" />
        <x-larastrap::hidden name="sender_id" />
        <x-larastrap::hidden name="target_type" />
        <x-larastrap::hidden name="target_id" />

        @if($amount_editable || $editable)
            <x-larastrap::price name="amount" :label="$amount_label" />
        @else
            <x-larastrap::price name="amount" :label="$amount_label" readonly />
        @endif

        @if($obj->sender && array_search('App\CreditableTrait', class_uses($obj->sender)) !== false && count($obj->sender->balanceFields()) == 1)
            <p class="sender-credit-status mb-3 alert alert-{{ $obj->amount < $obj->sender->current_balance_amount ? 'success' : 'danger' }}">
                {{ _i('Credito Attuale %s', [$obj->sender->printableName()]) }}: <span class="current-sender-credit">{{ $obj->sender->current_balance_amount }}</span> {{ $currentgas->currency }}
            </p>
        @endif

        @if($obj->target && array_search('App\CreditableTrait', class_uses($obj->target)) !== false && count($obj->target->balanceFields()) == 1)
            <p class="alert alert-success mb-3">
                {{ $obj->target->printableName() }}: {{ $obj->target->current_balance_amount }} {{ $currentgas->currency }}
            </p>
        @endif

        <x-larastrap::radios name="method" :label="_i('Metodo')" :options="$obj ? $obj->valid_payments : paymentTypes()" />
        <x-larastrap::datepicker name="date" :label="_i('Data')" defaults_now="true" />

        <div class="when-method-bank {{ $obj->method != 'bank' ? ' hidden' : '' }}">
            <x-larastrap::text name="identifier" :label="_i('Identificativo')" />
        </div>

        <x-larastrap::textarea name="notes" :label="_i('Note')" />
    </x-larastrap::iform>
</x-larastrap::modal>
