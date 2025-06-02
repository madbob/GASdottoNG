@php

$modifier_export_select = false;

$modifiers = $order->involvedModifiers(true);
foreach($modifiers as $modifier) {
	if ($modifier->movementType) {
		$modifier_export_select = true;
		break;
	}
}

@endphp

@if($modifier_export_select)
	<x-larastrap::radios name="extra_modifiers" tlabel="orders.include_all_modifiers" :options="['0' => __('generic.no'), '1' => __('generic.yes')]" value="0" tpophelp="orders.help.include_all_modifiers" />
@else
	<x-larastrap::hidden name="extra_modifiers" value="1" />
@endif
