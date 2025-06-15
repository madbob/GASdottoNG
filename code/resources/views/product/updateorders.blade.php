<x-larastrap::modal>
    <x-larastrap::iform method="POST" :action="route('products.updateprices', $product->id)">
        <input type="hidden" name="close-modal" value="1" class="skip-on-submit">

        <p>
            {{ __('texts.products.help.pending_orders_change_price') }}
        </p>
        <p>
            {{ __('texts.products.help.pending_orders_change_price_second') }}
        </p>

        <p>
            @foreach($orders as $order)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="orders[]" value="{{ $order->id }}" id="activate-order-{{ sanitizeId($order->id) }}">
                    <label class="form-check-label" for="activate-order-{{ sanitizeId($order->id) }}">
                        {{ $order->printableName() }}
                    </label>
                </div>
            @endforeach
        </p>
    </x-larastrap::iform>
</x-larastrap::modal>
