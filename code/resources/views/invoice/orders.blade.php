<x-larastrap::modal>
    <?php $orders = $invoice->ordersCandidates() ?>

    @if($orders->isEmpty())
        <div class="alert alert-danger">
            {{ __('invoices.help.no_orders') }}
        </div>
    @else
        <x-larastrap::iform method="POST" :action="url('invoices/wire/review/' . $invoice->id)">
            <input type="hidden" name="close-modal" value="1" />
            <input type="hidden" name="reload-loadable" value="#invoice-list" />
            <p>{{ __('invoices.help.filtered_orders') }}</p>

            <hr>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" width="10%"></th>
                        <th scope="col" width="30%">{{ __('orders.name') }}</th>
                        <th scope="col" width="20%">{{ __('orders.totals.taxable') }}</th>
                        <th scope="col" width="20%">{{ __('orders.totals.vat') }}</th>
                        <th scope="col" width="20%">{{ __('orders.totals.total') }}</th>
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
                    <td>{{ __('orders.totals.selected') }}</td>
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
                    <td>{{ __('generic.invoice') }}</td>
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
