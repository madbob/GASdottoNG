<?php

$summary = $order->calculateSummary();
$rand = rand();

?>

<form class="form-horizontal main-form order-editor" method="PUT" action="{{ url('orders/' . $order->id) }}">
    <input type="hidden" name="order_id" value="{{ $order->id }}" />

    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => 'Fornitore'])
            @include('commons.staticstringfield', ['obj' => $order, 'name' => 'internal_number', 'label' => 'Numero'])

            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.textfield', ['obj' => $order, 'name' => 'comment', 'label' => 'Commento'])
                @include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura', 'mandatory' => true])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'end',
                    'label' => 'Data Chiusura',
                    'mandatory' => true,
                    'extras' => [
                        'data-enforce-after' => '.date[name=start]'
                    ]
                ])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'shipping',
                    'label' => 'Data Consegna',
                    'extras' => [
                        'data-enforce-after' => '.date[name=end]'
                    ]
                ])
            @else
                @include('commons.staticstringfield', ['obj' => $order, 'name' => 'comment', 'label' => 'Commento'])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => 'Data Apertura'])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => 'Data Chiusura'])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => 'Data Consegna'])
            @endif

            @include('commons.orderstatus', ['order' => $order])
        </div>
        <div class="col-md-4">
            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.textfield', ['obj' => $order, 'name' => 'discount', 'label' => 'Sconto Globale', 'postlabel' => '€ / %'])
                @include('commons.decimalfield', ['obj' => $order, 'name' => 'transport', 'label' => 'Spese Trasporto', 'is_price' => true])
            @else
                @include('commons.staticpercentagefield', ['obj' => $order, 'name' => 'discount', 'label' => 'Sconto Globale'])
                @include('commons.staticpricefield', ['obj' => $order, 'name' => 'transport', 'label' => 'Spese Trasporto'])
            @endif

            @include('commons.movementfield', [
                'obj' => $order->payment,
                'name' => 'payment_id',
                'label' => 'Pagamento',
                'default' => \App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered),
                'to_modal' => [
                    'amount_editable' => true
                ]
            ])
        </div>
        <div class="col-md-4">
            <div class="list-group pull-right">
                <a href="{{ url('orders/document/' . $order->id . '/shipping') }}" class="list-group-item">Dettaglio Consegne</a>
                <a href="#" class="list-group-item" data-toggle="modal" data-target="#summary-products-document-{{ $rand }}">Riassunto Prodotti Ordinati</a>
                <a href="#" class="list-group-item" data-toggle="modal" data-target="#all-products-document-{{ $rand }}">Tabella Complessiva Prodotti</a>
            </div>
        </div>
    </div>

    <hr/>

    @include('order.summary', ['order' => $order, 'summary' => $summary])
    @include('commons.formbuttons', [
        'left_buttons' => [
            (object) [
                'label' => 'Esporta',
                'url' => $order->exportableURL(),
                'class' => ''
            ]
        ]
    ])
</form>

<div class="modal fade close-on-submit" id="summary-products-document-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ url('orders/document/' . $order->id . '/summary') }}" data-toggle="validator">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Riassunto Prodotti Ordinati</h4>
                </div>
                <div class="modal-body">
                    <p>
                        Da qui puoi ottenere un documento che riassume le quantità prenotate di tutti i prodotti: utile da inviare al fornitore, una volta chiuso l'ordine.
                    </p>
                    <p>
                        Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target="_blank" href="http://it.libreoffice.org/">LibreOffice</a>.
                    </p>

                    <hr/>

                    @include('commons.checkboxes', [
                        'name' => 'fields',
                        'label' => 'Colonne',
                        'labelsize' => 2,
                        'fieldsize' => 10,
                        'values' => App\Order::formattableColumns('summary')
                    ])
                    @include('commons.radios', [
                        'name' => 'format',
                        'label' => 'Formato',
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade close-on-submit" id="all-products-document-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ url('orders/document/' . $order->id . '/table') }}" data-toggle="validator">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Tabella Complessiva Prodotti</h4>
                </div>
                <div class="modal-body">
                    <p>
                        Da qui puoi ottenere un documento CSV coi dettagli di tutti i prodotti prenotati in quest'ordine.
                    </p>
                    <p>
                        Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target="_blank" href="http://it.libreoffice.org/">LibreOffice</a>.
                    </p>

                    <hr/>

                    @include('commons.radios', [
                        'name' => 'status',
                        'label' => 'Stato Prenotazioni',
                        'values' => [
                            'booked' => (object) [
                                'name' => 'Prenotate',
                                'checked' => true
                            ],
                            'delivered' => (object) [
                                'name' => 'Consegnate'
                            ],
                            'saved' => (object) [
                                'name' => 'Salvate'
                            ],
                        ]
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Download</button>
                </div>
            </form>
        </div>
    </div>
</div>
