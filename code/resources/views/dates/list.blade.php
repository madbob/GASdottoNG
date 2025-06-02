@if($dates->isEmpty() == false)
    <?php

    $today = date('Y-m-d');
    $automatic_dates = [];
    $effective_dates = [];

    foreach($dates as $d) {
        if ($d->type == 'order') {
            $automatic_dates = array_merge($automatic_dates, $d->all_dates);
        }
        else {
            $effective_dates = array_merge($effective_dates, $d->all_dates);
        }
    }

    sort($effective_dates);
    sort($automatic_dates);

    ?>

    @if(!empty($effective_dates))
        <p>
            {{ __('notifications.next_dates') }}
        </p>
        <ul>
            @foreach($effective_dates as $d)
                <li>{{ printableDate($d) }}</li>
            @endforeach
        </ul>
    @endif

    @if(!empty($automatic_dates))
        <p>
            {{ __('notifications.next_auto_orders') }}
        </p>
        <ul>
            @foreach($automatic_dates as $d)
                @if($d >= $today)
                    <li>{{ printableDate($d) }}</li>
                @endif
            @endforeach
        </ul>
    @endif
@endif
