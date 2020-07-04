<?php

if (!isset($skip_cells)) {
    $skip_cells = 2;
}

if (!isset($final_value)) {
    $final_value = false;
}

?>

<tr class="do-not-sort">
    <td>
        <label class="static-label">
            {{ $mod_value->modifier->modifierType->name }} - {{ $mod_value->modifier->target->printableName() }}
            @if($final_value == false && $mod_value->is_variable)
                <br><small>{{ _i("Il valore qui indicato è una stima, sarà finalizzato alla chiusura dell'ordine") }}</small>
            @endif
        </label>
    </td>

    @for($i = 0; $i < $skip_cells; $i++)
        <td>&nbsp;</td>
    @endfor

    <td>
        <input type="hidden" name="modifier-{{ $mod_value->modifier->id }}" class="skip-on-submit">
        <label class="pull-right">
            <span class="booking-modifier">{{ printablePrice($mod_value->effective_amount) }}</span> {{ $currentgas->currency }}
        </label>
    </td>
</tr>
