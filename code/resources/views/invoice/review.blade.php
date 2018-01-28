<?php $current_currency = $currentgas->currency ?>

<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('invoices/wire/movements/' . $invoice->id) }}" data-toggle="validator">
        <div class="modal-body">
            @foreach($orders as $order)
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
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
            <button type="submit" class="btn btn-success">{{ _i('Avanti') }}</button>
        </div>
    </form>
</div>
