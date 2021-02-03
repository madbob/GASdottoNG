<div class="modal fade modifier-modal" id="showModifier-{{ $modifier->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ $modifier->modifierType->name }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
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

                                @foreach($modifier->definitions as $def)
                                    <li>
                                        {!! sprintf('%s %s %s %s %s %s', $labels[$actual_strings_combination][0], $def->threshold, $labels[$actual_strings_combination][1], $labels[$actual_strings_combination][2], $def->amount, $labels[$actual_strings_combination][3]) !!}
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Chiudi') }}</button>
            </div>
        </div>
    </div>
</div>
