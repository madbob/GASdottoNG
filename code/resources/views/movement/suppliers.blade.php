<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">{{ _i('Stato Fornitori') }}</h4>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group hidden-md">
                <div class="input-group table-number-filters">
                    <div class="input-group-addon">
                        <label class="radio-inline">
                            <input type="radio" name="filter_mode" value="min" checked> {{ _i('Minore di') }}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="filter_mode" value="max"> {{ _i('Maggiore di') }}
                        </label>
                    </div>
                    <input type="number" class="form-control table-number-filter" placeholder="{{ _i('Filtra Credito') }}" data-list-target="#suppliersTable">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" id="credits_status_table">
            <table class="table" id="suppliersTable">
                <thead>
                    <tr>
                        <th width="75%">{{ _i('Nome') }}</th>
                        <th width="25%">{{ _i('Saldo Attuale') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currentgas->suppliers as $supplier)
                        <tr>
                            <td>
                                {{ $supplier->printableName() }}
                            </td>

                            <td class="text-filterable-cell">
                                {{ printablePriceCurrency($supplier->current_balance_amount) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <form class="form-inline iblock inner-form" action="{{ url('movements/document/suppliers/csv?dummy=1') }}" method="GET">
        <input type="hidden" name="pre-saved-function" value="formToDownload">
        <button type="submit" class="btn btn-success">{{ _i('Esporta CSV') }} <span class="glyphicon glyphicon-download" aria-hidden="true"></span></button>
    </form>
</div>
