<?php

$target_classes = [
    '' => __('generic.none'),
];

$classes = modelsUsingTrait(\App\Models\Concerns\CreditableTrait::class);
foreach($classes as $class => $name) {
    $target_classes[$class] = $name;
}

?>

<x-larastrap::text name="name" tlabel="generic.name" required />
<x-larastrap::check name="allow_negative" tlabel="movements.accepts_negative_value" tpophelp="movements.help.accepts_negative_value" />
<x-larastrap::price name="fixed_value" tlabel="movements.fixed_value" tpophelp="movements.help.fixed_value" />
<x-larastrap::select name="sender_type" tlabel="movements.paying" :options="$target_classes" tpophelp="movements.help.paying" />
<x-larastrap::select name="target_type" tlabel="movements.payed" :options="$target_classes" tpophelp="movements.help.payed" />
