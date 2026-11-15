<x-larastrap::modal
    :title="$modifier->modifierType->name"
    classes="modifier-modal"
    :data-target-type="$modifier->getModifierDefinition()->modelType()"
    size="fullscreen"
    :data-strings-source="route('modifiers.string', inlineId($modifier->target))">

    <x-larastrap::iform :obj="$modifier" method="POST" :action="route('modifiers.update', $modifier->id)">
        <input type="hidden" name="test-feedback" value="1">
        <input type="hidden" name="close-modal" value="1">
        <input type="hidden" name="update-field" value="modifier-button-{{ $modifier->modifierType->id }}-{{ $modifier->target_id }}">
        <input type="hidden" name="post-saved-function" value="afterModifierChange">
        <input type="hidden" name="_method" value="PUT">

        <div class="row">
            <div class="col">
                @php

                $definition = $modifier->getModifierDefinition();
                $config = $definition->configParams();
                $applies_types = $config['applies_types'];
                $values = $config['values'];
                $applies_targets = $config['applies_targets'];

                [$payment_types, $movement_type_alert] = $definition->movementTypes();

                $labels = App\View\Texts\Modifier::descriptions($modifier->target);
                $actual_strings_combination = $modifier->description_index;

                @endphp

                @if($config['always_on'])
                    <x-larastrap::check
                        name="always_on"
                        tlabel="movements.always_active_modifiers"
                        tpophelp="movements.help.always_active_modifiers" />
                @endif

                <x-larastrap::select
                    name="movement_type_id"
                    tlabel="movements.type"
                    :options="$payment_types"
                    classes="movement-type-selector"
                    :help="$movement_type_alert"
                    tpophelp="movements.help.type_for_modifier" />

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

                @if(count($applies_targets) > 1)
                    <x-larastrap::radios name="applies_target" tlabel="movements.reference_for_modifier" :options="$applies_targets" />
                @else
                    <x-ls::hidden name="applies_target" :value="array_key_first($applies_targets)" />
                @endif

                <div class="distribution_type_selection">
                    <x-larastrap::radios name="distribution_type" tlabel="movements.distribute_on" :options="[
                        'none' => (object) ['label' => __('texts.generic.none'), 'hidden' => true],
                        'quantity' => __('texts.generic.quantity'),
                        'price' => __('texts.generic.value'),
                        'weight' => __('texts.generic.weight')
                    ]" />
                </div>

                <div class="advanced_input">
                    <x-larastrap::radios name="scale" tlabel="generic.difference" :options="[
                        'minor' => __('texts.generic.minor_than'),
                        'major' => __('texts.generic.major_than')
                    ]" />

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

                <div class="simplified_input">
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
