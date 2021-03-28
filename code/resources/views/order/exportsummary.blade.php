<div class="modal fade close-on-submit order-document-download-modal" id="summary-products-document-{{ normalizeId($order->id) }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ url('orders/document/' . $order->id . '/summary') }}" data-toggle="validator" novalidate>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Riassunto Prodotti') }}</h4>
                </div>
                <div class="modal-body">
                    <p>
                        {{ ("Da qui puoi ottenere un documento che riassume le quantità prenotate di tutti i prodotti: utile da inviare al fornitore, una volta chiuso l'ordine.") }}
                    </p>
                    <p>
                        {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                    </p>

                    <hr/>

                    @if($currentgas->hasFeature('shipping_places'))
                        @include('commons.radios', [
                            'name' => 'shipping_place',
                            'label' => _i('Luogo di Consegna'),
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'values' => array_merge(
                                ['all_by_place' => (object)['name' => 'Tutti']],
                                as_choosable($currentgas->deliveries, function($i, $a) {
                                    return $a->id;
                                }, function($i, $a) {
                                    return $a->name;
                                }, function($i, $a) {
                                    return false;
                                })
                            )
                        ])
                    @endif

                    @include('commons.checkboxes', [
                        'name' => 'fields',
                        'label' => _i('Colonne'),
                        'labelsize' => 2,
                        'fieldsize' => 10,
                        'values' => App\Order::formattableColumns('summary')
                    ])

                    @include('commons.radios', [
                        'name' => 'status',
                        'label' => _i('Quantità'),
                        'labelsize' => 2,
                        'fieldsize' => 10,
                        'values' => [
                            'booked' => (object) [
                                'name' => _i('Prenotate'),
                                'checked' => true
                            ],
                            'delivered' => (object) [
                                'name' => _i('Consegnate')
                            ],
                        ]
                    ])

                    @include('commons.radios', [
                        'name' => 'format',
                        'label' => _i('Formato'),
                        'labelsize' => 2,
                        'fieldsize' => 10,
                        'values' => [
                            'pdf' => (object) [
                                'name' => 'PDF',
                                'checked' => true
                            ],
                            'csv' => (object) [
                                'name' => 'CSV'
                            ],
                            'gdxp' => (object) [
                                'name' => 'GDXP'
                            ],
                        ]
                    ])

                    @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
