<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Stato Crediti</h4>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right table-filters" data-table-target="#credits_status_table" data-toggle="buttons">
                <label class="btn btn-info active">
                    <input type="radio" name="credit" value="all" autocomplete="off" checked> Tutti
                </label>
                <label class="btn btn-info">
                    <input type="radio" name="credit" value="minor" autocomplete="off"> Credito < 0
                </label>
                <label class="btn btn-info">
                    <input type="radio" name="credit" value="major" autocomplete="off"> Credito >= 0
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" id="credits_status_table">
            <table class="table">
                <thead>
                    <tr>
                        @if(!empty($currentgas->iban))
                            <th width="50%">Nome</th>
                            <th width="35%">Credito Residuo</th>
                            <th width="15%">IBAN</th>
                        @else
                            <th width="60%">Nome</th>
                            <th width="40%">Credito Residuo</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach(App\User::sorted()->get() as $user)
                        <?php $amount = $user->current_balance_amount ?>
                        <tr data-filtered-credit="{{ $amount < 0 ? 'minor' : 'major' }}">
                            <td>{{ $user->printableName() }}</td>
                            <td>{{ $amount }} €</td>

                            @if(!empty($currentgas->iban))
                                <td>
                                    @if(empty($user->rid['iban']))
                                        <span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span>
                                    @else
                                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
    <a href="{{ url('movements/document/credits/csv') }}" class="btn btn-success">Esporta CSV</a>
    <a href="{{ url('movements/document/credits/rid') }}" class="btn btn-success">Esporta RID</a>
</div>
