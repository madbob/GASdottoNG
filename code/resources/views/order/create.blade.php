<x-larastrap::modal :title="_i('Crea Ordine')" id="createOrder">
    <x-larastrap::iform method="POST" action="orders">
        <input type="hidden" name="void-form" value="1">
        <input type="hidden" name="test-feedback" value="1">
        <input type="hidden" name="close-modal" value="1">
        <input type="hidden" name="update-list" value="order-list">

        <div class="row">
            <div class="col-md-6">
                @include('order.base-edit', ['order' => null])
            </div>
            <div class="col-md-6 border-start border-1">
                @include('dates.calendar')
            </div>
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
