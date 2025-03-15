@php
$suppliers = $currentuser->targetsByAction('supplier.orders');
@endphp

@if($currentuser->gas->hasFeature('auto_aggregates'))
    <x-larastrap::field :label="_i('Fornitori')" :pophelp="_i('Selezionando diversi fornitori, verranno generati i rispettivi ordini e saranno automaticamente aggregati. Questa funzione viene attivata se nel database sono presenti almeno 3 aggregati con almeno %d ordini ciascuno.', aggregatesConvenienceLimit())">
        @include('commons.manyrows', [
            'contents' => $order ? collect([$order]) : new Illuminate\Support\Collection(),
            'new_label' => _i('Aggiungi'),
            'columns' => [
                [
                    'label' => _i('Fornitore'),
                    'field' => 'supplier_id',
                    'type' => 'select-model',
                    'extra' => [
                        'options' => $suppliers
                    ]
                ],
            ]
        ])
    </x-larastrap::field>
@else
    <x-larastrap::select-model name="supplier_id" :label="_i('Fornitore')" :options="$suppliers" required />
@endif

<x-larastrap::textarea name="comment" :label="_i('Commento')" maxlength="190" rows="2" :pophelp="_i('Eventuale testo informativo da visualizzare nel titolo dell\'ordine. Se più lungo di %d caratteri, il testo viene invece incluso nel pannello delle relative prenotazioni.', [longCommentLimit()])" />
<x-larastrap::datepicker name="start" :label="_i('Data Apertura Prenotazioni')" defaults_now="true" required :pophelp="_i('Impostando qui una data futura, e lo stato In Sospeso, questo ordine sarà automaticamente aperto nella data specificata')" />
<x-larastrap::datepicker name="end" :label="_i('Data Chiusura Prenotazioni')" defaults_now="true" required data-enforce-after=".date[name=start]" :pophelp="_i('Data di chiusura dell\'ordine. Al termine del giorno qui indicato, l\'ordine sarà automaticamente impostato nello stato Prenotazioni Chiuse')" />
<x-larastrap::datepicker name="shipping" :label="_i('Data Consegna')" defaults_now="true" required data-enforce-after=".date[name=end]" />

<x-larastrap::field>
    @if(empty($suppliers) == false)
        <div class="supplier-future-dates">
            @include('dates.list', ['dates' => App\Supplier::find(array_values($suppliers)[0]->id)->calendarDates])
        </div>
    @endif
</x-larastrap::field>

@include('order.partials.groups', ['order' => null, 'readonly' => false])
@include('commons.orderstatus', ['order' => $order])
