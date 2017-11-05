<ul class="list-group">
    @foreach($obj->balanceFields() as $identifier => $name)
        <li class="list-group-item">
            Saldo {{ $name }}
            <span class="badge {{ $identifier }}"><span>{{ $obj->current_balance->$identifier }}</span> €</span>
        </li>
    @endforeach
</ul>

<div class="pull-right">
    <br/>
    <div class="form-inline iblock inner-form">
        <button class="btn btn-default" data-toggle="modal" data-target="#movements-history-{{ $obj->id }}">Storico Saldi</button>

        <div class="modal fade" id="movements-history-{{ $obj->id }}" tabindex="-1" role="dialog">
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
                                    @foreach($obj->balanceFields() as $identifier => $name)
                                        <th>{{ $name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($obj->balances as $index => $bal)
                                    <tr class="{{ $index == 0 ? 'current-balance' : '' }}">
                                        <td>{{ $index == 0 ? 'Saldo Corrente' : ucwords(strftime('%d %B %G', strtotime($bal->date))) }}</td>
                                        @foreach($obj->balanceFields() as $identifier => $name)
                                            <td class="{{ $index == 0 ? $identifier : '' }}"><span>{{ $bal->$identifier }}</span> €</td>
                                        @endforeach
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

    @if(get_class($obj) == 'App\Gas')
        <form class="form-inline iblock password-protected" id="recalculate-account" method="POST" action="{{ url('/movements/recalculate') }}">
            <div class="form-group">
                {!! csrf_field() !!}
                <input type="hidden" name="post-saved-function" value="displayRecalculatedBalances">
                <button type="submit" class="btn btn-danger">Ricalcola Saldi</button>
            </div>

            <div class="modal fade" id="display-recalculated-balance-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Bilanci Ricalcolati</h4>
                        </div>
                        <div class="modal-body">
                            <p>
                                Operazione conclusa.
                            </p>
                            <div class="hidden alert alert-danger broken">
                                <p>
                                    I seguenti saldi sono risultati diversi al termine del ricalcolo.
                                </p>
                                <br>
                                <table class="table" id="broken_balances">
                                    <thead>
                                        <tr>
                                            <th>Soggetto</th>
                                            <th>Prima</th>
                                            <th>Dopo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="" class="btn btn-default hidden table_to_csv" data-target="#broken_balances">Scarica CSV</a>
                            <a href="" class="btn btn-success">Ricarica la Pagina</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="iblock">
            <div class="form-group">
                <button type="submit" class="btn btn-danger" data-toggle="modal" data-target="#close-balance-modal">Archivia Saldi</button>
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
    @endif
</div>
