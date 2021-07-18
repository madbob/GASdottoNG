<ul class="list-group mb-2">
    @foreach($obj->balanceFields() as $identifier => $name)
        <li class="list-group-item">
            {{ $name }}
            <span class="badge bg-secondary float-end {{ $identifier }}"><span>{{ $obj->current_balance->$identifier }}</span> {{ $currentgas->currency }}</span>
        </li>
    @endforeach
</ul>

<div class="float-end">
    <div class="form-inline iblock inner-form">
        <x-larastrap::mbutton :label="_i('Storico Saldi')" :triggers_modal="sprintf('#movements-history-%s', $obj->id)" />

        <x-larastrap::modal :title="_i('Storico Saldi')" :id="sprintf('movements-history-%s', $obj->id)">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        @foreach($obj->balanceFields() as $identifier => $name)
                            <th>{{ $name }}</th>
                        @endforeach
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obj->balances as $index => $bal)
                        <tr class="{{ $index == 0 ? 'current-balance' : '' }}">
                            <td>{{ $index == 0 ? _i('Saldo Corrente') : ucwords(strftime('%d %B %Y', strtotime($bal->date))) }}</td>

                            @foreach($obj->balanceFields() as $identifier => $name)
                                <td class="{{ $index == 0 ? $identifier : '' }}">
                                    <span>{{ $bal->$identifier }}</span> {{ $currentgas->currency }}
                                </td>
                            @endforeach

                            <td>
                                @if($index != 0)
                                    <button class="btn btn-xs btn-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-balance-{{ $index }}"><i class="bi-x-lg"></i></button>

                                    <x-larastrap::modal :title="_i('Elimina Saldo Passato')" :id="sprintf('modal-delete-balance-%s', $index)">
                                        <x-larastrap::form classes="form-inline iblock password-protected" :id="sprintf('delete-balance-%s', $index)" :action="route('movements.deletebalance', $bal->id)">
                                            <input type="hidden" name="reload-whole-page" value="1">

                                            <div class="alert alert-danger">
                                                <p>
                                                    {{ _i("Attenzione! I saldi passati possono essere rimossi ma con prudenza, l'operazione non è reversibile, e non sarà più possibile ricalcolare questi valori in nessun modo!") }}
                                                </p>
                                            </div>
                                        </x-larastrap::form>
                                    </x-larastrap::modal>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-larastrap::modal>
    </div>

    @if(get_class($obj) == 'App\Gas')
        <form class="form-inline iblock password-protected" id="recalculate-account" method="POST" action="{{ url('/movements/recalculate') }}">
            <div class="form-group">
                {!! csrf_field() !!}
                <input type="hidden" name="post-saved-function" value="displayRecalculatedBalances">
                <button type="submit" class="btn btn-danger">{{ _i('Ricalcola Saldi') }} <i class="bi-window"></i></button>
            </div>

            <x-larastrap::modal :title="_i('Bilanci Ricalcolati')" id="display-recalculated-balance-modal">
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
            </x-larastrap::modal>
        </form>
        <div class="iblock">
            <div class="form-group">
                <x-larastrap::mbutton :label="_i('Archivia Saldi')" triggers_modal="#close-balance-modal" classes="btn-danger" />
            </div>

            <x-larastrap::modal :title="_i('Conferma Operazione')" id="close-balance-modal">
                <x-larastrap::form classes="password-protected" id="close-balance" :action="url('/movements/close')">
                    <input type="hidden" name="reload-whole-page" value="1">
                    <x-larastrap::datepicker name="date" defaults_now="true" :label="_i('Data Chiusura')" />
                </x-larastrap::form>
            </x-larastrap::modal>
        </div>
    @endif
</div>
