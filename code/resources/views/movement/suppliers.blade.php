<div class="modal fade delete-on-close">
    <?php $currencies = App\Currency::enabled() ?>

    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ _i('Stato Fornitori') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <div class="input-group table-number-filters" data-table-target="#suppliersTable">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="min" name="filter_mode">&nbsp;{{ _i('Minore di') }}
                                </div>
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value="max" name="filter_mode">&nbsp;{{ _i('Maggiore di') }}
                                </div>
                                <input type="number" class="form-control table-number-filter" placeholder="{{ _i('Filtra Credito') }}">
                                <div class="input-group-text">
                                    {{ $currentgas->currency }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col" id="credits_status_table">
                        <table class="table" id="suppliersTable">
                            <thead>
                                <tr>
                                    <th width="50%">{{ _i('Nome') }}</th>

                                    @foreach($currencies as $curr)
                                        <th width="{{ round(50 / $currencies->count(), 2) }}%">{{ _i('Saldo Attuale') }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentgas->suppliers as $supplier)
                                    <tr>
                                        <td>
                                            {{ $supplier->printableName() }}
                                        </td>

                                        @foreach($currencies as $curr)
                                            <td class="text-filterable-cell">
                                                {{ printablePriceCurrency($supplier->currentBalanceAmount($curr), '.', $curr) }}
                                            </td>
                                        @endforeach
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
                    <button type="submit" class="btn btn-success">{{ _i('Esporta CSV') }} <i class="bi-download"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
