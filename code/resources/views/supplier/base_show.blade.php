<x-larastrap::mform :obj="$supplier" nosave nodelete>
    <x-larastrap::hidden name="id" />

    @if($selfview == false)
        <div class="alert alert-info text-center mb-2">
            @php
            $current_orders = $supplier->orders()->accessibleBooking()->where('status', 'open')->get();
            @endphp

            @if($current_orders->isEmpty())
                {{ __('texts.orders.help.supplier_no_orders') }}
            @else
                {{ __('texts.orders.help.supplier_has_orders') }}
                @foreach($current_orders as $current_order)
                    <x-larastrap::link class="btn btn-info" :href="$current_order->getBookingURL()" :label="$current_order->printableName()" />
                @endforeach
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <x-larastrap::text name="name" tlabel="generic.name" readonly disabled />
            <x-larastrap::text name="business_name" tlabel="supplier.legal_name" readonly disabled />

            <x-larastrap::field tlabel="generic.description">
                <p class="form-control-plaintext">
                    {!! prettyFormatHtmlText($supplier->description) !!}
                </p>
            </x-larastrap::field>

            <x-larastrap::text name="taxcode" tlabel="user.taxcode" readonly disabled />
            <x-larastrap::text name="vat" tlabel="supplier.vat" readonly disabled />
            @include('commons.staticcontactswidget', ['obj' => $supplier])
        </div>
        <div class="col-md-6">
            @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => $editable])
        </div>
    </div>
</x-larastrap::mform>
