<ul>
    @if($modifier->definitions->isEmpty())
        <li>
            {{ __('generic.no_value') }}
        </li>
    @else
        <?php

        $labels = App\View\Texts\Modifier::descriptions($modifier->target);
        $actual_strings_combination = $modifier->description_index;

        ?>

        @if($modifier->applies_type != 'none')
            @foreach($modifier->definitions as $def)
                <li>
                    {!! ucfirst(sprintf('%s %s %s %s %s %s', $labels[$actual_strings_combination][0], $def->threshold, $labels[$actual_strings_combination][1], $labels[$actual_strings_combination][2], $def->amount, $labels[$actual_strings_combination][3])) !!}
                </li>
            @endforeach
        @else
            <li>
                {!! ucfirst(sprintf('%s %s %s %s', $labels[$actual_strings_combination][2], $modifier->definitions[0]->amount, $labels[$actual_strings_combination][3], $labels[$actual_strings_combination][4])) !!}
            </li>
        @endif
    @endif
</ul>
