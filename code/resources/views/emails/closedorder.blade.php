<p>
    {{ __('texts.orders.notices.closed_orders') }}
</p>
<ul>
    @foreach($orders as $order)
        <li>{{ $order->printableName() }}</li>
    @endforeach
</ul>
<p>
    {{ __('texts.orders.notices.email_attachments') }}
</p>
