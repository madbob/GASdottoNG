<x-larastrap::modal id="delete-confirm-modal" size="lg">
    <x-larastrap::iform method="DELETE" :action="route('products.destroy', $product->id)" id="form-delete-confirm-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'tlabel' => 'generic.confirm']]">
        <input type="hidden" name="close-modal" value="1">
        @include('commons.extrafields', [
            'extra' => [
                'post-saved-function' => ['removeTargetListItem'],
            ],
        ])

        @php

        $orders = App\Order::whereHas('products', function($query) use ($product) {
            $query->where('order_product.product_id', $product->id);
        })->whereIn('status', ['open', 'closed'])->get();

        @endphp

        @if($orders->count() > 0)
            {{ __('texts.products.help.notice_removing_product_in_orders') }}

            <hr>

            @foreach($orders as $order)
                <x-larastrap::radiolist :name="sprintf('order_%s', inlineId($order))" :label="$order->printableName()" :options="[
                    'keep' => __('texts.products.removing.keep'),
                    'leave' => __('texts.products.removing.leave')
                ]" value="keep" />
            @endforeach
        @else
            {{ __('texts.products.remove_confirm', ['name' => $product->printableName()]) }}
        @endif
    </x-larastrap::iform>
</x-larastrap::modal>
