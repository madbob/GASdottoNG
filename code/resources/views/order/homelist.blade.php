<ul class="list-group">
    @foreach($orders as $order)
        <a href="{{ $order->getDisplayURL() }}" class="list-group-item">
            {!! $order->printableHeader() !!}

            <?php

            $tot = 0;

            foreach($order->orders as $o) {
                $b = $o->userBooking();
                if ($b->exists())
                    $tot += $b->total_value;
            }

            ?>

            @if($tot == 0)
                <span class="pull-right">Non hai partecipato a quest'ordine</span>
            @else
                <span class="pull-right">Hai ordinato {{ printablePrice($tot) }} €</span>
            @endif
        </a>
    @endforeach
</ul>
