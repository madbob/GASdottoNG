@if($contacts->isEmpty() == false)
    <hr/>

    @include('commons.boolfield', [
        'obj' => null,
        'name' => 'send_mail',
        'label' => 'Inoltra Mail',
        'labelsize' => 2,
        'fieldsize' => 10
    ])

    <div class="form-group order_document_recipient_mail">
        <label for="contacts" class="col-sm-2 control-label">Destinatari</label>

        <div class="col-sm-10">
            @include('commons.manyrows', [
                'contents' => $contacts,
                'columns' => [
                    [
                        'label' => 'Valore',
                        'field' => 'value',
                        'type' => 'email',
                        'width' => 10,
                        'extra' => [
                            'prefix' => 'recipient_mail_'
                        ]
                    ]
                ]
            ])
        </div>
    </div>

    @include('commons.textarea', [
        'obj' => null,
        'name' => 'body_mail',
        'label' => 'Testo Mail',
        'labelsize' => 2,
        'fieldsize' => 10,
        'extra_wrap_class' => 'order_document_body_mail'
    ])
@endif
