<?php

$rand = rand();

$managed_gas = null;

if (isset($active_gas)) {
    if (is_object($active_gas)) {
        $managed_gas = $active_gas->id;
    }
    else {
        $managed_gas = 0;
        $active_gas = null;
    }
}
else {
    $active_gas = $currentgas;
}

?>

<div class="list-group">
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#shipping-products-aggregate-document-{{ $aggregate->id }}-{{ $rand }}">
        {{ _i('Dettaglio Consegne Aggregato') }}
        <span class="glyphicon glyphicon-modal-window pull-right" aria-hidden="true"></span>
    </a>
    <a href="#" class="list-group-item" data-toggle="modal" data-target="#summary-products-document-{{ $aggregate->id }}-{{ $rand }}">
        {{ _i('Riassunto Prodotti Aggregato') }}
        <span class="glyphicon glyphicon-modal-window pull-right" aria-hidden="true"></span>
    </a>
</div>

@push('postponed')

<div class="modal fade close-on-submit" id="shipping-products-aggregate-document-{{ $aggregate->id }}-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ route('aggregates.document', ['id' => $aggregate->id, 'type' => 'shipping']) }}" data-toggle="validator" novalidate>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Dettaglio Consegne Aggregato') }}</h4>
                </div>
                <div class="modal-body">
                    <p>
                        {{ _i("Da qui puoi ottenere un documento PDF formattato per la stampa, in cui si trovano le informazioni relative alle singole prenotazioni di tutti gli ordini inclusi in questo aggregato.") }}
                    </p>

                    <hr>

                    @if($managed_gas !== null)
                        <input type="hidden" name="managed_gas" value="{{ $managed_gas }}">
                    @endif

                    @if($active_gas && $active_gas->hasFeature('shipping_places'))
                        @include('commons.radios', [
                            'name' => 'shipping_place',
                            'label' => _i('Luogo di Consegna'),
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'values' => array_merge(
                                ['all_by_name' => (object)['name' => _i('Tutti (ordinati per utente)')]],
                                ['all_by_place' => (object)['name' => _i('Tutti (ordinati per luogo)')]],
                                as_choosable($active_gas->deliveries, function($i, $a) {
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
                        'label' => _i('Dati Utenti'),
                        'labelsize' => 2,
                        'fieldsize' => 10,
                        'values' => App\User::formattableColumns()
                    ])

                    @include('commons.checkboxes', [
                        'name' => 'fields',
                        'label' => _i('Colonne Prodotti'),
                        'labelsize' => 2,
                        'fieldsize' => 10,
                        'values' => App\Order::formattableColumns('shipping')
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade close-on-submit order-document-download-modal" id="summary-products-document-{{ $aggregate->id }}-{{ $rand }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal direct-submit" method="GET" action="{{ route('aggregates.document', ['id' => $aggregate->id, 'type' => 'summary']) }}" data-toggle="validator" novalidate>
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

                    @if($managed_gas !== null)
                        <input type="hidden" name="managed_gas" value="{{ $managed_gas }}">
                    @endif

                    @if($active_gas && $active_gas->hasFeature('shipping_places'))
                        @include('commons.radios', [
                            'name' => 'shipping_place',
                            'label' => _i('Luogo di Consegna'),
                            'labelsize' => 2,
                            'fieldsize' => 10,
                            'values' => array_merge(
                                ['all_by_place' => (object)['name' => 'Tutti']],
                                as_choosable($active_gas->deliveries, function($i, $a) {
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
