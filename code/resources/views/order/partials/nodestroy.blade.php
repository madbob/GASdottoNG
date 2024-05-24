<x-larastrap::modal :title="_i('Operazione non permessa')">
    <p>
        {{ _i("L'ordine %s ha attualmente delle prenotazioni attive, e non può essere pertanto rimosso.", [$order->printableName()]) }}
    </p>
    <p>
        {!! _i('Si raccomanda di accedere al <a href="%s">pannello delle prenotazioni per questo ordine</a> e, con lo strumento "Prenotazioni per Altri Utenti", invalidare le prenotazioni esistenti.', [$order->getBookingURL()]) !!}
    </p>
    <p>
        {{ _i("Questo meccanismo è deliberatemente non automatico e volutamente complesso, per evitare la perdita involontaria di dati.") }}
    </p>
</x-larastrap::modal>
