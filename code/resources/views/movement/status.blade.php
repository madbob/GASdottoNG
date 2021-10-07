@include('movement.summary', ['obj' => $obj])

<div class="float-end">
    <div class="form-inline iblock inner-form">
        <x-larastrap::ambutton :label="_i('Storico Saldi')" :data-modal-url="route('movements.history', inlineId($obj))" />
    </div>

    @if(get_class($obj) == 'App\Gas')
        <x-larastrap::iform classes="form-inline iblock" id="recalculate-account" method="POST" :action="url('/movements/recalculate')" :buttons="[['attributes' => ['type' => 'submit'], 'color' => 'danger', 'label' => _i('Ricalcola Saldi')]]">
            <input type="hidden" name="pre-saved-function" value="passwordProtected">
            <input type="hidden" name="post-saved-function" value="displayRecalculatedBalances">
        </x-larastrap::iform>

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

        <div class="iblock">
            <div class="form-group">
                <x-larastrap::mbutton :label="_i('Archivia Saldi')" triggers_modal="#close-balance-modal" classes="btn-danger" />
            </div>

            <x-larastrap::modal :title="_i('Archivia Saldi')" id="close-balance-modal">
                <p>
                    {{ _("È raccomandato archiviare i saldi periodicamente, ad esempio alla chiusura dell'anno sociale, dopo aver verificato che questi siano tutti corretti. In tal modo le successive operazioni di ricalcolo saranno molto più veloci, non dovendo computare ogni volta tutti i movimenti contabili esistenti ma solo quelli avvenuti dopo l'ultima archiviazione. I movimenti archiviati saranno comunque sempre consultabili.") }}
                </p>
                <p>
                    {{ _i('Questa operazione può richiedere diversi minuti per essere completata.') }}
                </p>

                <hr>

                <x-larastrap::iform id="close-balance" :action="url('/movements/close')">
                    <input type="hidden" name="reload-whole-page" value="1">
                    <input type="hidden" name="pre-saved-function" value="passwordProtected">
                    <x-larastrap::datepicker name="date" defaults_now="true" :label="_i('Data Chiusura')" />
                </x-larastrap::iform>
            </x-larastrap::modal>
        </div>
    @endif
</div>
