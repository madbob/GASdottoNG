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
    $buttons[] = ['color' => 'danger', 'label' => _i('Elimina'), 'classes' => ['float-start', 'async-modal'], 'attributes' => ['data-modal-url' => route('movements.askdelete', $obj->id)]];
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
            @include('commons.pricecurrency', ['allow_negative' => $obj->type_metadata->allow_negative])
        @else
            <x-larastrap::price name="amount" :label="$amount_label" readonly :currency="$obj->currency_id" />
        @endif

        @if($obj->sender && array_search(\App\Models\Concerns\CreditableTrait::class, class_uses($obj->sender)) !== false && count($obj->sender->balanceFields()) == 1)
            <p class="sender-credit-status mb-3 alert alert-{{ $obj->amount < $obj->sender->currentBalanceAmount() ? 'success' : 'danger' }}">
                {{ _i('Credito Attuale %s', [$obj->sender->printableName()]) }}: <span class="current-sender-credit">{{ $obj->sender->currentBalanceAmount() }}</span> {{ $currentgas->currency }}
            </p>
        @endif

        @if($obj->target && array_search(\App\Models\Concerns\CreditableTrait::class, class_uses($obj->target)) !== false && count($obj->target->balanceFields()) == 1)
            <p class="alert alert-success mb-3">
                {{ $obj->target->printableName() }}: {{ $obj->target->currentBalanceAmount() }} {{ $currentgas->currency }}
            </p>
        @endif

        @if($obj && empty($obj->valid_payments))
            <x-larastrap::field :label="_i('Metodo')">
                <div class="alert alert-danger">
                    {{ _i('Attenzione! Nessun metodo di pagamento è attivo per questo tipo di movimento (%s)! Si raccomanda di modificare le impostazioni nel pannello Contabilità -> Tipi Movimenti, o non sarà possibile salvare correttamente questo movimento!', [$obj->printableType()]) }}
                </div>
            </x-larastrap::field>
        @else
            <x-larastrap::radios name="method" :label="_i('Metodo')" :options="$obj ? $obj->valid_payments : paymentTypes()" />
        @endif

        <x-larastrap::datepicker name="date" :label="_i('Data')" defaults_now="true" />

        <div class="when-method-bank {{ $obj->method != 'bank' ? ' hidden' : '' }}">
            <x-larastrap::text name="identifier" :label="_i('Identificativo')" />
        </div>

        <x-larastrap::textarea name="notes" :label="_i('Note')" />
    </x-larastrap::iform>
</x-larastrap::modal>
