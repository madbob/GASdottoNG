<x-larastrap::accordionitem :label_html="formatAccordionLabel('generic.email', 'envelope')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="mails">

            <div class="col">
                <x-larastrap::suggestion>
                    {{ __('texts.orders.help.mail_order_notification') }}
                </x-larastrap::suggestion>

                <div class="table-responsive">
                    <table class="table inline-cells">
                        <thead>
                            <tr>
                                <th scope="col" width="20%">&nbsp;</th>
                                <th scope="col" width="20%">{{ __('texts.orders.statuses.open') }}</th>
                                <th scope="col" width="40%">{{ __('texts.orders.statuses.closing') }}</th>
                                <th scope="col" width="20%">{{ __('texts.orders.statuses.closed') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">{{ __('texts.user.all') }}</th>
                                <td>
                                    <x-larastrap::check name="notify_all_new_orders" squeeze />
                                    <x-larastrap::pophelp ttext="orders.help.target_supplier_notifications" />
                                </td>
                                <td>
                                    <x-larastrap::check name="enable_send_order_reminder" squeeze triggers_collapse="send_order_reminder" :value="$gas->hasFeature('send_order_reminder')" />
                                    <x-larastrap::collapse id="send_order_reminder" label_width="8" input_width="4">
                                        <x-larastrap::number :margins="[0,0,0,0]" name="send_order_reminder" tlabel="orders.notify_days_before" />
                                    </x-larastrap::collapse>
                                </td>
                                <td>
                                    <x-larastrap::check name="auto_user_order_summary" squeeze />
                                    <x-larastrap::pophelp ttext="orders.help.notify_only_partecipants" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">{{ __('texts.supplier.referents') }}</th>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    <x-larastrap::check name="auto_referent_order_summary" squeeze />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                <x-larastrap::suggestion>
                    <p>
                        {{ __('texts.gas.help.custom_emails') }}
                    </p>
                    <p>
                        {{ __('texts.gas.help.global_placeholders') }}
                    </p>
                    <ul>
                        <li>gas_name: {{ __('texts.gas.attribute_name') }}</li>
                    </ul>
                </x-larastrap::suggestion>

                @foreach(systemParameters('MailTypes') as $identifier => $metadata)
                    <?php

                    if ($metadata->enabled($gas) == false) {
                        continue;
                    }

                    $mail_help = $metadata->formatParams();
                    $current_config = json_decode($gas->getConfig('mail_' . $identifier));
                    $current_subject = $current_config->subject;
                    $current_body = $current_config->body;

                    ?>

                    <p>
                        {{ $metadata->description() }}
                    </p>

                    <x-larastrap::text :name="'custom_mails_' . $identifier . '_subject'" tlabel="generic.mailfield.subject" :value="$current_subject" />
                    <x-larastrap::textarea :name="'custom_mails_' . $identifier . '_body'" tlabel="generic.mailfield.body" :value="$current_body" :help="$mail_help" />

                    <hr>
                @endforeach
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
