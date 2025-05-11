@php

$loadable_attributes = [
    'identifier' => 'receipts-list',
    'items' => $receipts,
];

if ($user_id == '0') {
    $actions = [
        ['link' => route('receipts.search', ['format' => 'send']), 'label' => _i('Inoltra Ricevute in Attesa')],
    ];

    $downloads = [
        ['link' => route('receipts.search', ['format' => 'csv']), 'label' => _i('Esporta CSV')],
    ];

    $loadable_attributes['legend'] = (object)['class' => App\Receipt::class];
}
else {
    $actions = [];
    $downloads = [];
}

@endphp

<div>
    <div class="row">
        <div class="col-12 col-md-6">
            <x-filler :data-action="route('receipts.search')" data-fill-target="#receipts-in-range" :actionButtons="$actions" :downloadButtons="$downloads">
                @include('commons.genericdaterange', ['start_date' => strtotime('-1 months')])
                <x-larastrap::select-model name="supplier_id" tlabel="orders.supplier" :options="$currentgas->suppliers" :extra_options="[0 => _i('Nessuno')]" />

                @if($user_id == '0')
                    <x-larastrap::select-model name="user_id" :label="_i('Utente')" :options="$currentgas->users()->topLevel()->sorted()->get()" :extra_options="[0 => _i('Nessuno')]" />
                @else
                    <x-larastrap::hidden name="user_id" :value="$user_id" />
                @endif
            </x-filler>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col" id="receipts-in-range">
            @include('commons.loadablelist', $loadable_attributes)
        </div>
    </div>
</div>
