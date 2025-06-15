@php

$hide_delete = true;
$hide_suspend = true;

$status = $target->plainStatus() ?? 'active';

switch ($status) {
    case 'suspended':
        $hide_suspend = false;
        break;
    case 'deleted':
        $hide_delete = false;
        break;
}

if (is_a($target, 'App\User')) {
    $help_popover = __('texts.user.help.suspended');
}

$postfix = $postfix ?? false;

@endphp

<x-larastrap::field :pophelp="$help_popover" tlabel="generic.status" :squeeze="$squeeze" classes="status-selector">
    <x-larastrap::radios name="status" :npostfix="$postfix" :options="[
        'active' => __('texts.user.statuses.active'),
        'suspended' => __('texts.user.statuses.suspended'),
        'deleted' => __('texts.user.statuses.deleted')
    ]" :value="$status" squeeze />

    <x-larastrap::datepicker name="deleted_at" :hidden="$hide_delete" squeeze />
    <x-larastrap::datepicker name="suspended_at" :hidden="$hide_suspend" squeeze />
</x-larastrap::field>
