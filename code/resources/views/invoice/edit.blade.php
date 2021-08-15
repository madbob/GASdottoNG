<?php $rand = rand() ?>

<div>
    <x-larastrap::mform :obj="$invoice" classes="invoice-editor" method="PUT" :action="route('invoices.update', $invoice->id)">
        <div class="row">
            <div class="col-12 col-md-6">
                @if($invoice->status == 'payed')
                    <x-larastrap::selectobj
                        name="supplier_id"
                        :label="_i('Fornitore')"
                        :options="App\Supplier::orderBy('name', 'asc')->withTrashed()->get()"
                        disabled
                        readonly
                        :help="view('supplier.invoicedata', ['supplier' => $invoice->supplier])->render()" />

                    <x-larastrap::text name="number" :label="_i('Numero')" disabled readonly />
                    <x-larastrap::datepicker name="date" :label="_i('Data')" disabled readonly />
                @else
                    <x-larastrap::selectobj
                        name="supplier_id"
                        :label="_i('Fornitore')"
                        classes="select-fetcher"
                        :options="App\Supplier::orderBy('name', 'asc')->withTrashed()->get()"
                        :help="view('supplier.invoicedata', ['supplier' => $invoice->supplier])->render()"
                        :attributes="['data-fetcher-target' => '.form-text', 'data-fetcher-url' => route('suppliers.invoicedata', 'XXX')]" />

                    <x-larastrap::text name="number" :label="_i('Numero')" required />
                    <x-larastrap::datepicker name="date" :label="_i('Data')" required defaults_now />
                @endif

                <hr>

                @if($invoice->orders->count() > 0 || $invoice->status != 'payed')
                    <x-larastrap::field :label="_i('Ordini Coinvolti')" :pophelp="_i('Seleziona gli ordini che sono coinvolti in questa fattura. Quando la fatturà sarà marcata come pagata, ad essi sarà aggiunto il riferimento al movimento contabile di pagamento e saranno automaticamente archiviati')">
                        @if($invoice->orders->count() > 0)
                            @foreach($invoice->orders as $o)
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="static-label text-muted">
                                            {{ $o->printableName() }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach

                            <br>
                        @endif

                        @if($invoice->status != 'payed')
                            @can('movements.admin', $currentgas)
                                <x-larastrap::ambutton :label="_i('Modifica Ordini')" :data-modal-url="route('invoices.orders', $invoice->id)" />

                                @if($invoice->orders()->count() != 0)
                                    <x-larastrap::ambutton :label="_i('Verifica Contenuti')" :data-modal-url="route('invoices.products', $invoice->id)" />
                                @endif
                            @endcan
                        @endif
                    </x-larastrap::field>

                    <hr>
                @endif

                <?php

                $orders_total_taxable = 0;
                $orders_total_tax = 0;
                $orders_total = 0;
                $orders_modifiers = [];
                $calculated_summaries = [];

                foreach($invoice->orders as $o) {
                    $summary = $o->calculateInvoicingSummary();
                    $calculated_summaries[$o->id] = $summary;
                    $orders_total_taxable += $summary->total_taxable;
                    $orders_total_tax += $summary->total_tax;

                    $orders_total = $orders_total_taxable + $orders_total_tax;

                    $modifiers = $o->applyModifiers(null, false);
                    $aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers);

                    foreach($aggregated_modifiers as $am) {
                        if (!isset($orders_modifiers[$am->id])) {
                            $orders_modifiers[$am->id] = $am;
                        }
                        else {
                            $orders_modifiers[$am->id]->amount += $am->amount;
                        }

                        $orders_total += $am->amount;
                    }
                }

                ?>

                <div class="simple-sum-container">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ _i('Fattura') }}</th>
                                <th>{{ _i('Ordini Coinvolti') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ _i('Totale Imponibile') }}</td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" class="form-control number trim-2-ddigits simple-sum" name="total" value="{{ printablePrice($invoice->total) }}" required autocomplete="off">
                                        <div class="input-group-text">{{ $currentgas->currency }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" class="form-control number trim-2-ddigits" value="{{ printablePrice($orders_total_taxable) }}" disabled autocomplete="off">
                                        <div class="input-group-text">{{ $currentgas->currency }}</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ _i('Totale IVA') }}</td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" class="form-control number trim-2-ddigits simple-sum" name="total_vat" value="{{ printablePrice($invoice->total_vat) }}" required autocomplete="off">
                                        <div class="input-group-text">{{ $currentgas->currency }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" class="form-control number trim-2-ddigits" value="{{ printablePrice($orders_total_tax) }}" disabled autocomplete="off">
                                        <div class="input-group-text">{{ $currentgas->currency }}</div>
                                    </div>
                                </td>
                            </tr>

                            @foreach($orders_modifiers as $om)
                                <tr>
                                    <td>{{ $om->name }}</td>
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control number trim-2-ddigits" value="{{ printablePrice($om->amount) }}" disabled autocomplete="off">
                                            <div class="input-group-text">{{ $currentgas->currency }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <tr>
                                <td>{{ _i('Totale') }}</td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" class="form-control number trim-2-ddigits simple-sum-result" value="{{ printablePrice($invoice->total + $invoice->total_vat) }}" disabled autocomplete="off">
                                        <div class="input-group-text">{{ $currentgas->currency }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" class="form-control number trim-2-ddigits" value="{{ printablePrice($orders_total) }}" disabled autocomplete="off">
                                        <div class="input-group-text">{{ $currentgas->currency }}</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <x-larastrap::textarea name="notes" :label="_i('Note')" />
                <x-larastrap::select name="status" :label="_i('Stato')" :options="App\Invoice::statuses()" />

                <x-larastrap::field :label="_i('Pagamento')">
                    @if($invoice->payment)
                        <div class="row">
                            <div class="col">
                                @include('commons.movementfield', [
                                    'obj' => $invoice->payment,
                                    'name' => 'payment_id',
                                    'squeeze' => true,
                                    'editable' => true,
                                ])
                            </div>
                        </div>

                        @foreach($invoice->otherMovements as $om)
                            <div class="row">
                                <div class="col">
                                    @include('commons.movementfield', [
                                        'obj' => $om,
                                        'name' => 'payment',
                                        'squeeze' => true,
                                    ])
                                </div>
                            </div>
                        @endforeach

                        <br>
                    @endif

                    @if($invoice->status != 'payed')
                        <x-larastrap::ambutton :label="_i('Registra Pagamento')" :attributes="['data-modal-url' => route('invoices.movements', $invoice->id)]" />
                    @endif
                </x-larastrap::field>
            </div>
        </div>
    </x-larastrap::mform>

    @stack('postponed')
</div>
