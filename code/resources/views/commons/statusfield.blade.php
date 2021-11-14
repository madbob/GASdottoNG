<?php

$hide_delete = true;
$hide_suspend = true;

if ($target) {
    if (!is_null($target->deleted_at)) {
        $status = 'deleted';
        $hide_delete = false;
    }
    else if (!is_null($target->suspended_at)) {
        $status = 'suspended';
        $hide_suspend = false;
    }
    else {
        $status = 'active';
    }

    if (is_a($target, 'App\User')) {
        $help_popover = _i('Gli utenti Sospesi e Cessati non possono accedere alla piattaforma, pur restando registrati. È necessario specificare una data di cessazione/sospensione.');
    }
}
else {
    $status = 'active';
}

$postfix = $postfix ?? false;

?>

<x-larastrap::field :pophelp="$help_popover" :label="_i('Stato')" :squeeze="$squeeze" classes="status-selector">
    <x-larastrap::radios name="status" :npostfix="$postfix" :options="['active' => _i('Attivo'), 'suspended' => _i('Sospeso'), 'deleted' => _i('Cessato')]" :value="$status" squeeze />
    <x-larastrap::datepicker name="deleted_at" :hidden="$hide_delete" squeeze />
    <x-larastrap::datepicker name="suspended_at" :hidden="$hide_suspend" squeeze />
</x-larastrap::field>
