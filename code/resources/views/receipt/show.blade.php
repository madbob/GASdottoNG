<x-larastrap::mform :obj="$receipt" classes="receipt-editor" nosave nodelete :other_buttons="[['label' => __('texts.generic.download'), 'classes' => ['link-button'], 'attributes' => ['data-link' => route('receipts.download', $receipt->id)]]]">
    <div class="row">
        <div class="col-md-6">
            @include('commons.staticobjfield', ['obj' => $receipt, 'name' => 'user', 'label' => __('texts.user.name')])
            <x-larastrap::text name="number" tlabel="generic.number" readonly disabled />
            <x-larastrap::datepicker name="date" tlabel="generic.date" readonly disabled />

            @if($receipt->total_tax)
                <x-larastrap::price name="total" tlabel="orders.totals.taxable" readonly disabled />
                <x-larastrap::price name="total_tax" tlabel="orders.totals.vat" readonly disabled />
                <x-larastrap::price name="total_other" tlabel="generic.more" readonly disabled />
            @else
                <x-larastrap::price name="total" tlabel="orders.totals.total" readonly disabled />
            @endif
        </div>
        <div class="col-md-6">
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
    </div>
</x-larastrap::mform>
