<script>
    var dates_events = [
        @foreach(App\Aggregate::easyFilter(null, null, null, ['open', 'closed']) as $a)
            @if($a->shipping)
                {
                    title: '{!! join(', ', $a->orders->reduce(function($carry, $item) { $carry[] = addslashes($item->supplier->name); return $carry; }, [])) !!}',
                    date: '{{ $a->shipping }}',
                    className: 'calendar-shipping-{{ $a->status }}',
                    url: '{{ $a->getBookingURL() }}'
                },
            @endif
        @endforeach

        @foreach(App\Date::all() as $d)
            @foreach($d->dates as $dat)
                {
                    title: '{{ $d->calendar_string }}',
                    date: '{{ $dat }}',
                    className: 'calendar-date-{{ $d->type }}'

                    @if($d->type == 'internal')
                        , url: '{{ route('notifications.index') . '#' . $d->id }}'
                    @endif
                },
            @endforeach
        @endforeach
    ];

    var translated_days = [
        @foreach(localeDays() as $day => $offset)
            '{{ ucwords(substr($day, 0, 3)) }}',
        @endforeach
    ];

    var translated_months = [
        @foreach(localeMonths() as $month => $offset)
            '{{ ucwords(substr($month, 0, 3)) }}',
        @endforeach
    ];
</script>

<div id="dates-calendar">
    <div id="actual-calendar"></div>

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
