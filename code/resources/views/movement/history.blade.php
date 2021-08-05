<?php $can_edit = Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas) ?>

<x-larastrap::modal :title="_i('Storico Saldi')">
    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                @foreach($obj->balanceFields() as $identifier => $name)
                    <th>{{ $name }}</th>
                @endforeach

                @if($can_edit)
                    <th></th>
                @endif
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

                    @if($can_edit)
                        <td>
                            @if($index != 0)
                                <x-larastrap::iconbutton label="<i class='bi-x-lg'></i>" color="danger" :triggers_modal="sprintf('#modal-delete-balance-%s', $index)" />

                                <x-larastrap::modal :title="_i('Elimina Saldo Passato')" :id="sprintf('modal-delete-balance-%s', $index)" size="lg">
                                    <x-larastrap::iform classes="form-inline iblock" :action="route('movements.deletebalance', $bal->id)">
                                        <input type="hidden" name="reload-whole-page" value="1">
                                        <input type="hidden" name="pre-saved-function" value="passwordProtected">

                                        <div class="alert alert-danger">
                                            <p>
                                                {{ _i("Attenzione! I saldi passati possono essere rimossi ma con prudenza, l'operazione non è reversibile, e non sarà più possibile ricalcolare questi valori in nessun modo!") }}
                                            </p>
                                        </div>
                                    </x-larastrap::iform>
                                </x-larastrap::modal>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</x-larastrap::modal>
