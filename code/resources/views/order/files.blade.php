<?php $rand = rand() ?>

<div class="list-group pull-right">
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#shipping-products-document-{{ $rand }}">Dettaglio Consegne</a>
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#summary-products-document-{{ $rand }}">Riassunto Prodotti Ordinati</a>
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#all-products-document-{{ $rand }}">Tabella Complessiva Prodotti</a>
</div>

@push('postponed')

<div class="modal fade close-on-submit order-document-download-modal" id="shipping-products-document-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ url('orders/document/' . $order->id . '/shipping') }}" data-toggle="validator">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Dettaglio Consegne</h4>
                </div>
                <div class="modal-body">
                    <p>
                        Da qui puoi ottenere un documento PDF formattato per la stampa, in cui si trovano le informazioni relative alle singole prenotazioni.
                    </p>

                    <hr/>

                    @if(!empty($order->supplier->email))
                        @include('commons.boolfield', [
                            'obj' => null,
                            'name' => 'send_mail',
                            'label' => 'Inoltra Mail',
                            'labelsize' => 2,
                            'fieldsize' => 10
                        ])
                        @include('commons.textfield', [
                            'obj' => null,
                            'name' => 'recipient_mail',
                            'label' => 'Destinatari',
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'extra_wrap_class' => 'order_document_recipient_mail',
                            'help_text' => 'Puoi specificare più indirizzi mail, separandoli con una virgola'
                        ])
                        @include('commons.textarea', [
                            'obj' => null,
                            'name' => 'body_mail',
                            'label' => 'Testo Mail',
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'extra_wrap_class' => 'order_document_body_mail'
                        ])
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade close-on-submit order-document-download-modal" id="summary-products-document-{{ $rand }}" tabindex="-1" role="dialog">
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

                    @if(!empty($order->supplier->email))
                        @include('commons.boolfield', [
                            'obj' => null,
                            'name' => 'send_mail',
                            'label' => 'Inoltra Mail',
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'help_text' => sprintf('Sarà inviata a %s e, in CC, a %s', $order->supplier->email, $currentuser->email)
                        ])
                        @include('commons.textarea', [
                            'obj' => null,
                            'name' => 'body_mail',
                            'label' => 'Testo Mail',
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'extra_wrap_class' => 'order_document_body_mail'
                        ])
                    @endif
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

@endpush
