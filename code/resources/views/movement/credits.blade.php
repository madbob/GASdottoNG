<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">{{ _i('Stato Crediti') }}</h4>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right table-filters" data-table-target="#credits_status_table" data-toggle="buttons">
                <label class="btn btn-info active">
                    <input type="radio" name="credit" value="all" autocomplete="off" checked> {{ _i('Tutti') }}
                </label>
                <label class="btn btn-info">
                    <input type="radio" name="credit" value="minor" autocomplete="off"> {{ _i('Credito < 0') }}
                </label>
                <label class="btn btn-info">
                    <input type="radio" name="credit" value="major" autocomplete="off"> {{ _i('Credito >= 0') }}
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" id="credits_status_table">
            <table class="table">
                <thead>
                    <tr>
                        @if(!empty($currentgas->rid['iban']))
                            <th width="50%">{{ _i('Nome') }}</th>
                            <th width="35%">{{ _i('Credito Residuo') }}</th>
                            <th width="15%">{{ _i('IBAN') }}</th>
                        @else
                            <th width="60%">{{ _i('Nome') }}</th>
                            <th width="40%">{{ _i('Credito Residuo') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach(App\User::sorted()->get() as $user)
                        <?php $amount = $user->current_balance_amount ?>
                        <tr data-filtered-credit="{{ $amount < 0 ? 'minor' : 'major' }}">
                            <td>{{ $user->printableName() }}</td>
                            <td>{{ $amount }} €</td>

                            @if(!empty($currentgas->rid['iban']))
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
    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
    <a href="{{ url('movements/document/credits/csv') }}" class="btn btn-success">{{ _i('Esporta CSV') }}</a>
    <a href="{{ url('movements/document/credits/rid') }}" class="btn btn-success">{{ _i('Esporta RID') }}</a>
</div>
