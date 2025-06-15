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
        @if($mod_value && $mod_value->modifier->modifierType->id == 'arrotondamento-consegna')
            <span class="name">{{ $mod_value->modifier->modifierType->name }}</span>
        @else
            <span class="name">{{ $mod_value ? $mod_value->descriptive_name : '' }}</span>
            <span class="mutable {{ (is_null($mod_value) || $mod_value->is_variable == false || $final_value == true) ? 'hidden' : '' }}">
                <br><small>{{ __('texts.orders.help.extimated_value') }}</small>
            </span>
            <div class="float-end details-button-wrapper">
                @include('commons.detailsbutton', ['obj' => $mod_value ? $mod_value->modifier : null])
            </div>
        @endif
    </td>

    @for($i = 0; $i < $skip_cells; $i++)
        <td>&nbsp;</td>
    @endfor

    <td class="text-end">
        <input type="hidden" name="modifier-{{ $mod_value ? $mod_value->modifier->id : '0' }}" class="skip-on-submit">
        <span>
            <span class="booking-modifier">{{ $mod_value ? printablePrice($mod_value->effective_amount) : '' }}</span> {{ defaultCurrency()->symbol }}
        </span>
    </td>
</tr>
