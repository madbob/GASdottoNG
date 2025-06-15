<x-larastrap::accordionitem tlabel="gas.orders_and_deliveries">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="orders">

            <div class="col">
                <x-larastrap::check name="enable_restrict_booking_to_credit" tlabel="gas.only_bookings_with_credit" triggers_collapse="enable_restrict_booking_to_credit" :value="$gas->hasFeature('restrict_booking_to_credit')" />
                <x-larastrap::collapse id="enable_restrict_booking_to_credit">
                    <x-larastrap::number name="restrict_booking_to_credit->limit" tlabel="gas.only_bookings_with_credit_limit" :value="$gas->restrict_booking_to_credit['limit']" tpophelp="gas.help.only_bookings_with_credit_limit" :textappend="defaultCurrency()->symbol" />
                </x-larastrap::collapse>

                <x-larastrap::check name="unmanaged_shipping" tlabel="gas.enable_deliveries_no_quantities" tpophelp="gas.help.enable_deliveries_no_quantities" />

                @php

                $values_for_contacts = [
                    'none' => __('texts.generic.none'),
                    'manual' => __('texts.generic.manual_selection'),
                ];

                $supplier_roles = rolesByClass('App\Supplier');
                foreach($supplier_roles as $sr) {
                    $values_for_contacts[$sr->id] = __('texts.generic.named_all', ['name' => $sr->name]);
                }

                @endphp

                <x-larastrap::radios name="booking_contacts" tlabel="gas.display_contacts" :options="$values_for_contacts" classes="btn-group-vertical" />

                <x-larastrap::field tlabel="gas.active_columns_summary" tpophelp="gas.help.active_columns_summary">
                    <?php $columns = $currentgas->orders_display_columns ?>
                    @foreach(App\Order::displayColumns() as $identifier => $metadata)
                        <div class="form-check form-switch">
                            <input type="checkbox" name="orders_display_columns[]" class="form-check-input" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                            @if(isset($metadata->help))
                                <small> - {{ $metadata->help }}</small>
                            @endif
                        </div>
                    @endforeach
                </x-larastrap::field>

                <x-larastrap::field tlabel="gas.default_columns_shipping_document" tpophelp="gas.help.default_columns_shipping_document">
                    <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns('shipping')) ?>
                    <x-larastrap::checks name="orders_shipping_user_columns" :options="$options" squeeze />

                    <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('shipping')) ?>
                    <x-larastrap::checks name="orders_shipping_product_columns" :options="$options" squeeze classes="mt-3" />
                </x-larastrap::field>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
