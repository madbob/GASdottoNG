<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">{{ _i('Stato Crediti') }}</h4>
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
                    <input type="number" class="form-control table-number-filter" placeholder="{{ _i('Filtra Credito') }}" data-list-target="#creditsTable">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group hidden-md">
                <div class="btn-group table-filters" data-toggle="buttons" data-table-target="#creditsTable">
                    <label class="btn btn-default active">
                        <input type="radio" name="payment_method" class="active" value="all"> {{ _i('Tutti') }}
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="payment_method" value="none"> {{ _i('Non Specificato') }}
                    </label>
                    @foreach(App\MovementType::payments() as $payment_identifier => $payment_meta)
                        <label class="btn btn-default">
                            <input type="radio" name="payment_method" value="{{ $payment_identifier }}"> {{ $payment_meta->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" id="credits_status_table">
            <table class="table" id="creditsTable">
                <thead>
                    <tr>
                        <th width="50%">{{ _i('Nome') }}</th>
                        <th width="25%">{{ _i('Credito Residuo') }}</th>
                        <th width="25%">{{ _i('Modalità Pagamento') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currentgas->users()->topLevel()->get() as $user)
                        <tr data-filtered-payment_method="{{ $user->payment_method_id }}">
                            <td>
                                <input type="hidden" name="user_id[]" value="{{ $user->id }}">
                                {{ $user->printableName() }}
                            </td>

                            <td class="text-filterable-cell">
                                {{ printablePriceCurrency($user->current_balance_amount) }}
                            </td>

                            <td>
                                {{ $user->payment_method->name }}

                                @if(($user->payment_method->valid_config)($user) == false)
                                    <span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <form class="form-inline iblock inner-form" action="{{ url('movements/document/credits/csv?dummy=1') }}" method="GET">
        <input type="hidden" name="pre-saved-function" value="collectFilteredUsers">
        <input type="hidden" name="pre-saved-function" value="formToDownload">
        <button type="submit" class="btn btn-success">{{ _i('Esporta CSV') }} <span class="glyphicon glyphicon-download" aria-hidden="true"></span></button>
    </form>

    @if($currentgas->hasFeature('rid'))
        <a type="button" class="btn btn-success" data-toggle="collapse" href="#exportRID">{{ _i('Esporta SEPA') }}<span class="caret"></span></a>
    @endif

    <a type="button" class="btn btn-success" data-toggle="collapse" href="#sendCreditsMail">{{ _i('Notifica Utente Visualizzati') }}<span class="caret"></span></a>

    @if($currentgas->hasFeature('rid'))
        <div class="collapse well" id="exportRID">
            <form class="form-horizontal inner-form" action="{{ url('movements/document/credits/rid?download=1') }}" method="GET">
                <input type="hidden" name="pre-saved-function" value="formToDownload">

                @include('commons.datefield', [
                    'obj' => null,
                    'name' => 'date',
                    'label' => _i('Data'),
                    'mandatory' => true,
                    'defaults_now' => true
                ])

                @include('commons.textfield', [
                    'obj' => null,
                    'name' => 'body',
                    'label' => _i('Causale'),
                    'default_value' => _i('VERSAMENTO GAS')
                ])

                <button type="submit" class="btn btn-success">{{ _i('Esporta SEPA') }}</button>
            </form>
        </div>
    @endif

    <div class="collapse well" id="sendCreditsMail">
        <form class="form-horizontal inner-form" method="POST" action="{{ route('notifications.store') }}">
            <input type="hidden" name="close-modal" value="1">
            <input type="hidden" name="pre-saved-function" value="collectFilteredUsers">
            @include('notification.base-edit', ['notification' => null, 'select_users' => false])
            <button type="submit" class="btn btn-success">{{ _i('Notifica') }}</button>
        </form>
    </div>
</div>
