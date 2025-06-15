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
                    <x-larastrap::check name="always_on" tlabel="movements.always_active_modifiers" tpophelp="movements.help.always_active_modifiers" />
                @endif

                @php

                $types = [
                    'none' => __('texts.generic.none'),
                ];

                $booking_payment_type = movementTypes('booking-payment');

                foreach (movementTypes() as $info) {
                    if ($info->overlapsPaymentMethods($booking_payment_type) == false) {
                        $movement_type_alert = __('texts.movements.help.missing_movements_for_modifiers', ['methods' => join(', ', paymentsByType('booking-payment'))]);
                    }
                    else if ($info->visibility) {
                        $types[$info->id] = $info->name;
                    }
                }

                @endphp

                <x-larastrap::select name="movement_type_id" tlabel="movements.type" :options="$types" classes="movement-type-selector" :help="$movement_type_alert" tpophelp="movements.help.type_for_modifier" />

                <?php

                if ($modifier->target_type == 'App\Product') {
                    $values = [
                        'absolute' => __('texts.generic.absolute'),
                        'percentage' => __('texts.generic.percentage'),
						'mass' => __('texts.generic.by_weight'),
                        'price' => (object) ['label' => __('texts.products.prices.unit'), 'disabled' => $modifier->applies_type == 'none'],
                    ];

                    $applies_types = [
                        'none' => __('texts.movements.modifier_no_theshold'),
                        'quantity' => __('texts.generic.quantity'),
                        'price' => __('texts.generic.value'),
                        'order_price' => __('texts.movements.order_value'),
                        'weight' => __('texts.generic.weight'),
                    ];

                    $applies_targets = [
                        'product' => __('texts.products.name'),
                        'booking' => __('texts.movements.apply_to_booking'),
                        'order' => __('texts.movements.apply_to_order'),
                    ];

                    if ($modifier->applies_type == 'none') {
                        $modifier->applies_target = 'product';
                    }
                }
                else {
                    $values = [
                        'absolute' => __('texts.generic.absolute'),
                        'percentage' => __('texts.generic.percentage'),
						'mass' => __('texts.generic.by_weight'),
                    ];

                    $applies_types = [
                        'none' => __('texts.movements.modifier_no_theshold'),
                        'price' => __('texts.generic.value'),
                        'weight' => __('texts.generic.weight'),
                    ];

                    $applies_targets = [
                        'booking' => __('texts.movements.apply_to_booking'),
                        'order' => __('texts.movements.apply_to_order'),
                    ];

                    if ($modifier->applies_target == 'order' && $modifier->distribution_type == 'none') {
                        $modifier->distribution_type = 'price';
                    }
                }

                $labels = App\View\Texts\Modifier::descriptions($modifier->target);
                $actual_strings_combination = $modifier->description_index;

                ?>

                <x-larastrap::radios name="applies_type" tlabel="movements.apply_theshold_to" :options="$applies_types" />
                <x-larastrap::radios name="value" tlabel="generic.value" :options="$values" />

                <div class="arithmetic_type_selection {{ $modifier->value == 'price' ? 'd-none' : '' }}">
                    <x-larastrap::radios name="arithmetic" tlabel="generic.operation" :options="[
						'sum' => __('texts.generic.sum'),
						'sub' => __('texts.generic.sub'),
						'passive' => __('texts.generic.passive'),
						'apply' => (object) ['label' => __('texts.generic.apply'), 'hidden' => true
					]]" />
                </div>

                @if($modifier->target_type != 'App\Product')
                    @include('modifier.modtarget')
                @endif

                <div class="advanced_input {{ $modifier->applies_type == 'none' ? 'd-none' : '' }}">
                    @if($modifier->target_type == 'App\Product')
                        @include('modifier.modtarget')
                    @endif

                    <x-larastrap::radios name="scale" tlabel="generic.difference" :options="['minor' => __('texts.generic.minor_than'), 'major' => __('texts.generic.major_than')]" />

                    <hr>

                    @include('commons.manyrows', [
                        'contents' => $modifier->definitions,
                        'new_label' => __('texts.generic.add_new'),
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
                                'label' => __('texts.generic.theshold'),
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
                                'label' => __('texts.generic.cost'),
                                'field' => 'amount',
                                'type' => 'number',
                                'extra' => [
                                    'step' => 0.01,
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
