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
	<x-larastrap::radios name="extra_modifiers" :label="_i('Includi tutti i modificatori')" :options="['0' => _i('No'), '1' => _i('Sì')]" value="0" :pophelp="_i('Usa questa funzione per includere o meno i modificatori che non sono destinati al fornitore. È consigliato selezionare \'No\' se il documento sarà inoltrato al fornitore, e \'Si\' se il documento viene usato per le consegne da parte degli addetti.')" />
@else
	<x-larastrap::hidden name="extra_modifiers" value="1" />
@endif
