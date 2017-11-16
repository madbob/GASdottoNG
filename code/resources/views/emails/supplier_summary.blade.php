@if(!empty($txt_message))
    <p>
        {!! nl2br($txt_message) !!}
    </p>
@else
    <p>
        In allegato il file per l'ordine di {{ $currentuser->gas->printableName }}.
    </p>
    <p>
        Cordiali saluti,<br>
        {{ $currentuser->printableName() }}
    </p>
@endif
