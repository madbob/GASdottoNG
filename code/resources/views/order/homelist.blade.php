<ul class="list-group">
    @foreach($orders as $order)
        <a href="{{ $order->getBookingURL() }}" class="list-group-item">
            {!! $order->printableUserHeader() !!}
        </a>
    @endforeach
</ul>
