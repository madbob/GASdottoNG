<div class="wizard_page">
    <div class="modal-body">
        <p>
            Fornitori importati:
        </p>

        <ul class="list-group">
            @if(empty($data))
                <li>Nessuno!</li>
            @else
                @foreach($data as $supplier)
                    <li class="list-group-item">{{ $supplier->printableName() }}</li>
                @endforeach
            @endif
        </ul>

        <p class="clearfix"></p>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default reloader" data-dismiss="modal" data-reload-target="#user-list">Chiudi</button>
    </div>
</div>
