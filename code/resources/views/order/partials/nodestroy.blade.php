<x-larastrap::modal>
    <p>
        {{ __('orders.help.unremovable_warning', ['name' => $order->printableName()]) }}
    </p>
    <p>
        {!! __('orders.help.unremovable_instructions', ['link' => $order->getBookingURL()]) !!}
    </p>
    <p>
        {{ __('orders.help.unremovable_notice') }}
    </p>
</x-larastrap::modal>
