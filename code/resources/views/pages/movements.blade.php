@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('movements.admin', $currentgas)
            @include('commons.addingbutton', [
                'template' => 'movement.base-edit',
                'typename' => 'movement',
                'typename_readable' => 'Movimento',
                'targeturl' => 'movements',
                'extra' => [
                    'post-saved-function' => ['refreshFilter', 'refreshBalanceView']
                ]
            ])

            <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#handleMovementTypes">Amministra Tipi Movimento</button>
            <div class="collapse dynamic-contents" id="handleMovementTypes" tabindex="-1" role="dialog" data-contents-url="{{ url('movtypes') }}">
            </div>

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
                                    <label for="year_closing" class="col-sm-{{ $labelsize }} control-label">Chiusura Anno</label>
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
                                            <input type="number" class="form-control" name="annual_fee_amount" step="0.01" min="0" value="{{ printablePrice($currentgas->getConfig('annual_fee_amount')) }}" autocomplete="off">
                                            <div class="input-group-addon">€</div>
                                        </div>
                                        <span class="help-block">Se non configurato (valore = 0) non verranno gestite le quote di iscrizione</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="deposit_amount" class="col-sm-{{ $labelsize }} control-label">Cauzione</label>
                                    <div class="col-sm-{{ $fieldsize }}">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="deposit_amount" step="0.01" min="0" value="{{ printablePrice($currentgas->getConfig('deposit_amount')) }}" autocomplete="off">
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
        @endcan
    </div>

    <div class="clearfix"></div>
    <hr/>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-horizontal form-filler" data-action="{{ url('movements') }}" data-toggle="validator" data-fill-target="#movements-in-range">
            @include('commons.genericdaterange')
            @include('commons.selectmovementtypefield')
            @include('commons.selectobjfield', [
                'obj' => null,
                'name' => 'user_id',
                'label' => 'Utente',
                'objects' => App\User::orderBy('lastname', 'asc')->get(),
                'extra_selection' => [
                    '0' => 'Nessuno'
                ]
            ])
            @include('commons.selectobjfield', [
                'obj' => null,
                'name' => 'supplier_id',
                'label' => 'Fornitore',
                'objects' => App\Supplier::orderBy('name', 'asc')->get(),
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

    <div id="current-balance" class="col-md-3 col-md-offset-3">
        <ul class="list-group">
            <li class="list-group-item">
                Saldo Conto Corrente
                <span class="badge bank"><span>{{ $balance->bank }}</span> €</span>
            </li>
            <li class="list-group-item">
                Saldo Contanti
                <span class="badge cash"><span>{{ $balance->cash }}</span> €</span>
            </li>
            <li class="list-group-item">
                GAS
                <span class="badge gas"><span>{{ $balance->gas }}</span> €</span>
            </li>
            <li class="list-group-item">
                Fornitori
                <span class="badge suppliers"><span>{{ $balance->suppliers }}</span> €</span>
            </li>
            <li class="list-group-item">
                Depositi
                <span class="badge deposits"><span>{{ $balance->deposits }}</span> €</span>
            </li>
        </ul>

        <div class="pull-right">
            <div class="form-inline iblock inner-form">
                <div class="form-group">
                    <button class="btn btn-default" data-toggle="modal" data-target="#movements-history">Consulta Storico</button>

                    <div class="modal fade" id="movements-history" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">Storico Saldi</h4>
                                </div>
                                <div class="modal-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Conto Corrente</th>
                                                <th>Contanti</th>
                                                <th>Fornitori</th>
                                                <th>Depositi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($currentgas->balances as $index => $bal)
                                                <tr>
                                                    <td>{{ $index == 0 ? 'Saldo Corrente' : ucwords(strftime('%d %B %G', strtotime($bal->date))) }}</td>
                                                    <td>{{ $bal->bank }} €</td>
                                                    <td>{{ $bal->cash }} €</td>
                                                    <td>{{ $bal->suppliers }} €</td>
                                                    <td>{{ $bal->deposits }} €</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form class="form-inline iblock password-protected" id="recalculate-account" method="POST" action="{{ url('/movements/recalculate') }}">
                <div class="form-group">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-danger">Ricalcola Saldi</button>
                </div>
            </form>
            <div class="iblock">
                <div class="form-group">
                    <button type="submit" class="btn btn-danger" data-toggle="modal" data-target="#close-balance-modal">Chiudi Bilancio</button>
                </div>

                <div class="modal fade" id="close-balance-modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form class="form-horizontal password-protected" id="close-balance" method="POST" action="{{ url('/movements/close') }}">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">Conferma Operazione</h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="reload-whole-page" value="1">
                                    {!! csrf_field() !!}

                                    @include('commons.datefield', ['obj' => null, 'name' => 'date', 'label' => 'Data Chiusura'])
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                    <button type="submit" class="btn btn-success">Salva</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
