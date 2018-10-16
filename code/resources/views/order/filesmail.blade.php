@if($contacts->isEmpty() == false)
    <?php

    if (!isset($default_subject))
        $default_subject = _i('Documento allegato');
    if (!isset($default_text))
        $default_text = '';

    ?>

    <hr/>

    @include('commons.boolfield', [
        'obj' => null,
        'name' => 'send_mail',
        'label' => _i('Inoltra Mail'),
        'labelsize' => 2,
        'fieldsize' => 10
    ])

    <div class="form-group order_document_recipient_mail">
        <label for="contacts" class="col-sm-2 control-label">{{ _i('Destinatari') }}</label>

        <div class="col-sm-10">
            @include('commons.manyrows', [
                'contents' => $contacts,
                'columns' => [
                    [
                        'label' => _i('Valore'),
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

    @include('commons.textfield', [
        'obj' => null,
        'name' => 'subject_mail',
        'label' => _i('Soggetto Mail'),
        'default_value' => $default_subject,
        'labelsize' => 2,
        'fieldsize' => 10,
        'extra_wrap_class' => 'order_document_body_mail'
    ])

    @include('commons.textarea', [
        'obj' => null,
        'name' => 'body_mail',
        'label' => _i('Testo Mail'),
        'default_value' => $default_text,
        'labelsize' => 2,
        'fieldsize' => 10,
        'extra_wrap_class' => 'order_document_body_mail'
    ])
@endif
