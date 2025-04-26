@extends('app')

@section('content')

<div class="row">
    <div class="col">
        <x-larastrap::tabs>
            <x-larastrap::tabpane :label="_i('Movimenti')" active="true" icon="bi-piggy-bank">
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
                                'import_target' => 'movements',
                            ])

                            <x-larastrap::ambutton :label="_i('Stato Crediti')" :data-modal-url="route('movements.credits', ['type' => 'credits'])" />
                            <x-larastrap::ambutton :label="_i('Stato Fornitori')" :data-modal-url="route('movements.credits', ['type' => 'suppliers'])" />
                        </div>
                    </div>

                    <hr/>
                @endcan

                <div class="row">
                    <div class="col-12 order-2 order-md-1 col-md-6">
                        <x-filler :data-action="route('movements.index')" data-fill-target="#movements-in-range" :download-buttons="[['link' => route('movements.index', ['format' => 'csv']), 'label' => _i('Esporta CSV')], ['link' => route('movements.index', ['format' => 'pdf']), 'label' => _i('Esporta PDF')], ['link' => route('movements.index', ['format' => 'balance']), 'label' => _i('Esporta Bilancio')]]">
                            @include('commons.genericdaterange', ['start_date' => strtotime('-1 weeks')])
                            @include('commons.selectmovementtypefield', ['show_all' => true])
                            <x-larastrap::radios name="method" :label="_i('Pagamento')" :options="paymentsSimple()" value="none" />
                            <x-larastrap::select-model name="user_id" :label="_i('Utente')" :options="$currentgas->users()->topLevel()->get()" :extra_options="[0 => _i('Nessuno')]" />
                            <x-larastrap::select-model name="supplier_id" :label="_i('Fornitore')" :options="$currentuser->targetsByAction('movements.admin,supplier.orders,supplier.movements')" :extra_options="[0 => _i('Nessuno')]" />
                            <x-larastrap::text name="identifier" :label="_i('Identificativo')" />
                            <x-larastrap::text name="notes" :label="_i('Note')" />

                            <x-larastrap::field :label="_i('Importo')">
                                <div class="input-group">
                                    <div class="input-group-text">{{ _i('Da') }}</div>
                                    <input type="number" class="form-control" name="amountstart" autocomplete="off" step="0.01">
                                    <div class="input-group-text">{{ _i('a') }}</div>
                                    <input type="number" class="form-control" name="amountend" autocomplete="off" step="0.01">
                                </div>
                            </x-larastrap::field>

                            <?php $currencies = App\Currency::enabled() ?>
                            @if($currencies->count() > 1)
                                <x-larastrap::select-model name="currency_id" :label="_i('Valuta')" :options="$currencies" :extra_options="[0 => _i('Tutte')]" />
                            @endif
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
                <x-larastrap::tabpane :label="_i('Tipi Movimenti')" icon="bi-zoom-in">
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
                                'typename_readable' => _i('Tipo Movimento Contabile'),
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

            @if($currentuser->can('movements.admin', $currentgas) || $currentuser->can('supplier.movements', null) || $currentuser->can('supplier.invoices', null))
                <x-larastrap::remotetabpane :label="_i('Fatture')" :button_attributes="['data-tab-url' => route('invoices.index')]" icon="bi-files">
                </x-larastrap::remotetabpane>
            @endif

			@if($currentgas->hasFeature('extra_invoicing'))
				<x-larastrap::remotetabpane :label="_i('Ricevute')" :button_attributes="['data-tab-url' => route('receipts.index')]" icon="bi-files">
	            </x-larastrap::remotetabpane>
			@endif
        </x-larastrap::tabs>
    </div>
</div>

@endsection
