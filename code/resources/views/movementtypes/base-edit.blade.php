<?php

$target_classes = [
    '' => _i('Nessuno'),
];

$classes = modelsUsingTrait(\App\Models\Concerns\CreditableTrait::class);
foreach($classes as $class => $name) {
    $target_classes[$class] = $name;
}

?>

<x-larastrap::text name="name" tlabel="generic.name" required />
<x-larastrap::check name="allow_negative" :label="_i('Accetta Valori Negativi')" :pophelp="_i('Se disabilitato, impedisce di immettere un ammontare negativo per il movimento contabile')" />
<x-larastrap::price name="fixed_value" :label="_i('Valore Fisso')" :pophelp="_i('Se diverso da 0, non sarà possibile modificare il valore dei nuovi movimenti di questo tipo')" />
<x-larastrap::select name="sender_type" :label="_i('Pagante')" :options="$target_classes" :pophelp="_i('Il tipo di entità che effettua il pagamento. Se selezionato, sarà possibile selezionare l\'entità all\'interno del pannello di creazione di un nuovo movimento')" />
<x-larastrap::select name="target_type" :label="_i('Pagato')" :options="$target_classes" :pophelp="_i('Il tipo di entità che riceve il pagamento. Se selezionato, sarà possibile selezionare l\'entità all\'interno del pannello di creazione di un nuovo movimento')" />
