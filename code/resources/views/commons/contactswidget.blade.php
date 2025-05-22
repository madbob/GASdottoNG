<x-larastrap::field tlabel="generic.contacts" tpophelp="generic.help.contacts">
    @include('commons.manyrows', [
        'contents' => $obj ? $obj->contacts : [],
        'extra_class' => 'contacts-selection',
        'columns' => [
            [
                'label' => __('generic.id'),
                'field' => 'id',
                'type' => 'hidden',
                'extra' => [
                    'nprefix' => 'contact_'
                ]
            ],
            [
                'label' => __('generic.type'),
                'field' => 'type',
                'type' => 'select',
                'extra' => [
                    'nprefix' => 'contact_',
                    'options' => App\Contact::types()
                ]
            ],
            [
                'label' => __('generic.value'),
                'field' => 'value',
                'type' => 'text',
                'extra' => [
                    'nprefix' => 'contact_'
                ]
            ]
        ]
    ])
</x-larastrap::field>
