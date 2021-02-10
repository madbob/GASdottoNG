<?php

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

@include('commons.textfield', [
    'obj' => $movementtype,
    'name' => 'name',
    'label' => _i('Nome'),
    'mandatory' => true
])

@include('commons.boolfield', [
    'obj' => $movementtype,
    'name' => 'allow_negative',
    'label' => _i('Accetta Valori Negativi'),
    'help_popover' => _i("Se disabilitato, impedisce di immettere un ammontare negativo per il movimento contabile"),
])

@include('commons.decimalfield', [
    'obj' => $movementtype,
    'name' => 'fixed_value',
    'label' => _i('Valore Fisso'),
    'is_price' => true,
    'help_popover' => _i("Se diverso da 0, non sarà possibile modificare il valore dei nuovi movimenti di questo tipo"),
])

@include('commons.selectenumfield', [
    'obj' => $movementtype,
    'name' => 'sender_type',
    'label' => _i('Pagante'),
    'values' => $target_classes,
    'help_popover' => _i("Il tipo di entità che effettua il pagamento. Se selezionato, sarà possibile selezionare l'entità all'interno del pannello di creazione di un nuovo movimento"),
])

@include('commons.selectenumfield', [
    'obj' => $movementtype,
    'name' => 'target_type',
    'label' => _i('Pagato'),
    'values' => $target_classes,
    'help_popover' => _i("Il tipo di entità che riceve il pagamento. Se selezionato, sarà possibile selezionare l'entità all'interno del pannello di creazione di un nuovo movimento"),
])
