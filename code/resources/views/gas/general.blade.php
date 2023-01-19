<x-larastrap::accordionitem :label="_i('Configurazioni Generali')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="reload-whole-page" value="1">
            <input type="hidden" name="group" value="general">

            <div class="col">
                <x-larastrap::text name="name" :label="_i('Nome del GAS')" required maxlength="20" />
                <x-larastrap::email name="email" :label="_i('E-Mail di Riferimento')" required />
                @include('commons.imagefield', ['obj' => $gas, 'name' => 'logo', 'label' => _i('Logo Homepage'), 'valuefrom' => 'logo_url'])
                <x-larastrap::textarea name="message" :label="_i('Messaggio Homepage')" :pophelp="_i('Eventuale messaggio da visualizzare sulla pagina di autenticazione di GASdotto, utile per comunicazioni speciali verso i membri del GAS o come messaggio di benvenuto')" />
                <x-larastrap::select name="language" :label="_i('Lingua')" :options="getLanguages()" />
                <x-larastrap::text name="currency" :label="_i('Valuta')" :pophelp="_i('Simbolo della valuta in uso. Verrà usato in tutte le visualizzazioni in cui sono espressi dei prezzi')" :value="defaultCurrency()->symbol" />

                @if(someoneCan('gas.access', $gas))
                    <x-larastrap::check name="restricted" :label="_i('Modalità Manutenzione')" :pophelp="_i('Se abilitato, il login sarà inibito a tutti gli utenti che non hanno il permesso Accesso consentito anche in manutenzione')" />
                @endif
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
