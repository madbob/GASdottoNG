@extends('app')

@section('content')

<div class="row">
    <div class="col">
        <x-larastrap::tabs>
            <x-larastrap::tabpane tlabel="movements.all" active="true" icon="bi-piggy-bank">
                @can('movements.admin', $currentgas)
                    <div class="row">
                        <div class="col">
                            @include('commons.addingbutton', [
                                'typename' => 'movement',
                                'typename_readable' => __('texts.movements.name'),
                                'dynamic_url' => route('movements.create')
                            ])

                            @include('commons.importcsv', [
                                'modal_id' => 'importCSVmovements',
                                'import_target' => 'movements',
                            ])

                            <x-larastrap::ambutton tlabel="movements.credits_status" :data-modal-url="route('movements.credits', ['type' => 'credits'])" />
                            <x-larastrap::ambutton tlabel="movements.suppliers_status" :data-modal-url="route('movements.credits', ['type' => 'suppliers'])" />
                        </div>
                    </div>

                    <hr/>
                @endcan

                <div class="row">
                    <div class="col-12 order-2 order-md-1 col-md-6">
                        <x-filler :data-action="route('movements.index')" data-fill-target="#movements-in-range" :download-buttons="[['link' => route('movements.index', ['format' => 'csv']), 'label' => __('texts.generic.exports.csv')], ['link' => route('movements.index', ['format' => 'pdf']), 'label' => __('texts.generic.exports.pdf')], ['link' => route('movements.index', ['format' => 'balance']), 'label' => __('texts.export.do_balance')]]">
                            @include('commons.genericdaterange', ['start_date' => strtotime('-1 weeks')])
                            @include('commons.selectmovementtypefield', ['show_all' => true])
                            <x-larastrap::radios name="method" tlabel="generic.payment" :options="paymentsSimple()" value="none" />
                            <x-larastrap::select-model name="user_id" tlabel="user.name" :options="$currentgas->users()->topLevel()->get()" :extra_options="[0 => __('texts.generic.none')]" />
                            <x-larastrap::select-model name="supplier_id" tlabel="orders.supplier" :options="$currentuser->targetsByAction('movements.admin,supplier.orders,supplier.movements')" :extra_options="[0 => __('texts.generic.none')]" />
                            <x-larastrap::text name="identifier" tlabel="generic.identifier" />
                            <x-larastrap::text name="notes" tlabel="generic.notes" />

                            <x-larastrap::field tlabel="movements.amount">
                                <div class="input-group">
                                    <div class="input-group-text">{{ __('texts.generic.since') }}</div>
                                    <input type="number" class="form-control" name="amountstart" autocomplete="off" step="0.01">
                                    <div class="input-group-text">{{ __('texts.generic.to') }}</div>
                                    <input type="number" class="form-control" name="amountend" autocomplete="off" step="0.01">
                                </div>
                            </x-larastrap::field>

                            <?php $currencies = App\Currency::enabled() ?>
                            @if($currencies->count() > 1)
                                <x-larastrap::select-model name="currency_id" tlabel="movements.currency" :options="$currencies" :extra_options="[0 => __('texts.generic.all')]" />
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
                <x-larastrap::tabpane tlabel="movements.types" icon="bi-zoom-in">
                    <div class="row">
                        <div class="col">
                            <div class="alert alert-danger">
                                <p>
                                    {{ __('texts.movements.help.main_types_warning') }}
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
                                'typename_readable' => __('texts.movements.type'),
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
                <x-larastrap::remotetabpane tlabel="movements.invoices" :button_attributes="['data-tab-url' => route('invoices.index')]" icon="bi-files">
                </x-larastrap::remotetabpane>
            @endif

			@if($currentgas->hasFeature('extra_invoicing'))
				<x-larastrap::remotetabpane tlabel="generic.menu.receipts" :button_attributes="['data-tab-url' => route('receipts.index')]" icon="bi-files">
	            </x-larastrap::remotetabpane>
			@endif
        </x-larastrap::tabs>
    </div>
</div>

@endsection
