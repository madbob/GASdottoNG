<x-larastrap::accordionitem :label="_i('Utenti')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="users">

            <div class="col">
                <x-larastrap::check name="enable_public_registrations" :label="_i('Abilita Registrazione Pubblica')" classes="collapse_trigger" :value="$gas->hasFeature('public_registrations')" :pophelp="_i('Quando questa opzione è abilitata, chiunque potrà registrarsi all\'istanza per mezzo dell\'apposito pannello (accessibile da quello di login). Gli amministratori addetti agli utenti riceveranno una mail di notifica per ogni nuovo utente registrato')" />
                <div class="collapse" data-triggerable="enable_public_registrations">
                    <div class="card">
                        <div class="card-body">
                            <x-larastrap::url name="public_registrations->privacy_link" :label="_i('Link Privacy Policy')" :value="$gas->public_registrations['privacy_link']" />
                            <x-larastrap::url name="public_registrations->terms_link" :label="_i('Link Condizioni d\'Uso')" :value="$gas->public_registrations['terms_link']" />

                            <?php

                            $selectable_mandatory = [
                                'firstname' => _i('Nome'),
                                'lastname' => _i('Cognome'),
                                'email' => _i('E-Mail'),
                                'phone' => _i('Telefono'),
                            ];

                            foreach($selectable_mandatory as $identifier => $label) {
                                if (in_array($identifier, $gas->public_registrations['mandatory_fields'])) {
                                    $selected_mandatory[] = $identifier;
                                }
                            }

                            ?>

                            <x-larastrap::checks name="public_registrations->mandatory_fields" :label="_i('Campi Obbligatori')" :options="$selectable_mandatory" :value="$selected_mandatory" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
