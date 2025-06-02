<x-larastrap::accordionitem tlabel="user.all">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="users">

            <div class="col">
                <x-larastrap::check name="enable_public_registrations" tlabel="gas.enable_public_registration" triggers_collapse="enable_public_registrations" :value="$gas->hasFeature('public_registrations')" tpophelp="gas.help.enable_public_registration" />
                <x-larastrap::collapse id="enable_public_registrations">
                    <x-larastrap::check name="public_registrations->manual" tlabel="gas.manual_approve_users" :value="$gas->public_registrations['manual']" />
                    <x-larastrap::url name="public_registrations->privacy_link" tlabel="gas.privacy_policy_link" :value="$gas->public_registrations['privacy_link']" />
                    <x-larastrap::url name="public_registrations->terms_link" tlabel="gas.terms_link" :value="$gas->public_registrations['terms_link']" />

                    <?php

                    $selectable_mandatory = [
                        'firstname' => __('user.firstname'),
                        'lastname' => __('user.lastname'),
                        'email' => __('generic.email'),
                        'phone' => __('generic.phone'),
                    ];

                    $selected_mandatory = [];
                    foreach($selectable_mandatory as $identifier => $label) {
                        if (in_array($identifier, $gas->public_registrations['mandatory_fields'])) {
                            $selected_mandatory[] = $identifier;
                        }
                    }

                    ?>

                    <x-larastrap::checks name="public_registrations->mandatory_fields" tlabel="gas.mandatory_fields" :options="$selectable_mandatory" :value="$selected_mandatory" />
                </x-larastrap::collapse>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
