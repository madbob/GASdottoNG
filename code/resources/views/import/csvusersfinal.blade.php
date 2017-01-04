<div class="wizard_page">
    <div class="modal-body">
        <p>
            Utenti importati:
        </p>

        <ul class="list-group">
            @if(empty($users))
                <li>Nessuno!</li>
            @else
                @foreach($users as $u)
                    <li class="list-group-item">{{ $u->printableName() }}</li>
                @endforeach
            @endif
        </ul>

        @if(!empty($errors))
            <hr/>

            <p>
                Errori:
            </p>

            <ul class="list-group">
                @foreach($errors as $e)
                    <li class="list-group-item">{!! $e !!}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default reloader" data-dismiss="modal" data-reload-target="#user-list">Chiudi</button>
    </div>
</div>
