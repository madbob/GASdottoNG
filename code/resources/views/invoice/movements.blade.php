<x-larastrap::modal>
    <x-larastrap::iform method="POST" :action="route('invoices.savemovements', $invoice->id)">
        <input type="hidden" name="reload-loadable" value="#invoice-list">
        <input type="hidden" name="close-modal" value="1">

        <div class="row">
            <div class="col-md-6">
                <x-larastrap::text tlabel="orders.totals.invoice" :value="sprintf('%s + %s = %s', printablePriceCurrency($invoice->total), printablePriceCurrency($invoice->total_vat), printablePriceCurrency($invoice->total + $invoice->total_vat))" readonly disabled />
                <x-larastrap::text tlabel="orders.totals.orders" :value="sprintf('%s + %s = %s', printablePriceCurrency($total_orders), printablePriceCurrency($tax_orders), printablePriceCurrency($total_orders + $tax_orders))" readonly disabled />

                @foreach(App\Currency::enabled() as $curr)
                    <x-larastrap::text tlabel="invoices.balances.supplier" :value="printablePriceCurrency($invoice->supplier->currentBalanceAmount($curr), '.', $curr)" readonly disabled />
                @endforeach

                <hr>
            </div>
            <div class="col-md-6">
                @if(!empty($invoice->supplier->payment_method))
                    <x-larastrap::text name="payment_method" tlabel="user.payment_method" :obj="$invoice->supplier" readonly disabled />
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col">
                @include('commons.manyrows', [
                    'contents' => $movements,
                    'columns' => [
                        [
                            'label' => __('texts.generic.type'),
                            'field' => 'type',
                            'type' => 'select',
                            'width' => 3,
                            'extra' => [
                                'options' => $alternative_types
                            ]
                        ],
                        [
                            'label' => __('texts.generic.method'),
                            'field' => 'method',
                            'type' => 'select',
                            'width' => 2,
                            'extra' => [
                                'options' => paymentsSimple(),
                            ]
                        ],
                        [
                            'label' => __('texts.generic.value'),
                            'field' => 'amount',
                            'type' => 'price',
                            'width' => 2,
                            'extra' => [
                                'allow_negative' => true,
                            ]
                        ],
                        [
                            'label' => __('texts.generic.notes'),
                            'field' => 'notes',
                            'type' => 'text',
                            'width' => 4,
                        ]
                    ]
                ])
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
