<?php $rand = rand() ?>

<div class="list-group pull-right">
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#shipping-products-document-{{ $rand }}">{{ _i('Dettaglio Consegne') }}</a>
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#summary-products-document-{{ $rand }}">{{ _i('Riassunto Prodotti Ordinati') }}</a>
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#all-products-document-{{ $rand }}">{{ _i('Tabella Complessiva Prodotti') }}</a>
</div>

@push('postponed')

<?php $contacts = $order->supplier->involvedEmails() ?>

<div class="modal fade close-on-submit order-document-download-modal" id="shipping-products-document-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ url('orders/document/' . $order->id . '/shipping') }}" data-toggle="validator" novalidate>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Dettaglio Consegne') }}</h4>
                </div>
                <div class="modal-body">
                    <p>
                        {{ _i("Da qui puoi ottenere un documento PDF formattato per la stampa, in cui si trovano le informazioni relative alle singole prenotazioni.") }}
                    </p>

                    @if($currentgas->deliveries->isEmpty() == false)
                        @include('commons.radios', [
                            'name' => 'shipping_place',
                            'label' => _i('Luogo di Consegna'),
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'values' => array_merge(
                                [0 => (object)['name' => 'Tutti']],
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

                    @include('order.filesmail', ['contacts' => $contacts])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade close-on-submit order-document-download-modal" id="summary-products-document-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ url('orders/document/' . $order->id . '/summary') }}" data-toggle="validator" novalidate>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Riassunto Prodotti Ordinati') }}</h4>
                </div>
                <div class="modal-body">
                    <p>
                        {{ ("Da qui puoi ottenere un documento che riassume le quantità prenotate di tutti i prodotti: utile da inviare al fornitore, una volta chiuso l'ordine.") }}
                    </p>
                    <p>
                        {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                    </p>

                    <hr/>

                    @include('commons.checkboxes', [
                        'name' => 'fields',
                        'label' => _i('Colonne'),
                        'labelsize' => 2,
                        'fieldsize' => 10,
                        'values' => App\Order::formattableColumns('summary')
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
                        ]
                    ])

                    @include('order.filesmail', ['contacts' => $contacts])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade close-on-submit" id="all-products-document-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ url('orders/document/' . $order->id . '/table') }}" data-toggle="validator" novalidate>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Tabella Complessiva Prodotti') }}</h4>
                </div>
                <div class="modal-body">
                    <p>
                        {{ _i("Da qui puoi ottenere un documento CSV coi dettagli di tutti i prodotti prenotati in quest'ordine.") }}
                    </p>
                    <p>
                        {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                    </p>

                    <hr/>

                    @include('commons.radios', [
                        'name' => 'status',
                        'label' => _i('Stato Prenotazioni'),
                        'values' => [
                            'booked' => (object) [
                                'name' => _i('Prenotate'),
                                'checked' => true
                            ],
                            'delivered' => (object) [
                                'name' => _i('Consegnate')
                            ],
                            'saved' => (object) [
                                'name' => _i('Salvate')
                            ],
                        ]
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endpush
