<?php

$target_classes = [
    '' => _i('Nessuno'),
];

if ($type->system) {
    $classes = modelsUsingTrait(\App\Models\Concerns\PayableTrait::class);
}
else {
    $classes = modelsUsingTrait(\App\Models\Concerns\CreditableTrait::class);
}

foreach($classes as $class => $name) {
    $target_classes[$class] = $name;
}

?>

<x-larastrap::mform :obj="$type" classes="main-form movement-type-editor" method="PUT" :action="route('movtypes.update', $type->id)" :nodelete="$type->system">
    @if($type->system)
        <div class="row mb-4">
            <div class="col">
                <div class="alert alert-danger">
                    {{ _i('Questo è un tipo di movimento contabile indispensabile per il funzionamento del sistema: non può essere eliminato e può essere modificato solo parzialmente.') }}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-6">
            @if($type->system)
                <x-larastrap::text name="name" :label="_i('Nome')" required />
                <x-larastrap::check name="allow_negative" :label="_i('Accetta Valori Negativi')" :pophelp="_i('Se disabilitato, impedisce di immettere un ammontare negativo per il movimento contabile')" />
                <x-larastrap::price name="fixed_value" :label="_i('Valore Fisso')" disabled readonly />
                <x-larastrap::select name="sender_type" :label="_i('Pagante')" :options="$target_classes" disabled readonly />
                <x-larastrap::select name="target_type" :label="_i('Pagato')" :options="$target_classes" disabled readonly />
            @else
                @include('movementtypes.base-edit', ['movementtype' => $type])
            @endif
        </div>
        <div class="col-12 col-md-6">
            <x-larastrap::textarea name="default_notes" :label="_i('Note di Default')" />
        </div>

        <?php

        $ops = json_decode($type->function);
        $methods = [];
        $defaults = [];

        $payments = [];
        foreach(paymentTypes() as $id => $pay) {
            $payments[$id] = false;
        }

        foreach($ops as $o) {
            $methods[$o->method] = [];
            $defaults[$o->method] = isset($o->is_default) ? $o->is_default : false;
            $payments[$o->method] = true;

            $methods[$o->method][$type->target_type] = [];
            foreach($o->target->operations as $op)
                $methods[$o->method][$type->target_type][$op->field] = $op->operation;

            $methods[$o->method][$type->sender_type] = [];
            foreach($o->sender->operations as $op)
                $methods[$o->method][$type->sender_type][$op->field] = $op->operation;

            if($type->target_type != 'App\Gas' && $type->sender_type != 'App\Gas') {
                $methods[$o->method]['App\Gas'] = [];
                foreach($o->master->operations as $op)
                    $methods[$o->method]['App\Gas'][$op->field] = $op->operation;
            }
        }

        $width = floor(100 / (count(paymentTypes()) + 1));

        ?>

        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th width="{{ $width }}%">Saldo</th>

                        @foreach(paymentTypes() as $pay_id => $pay)
                            <th width="{{ $width }}%">
                                {{ $pay->name }}
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="{{ $pay_id }}" class="form-check-input" {{ $payments[$pay_id] ? 'checked' : '' }} data-active-for="{{ $pay->active_for }}" {{ $pay->active_for != null && $pay->active_for != $type->sender_type && $pay->active_for != $type->target_type ? 'disabled' : '' }}>
                                </div>
                                <div class="form-check form-switch p-0">
                                    <input type="radio" name="payment_default" value="{{ $pay_id }}" {{ isset($defaults[$pay_id]) && $defaults[$pay_id] ? 'checked' : '' }}> {{ _i('Default') }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($target_classes as $classname => $target_class)
                        @if(empty($classname))
                            @continue
                        @endif

                        @foreach((new $classname())->balanceFields() as $field => $fieldname)
                            <tr data-target-class="{{ $classname }}" class="{{ $classname != 'App\Gas' && $classname != $type->sender_type && $classname != $type->target_type ? 'hidden' : '' }}">
                                <td>{{ $classname::commonClassName() }}: {{ $fieldname }}</td>

                                @foreach(paymentTypes() as $pay_id => $pay)
                                    <?php

                                    $selection = 'ignore';
                                    if (isset($methods[$pay_id]) && isset($methods[$pay_id][$classname]) && isset($methods[$pay_id][$classname][$field]))
                                        $selection = $methods[$pay_id][$classname][$field];

                                    ?>

                                    <td>
                                        <div class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-light {{ $selection == 'increment' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $classname }}-{{ $field }}-{{ $pay_id }}" value="increment" autocomplete="off" {{ $selection == 'increment' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> +
                                            </label>
                                            <label class="btn btn-light {{ $selection == 'decrement' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $classname }}-{{ $field }}-{{ $pay_id }}" value="decrement" autocomplete="off" {{ $selection == 'decrement' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> -
                                            </label>
                                            <label class="btn btn-light {{ $selection == 'ignore' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $classname }}-{{ $field }}-{{ $pay_id }}" value="ignore" autocomplete="off" {{ $selection == 'ignore' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> =
                                            </label>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-larastrap::mform>
