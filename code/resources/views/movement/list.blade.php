@if($movements->count() == 0)

<div class="alert alert-info" role="alert">
    Non ci sono elementi da visualizzare.
</div>

@else

<table class="table">
    <thead>
        <tr>
            <th>Data</th>
            <th>Tipo</th>
            <th>Pagamento</th>
            <th>Pagante</th>
            <th>Pagato</th>
            <th>Valore</th>
        </tr>
    </thead>

    <tbody>
        @foreach($movements as $mov)
            <tr>
                <td>{{ $mov->printableDate('registration_date') }}</td>
                <td>{{ $mov->printableType() }}</td>
                <td><span class="glyphicon {{ $mov->payment_icon }}" aria-hidden="true"></span></td>
                <td>{{ $mov->sender->printableName() }}</td>
                <td>{{ $mov->target->printableName() }}</td>
                <td>{{ printablePrice($mov->amount) }} €</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endif
