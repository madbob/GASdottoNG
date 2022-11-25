<?php

$suppliers = $currentuser->targetsByAction('supplier.orders');
usort($suppliers, function($a, $b) {
    return $a->name <=> $b->name;
});

?>

@if($currentuser->gas->hasFeature('auto_aggregates'))
    <x-larastrap::field :label="_i('Fornitori')" :pophelp="_i('Selezionando diversi fornitori, verranno generati i rispettivi ordini e saranno automaticamente aggregati. Questa funzione viene attivata se nel database sono presenti almeno 3 aggregati con almeno %d ordini ciascuno.', App\Aggregate::aggregatesConvenienceLimit())">
        @include('commons.manyrows', [
            'contents' => $order ? collect([$order]) : new Illuminate\Support\Collection(),
            'new_label' => _i('Aggiungi'),
            'columns' => [
                [
                    'label' => _i('Fornitore'),
                    'field' => 'supplier_id',
                    'type' => 'selectobj',
                    'extra' => [
                        'options' => $suppliers
                    ]
                ],
            ]
        ])
    </x-larastrap::field>
@else
    <x-larastrap::selectobj name="supplier_id" :label="_i('Fornitore')" :options="$suppliers" required />
@endif

<x-larastrap::textarea name="comment" :label="_i('Commento')" maxlength="190" rows="2" :pophelp="_i('Eventuale testo informativo da visualizzare nel titolo dell\'ordine. Se più lungo di %d caratteri, il testo viene invece incluso nel pannello delle relative prenotazioni.', [App\Order::longCommentLimit()])" />
<x-larastrap::datepicker name="start" :label="_i('Data Apertura Prenotazioni')" defaults_now="true" required />
<x-larastrap::datepicker name="end" :label="_i('Data Chiusura Prenotazioni')" defaults_now="true" required data-enforce-after=".date[name=start]" :pophelp="_i('Data di chiusura dell\'ordine. Al termine del giorno qui indicato, l\'ordine sarà automaticamente impostato nello stato Prenotazioni Chiuse')" />
<x-larastrap::datepicker name="shipping" :label="_i('Data Consegna')" defaults_now="true" required data-enforce-after=".date[name=end]" />

<x-larastrap::field>
    @if(empty($suppliers) == false)
        <div class="supplier-future-dates">
            @include('dates.list', ['dates' => App\Supplier::find(array_values($suppliers)[0]->id)->calendarDates])
        </div>
    @endif
</x-larastrap::field>

@if($currentgas->hasFeature('shipping_places'))
    <x-larastrap::selectobj name="deliveries" :label="_i('Luoghi di Consegna')" :options="$currentgas->deliveries" multiple :pophelp="_i('Selezionando uno o più luoghi di consegna, l\'ordine sarà visibile solo agli utenti che hanno attivato quei luoghi. Se nessun luogo viene selezionato, l\'ordine sarà visibile a tutti.')" />
@endif

@include('commons.orderstatus', ['order' => $order])
