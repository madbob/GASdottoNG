<x-larastrap::modal>
    <x-larastrap::tabs>
        @foreach($invoice->orders as $index => $order)
            <x-larastrap::tabpane :label="sprintf('%s<br><small>%s</small>', $order->printableName(), __('texts.invoices.shipping_of', ['date' => printableDate($order->shipping)]))" :active="$index == 0" icon="bi-file-check">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" width="20%">{{ __('texts.products.name') }}</th>
                            <th scope="col" width="15%">{{ __('texts.products.vat_rate') }}</th>
                            <th scope="col" width="15%">{{ __('texts.orders.quantities.shipped') }}</th>
                            <th scope="col" width="15%">{{ __('texts.orders.totals.taxable') }}</th>
                            <th scope="col" width="15%">{{ __('texts.orders.totals.vat') }}</th>
                            <th scope="col" width="20%">{{ __('texts.orders.totals.total') }}</th>
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
                            <th scope="col">&nbsp;</th>
                            <th scope="col">&nbsp;</th>
                            <th scope="col">&nbsp;</th>
                            <th scope="col">{{ printablePriceCurrency($summaries[$order->id]->total_taxable) }}</th>
                            <th scope="col">{{ printablePriceCurrency($summaries[$order->id]->total_tax) }}</th>
                            <th scope="col">{{ printablePriceCurrency($summaries[$order->id]->total) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </x-larastrap::tabpane>
        @endforeach

        @if($invoice->orders->count() > 1)
            <x-larastrap::tabpane tlabel="orders.aggregate" icon="bi-files">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" width="20%">{{ __('texts.products.name') }}</th>
                            <th scope="col" width="15%">{{ __('texts.products.vat_rate') }}</th>
                            <th scope="col" width="15%">{{ __('texts.orders.quantities.shipped') }}</th>
                            <th scope="col" width="15%">{{ __('texts.orders.totals.taxable') }}</th>
                            <th scope="col" width="15%">{{ __('texts.orders.totals.vat') }}</th>
                            <th scope="col" width="20%">{{ __('texts.orders.totals.total') }}</th>
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
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="fw-bold">{{ printablePriceCurrency($global_summary->total_taxable) }}</td>
                            <td class="fw-bold">{{ printablePriceCurrency($global_summary->total_tax) }}</td>
                            <td class="fw-bold">{{ printablePriceCurrency($global_summary->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </x-larastrap::tabpane>
        @endif
    </x-larastrap::tabs>
</x-larastrap::modal>
