@php

$loadable_attributes = [
    'identifier' => 'receipts-list',
    'items' => $receipts,
];

if ($user_id == '0') {
    $actions = [
        ['link' => route('receipts.search', ['format' => 'send']), 'label' => __('texts.invoices.send_pending_receipts')],
    ];

    $downloads = [
        ['link' => route('receipts.search', ['format' => 'csv']), 'label' => __('texts.generic.exports.csv')],
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
                <x-larastrap::select-model name="supplier_id" tlabel="orders.supplier" :options="$currentgas->suppliers" :extra_options="[0 => __('texts.generic.none')]" />

                @if($user_id == '0')
                    <x-larastrap::select-model name="user_id" tlabel="user.name" :options="$currentgas->users()->topLevel()->sorted()->get()" :extra_options="[0 => __('texts.generic.none')]" />
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
