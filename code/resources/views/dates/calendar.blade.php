@php

$events = [];

foreach (easyFilterOrders(null, null, null, ['open', 'closed']) as $a) {
    if ($a->shipping) {
        $events[] = (object) [
            'title' => join(', ', $a->orders->reduce(function($carry, $item) { $carry[] = addslashes($item->supplier->name); return $carry; }, [])),
            'date' => $a->shipping->format('Y-m-d'),
            'className' => 'calendar-shipping-' . $a->status,
            'url' => $a->getBookingURL(),
        ];
    }
}

/*
    Memo: nel calendario vengono sempre mostrate solo le date di consegna degli
    ordini
*/
foreach (App\Date::localGas()->with(['target'])->get() as $d) {
    if ($d->type == 'order') {
        foreach($d->order_dates as $dat) {
            $events[] = (object) [
                'title' => str_replace("\n", " ", str_replace("\r", '', str_replace("'", "\'", $d->calendar_string))),
                'date' => $dat->shipping,
                'className' => 'calendar-date-' . $d->type,
            ];
        }
    }
    else {
        foreach ($d->all_dates as $dat) {
            $e = (object) [
                'title' => str_replace("\n", " ", str_replace("\r", '', str_replace("'", "\'", $d->calendar_string))),
                'date' => $dat,
                'className' => 'calendar-date-' . $d->type,
            ];

            if ($d->type == 'internal') {
                $e->url = route('notifications.index') . '#' . $d->id;
            }

            $events[] = $e;
        }
    }
}

$days = [];
foreach(localeDays() as $day => $offset) {
    $days[] = ucwords(substr($day, 0, 3));
}

$months = [];
foreach(localeMonths() as $month => $offset) {
    $months[] = ucwords(substr($month, 0, 3));
}

@endphp

<div id="dates-calendar">
    <div class="actual-calendar" data-days="{{ base64_encode(json_encode($days)) }}" data-months="{{ base64_encode(json_encode($months)) }}" data-events="{{ base64_encode(json_encode($events)) }}"></div>

    <div class="row">
        <div class="col-md-3">
            <a class="calendar-shipping-open">{{ __('orders.list_open') }}</a>
            <a class="calendar-shipping-closed">{{ __('notifications.list.closed_orders') }}</a>
            @if(App\Date::count())
                <a class="calendar-date-confirmed">{{ __('notifications.list.confirmed_dates') }}</a>
                <a class="calendar-date-temp">{{ __('notifications.list.temporary_dates') }}</a>
                <a class="calendar-date-internal">{{ __('notifications.list.appointments') }}</a>
            @endif
        </div>
    </div>
</div>
