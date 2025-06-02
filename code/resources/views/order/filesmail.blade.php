@if($contacts->isEmpty() == false)
    <?php

    if (!isset($default_subject)) {
        $default_subject = __('generic.attachment');
    }

    if (!isset($default_text)) {
        $default_text = '';
    }

    ?>

    <hr/>

    <x-larastrap::check name="action" value="email" tlabel="generic.forward" triggers_collapse="send_mail" />

    <x-larastrap::collapse id="send_mail">
        <x-larastrap::field tlabel="generic.recipients">
            @include('commons.manyrows', [
                'contents' => $contacts,
                'columns' => [
                    [
                        'label' => __('generic.value'),
                        'field' => 'value',
                        'type' => 'email',
                        'width' => 11,
                        'extra' => [
                            'nprefix' => 'recipient_mail_'
                        ]
                    ]
                ]
            ])
        </x-larastrap::field>

        <x-larastrap::text name="subject_mail" tlabel="generic.mailfield.subject" :value="$default_subject" />
        <x-larastrap::textarea name="body_mail" tlabel="generic.mailfield.body" :value="$default_text" />
    </x-larastrap::collapse>
@endif
