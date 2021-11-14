<ul class="list-group mb-2 balance-summary" data-reload-url="{{ route('movements.balance', inlineId($obj)) }}">
    <?php

    $currencies = App\Currency::enabled();
    $balances = [];
    foreach ($currencies as $curr) {
        $balances[$curr->id] = $obj->extendedCurrentBalance($curr);
    }

    ?>

    @foreach($obj->extendedBalanceFields() as $identifier => $name)
        <li class="list-group-item">
            {{ $name }}

            @foreach($currencies as $curr)
                <span class="badge bg-secondary float-end ms-2">{{ printablePriceCurrency($balances[$curr->id]->$identifier, '.', $curr) }}</span>
            @endforeach
        </li>
    @endforeach
</ul>
