<x-larastrap::form :obj="$aggregate" classes="main-form" method="PUT" :action="route('aggregates.update', $aggregate->id)">
    <input type="hidden" name="post-saved-function" value="afterAggregateChange" class="skip-on-submit">

    <div class="row">
        <div class="col-md-4">
            <?php $statuses = array_merge(['no' => _i('Invariato')], \App\Order::statuses()) ?>
            <x-larastrap::select name="status" :label="_i('Stato')" :options="$statuses" value="no" :pophelp="_i('Da qui puoi modificare lo stato di tutti gli ordini inclusi nell\'aggregato')" />

            <x-larastrap::textarea name="comment" :label="_i('Commento')" rows="2" />

            <x-larastrap::check name="change_dates" :label="_i('Modifica Date')" classes="collapse_trigger" :pophelp="_i('Da qui è possibile modificare la data di apertura, chiusura a consegna di tutti gli ordini inclusi nell\'aggregato')" checked="false" />
            <div class="collapse" data-triggerable="change_dates">
                <div class="col-md-12">
                    <x-larastrap::datepicker name="start" :label="_i('Data Apertura Prenotazioni')" />
                    <x-larastrap::datepicker name="end" :label="_i('Data Chiusura Prenotazioni')" />
                    <x-larastrap::datepicker name="shipping" :label="_i('Data Consegna')" />
                </div>
            </div>

            @if($currentgas->hasFeature('shipping_places'))
                <x-larastrap::selectobj name="deliveries" :label="_i('Luoghi di Consegna')" :options="$currentgas->deliveries" multiple />
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
