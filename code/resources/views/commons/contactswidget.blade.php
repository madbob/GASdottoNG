<div class="form-group">
    @if($squeeze == false)
        <label for="contacts" class="col-sm-{{ $labelsize }} control-label">Contatti</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        @include('commons.manyrows', [
            'contents' => $obj ? $obj->contacts : [],
            'columns' => [
                [
                    'label' => 'ID',
                    'field' => 'id',
                    'type' => 'hidden',
                    'extra' => [
                        'prefix' => 'contact_'
                    ]
                ],
                [
                    'label' => 'Tipo',
                    'field' => 'type',
                    'type' => 'selectenum',
                    'extra' => [
                        'prefix' => 'contact_',
                        'values' => App\Contact::types()
                    ]
                ],
                [
                    'label' => 'Valore',
                    'field' => 'value',
                    'type' => 'text',
                    'extra' => [
                        'prefix' => 'contact_'
                    ]
                ]
            ]
        ])
    </div>
</div>
