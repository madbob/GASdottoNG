<x-larastrap::mform :obj="$supplier" nosave nodelete>
    <x-larastrap::hidden name="id" />

    @if($selfview == false)
        <div class="alert alert-info text-center mb-2">
            @php
            $current_orders = $supplier->orders()->accessibleBooking()->where('status', 'open')->get();
            @endphp

            @if($current_orders->isEmpty())
                {{ _i('Attualmente non ci sono ordini aperti per questo fornitore.') }}
            @else
                {{ _i('Ci sono ordini aperti per questo fornitore:') }}
                @foreach($current_orders as $current_order)
                    <x-larastrap::link class="btn btn-info" :href="$current_order->getBookingURL()" :label="$current_order->printableName()" />
                @endforeach
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <x-larastrap::text name="name" :label="_i('Nome')" readonly disabled />
            <x-larastrap::text name="business_name" :label="_i('Ragione Sociale')" readonly disabled />

            <x-larastrap::field :label="_i('Descrizione')">
                <p class="form-control-plaintext">
                    {!! prettyFormatHtmlText($supplier->description) !!}
                </p>
            </x-larastrap::field>

            <x-larastrap::text name="taxcode" :label="_i('Codice Fiscale')" readonly disabled />
            <x-larastrap::text name="vat" :label="_i('Partita IVA')" readonly disabled />
            @include('commons.staticcontactswidget', ['obj' => $supplier])
        </div>
        <div class="col-md-6">
            @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => $editable])
        </div>
    </div>
</x-larastrap::mform>
