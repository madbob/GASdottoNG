<div class="wizard_page">
    <div class="modal-body">
        <p>
            {{ _i('Prodotti importati') }}:
        </p>

        <ul class="list-group">
            @if(empty($products))
                <li>{{ _i('Nessuno') }}</li>
            @else
                @foreach($products as $p)
                    <li class="list-group-item">{{ $p->name }}</li>
                @endforeach
            @endif
        </ul>

        @if(!empty($errors))
            <hr/>

            <p>
                {{ _i('Errori') }}:
            </p>

            <ul class="list-group">
                @foreach($errors as $e)
                    <li class="list-group-item">{!! $e !!}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default reloader" data-dismiss="modal" data-reload-target="#supplier-list">{{ _i('Chiudi') }}</button>
    </div>
</div>
