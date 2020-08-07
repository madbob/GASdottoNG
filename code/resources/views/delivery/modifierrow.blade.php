<?php

if (!isset($skip_cells)) {
    $skip_cells = 2;
}

if (!isset($final_value)) {
    $final_value = false;
}

?>

<tr class="modifier-row do-not-sort {{ $mod_value ? '' : 'hidden' }}">
    <td>
        <label class="static-label">
            <span class="name">{{ $mod_value ? $mod_value->modifier->modifierType->name . ' ' . $mod_value->modifier->target->printableName() : '' }}</span>
            <span class="mutable {{ (is_null($mod_value) || $mod_value->is_variable == false || $final_value == true) ? 'hidden' : '' }}">
                <br><small>{{ _i("Il valore qui indicato è una stima, sarà finalizzato alla chiusura dell'ordine") }}</small>
            </span>
        </label>
    </td>

    @for($i = 0; $i < $skip_cells; $i++)
        <td>&nbsp;</td>
    @endfor

    <td>
        <input type="hidden" name="modifier-{{ $mod_value ? $mod_value->modifier->id : '0' }}" class="skip-on-submit">
        <label class="pull-right">
            <span class="booking-modifier">{{ $mod_value ? printablePrice($mod_value->effective_amount) : '' }}</span> {{ $currentgas->currency }}
        </label>
    </td>
</tr>
