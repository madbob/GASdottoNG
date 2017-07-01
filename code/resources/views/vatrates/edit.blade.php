<form class="form-horizontal main-form vatrate-editor" method="PUT" action="{{ url('/vatrates/' . $vatrate->id) }}">
    <div class="row">
        <div class="col-md-12">
            @include('vatrates.base-edit', ['vatrate' => $vatrate])
        </div>
    </div>

    @include('commons.formbuttons')
</form>

@stack('postponed')
