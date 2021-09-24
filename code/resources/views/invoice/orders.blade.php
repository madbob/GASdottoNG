<x-larastrap::modal :title="_i('Modifica Ordini')">
    <?php $orders = $invoice->ordersCandidates() ?>

    @if($orders->isEmpty())
        <div class="alert alert-danger">
            {{ _i('Non ci sono ordini assegnabili a questa fattura. Gli ordini devono: fare riferimento allo stesso fornitore cui è assegnata la fattura; non avere un pagamento al fornitore già registrato; essere in stato "Consegnato" o "Archiviato"; avere almeno una prenotazione "Consegnata" (il totale delle prenotazioni consegnate viene usato per effettuare il calcolo del pagamento effettivo).') }}
        </div>
    @else
        <x-larastrap::iform method="POST" :action="url('invoices/wire/review/' . $invoice->id)">
            <input type="hidden" name="close-modal" value="1" />
            <input type="hidden" name="reload-loadable" value="#invoice-list" />

            <table class="table">
                <thead>
                    <tr>
                        <th width="10%"></th>
                        <th width="30%">Ordine</th>
                        <th width="20%">Totale Imponibile</th>
                        <th width="20%">Totale IVA</th>
                        <th width="20%">Totale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        <?php $summary = $calculated_summaries[$o->id] ?? $o->calculateInvoicingSummary() ?>
                        @if($summary->total != 0)
                            <tr class="orders-in-invoice-candidate">
                                <td><input type="checkbox" name="order_id[]" value="{{ $o->id }}"></td>
                                <td>
                                    {{ $o->printableName() }}<br>
                                    <small>{{ $o->printableDates() }}</small>
                                </td>
                                <td class="taxable">
                                    <x-larastrap::price squeeze :value="$summary->total_taxable" readonly disabled />
                                </td>
                                <td class="tax">
                                    <x-larastrap::price squeeze :value="$summary->total_tax" readonly disabled />
                                </td>
                                <td class="total">
                                    <x-larastrap::price squeeze :value="$summary->total" readonly disabled />
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                <tr class="orders-in-invoice-total">
                    <td>&nbsp;</td>
                    <td>Totale Selezionato</td>
                    <td class="taxable">
                        <x-larastrap::price squeeze value="0" readonly disabled />
                    </td>
                    <td class="tax">
                        <x-larastrap::price squeeze value="0" readonly disabled />
                    </td>
                    <td class="total">
                        <x-larastrap::price squeeze value="0" readonly disabled />
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td>{{ _i('Fattura') }}</td>
                    <td>
                        <x-larastrap::price squeeze :value="$invoice->total" readonly disabled />
                    </td>
                    <td>
                        <x-larastrap::price squeeze :value="$invoice->total_vat" readonly disabled />
                    </td>
                    <td>
                        <x-larastrap::price squeeze :value="$invoice->total + $invoice->total_vat" readonly disabled />
                    </td>
                </tr>
            </tbody>
        </table>
    </x-larastrap::iform>
</x-larastrap::modal>
