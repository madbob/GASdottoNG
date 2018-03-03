<div class="modal fade" id="products-invoice-{{ $invoice->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ _i('Modifica Ordini') }}</h4>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    @foreach($invoice->orders as $index => $order)
                        <li role="presentation" class="{{ $index == 0 ? 'active' : '' }}"><a href="#products-{{ $invoice->id }}-{{ $index }}" role="tab" data-toggle="tab">{{ $order->printableName() }}</a></li>
                    @endforeach

                    @if($invoice->orders->count() > 1)
                        <li role="presentation"><a href="#products-{{ $invoice->id }}-all" role="tab" data-toggle="tab">{{ _i('Aggregato') }}</a></li>
                    @endif
                </ul>

                <div class="tab-content">
                    @foreach($invoice->orders as $index => $order)
                        <div role="tabpanel" class="tab-pane {{ $index == 0 ? 'active' : '' }}" id="products-{{ $invoice->id }}-{{ $index }}">
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
                                    @foreach($order->products as $product)
                                        <tr>
                                            <td>{{ $product->printableName() }}</td>
                                            <td>{{ $product->vat_rate ? $product->vat_rate->printableName() : '' }}</td>
                                            <td>{{ printablePrice($summaries[$order->id]->products[$product->id]['total']) }} {{ $currentcurrency }}</td>
                                            <td>{{ printablePrice($summaries[$order->id]->products[$product->id]['total_vat']) }} {{ $currentcurrency }}</td>
                                            <td>{{ printablePrice($summaries[$order->id]->products[$product->id]['total'] + $summaries[$order->id]->products[$product->id]['total_vat']) }} {{ $currentcurrency }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    @if($invoice->orders->count() > 1)
                        <div role="tabpanel" class="tab-pane {{ $index == 0 ? 'active' : '' }}" id="products-{{ $invoice->id }}-all">
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
                                    @foreach($global_summary as $product)
                                        <tr>
                                            <td>{{ $product['name'] }}</td>
                                            <td>{{ $product['vat_rate'] }}</td>
                                            <td>{{ printablePrice($product['total']) }} {{ $currentcurrency }}</td>
                                            <td>{{ printablePrice($product['total_vat']) }} {{ $currentcurrency }}</td>
                                            <td>{{ printablePrice($product['total'] + $product['total_vat']) }} {{ $currentcurrency }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Chiudi') }}</button>
            </div>
        </div>
    </div>
</div>
