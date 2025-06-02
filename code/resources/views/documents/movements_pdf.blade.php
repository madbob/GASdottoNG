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
        <h3>{{ __('export.movements_heading', ['date' => date('d/m/Y')]) }}</h3>

        <hr/>

        <table border="1" style="width: 100%" cellpadding="5">
            <thead>
                <tr>
                    <th scope="col">{{ __('movements.registration_date') }}</th>
                    <th scope="col">{{ __('movements.execution_date') }}</th>
                    <th scope="col">{{ __('generic.type') }}</th>
                    <th scope="col">{{ __('generic.payment') }}</th>
                    <th scope="col">{{ __('movements.paying') }}</th>
                    <th scope="col">{{ __('movements.payed') }}</th>
                    <th scope="col">{{ __('generic.value') }}</th>
                    <th scope="col">{{ __('generic.notes') }}</th>
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
