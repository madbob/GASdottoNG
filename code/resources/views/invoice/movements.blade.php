<x-larastrap::modal :title="_i('Paga Fattura')">
    <x-larastrap::iform method="POST" :action="route('invoices.savemovements', $invoice->id)">
        <input type="hidden" name="reload-loadable" value="#invoice-list">
        <input type="hidden" name="close-modal" value="1">

        <div class="row">
            <div class="col-md-6">
                <x-larastrap::text :label="_i('Totale Fattura')" :value="sprintf('%s + %s = %s', printablePriceCurrency($invoice->total), printablePriceCurrency($invoice->total_vat), printablePriceCurrency($invoice->total + $invoice->total_vat))" readonly disabled />
                <x-larastrap::text :label="_i('Totale Ordini')" :value="sprintf('%s + %s = %s', printablePriceCurrency($total_orders), printablePriceCurrency($tax_orders), printablePriceCurrency($total_orders + $tax_orders))" readonly disabled />
                <x-larastrap::text :label="_i('Saldo Fornitore')" :value="printablePriceCurrency($invoice->supplier->current_balance_amount)" readonly disabled />
                <hr>
            </div>
            <div class="col-md-6">
                @if(!empty($invoice->supplier->payment_method))
                    <x-larastrap::text name="payment_method" :label="_i('Modalità Pagamento')" :obj="$invoice->supplier" readonly disabled />
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col">
                @include('commons.manyrows', [
                    'contents' => $movements,
                    'columns' => [
                        [
                            'label' => _i('Tipo'),
                            'field' => 'type',
                            'type' => 'select',
                            'width' => 3,
                            'extra' => [
                                'options' => $alternative_types
                            ]
                        ],
                        [
                            'label' => _i('Metodo'),
                            'field' => 'method',
                            'type' => 'select',
                            'width' => 2,
                            'extra' => [
                                'options' => App\MovementType::paymentsSimple(),
                            ]
                        ],
                        [
                            'label' => _i('Valore'),
                            'field' => 'amount',
                            'type' => 'price',
                            'width' => 2,
                            'extra' => [
                                'allow_negative' => true,
                            ]
                        ],
                        [
                            'label' => _i('Note'),
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
