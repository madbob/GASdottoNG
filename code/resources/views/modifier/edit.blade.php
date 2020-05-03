<div class="modal fade modifier-modal" id="editModifier-{{ $modifier->id }}" tabindex="-1" role="dialog" aria-labelledby="editModifier-{{ $modifier->id }}">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-form" method="POST" action="{{ route('modifiers.update', $modifier->id) }}">
                <input type="hidden" name="update-field" value="modifier-button-{{ $modifier->modifierType->id }}-{{ $modifier->target_id }}">
                <input type="hidden" name="close-modal" value="">
                <input type="hidden" name="modifier_type" value="{{ $modifier->modifierType->arithmetic }}">
                <input type="hidden" name="_method" value="PUT">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ $modifier->modifierType->name }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php

                            $labels = App\Modifier::descriptions();
                            $actual_strings_combination = $modifier->description_index;

                            /*
                                Solo se sto elaborando il modificatore di un
                                prodotto permetto di applicare le soglie sul
                                prodotto stesso, altrimenti non avrei modo di
                                valutarle
                            */
                            if ($modifier->target_type == 'App\Product') {
                                $distribution_targets = $applies_targets = [
                                    'product' => (object) [
                                        'name' => _i('Singolo Prodotto'),
                                    ],
                                    'booking' => (object) [
                                        'name' => _i('Singola Prenotazione'),
                                    ],
                                    'order' => (object) [
                                        'name' => _i('Ordine Complessivo'),
                                    ],
                                ];
                            }
                            else {
                                $distribution_targets = $applies_targets = [
                                    'booking' => (object) [
                                        'name' => _i('Singola Prenotazione'),
                                    ],
                                    'order' => (object) [
                                        'name' => _i('Ordine Complessivo'),
                                    ],
                                ];
                            }

                            ?>

                            <script>
                                var modifiers_strings = {!! json_encode($labels) !!};
                            </script>

                            @include('commons.radios', [
                                'obj' => $modifier,
                                'name' => 'value',
                                'label' => _i('Valore'),
                                'values' => [
                                    'absolute' => (object) [
                                        'name' => _i('Assoluto'),
                                    ],
                                    'percentage' => (object) [
                                        'name' => _i('Percentuale'),
                                    ],
                                ]
                            ])

                            @include('commons.radios', [
                                'obj' => $modifier,
                                'name' => 'applies_type',
                                'label' => _i('Misura su cui applicare le soglie'),
                                'values' => [
                                    'quantity' => (object) [
                                        'name' => _i('Quantità'),
                                    ],
                                    'price' => (object) [
                                        'name' => _i('Valore'),
                                    ],
                                    'weight' => (object) [
                                        'name' => _i('Peso'),
                                    ],
                                ]
                            ])

                            @include('commons.radios', [
                                'obj' => $modifier,
                                'name' => 'applies_target',
                                'label' => _i('Riferimento su cui applicare le soglie'),
                                'values' => $applies_targets,
                            ])

                            @include('commons.radios', [
                                'obj' => $modifier,
                                'name' => 'distribution_target',
                                'label' => _i('Riferimento su cui applicare il modificatore'),
                                'values' => $distribution_targets,
                            ])

                            @include('commons.radios', [
                                'obj' => $modifier,
                                'name' => 'distribution_type',
                                'label' => _i('Distribuzione sulle prenotazioni in base a'),
                                'values' => [
                                    'quantity' => (object) [
                                        'name' => _i('Quantità'),
                                    ],
                                    'price' => (object) [
                                        'name' => _i('Valore'),
                                    ],
                                    'weight' => (object) [
                                        'name' => _i('Peso'),
                                    ],
                                ]
                            ])

                            <hr>

                            @include('commons.manyrows', [
                                'contents' => $modifier->definitions,
                                'columns' => [
                                    [
                                        'label' => '',
                                        'field' => 'static',
                                        'type' => 'custom',
                                        'width' => 4,
                                        'contents' => $labels[$actual_strings_combination][0],
                                    ],
                                    [
                                        'label' => _i('Soglia'),
                                        'field' => 'threshold',
                                        'type' => 'number',
                                        'width' => 2,
                                        'extra' => [
                                            'postlabel' => $labels[$actual_strings_combination][1],
                                        ]
                                    ],
                                    [
                                        'label' => '',
                                        'field' => 'static',
                                        'type' => 'custom',
                                        'width' => 2,
                                        'contents' => $labels[$actual_strings_combination][2]
                                    ],
                                    [
                                        'label' => _i('Costo'),
                                        'field' => 'amount',
                                        'type' => 'number',
                                        'width' => 2,
                                        'extra' => [
                                            'postlabel' => $labels[$actual_strings_combination][3],
                                        ]
                                    ],
                                ]
                            ])
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    @if(empty($modifier->definitions) == false)
                        <button type="button" class="btn btn-danger spare-delete-button" data-delete-url="{{ route('modifiers.destroy', $modifier->id) }}">{{ _i('Elimina') }}</button>
                    @endif

                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
