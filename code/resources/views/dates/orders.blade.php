<x-larastrap::modal size="fullscreen">
    <div class="row">
        <div class="col-md-12">{{ __('texts.orders.help.automatic_instructions') }}</div>
    </div>

    <hr>

    <x-larastrap::iform method="POST" :action="route('dates.updateorders')">
        <input type="hidden" name="reload-whole-page" value="1">

        <div class="row">
            <div class="col-md-12 dates-for-orders" id="dates-in-range">
                @include('commons.manyrows', [
                    'contents' => $dates,
                    'show_columns' => true,
                    'columns' => [
                        [
                            'label' => __('texts.generic.id'),
                            'field' => 'id',
                            'type' => 'hidden',
                        ],
                        [
                            'label' => __('texts.generic.type'),
                            'field' => 'type',
                            'type' => 'hidden',
                            'extra' => [
                                'value' => 'order'
                            ]
                        ],
                        [
                            'label' => __('texts.orders.supplier'),
                            'field' => 'target_id',
                            'type' => 'select-model',
                            'width' => 15,
                            'extra' => [
                                'options' => $currentuser->targetsByAction('supplier.orders')
                            ]
                        ],
                        [
                            'label' => __('texts.notifications.recurrence'),
                            'field' => 'recurring',
                            'type' => 'periodic',
                            'width' => 20,
                        ],
                        [
                            'label' => __('texts.generic.to_do'),
                            'field' => 'action',
                            'type' => 'select',
                            'width' => 9,
                            'extra' => [
                                'options' => [
                                    'open' => __('texts.generic.opening'),
                                    'close' => __('texts.generic.closing'),
                                    'ship' => __('texts.orders.do_delivery'),
                                ]
                            ]
                        ],
                        [
                            'label' => __('texts.notifications.date_reference'),
                            'field' => 'first_offset',
                            'type' => 'number',
                            'width' => 18,
                            'extra' => [
                                'textprepend' => 'X',
                                'textappend' => 'X',
                                'attributes' => [
                                    'data-prelabel-open' => __('texts.orders.automatic_labels.close'),
                                    'data-postlabel-open' => __('texts.orders.automatic_labels.days_after'),
                                    'data-prelabel-close' => __('texts.orders.automatic_labels.open'),
                                    'data-postlabel-close' => __('texts.orders.automatic_labels.days_before'),
                                    'data-prelabel-ship' => __('texts.orders.automatic_labels.open'),
                                    'data-postlabel-ship' => __('texts.orders.automatic_labels.days_before'),
                                ]
                            ]
                        ],
                        [
                            'label' => __('texts.notifications.date_reference'),
                            'field' => 'second_offset',
                            'type' => 'number',
                            'width' => 18,
                            'extra' => [
                                'textprepend' => 'X',
                                'textappend' => 'X',
								'attributes' => [
                                    'data-prelabel-open' => __('texts.orders.automatic_labels.delivery'),
                                    'data-postlabel-open' => __('texts.orders.automatic_labels.days_after'),
                                    'data-prelabel-close' => __('texts.orders.automatic_labels.delivery'),
                                    'data-postlabel-close' => __('texts.orders.automatic_labels.days_after'),
                                    'data-prelabel-ship' => __('texts.orders.automatic_labels.close'),
                                    'data-postlabel-ship' => __('texts.orders.automatic_labels.days_before'),
								]
                            ]
                        ],
                        [
                            'label' => __('texts.generic.comment'),
                            'field' => 'comment',
                            'type' => 'text',
                            'width' => 10,
                            'extra' => [
                                'max_length' => 40
                            ]
                        ],
                        [
                            'label' => __('texts.generic.suspend'),
                            'field' => 'suspend',
                            'type' => 'check',
                            'width' => 5,
                            'help' => __('texts.notifications.help.suspend'),
                            'extra' => [
                                'reviewCallback' => function($component, $params) {
                                    $params['hidden'] = $params['obj'] ? false : true;
                                    $params['value'] = $params['obj'] ? $params['obj']->id : 0;
                                    $params['checked'] = $params['obj'] && $params['obj']->suspend;
                                    return $params;
                                }
                            ]
                        ],
                    ]
                ])
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
