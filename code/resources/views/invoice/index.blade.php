<div>
    @can('supplier.invoices', null)
        <div class="row">
            <div class="col">
                @can('supplier.invoices', null)
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

    	<hr/>
    @endcan

    <div class="row">
        <div class="col-12 col-md-6">
            <x-filler :data-action="route('invoices.search')" data-fill-target="#invoices-in-range" :downloadButtons="[['link' => route('invoices.search', ['format' => 'csv']), 'label' => _i('Esporta CSV')]]">
                @include('commons.genericdaterange', ['start_date' => strtotime('-1 months')])
                <x-larastrap::select-model name="supplier_id" tlabel="orders.supplier" :options="$currentuser->targetsByAction('movements.admin,supplier.orders,supplier.movements')" :extra_options="[0 => _i('Nessuno')]" />
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
                    'class' => App\Invoice::class,
                ],
            ])
        </div>
    </div>
</div>
