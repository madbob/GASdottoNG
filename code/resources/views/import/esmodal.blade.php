<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">
        {{ _i('Cerca in Repository') }}
    </h4>
</div>

<div class="wizard_page">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ _i('Nome') }}</th>
                            <th>{{ _i('Partita IVA') }}</th>
                            <th>{{ _i('Ultimo Aggiornamento') }}</th>
                            <th>{{ _i('Importa') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td>{{ $entry->name }}</td>
                                <td>{{ $entry->vat }}</td>
                                <td>{{ printableDate($entry->lastchange) }}</td>
                                <td>
                                    <form action="{{ url('import/gdxp') }}" method="POST">
                                        <input type="hidden" name="step" value="read">
                                        <input type="hidden" name="url" value="http://hub.gasdotto.net/api/get/{{ $entry->vat }}">
                                        <button type="submit" class="btn btn-sm btn-success">Importa</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
    </div>
</div>
