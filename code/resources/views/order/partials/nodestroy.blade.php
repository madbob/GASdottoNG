<x-larastrap::modal>
    <p>
        {{ __('texts.orders.help.unremovable_warning', ['name' => $order->printableName()]) }}
    </p>
    <p>
        {!! __('texts.orders.help.unremovable_instructions', ['link' => $order->getBookingURL()]) !!}
    </p>
    <p>
        {{ __('texts.orders.help.unremovable_notice') }}
    </p>
</x-larastrap::modal>
