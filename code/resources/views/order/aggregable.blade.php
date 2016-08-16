@foreach($orders as $order)
        <ul class="well" data-aggregate-id="{{ $order->id }}">
                @foreach($order->orders as $suborder)
                        <li data-order-id="{{ $suborder->id }}">
                                {{ $suborder->printableName() }}<br/>
                                <small>{{ $suborder->printableDates() }}</small>
                        </li>
                @endforeach
        </ul>
@endforeach

<ul class="well" data-aggregate-id="new">
        <!-- Questo viene lasciato deliberatamente vuoto per poter fungere da appoggio per la creazione di un nuovo aggregato -->
</ul>
