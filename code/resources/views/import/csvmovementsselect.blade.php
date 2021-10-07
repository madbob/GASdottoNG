<?php

$methods = [];

$types = [];
foreach (App\MovementType::types() as $info) {
    if ($info->visibility) {
        $types[$info->id] = $info->name;

        $methods[] = (object) [
            'method' => $info->id,
            'payments' => array_keys(App\MovementType::paymentsByType($info->id)),
            'default_payment' => App\MovementType::defaultPaymentByType($info->id),
        ];
    }
}

$payments = App\MovementType::paymentsSimple();
$currencies = App\Currency::enabled();

$users = App\User::sorted()->get();
$suppliers = App\Supplier::orderBy('name', 'asc')->get();

?>

<x-larastrap::modal :title="_i('Importa CSV')" size="fullscreen">
    <input type="hidden" name="matching_methods_for_movement_types" value='{!! json_encode($methods) !!}'>

    <div class="wizard_page">
        <x-larastrap::form method="POST" :action="url('import/csv?type=movements&step=run')" :buttons="[['color' => 'success', 'type' => 'submit', 'label' => _i('Avanti')]]">
            @if(!empty($errors))
                <p>
                    {{ _i('Errori') }}:
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
                        <th>{{ _i('Importa') }}</th>
                        <th>{{ _i('Data') }}</th>
                        <th>{{ _i('Utente') }}</th>
                        <th>{{ _i('Fornitore') }}</th>
                        <th>
                            <x-larastrap::select name="type" nprefix="skip" squeeze :options="$types" classes="triggers-all-selects csv_movement_type_select" data-target-class="csv_movement_type_select" />
                        </th>
                        <th>
                            <x-larastrap::select name="method" nprefix="skip" squeeze :options="$payments" classes="triggers-all-selects csv_movement_method_select" data-target-class="csv_movement_method_select" value="bank" />
                        </th>
                        <th>{{ _i('Valore') }}</th>
                        <th>
                            <x-larastrap::selectobj name="currency_id" nprefix="skip" squeeze :options="$currencies" classes="triggers-all-selects csv_movement_currency_select" data-target-class="csv_movement_currency_select" :value="defaultCurrency()->id" />
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
                                    <x-larastrap::selectobj name="sender_id" npostfix="[]" squeeze :options="$users" :extraitem="_i('Nessuno')" />
                                </td>
                                <td>
                                    <x-larastrap::selectobj name="target_id" npostfix="[]" squeeze :options="$suppliers" :extraitem="_i('Nessuno')" />
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
                                    <x-larastrap::selectobj name="currency_id" npostfix="[]" squeeze :options="$currencies" classes="csv_movement_currency_select" />
                                </td>
                            </tr>
                        </x-larastrap::enclose>
                    @endforeach
                </tbody>
            </table>
        </x-larastrap::form>
    </div>
</x-larastrap::modal>
