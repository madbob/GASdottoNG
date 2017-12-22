<?php
    $types = [];
    foreach (App\MovementType::types() as $info) {
        if ($info->visibility) {
            $types[] = [
                'label' => $info->name,
                'value' => $info->id,
            ];
        }
    }

    $payments = [];
    foreach (App\MovementType::payments() as $method_id => $info) {
        $payments[] = [
            'label' => $info->name,
            'value' => $method_id,
        ];
    }

    $users = App\User::sorted()->get();
?>

<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=movements&step=run') }}" data-toggle="validator">
        <div class="modal-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ _i('Importa') }}</th>
                        <th>{{ _i('Data') }}</th>
                        <th>{{ _i('Utente') }}</th>
                        <th>
                            @include('commons.selectenumfield', [
                                'obj' => null,
                                'squeeze' => true,
                                'prefix' => 'skip',
                                'name' => 'type',
                                'label' => _i('Tipo'),
                                'values' => $types,
                                'extra_class' => 'triggers-all-selects',
                                'extra_attrs' => [
                                    'data-target-class' => 'csv_movement_type_select',
                                ]
                            ])
                        </th>
                        <th>
                            @include('commons.selectenumfield', [
                                'obj' => null,
                                'squeeze' => true,
                                'prefix' => 'skip',
                                'name' => 'method',
                                'label' => _i('Metodo'),
                                'values' => $payments,
                                'enforced_default' => 'bank',
                                'extra_class' => 'triggers-all-selects',
                                'extra_attrs' => [
                                    'data-target-class' => 'csv_movement_method_select',
                                ]
                            ])
                        </th>
                        <th>{{ _i('Valore') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $index => $mov)
                        <tr>
                            <td>
                                <input type="checkbox" name="import[]" value="{{ $index }}" checked>
                            </td>
                            <td>
                                {{ $mov->printableDate('date') }}
                                <input type="hidden" name="date[]" value="{{ $mov->date }}">
                            </td>
                            <td>
                                @include('commons.selectobjfield', [
                                    'obj' => $mov,
                                    'squeeze' => true,
                                    'name' => 'sender_id',
                                    'postfix' => '[]',
                                    'objects' => $users,
                                    'extra_selection' => [
                                        '0' => _i('Nessuno')
                                    ]
                                ])
                            </td>
                            <td>
                                @include('commons.selectenumfield', [
                                    'obj' => $mov,
                                    'squeeze' => true,
                                    'prefix' => 'm',
                                    'name' => 'type',
                                    'postfix' => '[]',
                                    'label' => _i('Tipo'),
                                    'values' => $types,
                                    'extra_class' => 'csv_movement_type_select',
                                ])
                            </td>
                            <td>
                                @include('commons.selectenumfield', [
                                    'obj' => $mov,
                                    'squeeze' => true,
                                    'name' => 'method',
                                    'postfix' => '[]',
                                    'label' => _i('Metodo'),
                                    'values' => $payments,
                                    'extra_class' => 'csv_movement_method_select',
                                ])
                            </td>
                            <td>
                                {{ printablePrice($mov->amount) }} €
                                <input type="hidden" name="amount[]" value="{{ $mov->amount }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
            <button type="submit" class="btn btn-success">{{ _i('Avanti') }}</button>
        </div>
    </form>
</div>
