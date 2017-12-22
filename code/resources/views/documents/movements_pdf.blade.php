<html>
    <body>
        <h3>Esportazione Movimenti del GAS ad {{ date('d/m/Y') }}</h3>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    <th>Data Registrazione</th>
                    <th>Data Movimento</th>
                    <th>Tipo</th>
                    <th>Pagamento</th>
                    <th>Pagante</th>
                    <th>Pagato</th>
                    <th>Valore</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $mov)
                    <tr>
                        <td>{{ $mov->printableDate('registration_date') }}</td>
                        <td>{{ $mov->printableDate('date') }}</td>
                        <td>{{ $mov->printableType() }}</td>
                        <td>{{ $mov->printablePayment() }}</td>
                        <td>{{ $mov->sender ? $mov->sender->printableName() : '' }}</td>
                        <td>{{ $mov->target ? $mov->target->printableName() : '' }}</td>
                        <td>{{ printablePrice($mov->amount) }} €</td>
                        <td>{{ $mov->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
