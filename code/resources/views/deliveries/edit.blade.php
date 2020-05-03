<form class="form-horizontal main-form delivery-editor" method="PUT" action="{{ route('deliveries.update', $delivery->id) }}">
    <div class="row">
        <div class="col-md-12">
            @include('deliveries.base-edit', ['delivery' => $delivery])
            @include('commons.modifications', ['obj' => $delivery])
        </div>
    </div>

    @include('commons.formbuttons')
</form>

@stack('postponed')
