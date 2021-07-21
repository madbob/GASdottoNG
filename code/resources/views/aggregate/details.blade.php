<x-larastrap::form :obj="$aggregate" classes="main-form" method="PUT" :action="route('aggregates.update', $aggregate->id)">
    <div class="row">
        <div class="col-md-4">
            <?php $statuses = array_merge(['no' => _i('Invariato')], \App\Order::statuses()) ?>
            <x-larastrap::select name="status" :label="_i('Stato')" :options="$statuses" value="no" :pophelp="_i('Da qui puoi modificare lo stato di tutti gli ordini inclusi nell\'aggregato')" />

            <x-larastrap::textarea name="comment" :label="_i('Commento')" rows="2" />

            @include('commons.boolfield', [
                'obj' => null,
                'name' => 'change_dates',
                'label' => _i('Modifica Date'),
                'extra_class' => 'collapse_trigger',
                'default_checked' => false,
                'help_popover' => _i("Da qui è possibile modificare la data di apertura, chiusura a consegna di tutti gli ordini inclusi nell'aggregato"),
            ])

            <div class="collapse" data-triggerable="change_dates">
                <div class="col-md-12">
                    @include('commons.datefield', ['obj' => $aggregate, 'name' => 'start', 'label' => _i('Data Apertura')])
                    @include('commons.datefield', ['obj' => $aggregate, 'name' => 'end', 'label' => _i('Data Chiusura')])
                    @include('commons.datefield', ['obj' => $aggregate, 'name' => 'shipping', 'label' => _i('Data Consegna')])
                </div>
            </div>

            @if($currentgas->hasFeature('shipping_places'))
                <x-larastrap::selectobj name="deliveries" :label="_i('Luoghi di Consegna')" :options="$currentgas->deliveries" multiple="multiple" />
            @endif
        </div>
        <div class="col-md-4">
            @include('commons.modifications', ['obj' => $aggregate])
        </div>
        <div class="col-md-4">
            @include('aggregate.files', ['aggregate' => $aggregate, 'managed_gas' => $currentgas->id])
        </div>
    </div>
</x-larastrap::form>
