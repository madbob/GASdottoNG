<?php $suppliers = $currentuser->targetsByAction('supplier.orders') ?>

<x-larastrap::modal size="fullscreen">
    <div class="row">
        <div class="col-md-12">
            {{ __('texts.notifications.help.arbitrary_dates') }}
        </div>
    </div>

    <hr>

    <x-larastrap::iform method="PUT" :action="route('dates.update', 0)">
        <input type="hidden" name="reload-whole-page" value="1">

        <div class="row">
            <div class="col-md-12" id="dates-in-range">
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
                            'label' => __('texts.orders.supplier'),
                            'field' => 'target_id',
                            'type' => 'select-model',
                            'width' => 15,
                            'extra' => [
                                'options' => $suppliers
                            ]
                        ],
                        [
                            'label' => __('texts.generic.date'),
                            'field' => 'date',
                            'type' => 'datepicker',
                            'width' => 20,
                            'extra' => [
                                'defaults_now' => true
                            ]
                        ],
                        [
                            'label' => __('texts.notifications.recurrence'),
                            'field' => 'recurring',
                            'type' => 'periodic',
                            'width' => 30,
                        ],
                        [
                            'label' => __('texts.generic.description'),
                            'field' => 'description',
                            'type' => 'text',
                            'width' => 20,
                        ],
                        [
                            'label' => __('texts.generic.type'),
                            'field' => 'type',
                            'type' => 'select',
                            'width' => 10,
                            'extra' => [
                                'options' => App\Date::types()
                            ]
                        ],
                    ]
                ])
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
