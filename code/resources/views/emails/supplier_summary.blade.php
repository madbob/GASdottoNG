@if(!empty($txt_message))
    <p>
        {!! nl2br($txt_message) !!}
    </p>
@else
    <p>
        {{ _i("In allegato il file per l'ordine di %s.", $currentuser->gas->printableName) }}
    </p>
    <p>
        {{ _i('Cordiali saluti') }},<br>
        {{ $currentuser->printableName() }}
    </p>
@endif
