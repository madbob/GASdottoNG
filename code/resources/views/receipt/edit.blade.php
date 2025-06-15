<x-larastrap::mform :obj="$receipt" classes="receipt-editor" method="PUT" :action="route('receipts.update', $receipt->id)">
    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $receipt, 'name' => 'user', 'label' => __('texts.user.name')])
            <x-larastrap::text name="number" tlabel="generic.number" readonly disabled />
            <x-larastrap::datepicker name="date" tlabel="generic.date" />
            <x-larastrap::price name="total" tlabel="orders.totals.taxable" readonly disabled />
            <x-larastrap::price name="total_tax" tlabel="orders.totals.vat" readonly disabled />
            <x-larastrap::price name="total_other" tlabel="generic.more" readonly disabled />
        </div>
        <div class="col-md-4">
            <x-larastrap::field tlabel="generic.menu.bookings">
                @foreach($receipt->bookings as $booking)
                    <div class="row">
                        <div class="col-md-12">
                            <label class="static-label">
                                {{ $booking->printableName() }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </x-larastrap::field>
        </div>
        <div class="col-md-4">
            <div class="list-group">
                <x-larastrap::ambutton tlabel="invoices.get_or_send" :data-modal-url="route('receipts.handle', $receipt->id)" />
            </div>
        </div>
    </div>
</x-larastrap::mform>
