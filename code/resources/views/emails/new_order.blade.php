<p>
    {{ _i('È stato aperto da %s un nuovo ordine per il fornitore %s.', [$user->gas->name, $order->supplier->name]) }}
</p>
<p>
    {{ _i('Per partecipare, accedi al seguente indirizzo:') }}
</p>
<p>
    {{ $order->getBookingURL() }}
</p>
<p>
    {{ _i('Le prenotazioni verranno chiuse %s.', printableDate($order->end)) }}
</p>
