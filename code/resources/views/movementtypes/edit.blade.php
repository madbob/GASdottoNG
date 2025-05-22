<?php

$target_classes = [
    '' => __('generic.none'),
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
    <input type="hidden" name="pre-saved-function" value="filterOutUnusedRules" class="skip-on-submit">
	<input type="hidden" name="post-saved-function" value="afterMovementTypeChange" class="skip-on-submit">

	@if($type->system)
        <div class="row mb-4">
            <div class="col">
                <div class="alert alert-danger">
                    {{ _i('Questo è un tipo di movimento contabile indispensabile per il funzionamento del sistema: non può essere eliminato e può essere modificato solo parzialmente.') }}
                </div>
            </div>
        </div>
    @endif

	@if($type->hasBrokenModifier())
		<div class="row mb-4">
			<div class="col">
				@include('movementtypes.broken_modifier', ['id' => $type->id])
			</div>
		</div>
	@endif

    <div class="row">
        <div class="col-12 col-md-6">
            @if($type->system)
                <x-larastrap::text name="name" tlabel="generic.notes" required />
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

            $methods[$o->method]['target'] = [];
            foreach($o->target->operations as $op) {
                $methods[$o->method]['target'][$op->field] = $op->operation;
            }

            $methods[$o->method]['sender'] = [];
            foreach($o->sender->operations as $op) {
                $methods[$o->method]['sender'][$op->field] = $op->operation;
            }

            if($type->target_type != 'App\Gas' && $type->sender_type != 'App\Gas') {
                $methods[$o->method]['master'] = [];
                foreach($o->master->operations as $op) {
                    $methods[$o->method]['master'][$op->field] = $op->operation;
                }
            }
        }

        $width = floor(100 / (count(paymentTypes()) + 1));

        ?>

        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" width="{{ $width }}%">Saldo</th>

                        @foreach(paymentTypes() as $pay_id => $pay)
                            <th scope="col" width="{{ $width }}%">
                                {{ $pay->name }}
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="{{ $pay_id }}" class="form-check-input" {{ $payments[$pay_id] ? 'checked' : '' }} data-active-for="{{ $pay->active_for }}" {{ $pay->active_for != null && $pay->active_for != $type->sender_type && $pay->active_for != $type->target_type ? 'disabled' : '' }}>
                                </div>
                                <div class="form-check form-switch p-0">
                                    <input type="radio" name="payment_default" value="{{ $pay_id }}" {{ isset($defaults[$pay_id]) && $defaults[$pay_id] ? 'checked' : '' }}> {{ _i('Default') }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php

                    /*
                        Per ciascuna classe coinvolgibile in un movimento
                        contabile prevedo una riga per ciascuna voce di
                        bilancio, ciascuna duplicata per i casi in cui tale
                        classe sia il pagato o il pagante. In questo modo ho
                        tutte le possibili combinazioni, incluse quelle in cui
                        la classe pagante e la classe pagata siano la stessa
                        (e.g. movimento contabile di trasferimento di credito da
                        un utente all'altro)
                    */

                    $actual_classes = [];

                    $classname = 'App\Gas';
                    $fields = (new $classname())->balanceFields();
                    $label = $classname::commonClassName();

                    $actual_classes[] = (object) [
                        'peer' => 'master',
                        'classname' => $classname,
                        'fields' => $fields,
                        'label' => $label,
                        'visible' => $classname != $type->sender_type && $classname != $type->target_type,
                    ];

                    foreach($target_classes as $classname => $target_class) {
                        if (empty($classname)) {
                            continue;
                        }

                        $fields = (new $classname())->balanceFields();
                        $label = $classname::commonClassName();

                        $actual_classes[] = (object) [
                            'peer' => 'sender',
                            'classname' => $classname,
                            'fields' => $fields,
                            'label' => sprintf('%s - %s', _i('Pagante'), $label),
                            'visible' => $classname == $type->sender_type,
                        ];

                        $actual_classes[] = (object) [
                            'peer' => 'target',
                            'classname' => $classname,
                            'fields' => $fields,
                            'label' => sprintf('%s - %s', _i('Pagato'), $label),
                            'visible' => $classname == $type->target_type,
                        ];
                    }

                    usort($actual_classes, fn($a, $b) => $a->label <=> $b->label);

                    @endphp

                    @foreach($actual_classes as $meta)
                        @foreach($meta->fields as $field => $fieldname)
                            <tr data-target-class="{{ $meta->peer }}-{{ $meta->classname }}" {{ $meta->visible ? '' : 'hidden' }}>
                                <td>{{ $meta->label }} - {{ $fieldname }}</td>

                                @foreach(paymentTypes() as $pay_id => $pay)
                                    <?php

                                    $selection = 'ignore';
                                    if (isset($methods[$pay_id]) && isset($methods[$pay_id][$meta->peer]) && isset($methods[$pay_id][$meta->peer][$field])) {
                                        $selection = $methods[$pay_id][$meta->peer][$field];
                                    }

                                    $row_name = sprintf('%s-%s-%s-%s', $meta->peer, $meta->classname, $field, $pay_id);

                                    ?>

                                    <td>
                                        <div class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-light" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $row_name }}" value="increment" autocomplete="off" {{ $selection == 'increment' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> +
                                            </label>
                                            <label class="btn btn-light" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $row_name }}" value="decrement" autocomplete="off" {{ $selection == 'decrement' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> -
                                            </label>
                                            <label class="btn btn-light" {{ $payments[$pay_id] ? '' : 'disabled' }}>
                                                <input type="radio" name="{{ $row_name }}" value="ignore" autocomplete="off" {{ $selection == 'ignore' ? 'checked' : '' }} {{ $payments[$pay_id] ? '' : 'disabled="disabled"' }}> =
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
