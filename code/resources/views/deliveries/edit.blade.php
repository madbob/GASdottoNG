<x-larastrap::mform :obj="$delivery" classes="form-horizontal main-form delivery-editor" method="PUT" :action="route('deliveries.update', $delivery->id)">
    <div class="row">
        <div class="col-md-12">
            @include('deliveries.base-edit', ['delivery' => $delivery])
            @include('commons.modifications', ['obj' => $delivery])
        </div>
    </div>
</x-larastrap::form>

@stack('postponed')
