@php

use Illuminate\Support\Collection;

if (is_null($order)) {
    $order = new App\Order();
}

@endphp

@if(is_a($order, App\Order::class) && $order->aggregate && $order->aggregate->orders()->count() != 1)
    @foreach($order->circles as $circle)
        <x-larastrap::hidden name="circles[]" :value="$circle->id" />
    @endforeach
@else
    @php

    $eligible_groups = $order->eligibleGroups();

    $limiting = $eligible_groups->filter(fn($g) => $g->context == 'user' && $g->filters_orders)->reduce(function($tot, $g) {
        return $tot->concat($g->circles);
    }, new Collection());

    $selectable = $eligible_groups->filter(fn($g) => $g->context == 'booking')->reduce(function($tot, $g) {
        return $tot->concat($g->circles);
    }, new Collection());

    $info_groups = $eligible_groups->filter(fn($g) => $g->context == 'order')->reduce(function($tot, $g) {
        return $tot->concat($g->circles);
    }, new Collection());

    @endphp

    @if($limiting->isEmpty() == false || $selectable->isEmpty() == false || $info_groups->isEmpty() == false)
        <div class="card mb-4">
            <div class="card-header">{{ _i('Gruppi') }}</div>
            <div class="card-body">
                @if($limiting->isEmpty() == false)
                    <x-larastrap::checklist-model :label="_i('Limita accesso')" name="circles" :options="$limiting" :readonly="$readonly" :disabled="$readonly" :pophelp="_i('Selezionando uno o più elementi, l\'ordine sarà visibile solo agli utenti assegnati alle rispettive Cerchie. Se nessun elemento viene selezionato, l\'ordine sarà visibile a tutti.')" />
                @endif

                @if($selectable->isEmpty() == false)
                    <x-larastrap::checklist-model :label="_i('Permetti selezione')" name="circles" :options="$selectable" :readonly="$readonly" :disabled="$readonly" :pophelp="_i('Selezionando uno o più elementi, gli utenti potranno sceglierne uno di questi in fase di prenotazione.')" />
                @endif

                @if($info_groups->isEmpty() == false)
                    <x-larastrap::checklist-model :label="_i('Visualizza selezione')" name="circles" :options="$info_groups" :readonly="$readonly" :disabled="$readonly" />
                @endif
            </div>
        </div>
    @endif
@endif
