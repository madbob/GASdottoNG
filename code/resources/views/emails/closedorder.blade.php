<p>
    {{ __('orders.notices.closed_orders') }}
</p>
<ul>
    @foreach($orders as $order)
        <li>{{ $order->printableName() }}</li>
    @endforeach
</ul>
<p>
    {{ __('orders.notices.email_attachments') }}
</p>
