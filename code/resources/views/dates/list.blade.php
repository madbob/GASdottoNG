@if($dates->isEmpty() == false)
    <div class="form-group">
        <div class="col-sm-offset-{{ $labelsize }} col-sm-{{ $fieldsize }}">
            <p>
                {{ _i('Prossime date in calendario:') }}
            </p>
            <ul>
                @foreach($dates as $d)
                    <li>{{ printableDate($d->date) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
