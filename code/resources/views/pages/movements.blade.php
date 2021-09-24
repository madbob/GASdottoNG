@extends('app')

@section('content')

<div class="row">
    <div class="col">
        <x-larastrap::tabs>
            <x-larastrap::tabpane :label="_i('Movimenti')" active="true">
                @can('movements.admin', $currentgas)
                    <div class="row">
                        <div class="col">
                            @include('commons.addingbutton', [
                                'typename' => 'movement',
                                'typename_readable' => _i('Movimento'),
                                'dynamic_url' => route('movements.create')
                            ])

                            @include('commons.importcsv', [
                                'modal_id' => 'importCSVmovements',
                                'import_target' => 'movements'
                            ])

                            <x-larastrap::ambutton :label="_i('Stato Crediti')" :data-modal-url="url('movements/showcredits')" />
                            <x-larastrap::ambutton :label="_i('Stato Fornitori')" :data-modal-url="url('movements/showsuppliers')" />
                        </div>
                    </div>

                    <hr/>
                @endcan

                <div class="row">
                    <div class="col-12 order-2 order-md-1 col-md-6">
                        <x-filler :data-action="route('movements.index')" data-fill-target="#movements-in-range" :download-buttons="[['link' => route('movements.index', ['format' => 'csv']), 'label' => _i('Esporta CSV')], ['link' => route('movements.index', ['format' => 'pdf']), 'label' => _i('Esporta PDF')]]">
                            @include('commons.genericdaterange', ['start_date' => strtotime('-1 weeks')])
                            @include('commons.selectmovementtypefield', ['show_all' => true])
                            <x-larastrap::radios name="method" :label="_i('Pagamento')" :options="App\MovementType::paymentsSimple()" value="none" />
                            <x-larastrap::selectobj name="user_id" :label="_i('Utente')" :options="$currentgas->users" :extraitem="_i('Nessuno')" />
                            <x-larastrap::selectobj name="supplier_id" :label="_i('Fornitore')" :options="$currentgas->suppliers" :extraitem="_i('Nessuno')" />

                            <x-larastrap::field :label="_i('Importo')">
                                <div class="input-group">
                                    <div class="input-group-text">{{ _i('Da %s', [$currentgas->currency]) }}</div>
                                    <input type="number" class="form-control" name="amountstart" autocomplete="off" step="0.01">
                                    <div class="input-group-text">{{ _i('a %s', $currentgas->currency) }}</div>
                                    <input type="number" class="form-control" name="amountend" autocomplete="off" step="0.01">
                                </div>
                            </x-larastrap::field>
                        </x-filler>
                    </div>

                    <div class="col-12 order-1 order-md-2 col-md-4 offset-md-2 current-balance mb-3">
                        @include('movement.status', ['obj' => $currentgas])
                    </div>
                </div>

                <hr/>

                <div class="row">
                    <div class="col" id="movements-in-range">
                        @include('movement.list', ['movements' => $movements])
                    </div>
                </div>
            </x-larastrap::tabpane>

            @can('movements.types', $currentgas)
                <x-larastrap::tabpane :label="_i('Tipi Movimenti')">
                    <div class="row">
                        <div class="col">
                            <div class="alert alert-danger">
                                <p>
                                    {{ _i('Attenzione! Modifica i comportamenti dei tipi di movimento contabile con molta cautela!') }}
                                </p>
                                <p>
                                    {{ _i('Prima di modificare il comportamento di un tipo esistente, e magari già assegnato a qualche movimento contabile registrato, si raccomanda di usare la funzione "Archivia Saldi" in modo che i movimenti precedentemente contabilizzati non vengano rielaborati usando il nuovo comportamento (producendo saldi completamente diversi da quelli attuali).') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col">
                            @include('commons.addingbutton', [
                                'template' => 'movementtypes.base-edit',
                                'typename' => 'movementtype',
                                'typename_readable' => _i('Tipo Movimento'),
                                'targeturl' => 'movtypes'
                            ])
                        </div>
                    </div>

                    <hr/>

                    <div class="row">
                        <div class="col">
                            @include('commons.loadablelist', [
                                'identifier' => 'movementtype-list',
                                'items' => $types,
                            ])
                        </div>
                    </div>
                </x-larastrap::tabpane>
            @endcan

            <x-larastrap::tabpane :label="_i('Fatture')">
                <div class="row">
                    <div class="col">
                        @can('movements.admin', $currentgas)
                            @include('commons.addingbutton', [
                                'template' => 'invoice.base-edit',
                                'typename' => 'invoice',
                                'typename_readable' => _i('Fattura'),
                                'button_label' => _i('Carica Nuova Fattura'),
                                'targeturl' => 'invoices'
                            ])
                        @endcan
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-filler :data-action="route('invoices.search')" data-fill-target="#invoices-in-range" :downloadButtons="[['link' => route('invoices.search', ['format' => 'csv']), 'label' => _i('Esporta CSV')]]">
                            @include('commons.genericdaterange', ['start_date' => strtotime('-1 months')])
                            <x-larastrap::selectobj name="supplier_id" :label="_i('Fornitore')" :options="$currentgas->suppliers" :extraitem="_i('Nessuno')" />
                        </x-filler>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col" id="invoices-in-range">
                        @include('commons.loadablelist', [
                            'identifier' => 'invoice-list',
                            'items' => $invoices,
                            'legend' => (object)[
                                'class' => $currentgas->hasFeature('extra_invoicing') ? ['Invoice', 'Receipt'] : 'Invoice'
                            ],
                        ])
                    </div>
                </div>
            </x-larastrap::tabpane>
        </x-larastrap::tabs>
    </div>
</div>

@include('commons.deleteconfirm', [
    'url' => 'movements',
    'password_protected' => true,
    'extra' => [
        'close-all-modal' => '1',
        'post-saved-function' => ['refreshFilter', 'refreshBalanceView']
    ]
])

@endsection
