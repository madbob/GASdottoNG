<?php

$methods = [];

$types = [];
foreach (movementTypes() as $info) {
    if ($info->visibility) {
        $types[$info->id] = $info->name;

        $methods[] = (object) [
            'method' => $info->id,
            'payments' => array_keys(paymentsByType($info->id)),
            'default_payment' => defaultPaymentByType($info->id),
        ];
    }
}

$payments = paymentsSimple();
$currencies = App\Currency::enabled();

$users = App\User::sorted()->get();
$suppliers = App\Supplier::orderBy('name', 'asc')->get();

?>

<x-larastrap::modal size="fullscreen">
    <input type="hidden" name="matching_methods_for_movement_types" value='{!! json_encode($methods) !!}'>

    <div class="wizard_page">
        <x-larastrap::wizardform :action="url('import/csv?type=movements&step=run')">
            @if(!empty($errors))
                <p>
                    {{ __('generic.errors') }}:
                </p>

                <ul class="list-group">
                    @foreach($errors as $e)
                        <li class="list-group-item">{!! $e !!}</li>
                    @endforeach
                </ul>

                <hr/>
            @endif

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('imports.do') }}</th>
                        <th scope="col">{{ __('generic.date') }}</th>
                        <th scope="col">{{ __('user.name') }}</th>
                        <th scope="col">{{ __('orders.supplier') }}</th>
                        <th scope="col">{{ __('generic.identifier') }}</th>
                        <th scope="col">{{ __('generic.notes') }}</th>
                        <th scope="col">
                            <x-larastrap::select name="type" nprefix="skip" squeeze :options="$types" classes="triggers-all-selects csv_movement_type_select" data-target-class="csv_movement_type_select" />
                        </th>
                        <th scope="col">
                            <x-larastrap::select name="method" nprefix="skip" squeeze :options="$payments" classes="triggers-all-selects csv_movement_method_select" data-target-class="csv_movement_method_select" value="bank" />
                        </th>
                        <th scope="col">{{ __('generic.value') }}</th>
                        <th scope="col">
                            <x-larastrap::select-model name="currency_id" nprefix="skip" squeeze :options="$currencies" classes="triggers-all-selects csv_movement_currency_select" data-target-class="csv_movement_currency_select" :value="defaultCurrency()->id" />
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $index => $mov)
                        <x-larastrap::enclose :obj="$mov">
                            <tr>
                                <td>
                                    <input type="checkbox" name="import[]" value="{{ $index }}" checked>
                                </td>
                                <td>
                                    {{ $mov->printableDate('date') }}
                                    <x-larastrap::hidden name="date" npostfix="[]" />
                                </td>
                                <td>
                                    <x-larastrap::select-model name="sender_id" npostfix="[]" squeeze :options="$users" :extra_options="[0 => __('generic.none')]" />
                                </td>
                                <td>
                                    <x-larastrap::select-model name="target_id" npostfix="[]" squeeze :options="$suppliers" :extra_options="[0 => __('generic.none')]" />
                                </td>
								<td>
									<x-larastrap::text name="identifier" npostfix="[]" squeeze />
                                </td>
                                <td>
                                    <x-larastrap::text name="notes" npostfix="[]" squeeze />
                                </td>
                                <td>
                                    <x-larastrap::select name="type" nprefix="m" npostfix="[]" squeeze :options="$types" classes="csv_movement_type_select" />
                                </td>
                                <td>
                                    <x-larastrap::select name="method" npostfix="[]" squeeze :options="$payments" classes="csv_movement_method_select" />
                                </td>
                                <td>
                                    {{ printablePriceCurrency($mov->amount) }}
                                    <x-larastrap::hidden name="amount" npostfix="[]" />
                                </td>
                                <td>
                                    <x-larastrap::select-model name="currency_id" npostfix="[]" squeeze :options="$currencies" classes="csv_movement_currency_select" />
                                </td>
                            </tr>
                        </x-larastrap::enclose>
                    @endforeach
                </tbody>
            </table>
        </x-larastrap::wizardform>
    </div>
</x-larastrap::modal>
