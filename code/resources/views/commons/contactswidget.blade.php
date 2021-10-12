<x-larastrap::field :label="_i('Contatti')" :pophelp="_i('Qui si può specificare un numero arbitrario di contatti per il soggetto. Le notifiche saranno spedite a tutti gli indirizzi e-mail indicati. Si raccomanda di specificare un solo contatto per riga.')">
    @include('commons.manyrows', [
        'contents' => $obj ? $obj->contacts : [],
        'extra_class' => 'contacts-selection',
        'columns' => [
            [
                'label' => _i('ID'),
                'field' => 'id',
                'type' => 'hidden',
                'extra' => [
                    'nprefix' => 'contact_'
                ]
            ],
            [
                'label' => _i('Tipo'),
                'field' => 'type',
                'type' => 'select',
                'extra' => [
                    'nprefix' => 'contact_',
                    'options' => App\Contact::types()
                ]
            ],
            [
                'label' => _i('Valore'),
                'field' => 'value',
                'type' => 'text',
                'extra' => [
                    'nprefix' => 'contact_'
                ]
            ]
        ]
    ])
</x-larastrap::field>
