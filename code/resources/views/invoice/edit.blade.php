<?php $rand = rand() ?>

<div>
    <x-larastrap::mform :obj="$invoice" classes="invoice-editor" method="PUT" :action="route('invoices.update', $invoice->id)">
        <div class="row">
            <div class="col-12 col-md-6">
                @if($invoice->status == 'payed')
                    @include('commons.staticobjfield', ['obj' => $invoice, 'name' => 'supplier', 'label' => _i('Fornitore')])
                    <x-larastrap::text name="number" :label="_i('Numero')" disabled readonly />
                    <x-larastrap::datepicker name="date" :label="_i('Data')" disabled readonly />
                @else
                    <x-larastrap::selectobj
                        name="supplier_id"
                        :label="_i('Fornitore')"
                        classes="select-fetcher"
                        :options="$currentuser->targetsByAction('supplier.invoices')"
                        :help="view('supplier.invoicedata', ['supplier' => $invoice->supplier])->render()"
                        :attributes="['data-fetcher-target' => '.form-text', 'data-fetcher-url' => route('suppliers.invoicedata', 'XXX')]" />

                    <x-larastrap::text name="number" :label="_i('Numero')" required />
                    <x-larastrap::datepicker name="date" :label="_i('Data')" required defaults_now />
                @endif

                <x-larastrap::field :label="_i('Allegato')">
                    <x-larastrap::file name="file" :attributes="['data-max-size' => serverMaxUpload()]" squeeze="true" />

                    <div class="mt-2">
                        @foreach($invoice->attachments as $attachment)
                            <div class="row">
                                <div class="col">
                                    <a class="btn btn-info" href="{{ $attachment->download_url }}">
                                        {{ $attachment->name }} <i class="bi-download"></i>
                                    </a>
                                </div>
                                <div class="col">
                                    <x-larastrap::check name="delete_attachment[]" :label="_i('Elimina')" :value="$attachment->id" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-larastrap::field>

                <hr>

                @if($invoice->orders->count() > 0 || $invoice->status != 'payed')
                    <x-larastrap::field :label="_i('Ordini Coinvolti')" :pophelp="_i('Seleziona gli ordini che sono coinvolti in questa fattura. Quando la fatturà sarà marcata come pagata, ad essi sarà aggiunto il riferimento al movimento contabile di pagamento e saranno automaticamente archiviati')">
                        @if($invoice->orders->count() > 0)
                            @foreach($invoice->orders as $o)
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="static-label">
                                            {{ $o->printableName() }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach

                            <br>
                        @endif

                        @if($invoice->status != 'payed')
                            @can('supplier.invoices', $invoice->supplier)
                                <x-larastrap::ambutton :label="_i('Modifica Ordini')" :data-modal-url="route('invoices.orders', $invoice->id)" />

                                @if($invoice->orders()->count() != 0)
                                    <x-larastrap::ambutton :label="_i('Verifica Contenuti')" :data-modal-url="route('invoices.products', $invoice->id)" />
                                @endif
                            @endcan
                        @endif
                    </x-larastrap::field>

                    <hr>
                @endif

                @include('invoice.partials.totals_table', [
                    'editable' => true,
                ])
            </div>
            <div class="col-12 col-md-6">
                <x-larastrap::textarea name="notes" :label="_i('Note')" />
                <x-larastrap::select name="status" :label="_i('Stato')" :options="App\Helpers\Status::invoices()" />

                @if($currentuser->can('movements.admin', $currentgas) || $currentuser->can('supplier.movements', $invoice->supplier))
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
                @endcan
            </div>
        </div>

        <hr/>
    </x-larastrap::mform>

    @stack('postponed')
</div>
