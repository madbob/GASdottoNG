<script>
    window.dates_events = [
        @foreach(easyFilterOrders(null, null, null, ['open', 'closed']) as $a)
            @if($a->shipping)
                {
                    title: '{!! join(', ', $a->orders->reduce(function($carry, $item) { $carry[] = addslashes($item->supplier->name); return $carry; }, [])) !!}',
                    date: '{{ $a->shipping }}',
                    className: 'calendar-shipping-{{ $a->status }}',
                    url: '{{ $a->getBookingURL() }}'
                },
            @endif
        @endforeach

        @foreach(App\Date::localGas()->with(['target'])->get() as $d)
            @if($d->type == 'order')
                @foreach($d->all_dates as $dat)
                    {
                        title: '{{ str_replace("\n", " ", str_replace("\r", '', str_replace("'", "\'", $d->calendar_string))) }}',
                        date: '{{ date('Y-m-d', strtotime($dat . ' +' . $d->shipping . ' days')) }}',
                        className: 'calendar-date-{{ $d->type }}'
                    },
                @endforeach
            @else
                @foreach($d->all_dates as $dat)
                    {
                        title: '{{ str_replace("\n", " ", str_replace("\r", '', str_replace("'", "\'", $d->calendar_string))) }}',
                        date: '{{ $dat }}',
                        className: 'calendar-date-{{ $d->type }}'

                        @if($d->type == 'internal')
                            , url: '{{ route('notifications.index') . '#' . $d->id }}'
                        @endif
                    },
                @endforeach
            @endif
        @endforeach
    ];

    window.translated_days = [
        @foreach(localeDays() as $day => $offset)
            '{{ ucwords(substr($day, 0, 3)) }}',
        @endforeach
    ];

    window.translated_months = [
        @foreach(localeMonths() as $month => $offset)
            '{{ ucwords(substr($month, 0, 3)) }}',
        @endforeach
    ];
</script>

@php

$events = [];

foreach (easyFilterOrders(null, null, null, ['open', 'closed']) as $a) {
    if ($a->shipping) {
        $events[] = (object) [
            'title' => join(', ', $a->orders->reduce(function($carry, $item) { $carry[] = addslashes($item->supplier->name); return $carry; }, [])),
            'date' => $a->shipping,
            'className' => 'calendar-shipping-' . $a->status,
            'url' => $a->getBookingURL(),
        ];
    }
}

foreach (App\Date::localGas()->with(['target'])->get() as $d) {
    if ($d->type == 'order') {
        foreach($d->all_dates as $dat) {
            $events[] = (object) [
                'title' => str_replace("\n", " ", str_replace("\r", '', str_replace("'", "\'", $d->calendar_string))),
                'date' => date('Y-m-d', strtotime($dat . ' +' . $d->shipping . ' days')),
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
            <a class="calendar-shipping-open">{{ _i('Ordini Aperti') }}</a>
            <a class="calendar-shipping-closed">{{ _i('Ordini Chiusi') }}</a>
            @if(App\Date::count())
                <a class="calendar-date-confirmed">{{ _i('Date Confermate') }}</a>
                <a class="calendar-date-temp">{{ _i('Date Temporanee') }}</a>
                <a class="calendar-date-internal">{{ _i('Appuntamenti') }}</a>
            @endif
        </div>
    </div>
</div>
