@if($dates->isEmpty() == false)
    <?php

    $effective_dates = [];

    foreach($dates as $d) {
        $effective_dates = array_merge($effective_dates, $d->dates);
    }

    $effective_dates = array_sort($effective_dates);

    ?>

    <div class="form-group suggested-dates">
        <div class="col-sm-offset-{{ $labelsize }} col-sm-{{ $fieldsize }}">
            <p>
                {{ _i('Prossime date in calendario:') }}
            </p>
            <ul>
                @foreach($effective_dates as $d)
                    <li>{{ printableDate($d) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
