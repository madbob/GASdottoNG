<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('import/gdxp?step=run') }}" data-toggle="validator">
        <input type="hidden" name="path" value="{{ $path }}">

        <div class="modal-body">
            @foreach($data as $supplier)
                @if($supplier->orders->isEmpty() == false)
                    <h3>Importa Ordine</h3>

                    @include('commons.staticdatefield', ['obj' => $supplier->orders->first(), 'name' => 'start', 'label' => 'Data Apertura'])
                    @include('commons.staticdatefield', ['obj' => $supplier->orders->first(), 'name' => 'end', 'label' => 'Data Chiusura'])
                @else
                    <h3>Importa Fornitore</h3>
                @endif

                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">Fornitore</label>
                    <div class="col-sm-{{ $fieldsize }}">
                        <div class="radio">
                            <label>
                                <input type="radio" name="supplier_source" value="new" checked>
                                Crea nuovo: {{ $supplier->name }}<br/>
                                <small>Verrà generato un nuovo fornitore con il listino allegato.</small>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="supplier_source" value="update">
                                Aggiorna fornitore esistente: <select name="supplier_update" class="form-control">
                                    <option value="none" selected>Selezione un fornitore</option>
                                    @foreach($currentgas->suppliers as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select><br/>
                                <small>Tutti i prodotti del fornitore saranno archiviati e sostituiti con quelli presenti nel file. Eventuali ordini aperti continueranno a fare riferimento ai prodotti archiviati.</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">Prodotti</label>
                    <div class="col-sm-{{ $fieldsize }}">
                        <label class="static-label text-muted">
                            Nel file ci sono {{ $supplier->products->count() }} prodotti.
                        </label>
                    </div>
                </div>
            @endforeach

            <div class="clearfix"></div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
            <button type="submit" class="btn btn-success">Avanti</button>
        </div>
    </form>
</div>
