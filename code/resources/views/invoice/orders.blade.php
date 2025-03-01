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

            <p>
                {{ _i("Qui appaiono gli ordini che: appartengono al fornitore intestatario della fattura; sono in stato Consegnato o Archiviato; hanno almeno una prenotazione marcata come Consegnata. I totali vengono calcolati sulle quantità effettivamente consegnate, non sulle prenotazioni.") }}
            </p>

            <hr>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" width="10%"></th>
                        <th scope="col" width="30%">{{ _i('Ordine') }}</th>
                        <th scope="col" width="20%">{{ _i('Totale Imponibile') }}</th>
                        <th scope="col" width="20%">{{ _i('Totale IVA') }}</th>
                        <th scope="col" width="20%">{{ _i('Totale') }}</th>
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
                                <td>
                                    <span class="taxable">{{ $summary->total_taxable }}</span> {{ currentAbsoluteGas()->currency }}
                                </td>
                                <td>
                                    <span class="tax">{{ $summary->total_tax }}</span> {{ currentAbsoluteGas()->currency }}
                                </td>
                                <td>
                                    <span class="total">{{ $summary->total }}</span> {{ currentAbsoluteGas()->currency }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                <tr class="orders-in-invoice-total">
                    <td>&nbsp;</td>
                    <td>{{ _i('Totale Selezionato') }}</td>
                    <td>
                        <span class="taxable">0</span> {{ currentAbsoluteGas()->currency }}
                    </td>
                    <td>
                        <span class="tax">0</span> {{ currentAbsoluteGas()->currency }}
                    </td>
                    <td>
                        <span class="total">0</span> {{ currentAbsoluteGas()->currency }}
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td>{{ _i('Fattura') }}</td>
                    <td>
                        <span>{{ $invoice->total }}</span> {{ currentAbsoluteGas()->currency }}
                    </td>
                    <td>
                        <span>{{ $invoice->total_vat }}</span> {{ currentAbsoluteGas()->currency }}
                    </td>
                    <td>
                        <span>{{ $invoice->total + $invoice->total_vat }}</span> {{ currentAbsoluteGas()->currency }}
                    </td>
                </tr>
            </tbody>
        </table>
    </x-larastrap::iform>
</x-larastrap::modal>
