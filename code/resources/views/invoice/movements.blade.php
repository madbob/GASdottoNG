<div class="modal fade" id="payment-invoice-{{ $invoice->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ _i('Paga Fattura') }}</h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('invoices.savemovements', $invoice->id) }}" data-toggle="validator">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Totale Fattura') }}</label>
                                <div class="col-sm-{{ $fieldsize }}">
                                    <label class="static-label text-muted">
                                        {{ printablePriceCurrency($invoice->total) }} + {{ printablePriceCurrency($invoice->total_vat) }} = {{ printablePriceCurrency($invoice->total + $invoice->total_vat) }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Totale Ordini') }}</label>
                                <div class="col-sm-{{ $fieldsize }}">
                                    <label class="static-label text-muted">
                                        {{ printablePriceCurrency($total_orders) }} + {{ printablePriceCurrency($tax_orders) }} + {{ printablePriceCurrency($transport_orders) }} = {{ printablePriceCurrency($total_orders + $tax_orders + $transport_orders) }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Saldo Fornitore') }}</label>
                                <div class="col-sm-{{ $fieldsize }}">
                                    <label class="static-label text-muted">
                                        {{ printablePriceCurrency($invoice->supplier->current_balance_amount) }}
                                    </label>
                                </div>
                            </div>

                            <hr>
                        </div>
                        <div class="col-md-6">
                            @if(!empty($invoice->supplier->payment_method))
                                @include('commons.staticstringfield', ['obj' => $invoice->supplier, 'name' => 'payment_method', 'label' => _i('Modalità Pagamento')])
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.manyrows', [
                                'contents' => $movements,
                                'columns' => [
                                    [
                                        'label' => _i('Tipo'),
                                        'field' => 'type',
                                        'type' => 'selectenum',
                                        'width' => 3,
                                        'extra' => [
                                            'values' => $alternative_types
                                        ]
                                    ],
                                    [
                                        'label' => _i('Metodo'),
                                        'field' => 'method',
                                        'type' => 'selectenum',
                                        'width' => 2,
                                        'extra' => [
                                            'values' => as_selectable(
                                                App\MovementType::payments(),
                                                function($index, $obj) {
                                                    return $index;
                                                },
                                                function ($index, $obj) {
                                                    return $obj->name;
                                                }
                                            )
                                        ]
                                    ],
                                    [
                                        'label' => _i('Valore'),
                                        'field' => 'amount',
                                        'type' => 'decimal',
                                        'width' => 2,
                                        'extra' => [
                                            'allow_negative' => true,
                                            'is_price' => true
                                        ]
                                    ],
                                    [
                                        'label' => _i('Note'),
                                        'field' => 'notes',
                                        'type' => 'text',
                                        'width' => 3,
                                    ]
                                ]
                            ])
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success reloader" data-reload-target="#invoice-list">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
