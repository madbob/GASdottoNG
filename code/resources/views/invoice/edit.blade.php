<?php
$rand = rand();
?>

<form class="form-horizontal main-form invoice-editor" method="PUT" action="{{ route('invoices.update', $invoice->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.selectobjfield', [
                'obj' => $invoice,
                'name' => 'supplier_id',
                'label' => _i('Fornitore'),
                'extra_class' => 'select-fetcher',
                'mandatory' => true,
                'objects' => App\Supplier::orderBy('name', 'asc')->withTrashed()->get(),
                'disabled' => ($invoice && $invoice->status == 'payed'),
                'help_text' => view('supplier.invoicedata', ['supplier' => $invoice->supplier])->render(),
                'datafields' => [
                    'id'
                ],
                'extras' => [
                    'data-fetcher-target' => '.help-block',
                    'data-fetcher-url' => route('suppliers.invoicedata', 'XXX')
                ],
            ])

            @include('commons.textfield', [
                'obj' => $invoice,
                'name' => 'number',
                'label' => _i('Numero'),
                'mandatory' => true,
                'disabled' => ($invoice && $invoice->status == 'payed')
            ])

            @include('commons.datefield', [
                'obj' => $invoice,
                'name' => 'date',
                'label' => _i('Data'),
                'mandatory' => true,
                'defaults_now' => true,
                'disabled' => ($invoice && $invoice->status == 'payed')
            ])

            <hr>

            <div class="form-group">
                <label for="orders" class="col-sm-{{ $labelsize }} control-label">{{ _i('Ordini Coinvolti') }}</label>

                <div class="col-sm-{{ $fieldsize }}">
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
                            <button class="btn btn-default" data-toggle="modal" data-target="#orders-invoice-{{ $rand }}">{{ _i('Modifica Ordini') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>

                            @if($invoice->orders()->count() != 0)
                                <button class="btn btn-default async-modal" data-target-url="{{ route('invoices.products', $invoice->id) }}">{{ _i('Verifica Contenuti') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>

            <hr>

            <?php

            $orders_total_taxable = 0;
            $orders_total_tax = 0;
            $orders_total = 0;

            foreach($invoice->orders as $o) {
                $summary = $o->calculateInvoicingSummary();
                $orders_total_taxable += $summary->total_taxable;
                $orders_total_tax += $summary->total_tax;
                $orders_total = $orders_total_taxable + $orders_total_tax;
            }

            ?>

            <div class="simple-sum-container">
                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label"></label>
                    <div class="col-sm-{{ $fieldsize / 2 }}">{{ _i('Fattura') }}</div>
                    <div class="col-sm-{{ $fieldsize / 2 }}">{{ _i('Ordini Coinvolti') }}</div>
                </div>

                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Totale Imponibile') }}</label>

                    <div class="col-sm-{{ $fieldsize / 2 }}">
                        <div class="input-group">
                            <input type="text"
                                class="form-control number trim-2-ddigits simple-sum"
                                name="total"
                                value="{{ printablePrice($invoice->total) }}"
                                required
                                autocomplete="off">

                            <div class="input-group-addon">{{ $currentgas->currency }}</div>
                        </div>
                    </div>

                    <div class="col-sm-{{ $fieldsize / 2 }}">
                        <div class="input-group">
                            <input type="text"
                                class="form-control number trim-2-ddigits"
                                value="{{ printablePrice($orders_total_taxable) }}"
                                disabled
                                autocomplete="off">

                            <div class="input-group-addon">{{ $currentgas->currency }}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Totale IVA') }}</label>

                    <div class="col-sm-{{ $fieldsize / 2 }}">
                        <div class="input-group">
                            <input type="text"
                                class="form-control number trim-2-ddigits simple-sum"
                                name="total_vat"
                                value="{{ printablePrice($invoice->total_vat) }}"
                                required
                                autocomplete="off">

                            <div class="input-group-addon">{{ $currentgas->currency }}</div>
                        </div>
                    </div>

                    <div class="col-sm-{{ $fieldsize / 2 }}">
                        <div class="input-group">
                            <input type="text"
                                class="form-control number trim-2-ddigits"
                                value="{{ printablePrice($orders_total_tax) }}"
                                disabled
                                autocomplete="off">

                            <div class="input-group-addon">{{ $currentgas->currency }}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="total" class="col-sm-{{ $labelsize }} control-label">{{ _i('Totale') }}</label>

                    <div class="col-sm-{{ $fieldsize / 2 }}">
                        <div class="input-group">
                            <input type="text"
                                class="form-control number trim-2-ddigits simple-sum-result"
                                value="{{ printablePrice($invoice->total + $invoice->total_vat) }}"
                                disabled
                                autocomplete="off">

                            <div class="input-group-addon">{{ $currentgas->currency }}</div>
                        </div>
                    </div>

                    <div class="col-sm-{{ $fieldsize / 2 }}">
                        <div class="input-group">
                            <input type="text"
                                class="form-control number trim-2-ddigits"
                                value="{{ printablePrice($orders_total) }}"
                                disabled
                                autocomplete="off">

                            <div class="input-group-addon">{{ $currentgas->currency }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @include('commons.textarea', [
                'obj' => $invoice,
                'name' => 'notes',
                'label' => _i('Note')
            ])

            @include('commons.selectenumfield', [
                'obj' => $invoice,
                'name' => 'status',
                'label' => _i('Stato'),
                'values' => App\Invoice::statuses()
            ])

            <div class="form-group">
                <label for="payment" class="col-sm-{{ $labelsize }} control-label">{{ _i('Pagamento') }}</label>

                <div class="col-sm-{{ $fieldsize }}">
                    @if($invoice->payment)
                        <?php $rand = rand() ?>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="static-label text-muted" data-updatable-name="movement-date-{{ $rand }}" data-updatable-field="name">
                                    {!! $invoice->payment->printableName() !!}
                                </label>

                                <div class="pull-right">
                                    <input type="hidden" name="payment" value="{{ $invoice->payment->id }}" data-updatable-name="movement-id-{{ $rand }}" data-updatable-field="id">
                                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#editMovement-{{ $rand }}">
                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                    </button>
                                </div>

                                @push('postponed')
                                    @include('movement.modal', ['obj' => $invoice->payment, 'dom_id' => $rand])
                                @endpush
                            </div>
                        </div>

                        @foreach($invoice->otherMovements as $om)
                            <?php $rand = rand() ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <label class="static-label text-muted" data-updatable-name="movement-date-{{ $rand }}" data-updatable-field="name">
                                        {!! $om->printableName() !!}
                                    </label>

                                    <div class="pull-right">
                                        <input type="hidden" name="payment" value="{{ $om->id }}" data-updatable-name="movement-id-{{ $rand }}" data-updatable-field="id">
                                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#editMovement-{{ $rand }}">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>

                                    @push('postponed')
                                        @include('movement.modal', ['obj' => $om, 'dom_id' => $rand])
                                    @endpush
                                </div>
                            </div>
                        @endforeach

                        <br>
                    @endif

                    @if($invoice->status != 'payed')
                        <button class="btn btn-default async-modal" data-target-url="{{ route('invoices.movements', $invoice->id) }}">{{ _i('Registra Pagamento') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('commons.formbuttons')
</form>

@can('movements.admin', $currentgas)
    <div class="modal fade" id="orders-invoice-{{ $rand }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-extra-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Modifica Ordini') }}</h4>
                </div>
                <form class="form-horizontal" method="POST" action="{{ url('invoices/wire/review/' . $invoice->id) }}" data-toggle="validator">
                    <div class="modal-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Ordine</th>
                                    <th>Totale Imponibile</th>
                                    <th>Totale IVA</th>
                                    <th>Totale Trasporto</th>
                                    <th>Totale</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->ordersCandidates() as $o)
                                    <?php $summary = $o->calculateInvoicingSummary() ?>
                                    <tr class="orders-in-invoice-candidate">
                                        <td><input type="checkbox" name="order_id[]" value="{{ $o->id }}"></td>
                                        <td>
                                            {{ $o->printableName() }}<br>
                                            <small>{{ $o->printableDates() }}</small>
                                        </td>
                                        <td class="taxable">
                                            @include('commons.staticpricelabel', ['value' => $summary->total_taxable])
                                        </td>
                                        <td class="tax">
                                            @include('commons.staticpricelabel', ['value' => $summary->total_tax])
                                        </td>
                                        <td class="transport">
                                            @include('commons.staticpricelabel', ['value' => $summary->transport])
                                        </td>
                                        <td class="total">
                                            @include('commons.staticpricelabel', ['value' => $summary->total])
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="orders-in-invoice-total">
                                    <td>&nbsp;</td>
                                    <td>Totale Selezionato</td>
                                    <td class="taxable">
                                        @include('commons.staticpricelabel', ['value' => 0])
                                    </td>
                                    <td class="tax">
                                        @include('commons.staticpricelabel', ['value' => 0])
                                    </td>
                                    <td class="transport">
                                        @include('commons.staticpricelabel', ['value' => 0])
                                    </td>
                                    <td class="total">
                                        @include('commons.staticpricelabel', ['value' => 0])
                                    </td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                    <td>{{ _i('Fattura') }}</td>
                                    <td>
                                        @include('commons.staticpricelabel', ['value' => $invoice->total])
                                    </td>
                                    <td>
                                        @include('commons.staticpricelabel', ['value' => $invoice->total_vat])
                                    </td>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        @include('commons.staticpricelabel', ['value' => $invoice->total + $invoice->total_vat])
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                        <button type="submit" class="btn btn-success reloader" data-reload-target="#invoice-list">{{ _i('Salva') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@stack('postponed')
