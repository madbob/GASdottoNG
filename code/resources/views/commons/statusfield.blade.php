<?php

$hide_delete = true;
$hide_suspend = true;

$status = $target->plainStatus() ?? 'active';

switch ($status) {
    case 'removed':
        $hide_delete = false;
        break;
    case 'suspended':
        $hide_suspend = false;
    break;
    case 'deleted':
        $hide_delete = false;
        break;    
    default:
        $status = 'active';
}
if (is_a($target, 'App\User')) {
    $help_popover = _i('Gli utenti Sospesi e Cessati non possono accedere alla piattaforma, pur restando registrati. È necessario specificare una data di cessazione/sospensione.');
}

$postfix = $postfix ?? false;

?>

<x-larastrap::field :pophelp="$help_popover" :label="_i('Stato')" :squeeze="$squeeze" classes="status-selector">
    <x-larastrap::radios name="status" :npostfix="$postfix" :options="['active' => _i('Attivo'), 'suspended' => _i('Sospeso'), 'deleted' => _i('Cessato'), 'removed' => _i('Rimosso')]" :value="$status" squeeze />
    <x-larastrap::datepicker name="deleted_at" :hidden="$hide_delete" squeeze />
    <x-larastrap::datepicker name="suspended_at" :hidden="$hide_suspend" squeeze />
</x-larastrap::field>
