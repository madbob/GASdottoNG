<p>
    @if(empty($txt_message))
        {{ _i("In allegato l'ultima fattura da %s.", [$currentgas->name]) }}
    @else
        {{ nl2br($txt_message) }}
    @endif
</p>
