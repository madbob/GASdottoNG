<x-larastrap::mform :obj="$receipt" classes="receipt-editor" method="PUT" :action="route('receipts.update', $receipt->id)">
    <div class="row">
        <div class="col-md-4">
            @include('commons.staticobjfield', ['obj' => $receipt, 'name' => 'user', 'label' => _i('Utente')])
            <x-larastrap::text name="number" :label="_i('Numero')" readonly disabled />
            <x-larastrap::datepicker name="date" :label="_i('Data')" />
            <x-larastrap::price name="total" :label="_i('Totale Imponibile')" readonly disabled />
            <x-larastrap::price name="total_tax" :label="_i('Totale IVA')" readonly disabled />
        </div>
        <div class="col-md-4">
            <x-larastrap::field :label="_i('Prenotazioni Coinvolte')">
                @foreach($receipt->bookings as $booking)
                    <div class="row">
                        <div class="col-md-12">
                            <label class="static-label text-muted">
                                {{ $booking->printableName() }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </x-larastrap::field>
        </div>
        <div class="col-md-4">
            <div class="list-group">
                <x-larastrap::ambutton :label="_i('Scarica o Inoltra')" :data-modal-url="route('receipts.handle', $receipt->id)" />
            </div>
        </div>
    </div>
</x-larastrap::mform>
