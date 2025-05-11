<x-larastrap::mform :obj="$receipt" classes="receipt-editor" nosave nodelete :other_buttons="[['label' => _i('Scarica'), 'classes' => ['link-button'], 'attributes' => ['data-link' => route('receipts.download', $receipt->id)]]]">
    <div class="row">
        <div class="col-md-6">
            @include('commons.staticobjfield', ['obj' => $receipt, 'name' => 'user', 'label' => _i('Utente')])
            <x-larastrap::text name="number" tlabel="generic.number" readonly disabled />
            <x-larastrap::datepicker name="date" :label="_i('Data')" readonly disabled />

            @if($receipt->total_tax)
                <x-larastrap::price name="total" :label="_i('Totale Imponibile')" readonly disabled />
                <x-larastrap::price name="total_tax" :label="_i('Totale IVA')" readonly disabled />
                <x-larastrap::price name="total_other" :label="_i('Altro')" readonly disabled />
            @else
                <x-larastrap::price name="total" :label="_i('Totale')" readonly disabled />
            @endif
        </div>
        <div class="col-md-6">
            <x-larastrap::field :label="_i('Prenotazioni Coinvolte')">
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
