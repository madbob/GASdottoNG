<div class="modal fade modifier-modal" id="editModifier-{{ $modifier->id }}" tabindex="-1" role="dialog" aria-labelledby="editModifier-{{ $modifier->id }}" data-target-type="{{ $modifier->model_type }}">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-form" method="POST" action="{{ route('modifiers.update', $modifier->id) }}">
                <input type="hidden" name="update-field" value="modifier-button-{{ $modifier->modifierType->id }}-{{ $modifier->target_id }}">
                <input type="hidden" name="close-modal" value="">
                <input type="hidden" name="_method" value="PUT">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ $modifier->modifierType->name }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php

                            if ($modifier->target_type == 'App\Product') {
                                $values = [
                                    'absolute' => (object) [
                                        'name' => _i('Assoluto'),
                                    ],
                                    'percentage' => (object) [
                                        'name' => _i('Percentuale'),
                                    ],
                                    'price' => (object) [
                                        'name' => _i('Prezzo Unitario'),
                                        'disabled' => $modifier->applies_type == 'none',
                                    ],
                                ];

                                $applies_types = [
                                    'none' => (object) [
                                        'name' => _i('Nessuna soglia'),
                                    ],
                                    'quantity' => (object) [
                                        'name' => _i('Quantità'),
                                    ],
                                    'price' => (object) [
                                        'name' => _i('Valore'),
                                    ],
                                    'weight' => (object) [
                                        'name' => _i('Peso'),
                                    ],
                                ];

                                $applies_targets = [
                                    'product' => (object) [
                                        'name' => _i('Prodotto'),
                                        'hidden' => true,
                                    ],
                                    'booking' => (object) [
                                        'name' => _i('Singola Prenotazione'),
                                    ],
                                    'order' => (object) [
                                        'name' => _i('Ordine Complessivo'),
                                    ],
                                ];

                                if ($modifier->applies_type == 'none') {
                                    $modifier->applies_target = 'product';
                                }
                            }
                            else {
                                $values = [
                                    'absolute' => (object) [
                                        'name' => _i('Assoluto'),
                                    ],
                                    'percentage' => (object) [
                                        'name' => _i('Percentuale'),
                                    ],
                                ];

                                $applies_types = [
                                    'none' => (object) [
                                        'name' => _i('Nessuna soglia'),
                                    ],
                                    'price' => (object) [
                                        'name' => _i('Valore'),
                                    ],
                                    'weight' => (object) [
                                        'name' => _i('Peso'),
                                    ],
                                ];

                                $applies_targets = [
                                    'booking' => (object) [
                                        'name' => _i('Singola Prenotazione'),
                                    ],
                                    'order' => (object) [
                                        'name' => _i('Ordine Complessivo'),
                                    ],
                                ];
                            }

                            $labels = App\Modifier::descriptions();
                            $actual_strings_combination = $modifier->description_index;

                            ?>

                            <script>
                                var modifiers_strings = {!! json_encode($labels) !!};
                            </script>

                            @include('commons.radios', [
                                'obj' => $modifier,
                                'name' => 'applies_type',
                                'label' => _i('Misura su cui applicare le soglie'),
                                'values' => $applies_types,
                            ])

                            @include('commons.radios', [
                                'obj' => $modifier,
                                'name' => 'value',
                                'label' => _i('Valore'),
                                'values' => $values,
                            ])

                            <div class="arithmetic_type_selection {{ $modifier->value == 'price' ? 'hidden' : '' }}">
                                @include('commons.radios', [
                                    'obj' => $modifier,
                                    'name' => 'arithmetic',
                                    'label' => _i('Operazione'),
                                    'values' => [
                                        'sum' => (object) [
                                            'name' => _i('Somma'),
                                        ],
                                        'sub' => (object) [
                                            'name' => _i('Sottrazione')
                                        ],
                                        'apply' => (object) [
                                            'name' => _i('Applica'),
                                            'hidden' => true,
                                        ],
                                    ]
                                ])
                            </div>

                            @if($modifier->target_type != 'App\Product')
                                @include('modifier.modtarget')
                            @endif

                            <div class="advanced_input {{ $modifier->applies_type == 'none' ? 'hidden' : '' }}">
                                @include('commons.radios', [
                                    'obj' => $modifier,
                                    'name' => 'scale',
                                    'label' => _i('Differenza'),
                                    'values' => [
                                        'minor' => (object) [
                                            'name' => _i('Minore di'),
                                        ],
                                        'major' => (object) [
                                            'name' => _i('Maggiore di')
                                        ],
                                    ]
                                ])

                                @if($modifier->target_type == 'App\Product')
                                    @include('modifier.modtarget')
                                @endif

                                <hr>

                                @include('commons.manyrows', [
                                    'contents' => $modifier->definitions,
                                    'columns' => [
                                        [
                                            'label' => '',
                                            'field' => 'static',
                                            'type' => 'custom',
                                            'width' => 2,
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
                                        [
                                            'label' => '',
                                            'field' => 'static',
                                            'type' => 'custom',
                                            'width' => 2,
                                            'contents' => $labels[$actual_strings_combination][4]
                                        ],
                                    ]
                                ])
                            </div>

                            <div class="simplified_input {{ $modifier->applies_type != 'none' ? 'hidden' : '' }}">
                                <hr>

                                <div class="row">
                                    <div class="col-md-2 col-xs-2 col-md-offset-3 form-control-static">{{ $labels[$actual_strings_combination][2] }}</div>

                                    <div class="col-md-2 col-xs-2">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <input type="text" class="form-control number" name="simplified_amount" value="{{ $modifier->definitions[0]->amount ?? 0 }}" placeholder="Costo" autocomplete="off">
                                                    <div class="input-group-addon">{{ $labels[$actual_strings_combination][3] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-xs-2 form-control-static">{{ $labels[$actual_strings_combination][4] }}</div>
                                </div>
                            </div>
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
