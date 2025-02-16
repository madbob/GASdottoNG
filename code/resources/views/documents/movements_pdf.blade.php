<html>
    <head>
        <style>
            table {
                border-spacing: 0;
                border-collapse: collapse;
            }
        </style>
    </head>

    <body>
        <h3>{{ _i('Esportazione Movimenti del GAS al %s', [date('d/m/Y')]) }}</h3>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    <th scope="col">{{ _i('Data Registrazione') }}</th>
                    <th scope="col">{{ _i('Data Movimento') }}</th>
                    <th scope="col">{{ _i('Tipo') }}</th>
                    <th scope="col">{{ _i('Pagamento') }}</th>
                    <th scope="col">{{ _i('Pagante') }}</th>
                    <th scope="col">{{ _i('Pagato') }}</th>
                    <th scope="col">{{ _i('Valore') }}</th>
                    <th scope="col">{{ _i('Note') }}</th>
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
                        <td>{{ printablePriceCurrency($mov->amount) }}</td>
                        <td>{{ $mov->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
