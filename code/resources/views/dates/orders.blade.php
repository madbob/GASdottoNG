<x-larastrap::modal size="fullscreen">
    <div class="row">
        <div class="col-md-12">{{ __('orders.help.automatic_instructions') }}</div>
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
                            'label' => __('generic.id'),
                            'field' => 'id',
                            'type' => 'hidden',
                        ],
                        [
                            'label' => __('generic.type'),
                            'field' => 'type',
                            'type' => 'hidden',
                            'extra' => [
                                'value' => 'order'
                            ]
                        ],
                        [
                            'label' => __('orders.supplier'),
                            'field' => 'target_id',
                            'type' => 'select-model',
                            'width' => 15,
                            'extra' => [
                                'options' => $currentuser->targetsByAction('supplier.orders')
                            ]
                        ],
                        [
                            'label' => __('notifications.recurrence'),
                            'field' => 'recurring',
                            'type' => 'periodic',
                            'width' => 20,
                        ],
                        [
                            'label' => __('generic.to_do'),
                            'field' => 'action',
                            'type' => 'select',
                            'width' => 9,
                            'extra' => [
                                'options' => [
                                    'open' => __('generic.opening'),
                                    'close' => __('generic.closing'),
                                    'ship' => __('orders.do_delivery'),
                                ]
                            ]
                        ],
                        [
                            'label' => __('notifications.date_reference'),
                            'field' => 'first_offset',
                            'type' => 'number',
                            'width' => 18,
                            'extra' => [
                                'textprepend' => 'X',
                                'textappend' => 'X',
                                'attributes' => [
                                    'data-prelabel-open' => __('orders.automatic_labels.close'),
                                    'data-postlabel-open' => __('orders.automatic_labels.days_after'),
                                    'data-prelabel-close' => __('orders.automatic_labels.open'),
                                    'data-postlabel-close' => __('orders.automatic_labels.days_before'),
                                    'data-prelabel-ship' => __('orders.automatic_labels.open'),
                                    'data-postlabel-ship' => __('orders.automatic_labels.days_before'),
                                ]
                            ]
                        ],
                        [
                            'label' => __('notifications.date_reference'),
                            'field' => 'second_offset',
                            'type' => 'number',
                            'width' => 18,
                            'extra' => [
                                'textprepend' => 'X',
                                'textappend' => 'X',
								'attributes' => [
                                    'data-prelabel-open' => __('orders.automatic_labels.delivery'),
                                    'data-postlabel-open' => __('orders.automatic_labels.days_after'),
                                    'data-prelabel-close' => __('orders.automatic_labels.delivery'),
                                    'data-postlabel-close' => __('orders.automatic_labels.days_after'),
                                    'data-prelabel-ship' => __('orders.automatic_labels.close'),
                                    'data-postlabel-ship' => __('orders.automatic_labels.days_before'),
								]
                            ]
                        ],
                        [
                            'label' => __('generic.comment'),
                            'field' => 'comment',
                            'type' => 'text',
                            'width' => 10,
                            'extra' => [
                                'max_length' => 40
                            ]
                        ],
                        [
                            'label' => __('generic.suspend'),
                            'field' => 'suspend',
                            'type' => 'check',
                            'width' => 5,
                            'help' => __('notifications.help.suspend'),
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
