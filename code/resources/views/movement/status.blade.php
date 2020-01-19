<ul class="list-group">
    @foreach($obj->balanceFields() as $identifier => $name)
        <li class="list-group-item">
            {{ $name }}
            <span class="badge {{ $identifier }}"><span>{{ $obj->current_balance->$identifier }}</span> {{ $currentgas->currency }}</span>
        </li>
    @endforeach
</ul>

<div class="pull-right">
    <div class="form-inline iblock inner-form">
        <button class="btn btn-default" data-toggle="modal" data-target="#movements-history-{{ $obj->id }}">{{ _i('Storico Saldi') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>

        <div class="modal fade" id="movements-history-{{ $obj->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{ _i('Storico Saldi') }}</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    @foreach($obj->balanceFields() as $identifier => $name)
                                        <th>{{ $name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($obj->balances as $index => $bal)
                                    <tr class="{{ $index == 0 ? 'current-balance' : '' }}">
                                        <td>{{ $index == 0 ? _i('Saldo Corrente') : ucwords(strftime('%d %B %G', strtotime($bal->date))) }}</td>
                                        @foreach($obj->balanceFields() as $identifier => $name)
                                            <td class="{{ $index == 0 ? $identifier : '' }}"><span>{{ $bal->$identifier }}</span> {{ $currentgas->currency }}</td>
                                        @endforeach
                                        <td>
                                            @if($index != 0)
                                                <button class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-delete-balance-{{ $index }}"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>

                                                <div class="modal fade" id="modal-delete-balance-{{ $index }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                <h4 class="modal-title">{{ _i('Elimina Saldo Passato') }}</h4>
                                                            </div>
                                                            <form class="form-inline iblock password-protected" id="delete-balance-{{ $index }}" method="POST" action="{{ route('movements.deletebalance', $bal->id) }}">
                                                                {!! csrf_field() !!}
                                                                <input type="hidden" name="reload-whole-page" value="1">

                                                                <div class="modal-body">
                                                                    <div class="alert alert-danger">
                                                                        <p>
                                                                            {{ _i("Attenzione! I saldi passati possono essere rimossi ma con prudenza, l'operazione non è reversibile, e non sarà più possibile ricalcolare questi valori in nessun modo!") }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                                                    <button type="submit" class="btn btn-danger">{{ _i('Ho capito') }}</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Chiudi') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(get_class($obj) == 'App\Gas')
        <form class="form-inline iblock password-protected" id="recalculate-account" method="POST" action="{{ url('/movements/recalculate') }}">
            <div class="form-group">
                {!! csrf_field() !!}
                <input type="hidden" name="post-saved-function" value="displayRecalculatedBalances">
                <button type="submit" class="btn btn-danger">{{ _i('Ricalcola Saldi') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
            </div>

            <div class="modal fade" id="display-recalculated-balance-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">{{ _i('Bilanci Ricalcolati') }}</h4>
                        </div>
                        <div class="modal-body">
                            <p>
                                {{ _i('Operazione conclusa.') }}
                            </p>
                            <div class="hidden alert alert-danger broken">
                                <p>
                                    {{ _i('I seguenti saldi sono risultati diversi al termine del ricalcolo.') }}
                                </p>
                                <br>
                                <table class="table" id="broken_balances">
                                    <thead>
                                        <tr>
                                            <th>{{ _i('Soggetto') }}</th>
                                            <th>{{ _i('Prima') }}</th>
                                            <th>{{ _i('Dopo') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="hidden alert alert-success fixed">
                                <p>
                                    {{ _i('Tutti i saldi risultano coerenti.') }}
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="" class="btn btn-default hidden table_to_csv" data-target="#broken_balances">{{ _i('Esporta CSV') }} <span class="glyphicon glyphicon-download" aria-hidden="true"></span></a>
                            <a href="" class="btn btn-success">{{ _i('Ricarica la Pagina') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="iblock">
            <div class="form-group">
                <button type="submit" class="btn btn-danger" data-toggle="modal" data-target="#close-balance-modal">{{ _i('Archivia Saldi') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
            </div>

            <div class="modal fade" id="close-balance-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal password-protected" id="close-balance" method="POST" action="{{ url('/movements/close') }}">
                            <input type="hidden" name="reload-whole-page" value="1">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{{ _i('Conferma Operazione') }}</h4>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="reload-whole-page" value="1">
                                {!! csrf_field() !!}

                                @include('commons.datefield', [
                                    'obj' => null,
                                    'name' => 'date',
                                    'defaults_now' => true,
                                    'label' => 'Data Chiusura'
                                ])
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
