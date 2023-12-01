@if(Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas))
    <div class="row">
        <div class="col">
            <h4>{{ _i('Ordini da pagare') }}</h4>

            <?php $orders = $supplier->orders()->whereDoesntHave('payment')->get() ?>

            @if($orders->isEmpty())
                <x-larastrap::suggestion>
                    {{ _i('Non ci sono elementi da visualizzare.') }}
                </x-larastrap::suggestion>
            @else
                <ul class="list-group">
                    @foreach($orders as $order)
                        <a href="{{ route('orders.index') . '#' . $order->aggregate_id }}" class="list-group-item">
                            {!! $order->printableHeader() !!}
                        </a>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <hr>

    @include('movement.targetlist', ['target' => $supplier])
@endif
