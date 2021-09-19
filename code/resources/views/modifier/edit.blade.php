<x-larastrap::modal :title="$modifier->modifierType->name" classes="modifier-modal" :data-target-type="$modifier->model_type" size="fullscreen">
    <x-larastrap::iform :obj="$modifier" method="POST" :action="route('modifiers.update', $modifier->id)">
        <input type="hidden" name="test-feedback" value="1">
        <input type="hidden" name="close-modal" value="1">
        <input type="hidden" name="update-field" value="modifier-button-{{ $modifier->modifierType->id }}-{{ $modifier->target_id }}">
        <input type="hidden" name="post-saved-function" value="afterModifierChange">
        <input type="hidden" name="_method" value="PUT">

        <div class="row">
            <div class="col">
                <?php

                if ($modifier->target_type == 'App\Product') {
                    $values = [
                        'absolute' => _i('Assoluto'),
                        'percentage' => _i('Percentuale'),
                        'price' => (object) ['label' => _i('Prezzo Unitario'), 'disabled' => $modifier->applies_type == 'none'],
                    ];

                    $applies_types = [
                        'none' => _i('Nessuna soglia'),
                        'quantity' => _i('Quantità'),
                        'price' => _i('Valore'),
                        'weight' => _i('Peso'),
                    ];

                    $applies_targets = [
                        'product' => (object) ['label' => _i('Prodotto'), 'hidden' => true],
                        'booking' => _i('Singola Prenotazione'),
                        'order' => _i('Ordine Complessivo'),
                    ];

                    if ($modifier->applies_type == 'none') {
                        $modifier->applies_target = 'product';
                    }
                }
                else {
                    $values = [
                        'absolute' => _i('Assoluto'),
                        'percentage' => _i('Percentuale'),
                    ];

                    $applies_types = [
                        'none' => _i('Nessuna soglia'),
                        'price' => _i('Valore'),
                        'weight' => _i('Peso'),
                    ];

                    $applies_targets = [
                        'booking' => _i('Singola Prenotazione'),
                        'order' => _i('Ordine Complessivo'),
                    ];

                    if ($modifier->applies_target == 'order' && $modifier->distribution_type == 'none') {
                        $modifier->distribution_type = 'price';
                    }
                }

                $labels = App\Modifier::descriptions();
                $actual_strings_combination = $modifier->description_index;

                ?>

                <x-larastrap::radios name="applies_type" :label="_i('Misura su cui applicare le soglie')" :options="$applies_types" />
                <x-larastrap::radios name="value" :label="_i('Valore')" :options="$values" />

                <div class="arithmetic_type_selection {{ $modifier->value == 'price' ? 'd-none' : '' }}">
                    <x-larastrap::radios name="arithmetic" :label="_i('Operazione')" :options="['sum' => _i('Somma'), 'sub' => _i('Sottrazione'), 'passive' => _i('Passivo'), 'apply' => (object) ['label' => _i('Applica'), 'hidden' => true]]" />
                </div>

                @if($modifier->target_type != 'App\Product')
                    @include('modifier.modtarget')
                @endif

                <div class="advanced_input {{ $modifier->applies_type == 'none' ? 'd-none' : '' }}">
                    @if($modifier->target_type == 'App\Product')
                        @include('modifier.modtarget')
                    @endif

                    <x-larastrap::radios name="scale" :label="_i('Differenza')" :options="['minor' => _i('Minore di'), 'major' => _i('Maggiore di')]" />

                    <hr>

                    @include('commons.manyrows', [
                        'contents' => $modifier->definitions,
                        'new_label' => _i('Aggiungi Soglia'),
                        'columns' => [
                            [
                                'label' => '',
                                'field' => 'static',
                                'type' => 'custom',
                                'contents' => $labels[$actual_strings_combination][0],
                                'extra' => [
                                    'readonly' => true,
                                    'disabled' => true,
                                ],
                            ],
                            [
                                'label' => _i('Soglia'),
                                'field' => 'threshold',
                                'type' => 'number',
                                'extra' => [
                                    'textappend' => $labels[$actual_strings_combination][1],
                                ],
                            ],
                            [
                                'label' => '',
                                'field' => 'static',
                                'type' => 'custom',
                                'contents' => $labels[$actual_strings_combination][2],
                                'extra' => [
                                    'readonly' => true,
                                    'disabled' => true,
                                ],
                            ],
                            [
                                'label' => _i('Costo'),
                                'field' => 'amount',
                                'type' => 'number',
                                'extra' => [
                                    'textappend' => $labels[$actual_strings_combination][3],
                                ],
                            ],
                            [
                                'label' => '',
                                'field' => 'static',
                                'type' => 'custom',
                                'contents' => $labels[$actual_strings_combination][4],
                                'extra' => [
                                    'readonly' => true,
                                    'disabled' => true,
                                ],
                            ],
                        ]
                    ])
                </div>

                <div class="simplified_input {{ $modifier->applies_type != 'none' ? 'd-none' : '' }}">
                    <hr>

                    <div class="row">
                        <div class="col-md-2 col-xs-2 offset-md-3 form-control-static">{{ $labels[$actual_strings_combination][2] }}</div>

                        <div class="col-md-2 col-xs-2">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control number" name="simplified_amount" value="{{ $modifier->definitions[0]->amount ?? 0 }}" placeholder="Costo" autocomplete="off">
                                        <div class="input-group-text">{{ $labels[$actual_strings_combination][3] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 col-xs-2 form-control-static">{{ $labels[$actual_strings_combination][4] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
