@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('movements.admin', $currentgas)
            @include('commons.addingbutton', [
                'typename' => 'movement',
                'typename_readable' => 'Movimento',
                'dynamic_url' => url('movements/create')
            ])

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#configAccounting">Configurazione Contabilità</button>
            <div class="modal fade" id="configAccounting" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal inner-form" method="POST" action="{{ url('gas/' . $currentgas->id) }}">
                            <input type="hidden" name="reload-whole-page" value="1">
                            <input type="hidden" name="_method" value="PUT">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Configurazione Contabilità</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="year_closing" class="col-sm-{{ $labelsize }} control-label">Inizio Anno Sociale</label>
                                    <div class="col-sm-{{ $fieldsize }}">
                                        <div class="input-group">
                                            <input type="text" class="date-to-month form-control" name="year_closing" value="{{ ucwords(strftime('%d %B', strtotime($currentgas->getConfig('year_closing')))) }}" required autocomplete="off">
                                            <div class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                            </div>
                                        </div>
                                        <span class="help-block">In questa data le quote di iscrizione verranno automaticamente fatte scadere e dovranno essere rinnovate</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="annual_fee_amount" class="col-sm-{{ $labelsize }} control-label">Quota Annuale</label>
                                    <div class="col-sm-{{ $fieldsize }}">
                                        <div class="input-group">
                                            <input type="text" class="form-control number" name="annual_fee_amount" value="{{ printablePrice($currentgas->getConfig('annual_fee_amount')) }}" autocomplete="off">
                                            <div class="input-group-addon">€</div>
                                        </div>
                                        <span class="help-block">Se non configurato (valore = 0) non verranno gestite le quote di iscrizione</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="deposit_amount" class="col-sm-{{ $labelsize }} control-label">Cauzione</label>
                                    <div class="col-sm-{{ $fieldsize }}">
                                        <div class="input-group">
                                            <input type="text" class="form-control number" name="deposit_amount" value="{{ printablePrice($currentgas->getConfig('deposit_amount')) }}" autocomplete="off">
                                            <div class="input-group-addon">€</div>
                                        </div>
                                        <span class="help-block">Se non configurato (valore = 0) non verranno gestite le cauzioni da parte dei nuovi soci</span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                <button type="submit" class="btn btn-success">Salva</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#creditsStatus">Stato Crediti</button>
            <div class="modal fade dynamic-contents" id="creditsStatus" tabindex="-1" data-contents-url="{{ url('movements/showcredits') }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    </div>
                </div>
            </div>
        @endcan

        @can('movements.types', $currentgas)
            <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#handleMovementTypes">Amministra Tipi Movimento</button>
            <div class="collapse dynamic-contents" id="handleMovementTypes" tabindex="-1" data-contents-url="{{ url('movtypes') }}">
            </div>
        @endcan
    </div>

    <div class="clearfix"></div>
    <hr/>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-horizontal form-filler" data-action="{{ url('movements') }}" data-toggle="validator" data-fill-target="#movements-in-range">
            @include('commons.genericdaterange', [
                'start_date' => strtotime('-1 weeks'),
            ])
            @include('commons.selectmovementtypefield', ['show_all' => true])
            @include('commons.radios', [
                'name' => 'method',
                'label' => 'Pagamento',
                'values' => ['all' => (object)['name' => 'Tutti', 'checked' => true]] + App\MovementType::payments()
            ])
            @include('commons.selectobjfield', [
                'obj' => null,
                'name' => 'user_id',
                'label' => 'Utente',
                'objects' => $currentgas->users,
                'extra_selection' => [
                    '0' => 'Nessuno'
                ]
            ])
            @include('commons.selectobjfield', [
                'obj' => null,
                'name' => 'supplier_id',
                'label' => 'Fornitore',
                'objects' => $currentgas->suppliers,
                'extra_selection' => [
                    '0' => 'Nessuno'
                ]
            ])
            @include('commons.decimalfield', ['obj' => null, 'name' => 'amountstart', 'label' => 'Importo Minimo', 'is_price' => true])
            @include('commons.decimalfield', ['obj' => null, 'name' => 'amountend', 'label' => 'Importo Massimo', 'is_price' => true])

            <div class="form-group">
                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-success">Ricerca</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-md-offset-3 current-balance">
        @include('movement.status', ['obj' => $currentgas])
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-12" id="movements-in-range">
        @include('movement.list', ['movements' => $movements])
    </div>
</div>

@include('commons.deleteconfirm', [
    'url' => 'movements',
    'password_protected' => true,
    'extra' => [
        'close-all-modal' => '1',
        'post-saved-function' => ['refreshFilter', 'refreshBalanceView']
    ]
])

@include('commons.passwordmodal')

@endsection
