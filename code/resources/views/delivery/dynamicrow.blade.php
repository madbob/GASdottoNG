<?php

if (!isset($skip_cells)) {
    $skip_cells = 2;
}

?>

<tr class="booking-{{ $identifier }} do-not-sort">
    <td>
        <label class="static-label">{{ $label }} {{ isPercentage($order->$identifier) ? printablePercentage($order->$identifier) : '' }}</label>
    </td>

    @for($i = 0; $i < $skip_cells; $i++)
        <td>&nbsp;</td>
    @endfor

    <td>
        @if(isPercentage($order->$identifier))
            <input type="hidden" name="global-{{ $identifier }}-value" value="{{ $order->$identifier }}">
            <label class="static-label booking-{{ $identifier }}-value pull-right">
                <span>{{ printablePrice($o->status == 'pending' ? 0 : $o->getValue($identifier, true)) }}</span> {{ $currentgas->currency }}
            </label>
        @else
            <input type="hidden" name="global-{{ $identifier }}-value" value="{{ $order->total_value . ':' . $order->$identifier }}">
            <label class="static-label booking-{{ $identifier }}-value pull-right">
                <span>{{ printablePrice($o->status == 'pending' ? 0 : $o->getValue($identifier, true)) }}</span> {{ $currentgas->currency }}
            </label>
        @endif
    </td>
</tr>
