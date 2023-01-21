<div class="modal fade delete-on-close">
    <?php $currencies = App\Currency::enabled() ?>

    <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ _i('Stato Crediti') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <div class="input-group table-number-filters" data-table-target="#creditsTable">
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
                    <div class="col">
                        <div class="form-group">
                            <div class="btn-group table-filters" data-bs-toggle="buttons" data-table-target="#creditsTable">
                                <label class="btn btn-light active">
                                    <input type="radio" name="payment_method" class="active" value="all"> {{ _i('Tutti') }}
                                </label>
                                <label class="btn btn-light">
                                    <input type="radio" name="payment_method" value="none"> {{ _i('Non Specificato') }}
                                </label>
                                @foreach(paymentTypes() as $payment_identifier => $payment_meta)
                                    <label class="btn btn-light">
                                        <input type="radio" name="payment_method" value="{{ $payment_identifier }}"> {{ $payment_meta->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col" id="credits_status_table">
                        <div class="table-responsive">
                            <table class="table" id="creditsTable">
                                <thead>
                                    <tr>
                                        <th width="40%">{{ _i('Nome') }}</th>
                                        @foreach($currencies as $curr)
                                            <th width="{{ round(35 / $currencies->count(), 2) }}%">{{ _i('Credito Residuo') }}</th>
                                        @endforeach
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

                                            @foreach($currencies as $curr)
                                                <td class="text-filterable-cell">
                                                    {{ printablePriceCurrency($user->currentBalanceAmount($curr), '.', $curr) }}
                                                </td>
                                            @endforeach

                                            <td>
                                                {{ $user->payment_method->name }}

                                                @if(($user->payment_method->valid_config)($user) == false)
                                                    <i class="bi-slash-circle"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <form class="form-inline iblock inner-form" action="{{ url('movements/document/credits/csv?dummy=1') }}" method="GET">
                    <input type="hidden" name="pre-saved-function" value="collectFilteredUsers">
                    <input type="hidden" name="pre-saved-function" value="formToDownload">
                    <button type="submit" class="btn btn-success">{{ _i('Esporta CSV') }} <i class="bi-download"></i></button>
                </form>

                @if($currentgas->hasFeature('rid'))
                    <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#exportRID">{{ _i('Esporta SEPA') }}<span class="caret"></span></a>
                @endif

                @if($currentgas->hasFeature('integralces'))
                    <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#exportIntegralCES">{{ _i('Esporta IntegralCES') }}<span class="caret"></span></a>
                @endif

                <a type="button" class="btn btn-success" data-bs-toggle="collapse" href="#sendCreditsMail">{{ _i('Notifica Utenti Visualizzati') }}<span class="caret"></span></a>

                @if($currentgas->hasFeature('rid'))
                    <div class="collapse well" id="exportRID">
                        <form class="form-horizontal inner-form" action="{{ url('movements/document/credits/rid?download=1') }}" method="GET">
                            <input type="hidden" name="pre-saved-function" value="formToDownload">
                            <x-larastrap::datepicker name="date" :label="_i('Data')" requird defaults_now />
                            <x-larastrap::text name="body" :label="_i('Causale')" :value="_i('VERSAMENTO GAS')" />
                            <button type="submit" class="btn btn-success">{{ _i('Esporta SEPA') }}</button>
                        </form>
                    </div>
                @endif

                @if($currentgas->hasFeature('integralces'))
                    <div class="collapse well" id="exportIntegralCES">
                        <form class="form-horizontal inner-form" action="{{ url('movements/document/credits/integralces?download=1') }}" method="GET">
                            <input type="hidden" name="pre-saved-function" value="formToDownload">
                            <x-larastrap::text name="body" :label="_i('Causale')" :value="_i('Versamento GAS')" />
                            <button type="submit" class="btn btn-success">{{ _i('Esporta IntegralCES') }}</button>
                        </form>
                    </div>
                @endif

                <div class="collapse well" id="sendCreditsMail">
                    <form class="form-horizontal inner-form" method="POST" action="{{ route('notifications.store') }}">
                        <input type="hidden" name="close-modal" value="1">
                        <input type="hidden" name="pre-saved-function" value="collectFilteredUsers">
                        <input type="hidden" name="type" value="notification">

                        @include('notification.base-edit', [
							'notification' => null,
							'select_users' => false,
							'instant' => true,
							'mailtype' => 'credit_notification',
						])

                        <button type="submit" class="btn btn-success">{{ _i('Notifica') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
