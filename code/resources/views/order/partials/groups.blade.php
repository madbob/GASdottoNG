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

    @endphp

    @if($limiting->isEmpty() == false || $selectable->isEmpty() == false)
        <div class="card shadow mb-4">
            <div class="card-header">{{ __('texts.aggregations.all') }}</div>
            <div class="card-body">
                @if($limiting->isEmpty() == false)
                    <x-larastrap::checklist-model tlabel="aggregations.limit_access" name="circles" :options="$limiting" :readonly="$readonly" :disabled="$readonly" tpophelp="aggregations.help.limit_access_to_order" />
                @endif

                @if($selectable->isEmpty() == false)
                    <x-larastrap::checklist-model tlabel="aggregations.permit_selection" name="circles" :options="$selectable" :readonly="$readonly" :disabled="$readonly" tpophelp="aggregations.help.permit_selection" />
                @endif
            </div>
        </div>
    @endif
@endif
