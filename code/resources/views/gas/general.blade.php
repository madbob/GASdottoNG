<x-larastrap::accordionitem tlabel="generic.menu.configs">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="reload-whole-page" value="1">
            <input type="hidden" name="group" value="general">

            <div class="col">
                <x-larastrap::text name="name" tlabel="gas.attribute_name" required maxlength="20" />
                <x-larastrap::email name="email" tlabel="generic.email" required />
                @include('commons.imagefield', ['obj' => $gas, 'name' => 'logo', 'label' => __('gas.logo'), 'valuefrom' => 'logo_url'])
                <x-larastrap::textarea name="message" tlabel="gas.home_message" tpophelp="gas.help.home_message" />
                <x-larastrap::select name="language" tlabel="gas.language" :options="getLanguages()" />
                <x-larastrap::text name="currency" tlabel="movements.currency" tpophelp="gas.help.currency" :value="defaultCurrency()->symbol" />

                @if(someoneCan('gas.access', $gas))
                    <x-larastrap::check name="restricted" tlabel="gas.maintenance_mode" tpophelp="gas.help.maintenance_mode" />
                @endif

                <x-larastrap::check name="multigas" tlabel="gas.multigas_mode" tpophelp="gas.help.multigas_mode" />
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
