<x-larastrap::form :obj="$aggregate" classes="main-form" method="PUT" :action="route('aggregates.update', $aggregate->id)">
    <div class="row">
        <div class="col-md-4">
            <?php $statuses = array_merge(['no' => _i('Invariato')], \App\Order::statuses()) ?>
            <x-larastrap::select name="status" :label="_i('Stato')" :options="$statuses" value="no" :pophelp="_i('Da qui puoi modificare lo stato di tutti gli ordini inclusi nell\'aggregato')" />

            <x-larastrap::textarea name="comment" :label="_i('Commento')" rows="2" />

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
