<x-larastrap::modal :title="$modifier->modifierType->name" classes="modifier-modal" :data-target-type="$modifier->model_type" size="fullscreen" :data-strings-source="route('modifiers.string', inlineId($modifier->target))">
    <x-larastrap::iform :obj="$modifier" method="POST" :action="route('modifiers.update', $modifier->id)">
        <input type="hidden" name="test-feedback" value="1">
        <input type="hidden" name="close-modal" value="1">
        <input type="hidden" name="update-field" value="modifier-button-{{ $modifier->modifierType->id }}-{{ $modifier->target_id }}">
        <input type="hidden" name="post-saved-function" value="afterModifierChange">
        <input type="hidden" name="_method" value="PUT">

        <div class="row">
            <div class="col">
                @if($modifier->target_type == 'App\Supplier')
                    <x-larastrap::check name="always_on" :label="_i('Modificatore sempre attivo')" :pophelp="_i('Se attivo, il modificatore viene sempre incluso nei nuovi ordini per questo fornitore anche se non viene qui valorizzato. Questo permette di avere sempre il modificatore disponibile nel contesto degli ordini e di poterlo aggiornare di volta in volta.')" />
                @endif

                @php

                $types = [
                    'none' => _i('Nessuno'),
                ];

                $booking_payment_type = movementTypes('booking-payment');

                foreach (movementTypes() as $info) {
                    if ($info->overlapsPaymentMethods($booking_payment_type) == false) {
                        $movement_type_alert = _i('Alcuni tipi di movimento contabile non sono inclusi in questa lista in quanto non ne è stato definito il comportamento per tutti i metodi di pagamenti previsti in fase di consegna (%s). Revisiona i tipi di movimento dal pannello Contabilità -> Tipi Movimenti', [join(', ', paymentsByType('booking-payment'))]);
                    }
                    else if ($info->visibility) {
                        $types[$info->id] = $info->name;
                    }
                }

                @endphp

                <x-larastrap::select name="movement_type_id" :label="_i('Tipo Movimento Contabile')" :options="$types" classes="movement-type-selector" :help="$movement_type_alert" :pophelp="_i('Selezionando un tipo di movimento contabile, al pagamento della consegna verrà generato un movimento con lo stesso valore del modificatore calcolato. Altrimenti, il valore del modificatore sarà incorporato nel pagamento della prenotazione stessa e andrà ad alterare il saldo complessivo del fornitore. Usa questa funzione se vuoi tenere traccia dettagliata degli importi pagati tramite questo modificatore.')" />

                <?php

                if ($modifier->target_type == 'App\Product') {
                    $values = [
                        'absolute' => _i('Assoluto'),
                        'percentage' => _i('Percentuale'),
						'mass' => _i('A Peso'),
                        'price' => (object) ['label' => _i('Prezzo Unitario'), 'disabled' => $modifier->applies_type == 'none'],
                    ];

                    $applies_types = [
                        'none' => _i('Nessuna soglia'),
                        'quantity' => _i('Quantità'),
                        'price' => _i('Valore'),
                        'order_price' => _i("Valore dell'Ordine"),
                        'weight' => _i('Peso'),
                    ];

                    $applies_targets = [
                        'product' => _i('Prodotto'),
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
						'mass' => _i('A Peso'),
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

                $labels = App\View\Texts\Modifier::descriptions($modifier->target);
                $actual_strings_combination = $modifier->description_index;

                ?>

                <x-larastrap::radios name="applies_type" :label="_i('Misura su cui applicare le soglie')" :options="$applies_types" />
                <x-larastrap::radios name="value" :label="_i('Valore')" :options="$values" />

                <div class="arithmetic_type_selection {{ $modifier->value == 'price' ? 'd-none' : '' }}">
                    <x-larastrap::radios name="arithmetic" :label="_i('Operazione')" :options="[
						'sum' => _i('Somma'),
						'sub' => _i('Sottrazione'),
						'passive' => _i('Passivo'),
						'apply' => (object) ['label' => _i('Applica'), 'hidden' => true
					]]" />
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
