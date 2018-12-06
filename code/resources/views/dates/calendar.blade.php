<script>
    var dates_events = [
        @foreach(App\Aggregate::easyFilter(null, null, null, ['open', 'closed']) as $a)
            @if($a->shipping)
                {
                    title: '{!! join(', ', $a->orders->reduce(function($carry, $item) { $carry[] = addslashes($item->supplier->name); return $carry; }, [])) !!}',
                    start: '{{ $a->shipping }}',
                    className: 'calendar-shipping-{{ $a->status }}'
                },
            @endif
        @endforeach

        @foreach(App\Date::all() as $d)
            {
                title: '{{ sprintf('%s: %s', $d->target->name, $d->description) }}',
                start: '{{ $d->date }}',
                className: 'calendar-date-{{ $d->type }}'
            },
        @endforeach
    ];
</script>

<div id="dates-calendar"></div>
