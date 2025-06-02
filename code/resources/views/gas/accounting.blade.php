<x-larastrap::accordionitem tlabel="generic.menu.accounting">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="banking">

            <div class="col">
                <x-larastrap::text name="year_closing" tlabel="gas.social_year" classes="date-to-month" :value="ucwords(\Carbon\Carbon::parse($gas->getConfig('year_closing'))->isoFormat('DD MMMM'))" textappend="<i class='bi-calendar'></i>" tpophelp="gas.help.social_year" />
                <x-larastrap::price name="annual_fee_amount" tlabel="user.fee" tpophelp="gas.help.fee" />
                <x-larastrap::price name="deposit_amount" tlabel="user.deposit" tpophelp="gas.help.deposit" />
                <x-larastrap::check name="auto_fee" tlabel="gas.automatic_fees" tpophelp="gas.help.automatic_fees" />

                <x-larastrap::field label="Informazioni in homepage">
                    <x-larastrap::check name="credit_home->current_credit" tlabel="movements.current_credit" :checked="$gas->credit_home['current_credit']" squeeze inline switch />
                    <x-larastrap::check name="credit_home->to_pay" tlabel="orders.bookings_to_pay" :checked="$gas->credit_home['to_pay']" squeeze inline switch />
                </x-larastrap::field>

                <hr>

                <x-larastrap::check name="enable_rid" tlabel="gas.enable_sepa" triggers_collapse="enable_rid" :value="$gas->hasFeature('rid')" tpophelp="gas.help.enable_sepa" />
                <x-larastrap::collapse id="enable_rid">
                    <x-larastrap::text name="rid->iban" tlabel="generic.iban" :value="$gas->rid['iban'] ?? ''" />
                    <x-larastrap::text name="rid->id" tlabel="movements.sepa.creditor_identifier" :value="$gas->rid['id'] ?? ''" />
                    <x-larastrap::text name="rid->org" tlabel="movements.sepa.business_code" :value="$gas->rid['org'] ?? ''" />
                </x-larastrap::collapse>

                <x-larastrap::check name="enable_satispay" tlabel="movements.enable_satispay" triggers_collapse="enable_satispay" :value="$gas->hasFeature('satispay')" tpophelp="gas.help.enable_satispay" />
                <x-larastrap::collapse id="enable_satispay">
                    @if($gas->hasFeature('satispay'))
                        {{ __('gas.help.enabled_satispay') }}
                    @else
                        <x-larastrap::text name="satispay_auth_code" tlabel="gas.satispay.activation_code" tpophelp="gas.help.satispay_activation_code" value="" />
                    @endif
                </x-larastrap::collapse>

                <x-larastrap::check name="enable_integralces" tlabel="gas.enable_integralces" triggers_collapse="enable_integralces" :value="$gas->hasFeature('integralces')" tpophelp="gas.help.enable_integralces" />
                <x-larastrap::collapse id="enable_integralces">
                    <x-larastrap::text name="integralces->symbol" tlabel="movements.currency" :value="$gas->integralces['symbol']" />
                    <x-larastrap::text name="integralces->identifier" tlabel="gas.integralces_identifier" :value="$gas->integralces['identifier']" />
                </x-larastrap::collapse>

                <x-larastrap::check name="enable_extra_invoicing" tlabel="gas.enable_invoicing" triggers_collapse="enable_extra_invoicing" :value="$gas->hasFeature('extra_invoicing')" tpophelp="gas.help.enable_invoicing" />
                <x-larastrap::collapse id="enable_extra_invoicing">
                    <x-larastrap::text name="extra_invoicing->business_name" tlabel="supplier.legal_name" :value="$gas->extra_invoicing['business_name']" />
                    <x-larastrap::text name="extra_invoicing->taxcode" tlabel="user.taxcode" :value="$gas->extra_invoicing['taxcode']" classes="required_when_triggered" data-alternative-required="extra_invoicing->vat" />
                    <x-larastrap::text name="extra_invoicing->vat" tlabel="supplier.vat" :value="$gas->extra_invoicing['vat']" classes="required_when_triggered" data-alternative-required="extra_invoicing->taxcode" />
                    <x-larastrap::text name="extra_invoicing->address" tlabel="generic.address" :value="$gas->extra_invoicing['address']" />
                    <x-larastrap::number name="extra_invoicing->invoices_counter" tlabel="gas.invoices_counter" :value="$gas->extra_invoicing['invoices_counter']" tpophelp="gas.help.invoices_counter" />
                </x-larastrap::collapse>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
