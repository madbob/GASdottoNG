<script>
    var dates_events = [
        @foreach(App\Aggregate::easyFilter(null, null, null, ['open', 'closed']) as $a)
            @if($a->shipping)
                {
                    title: '{{ $a->printableName() }}',
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
