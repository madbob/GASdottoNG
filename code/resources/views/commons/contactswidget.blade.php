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
                    'width' => 0,
                    'extra' => [
                        'prefix' => 'contact_'
                    ]
                ],
                [
                    'label' => 'Tipo',
                    'field' => 'type',
                    'type' => 'selectenum',
                    'width' => 4,
                    'extra' => [
                        'prefix' => 'contact_',
                        'values' => App\Contact::types()
                    ]
                ],
                [
                    'label' => 'Valore',
                    'field' => 'value',
                    'type' => 'text',
                    'width' => 6,
                    'extra' => [
                        'prefix' => 'contact_'
                    ]
                ]
            ]
        ])
    </div>
</div>
