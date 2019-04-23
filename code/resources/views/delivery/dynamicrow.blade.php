{{--
    Ratio: se il valore di riferimento è percentuale, esso verrà applicato
    dinamicamente al valore totale di quanto consegnato. Altrimenti tengo per
    buoni i valori assoluti calcolati sul prenotato.
--}}

@if(!empty($order->$identifier) && $order->$identifier != 0)
    <tr class="booking-{{ $identifier }}">
        <td>
            <label class="static-label">{{ $label }} {{ isPercentage($order->$identifier) ? printablePercentage($order->$identifier) : '' }}</label>
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>
            @if(isPercentage($order->$identifier))
                <input type="hidden" name="global-{{ $identifier }}-value" value="{{ $order->$identifier }}">
                <label class="static-label booking-{{ $identifier }}-value pull-right">
                    <span>{{ printablePrice($o->status == 'pending' ? 0 : $o->getValue($identifier, true)) }}</span> {{ $currentgas->currency }}
                </label>
            @else
                <input type="hidden" name="global-{{ $identifier }}-value" value="{{ $o->getValue($identifier, true) }}">
                <label class="static-label booking-{{ $identifier }}-value pull-right">
                    <span>{{ printablePrice($o->getValue($identifier, true)) }}</span> {{ $currentgas->currency }}
                </label>
            @endif
        </td>
    </tr>
@endif
