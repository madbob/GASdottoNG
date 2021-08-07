<ul>
    @if($modifier->definitions->isEmpty())
        <li>
            {{ _i('Nessun Valore') }}
        </li>
    @else
        <?php

        $labels = App\Modifier::descriptions();
        $actual_strings_combination = $modifier->description_index;

        ?>

        @if($modifier->applies_type != 'none')
            @foreach($modifier->definitions as $def)
                <li>
                    {!! sprintf('%s %s %s %s %s %s', $labels[$actual_strings_combination][0], $def->threshold, $labels[$actual_strings_combination][1], $labels[$actual_strings_combination][2], $def->amount, $labels[$actual_strings_combination][3]) !!}
                </li>
            @endforeach
        @else
            <li>
                {!! sprintf('%s %s %s %s', $labels[$actual_strings_combination][2], $modifier->definitions[0]->amount, $labels[$actual_strings_combination][3], $labels[$actual_strings_combination][4]) !!}
            </li>
        @endif
    @endif
</ul>
