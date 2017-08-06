<?php

$classes = App\CreditableTrait::acceptedClasses();
$target_classes = [];

$target_classes[] = [
    'value' => null,
    'label' => 'Nessuno',
];

foreach($classes as $class => $name) {
    $target_classes[] = [
        'value' => $class,
        'label' => $name,
    ];
}

?>

<form class="form-horizontal main-form movement-type-editor" method="PUT" action="{{ url('movtypes/' . $type->id) }}">
    <div class="row">
        <div class="col-md-12">
            @if($type->system)
                @include('commons.staticstringfield', ['obj' => $type, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
                @include('commons.staticboolfield', ['obj' => $type, 'name' => 'allow_negative', 'label' => 'Accetta Valori Negativi'])
                @include('commons.staticpricefield', ['obj' => $type, 'name' => 'fixed_value', 'label' => 'Valore Fisso'])

                @include('commons.staticenumfield', [
                    'obj' => $type,
                    'name' => 'sender_type',
                    'label' => 'Pagante',
                    'values' => $target_classes
                ])

                @include('commons.staticenumfield', [
                    'obj' => $type,
                    'name' => 'target_type',
                    'label' => 'Pagato',
                    'values' => $target_classes
                ])
            @else
                @include('movementtypes.base-edit', ['movementtype' => $type])
            @endif
        </div>

        <?php

        $ops = json_decode($type->function);
        $methods = [];

        $payments = [];
        foreach(App\MovementType::payments() as $id => $pay)
            $payments[$id] = false;

        foreach($ops as $o) {
            $methods[$o->method] = [];
            $payments[$o->method] = true;

            $methods[$o->method][$type->sender_type] = [];
            foreach($o->sender->operations as $op)
                $methods[$o->method][$type->sender_type][$op->field] = $op->operation;

            $methods[$o->method][$type->target_type] = [];
            foreach($o->target->operations as $op)
                $methods[$o->method][$type->target_type][$op->field] = $op->operation;

            if($type->target_type != 'App\Gas' && $type->sender_type != 'App\Gas') {
                $methods[$o->method]['App\Gas'] = [];
                foreach($o->master->operations as $op)
                    $methods[$o->method]['App\Gas'][$op->field] = $op->operation;
            }
        }

        ?>

        <div class="col-md-12">
            <table class="table {{ $type->system ? 'system-type' : '' }}">
                <thead>
                    <tr>
                        <th>Saldo</th>

                        @foreach(App\MovementType::payments() as $pay_id => $pay)
                            <th>{{ $pay->name }} <input type="checkbox" data-toggle="toggle" data-size="mini" name="{{ $pay_id }}" {{ $payments[$pay_id] ? 'checked' : '' }}></th>
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
                                            <label class="btn btn-default {{ $selection == 'increment' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}>
                                                <input type="radio" name="{{ $classname }}-{{ $field }}-{{ $pay_id }}" value="increment" autocomplete="off" {{ $selection == 'increment' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> +
                                            </label>
                                            <label class="btn btn-default {{ $selection == 'decrement' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}>
                                                <input type="radio" name="{{ $classname }}-{{ $field }}-{{ $pay_id }}" value="decrement" autocomplete="off" {{ $selection == 'decrement' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> -
                                            </label>
                                            <label class="btn btn-default {{ $selection == 'ignore' ? 'active' : '' }}" {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}>
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

    @include('commons.formbuttons', ['no_delete' => $type->system, 'no_save' => $type->system])
</form>

@stack('postponed')
