<p>
    {{ _i("I seguenti ordini sono stati chiusi:") }}
</p>
<ul>
    @foreach($orders as $order)
        <li>{{ $order->printableName() }}</li>
    @endforeach
</ul>
<p>
    {{ _i("In allegato i relativi riassunti prodotti, in PDF e CSV.") }}
</p>
