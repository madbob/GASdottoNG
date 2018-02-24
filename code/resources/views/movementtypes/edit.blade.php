<?php

if($type->system)
    $classes = modelsUsingTrait('App\PayableTrait');
else
    $classes = modelsUsingTrait('App\CreditableTrait');

$target_classes = [];

$target_classes[] = [
    'value' => null,
    'label' => _i('Nessuno'),
];

foreach($classes as $class => $name) {
    $target_classes[] = [
        'value' => $class,
        'label' => $name,
    ];
}

?>

<form class="form-horizontal main-form movement-type-editor" method="PUT" action="{{ route('movtypes.update', $type->id) }}">
    <div class="row">
        <div class="col-md-6">
            @if($type->system)
                @include('commons.textfield', ['obj' => $type, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])
                @include('commons.boolfield', ['obj' => $type, 'name' => 'allow_negative', 'label' => _i('Accetta Valori Negativi')])

                @include('commons.staticpricefield', ['obj' => $type, 'name' => 'fixed_value', 'label' => _i('Valore Fisso')])

                @include('commons.staticenumfield', [
                    'obj' => $type,
                    'name' => 'sender_type',
                    'label' => _i('Pagante'),
                    'values' => $target_classes
                ])

                @include('commons.staticenumfield', [
                    'obj' => $type,
                    'name' => 'target_type',
                    'label' => _i('Pagato'),
                    'values' => $target_classes
                ])
            @else
                @include('movementtypes.base-edit', ['movementtype' => $type])
            @endif
        </div>
        <div class="col-md-6">
            @include('commons.textarea', ['obj' => $type, 'name' => 'default_notes', 'label' => _i('Note di Default')])
        </div>

        <?php

        $ops = json_decode($type->function);
        $methods = [];
        $defaults = [];

        $payments = [];
        foreach(App\MovementType::payments() as $id => $pay)
            $payments[$id] = false;

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

        $width = floor(100 / (count(App\MovementType::payments()) + 1));

        ?>

        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th width="{{ $width }}%">Saldo</th>

                        @foreach(App\MovementType::payments() as $pay_id => $pay)
                            <th width="{{ $width }}%">
                                {{ $pay->name }}
                                <input type="checkbox" data-toggle="toggle" data-size="mini" name="{{ $pay_id }}" {{ $payments[$pay_id] ? 'checked' : '' }} data-active-for="{{ $pay->active_for }}" {{ $pay->active_for != null && $pay->active_for != $type->sender_type && $pay->active_for != $type->target_type ? 'disabled' : '' }}>
                                <span class="decorated_radio">
                                    <input type="radio" name="payment_default" value="{{ $pay_id }}" {{ isset($defaults[$pay_id]) && $defaults[$pay_id] ? 'checked' : '' }}>
                                    <label>{{ ('default') }}</label>
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($target_classes as $target_class)
                        <?php $classname = $target_class['value'] ?>
                        @if($classname == null)
                            @continue
                        @endif

                        @foreach($classname::balanceFields() as $field => $fieldname)
                            <tr data-target-class="{{ $classname }}" class="{{ $classname != 'App\Gas' && $classname != $type->sender_type && $classname != $type->target_type ? 'hidden' : '' }}">
                                <td>{{ $classname::commonClassName() }}: {{ $fieldname }}</td>

                                @foreach(App\MovementType::payments() as $pay_id => $pay)
                                    <?php

                                    $selection = 'ignore';
                                    if (isset($methods[$pay_id]) && isset($methods[$pay_id][$classname]) && isset($methods[$pay_id][$classname][$field]))
                                        $selection = $methods[$pay_id][$classname][$field];

                                    ?>

                                    <td>
                                        <div class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-default {{ $selection == 'increment' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $classname }}-{{ $field }}-{{ $pay_id }}" value="increment" autocomplete="off" {{ $selection == 'increment' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> +
                                            </label>
                                            <label class="btn btn-default {{ $selection == 'decrement' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $classname }}-{{ $field }}-{{ $pay_id }}" value="decrement" autocomplete="off" {{ $selection == 'decrement' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> -
                                            </label>
                                            <label class="btn btn-default {{ $selection == 'ignore' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled' }}>
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

    @include('commons.formbuttons', ['no_delete' => $type->system])
</form>

@stack('postponed')
