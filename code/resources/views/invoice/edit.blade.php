<form class="form-horizontal main-form invoice-editor" method="PUT" action="{{ url('/invoices/' . $invoice->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('invoice.base-edit', ['invoice' => $invoice])
        </div>
        <div class="col-md-6">
            @include('commons.selectenumfield', [
                'obj' => $invoice,
                'name' => 'status',
                'label' => _i('Stato'),
                'values' => App\Invoice::statuses()
            ])

            @if($invoice->payment)
                @include('commons.movementfield', [
                    'obj' => $invoice->payment,
                    'name' => 'payment_id',
                    'label' => _i('Pagamento'),
                    'default' => null,
                    'to_modal' => [
                        'amount_editable' => $currentuser->can('movements.admin', $currentgas)
                    ]
                ])
            @else
                @include('commons.staticmovementfield', [
                    'obj' => null,
                    'name' => 'payment_id',
                    'label' => _i('Pagamento'),
                ])
            @endif

            <div class="form-group">
                <label for="orders" class="col-sm-{{ $labelsize }} control-label">{{ _i('Ordini Coinvolti') }}</label>

                <div class="col-sm-{{ $fieldsize }}">
                    @foreach($invoice->orders as $o)
                        <p>{{ $o->printableName() }}</p>
                    @endforeach

                    @if($invoice->status != 'payed')
                        @can('movements.admin', $currentgas)
                            <button class="btn btn-default" data-toggle="modal" data-target="#orders-invoice-{{ $invoice->id }}">{{ _i('Modifica Ordini') }}</button>

                            @if($invoice->orders()->count() != 0)
                                <button class="btn btn-default" data-toggle="modal" data-target="#products-invoice-{{ $invoice->id }}">{{ _i('Verifica Contenuti') }}</button>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('commons.formbuttons')
</form>

@can('movements.admin', $currentgas)
    <?php $current_currency = $currentgas->currency ?>

    <div class="modal fade" id="orders-invoice-{{ $invoice->id }}" tabindex="-1" role="dialog">
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

    @if($invoice->orders()->count() != 0)
        <div class="modal fade" id="products-invoice-{{ $invoice->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-extra-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{ _i('Modifica Ordini') }}</h4>
                    </div>

                    <form class="form-horizontal" method="POST" action="{{ url('invoices/wire/movements/' . $invoice->id) }}" data-toggle="validator">
                        <div class="modal-body">
                            @foreach($invoice->orders as $order)
                                <input type="hidden" name="order_id[]" value="{{ $order->id }}">

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th width="20%">{{ _i('Prodotto') }}</th>
                                            <th width="10%">{{ _i('Aliquota IVA') }}</th>
                                            <th width="10%">{{ _i('Totale Imponibile') }}</th>
                                            <th width="10%">{{ _i('Totale IVA') }}</th>
                                            <th width="10%">{{ _i('Totale') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $summary = $order->calculateInvoicingSummary() ?>
                                        @foreach($order->products as $product)
                                            <tr>
                                                <td>{{ $product->printableName() }}</td>
                                                <td>{{ $product->vat_rate ? $product->vat_rate->printableName() : '' }}</td>
                                                <td>{{ printablePrice($summary->products[$product->id]['total']) }} {{ $current_currency }}</td>
                                                <td>{{ printablePrice($summary->products[$product->id]['total_vat']) }} {{ $current_currency }}</td>
                                                <td>{{ printablePrice($summary->products[$product->id]['total'] + $summary->products[$product->id]['total_vat']) }} {{ $current_currency }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Chiudi') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endcan

@stack('postponed')
