<form class="form-horizontal main-form date-editor" method="PUT" action="{{ route('dates.update', $date->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.textarea', ['obj' => $date, 'name' => 'description', 'label' => _i('Contenuto'), 'mandatory' => true])
            @include('commons.datefield', ['obj' => $date, 'name' => 'date', 'label' => _i('Data'), 'mandatory' => true])
        </div>
        <div class="col-md-6">
        </div>
    </div>

    @include('commons.formbuttons')
</form>

@stack('postponed')
