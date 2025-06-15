<?php $rand = rand() ?>

<div>
    <x-larastrap::mform :obj="$invoice" classes="invoice-editor" method="PUT" :action="route('invoices.update', $invoice->id)">
        <div class="row">
            <div class="col-12 col-md-6">
                @include('commons.staticobjfield', ['obj' => $invoice, 'name' => 'supplier', 'label' => __('texts.orders.supplier')])
                <x-larastrap::text name="number" tlabel="generic.number" disabled readonly />
                <x-larastrap::datepicker name="date" tlabel="generic.date" disabled readonly />

                <x-larastrap::field tlabel="generic.attachment">
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
                                    <x-larastrap::check name="delete_attachment[]" tlabel="generic.remove" :value="$attachment->id" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-larastrap::field>

                <hr>

                @if($invoice->orders->count() > 0)
                    <x-larastrap::field tlabel="invoices.orders">
                        @foreach($invoice->orders as $o)
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="static-label">
                                        {{ $o->printableName() }}
                                    </label>
                                </div>
                            </div>
                        @endforeach

                        @if($invoice->status != 'payed')
                            <x-larastrap::ambutton tlabel="invoices.verify" :data-modal-url="route('invoices.products', $invoice->id)" />
                        @endif
                    </x-larastrap::field>

                    <hr>
                @endif

                @include('invoice.partials.totals_table', [
                    'editable' => false,
                ])
            </div>
            <div class="col-12 col-md-6">
                <x-larastrap::textarea name="notes" tlabel="generic.notes" />
                <x-larastrap::select name="status" tlabel="generic.status" :options="App\Helpers\Status::invoices()" />

                @if($currentuser->can('movements.admin', $currentgas) || $currentuser->can('supplier.movements', $invoice->supplier))
                    <x-larastrap::field tlabel="generic.payment">
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
                            <x-larastrap::ambutton tlabel="invoices.payment" :attributes="['data-modal-url' => route('invoices.movements', $invoice->id)]" />
                        @endif
                    </x-larastrap::field>
                @endcan
            </div>
        </div>

        <hr/>
    </x-larastrap::mform>

    @stack('postponed')
</div>
