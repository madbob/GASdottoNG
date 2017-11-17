@foreach($orders as $index => $order)
    @if($index % 5 == 0)
        <div class="well" data-aggregate-id="new">
            <span class="glyphicon glyphicon-fullscreen pull-right explode-aggregate" aria-hidden="true"></span>

            <ul>
                <!-- Questo viene lasciato deliberatamente vuoto per poter fungere da appoggio per la creazione di un nuovo aggregato -->
            </ul>
        </div>
    @endif

    <div class="well" data-aggregate-id="{{ $order->id }}">
        <span class="glyphicon glyphicon-fullscreen pull-right explode-aggregate" aria-hidden="true"></span>

        <ul>
            @foreach($order->orders as $suborder)
                <li data-order-id="{{ $suborder->id }}">
                    {{ $suborder->printableName() }}<br/>
                    <small>{{ $suborder->printableDates() }}</small>
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
