<x-larastrap::modal :title="_i('Verifica Contenuti')">
    <x-larastrap::tabs>
        @foreach($invoice->orders as $index => $order)
            <x-larastrap::tabpane :label="sprintf('%s<br><small>%s</small>', $order->printableName(), _i('Consegna: %s', printableDate($order->shipping)))" :active="$index == 0" icon="bi-file-check">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="20%">{{ _i('Prodotto') }}</th>
                            <th width="15%">{{ _i('Aliquota IVA') }}</th>
                            <th width="15%">{{ _i('Quantità Consegnata') }}</th>
                            <th width="15%">{{ _i('Totale Imponibile') }}</th>
                            <th width="15%">{{ _i('Totale IVA') }}</th>
                            <th width="20%">{{ _i('Totale') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->products as $product)
                            @if($summaries[$order->id]->products[$product->id]['total'] > 0)
                                <tr>
                                    <td>{{ $product->printableName() }}</td>
                                    <td>{{ $product->vat_rate ? $product->vat_rate->printableName() : '' }}</td>
                                    <td>{{ printableQuantity($summaries[$order->id]->products[$product->id]['delivered'], $product->measure->discrete) }} {{ $product['measure']->name }}</td>
                                    <td>{{ printablePriceCurrency($summaries[$order->id]->products[$product->id]['total']) }}</td>
                                    <td>{{ printablePriceCurrency($summaries[$order->id]->products[$product->id]['total_vat']) }}</td>
                                    <td>{{ printablePriceCurrency($summaries[$order->id]->products[$product->id]['total'] + $summaries[$order->id]->products[$product->id]['total_vat']) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>{{ printablePriceCurrency($summaries[$order->id]->total_taxable) }}</th>
                            <th>{{ printablePriceCurrency($summaries[$order->id]->total_tax) }}</th>
                            <th>{{ printablePriceCurrency($summaries[$order->id]->total) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </x-larastrap::tabpane>
        @endforeach

        @if($invoice->orders->count() > 1)
            <x-larastrap::tabpane :label="_i('Aggregato')" icon="bi-files">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="20%">{{ _i('Prodotto') }}</th>
                            <th width="15%">{{ _i('Aliquota IVA') }}</th>
                            <th width="15%">{{ _i('Quantità Consegnata') }}</th>
                            <th width="15%">{{ _i('Totale Imponibile') }}</th>
                            <th width="15%">{{ _i('Totale IVA') }}</th>
                            <th width="20%">{{ _i('Totale') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($global_summary->products as $product)
                            @if($product['total'] > 0)
                                <tr>
                                    <td>{{ $product['name'] }}</td>
                                    <td>{{ $product['vat_rate'] }}</td>
                                    <td>{{ printableQuantity($product['delivered'], $product['measure']->discrete) }} {{ $product['measure']->name }}</td>
                                    <td>{{ printablePriceCurrency($product['total']) }}</td>
                                    <td>{{ printablePriceCurrency($product['total_vat']) }}</td>
                                    <td>{{ printablePriceCurrency($product['total'] + $product['total_vat']) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>{{ printablePriceCurrency($global_summary->total_taxable) }}</th>
                            <th>{{ printablePriceCurrency($global_summary->total_tax) }}</th>
                            <th>{{ printablePriceCurrency($global_summary->total) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </x-larastrap::tabpane>
        @endif
    </x-larastrap::tabs>
</x-larastrap::modal>
