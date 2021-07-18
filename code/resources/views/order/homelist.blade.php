<div class="list-group list-group-flush">
    @foreach($orders as $order)
        <a href="{{ $order->getBookingURL() }}" class="list-group-item list-group-item-action">
            {!! $order->printableUserHeader() !!}
        </a>
    @endforeach
</div>
